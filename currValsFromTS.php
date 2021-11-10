<?php

require_once ('constants.php');
require_once ('configParameters.php');		//	Параметры подключения к БД и связи с Термосервером
require_once ('dbDebugTables.php');			//	Создание, удаление и изменение отладочных таблиц в БД
require_once ('dbDDL.php');					//	Создание и инициализация всех таблиц в БД
require_once ('dbCurrVals.php');			//	Запись текущих измеренных значений в БД

$configOK=true;
$errors=array();
$arrayOfTemperatures=array(); $arrayOfTempSpeeds=array(); $arrayOfLevels=array(); $serverDate="";

//	ПРОЦЕДУРА ПРОВЕРКИ

//	Проверка существования таблицы sensors в базе данных проекта
if( isTableExistAndNotEmpty($dbh,"zernoib.sensors") ){
	goto tableSensorsOK;
}

//	Обязательно необходимо преобразовать файлы из кодировки WINDOWS-1251 в UTF-8
$termoServerINI  =	@parse_ini_string(replaceForbiddenChars(mb_convert_encoding(file_get_contents('settings/TermoServer.ini'), "UTF-8", "WINDOWS-1251")), true);
$termoClientINI  =	@parse_ini_string(replaceForbiddenChars(mb_convert_encoding(file_get_contents('settings/TermoClient.ini'), "UTF-8", "WINDOWS-1251")), true);

//	Таблицы не существует, или она пустая
//	Выполняем проверку ini-файлов
if( count($termoServerINI)==0 ){
	$configOK=false;
	array_push($errors, "NoTermoServer.ini");				//	Файла TermoServer.ini нет в папке settings
}

if( count($termoClientINI)==0 ){
	$configOK=false;
	array_push($errors, "NoTermoClient.ini");				//	Файла TermoClient.ini нет в папке settings
}

if(count($errors)>0){
	goto exit_from_script;
}

if(!isIniFileTermoServerOK($termoServerINI)){
	$configOK=false;
	array_push($errors, "DamagedTermoServer.ini");			//	Файл TermoServer.ini поврежден
}

if(!isIniFileTermoClientOK($termoClientINI)){
	$configOK=false;
	array_push($array, "DamagedTermoClient.ini");			//	Файл TermoClient.ini поврежден
}

if(count($errors)>0){
	goto exit_from_script;
}

if(!areIniFilesConsistent($termoServerINI,$termoClientINI)){
	$configOK=false;
	array_push($errors, "IniFilesInconsistent");				//	Файлы TermoServer.ini и TermoClient.ini не соответствуют друг другу
}

if(count($errors)>0){
	goto exit_from_script;
}

//	Если прошли проверку
//	Создаем все необходимые таблицы в Базе Данных исходя из их содержимого
if($configOK){
	projectUpdate($dbh, $termoClientINI, $termoServerINI);
}

tableSensorsOK:		//	Таблицы существуют и заполнены данными

//	Заполнение массивов $arrayOfTemperatures; $arrayOfTempSpeed; $arrayOfLevels и переменной $serverDate
//	РАБОЧИЙ РЕЖИМ:	значения шлет Термосервер
//	РЕЖИМ ОТЛАДКИ:	значения вычитываются из БД (запись значений производится из отладочной страницы визуализации)
if( ! $simulation_mode) {

	$inputValsArr		 = getFromTS_inputArray ( getFromTS_inputString($IPAddr, $port) );	//	[температуры][скорости][уровни][дата]
	if($inputValsArr==false){
		array_push($errors, "TermoServerIsOff");
		goto exit_from_script;
	}
	$arrayOfTemperatures = getFromTS_arrayOfValues3d($inputValsArr[0]);
	$arrayOfTempSpeeds   = getFromTS_arrayOfValues3d($inputValsArr[1]);
	$arrayOfLevels       = getFromTS_grainLevels($inputValsArr[2]);
	$serverDate			 = $inputValsArr[3];

} else {

	//	Если включен режим отладки
	$arrayOfTemperatures = db_debug_update_temperatures($dbh);
	$arrayOfTempSpeeds   = db_debug_update_temperatureSpeeds($dbh);
	$arrayOfLevels       = db_debug_update_grainLevels($dbh);
	date_default_timezone_set('Europe/Kiev');
	$date = date('d.m.Y H:i:s', time());
	$serverDate			 = $date;

}

//	Данные вычитаны
//	Необходимо проверить их соотетствие таблицам в Базе Данных
if(!checkReadValsToDBSensors($dbh, $arrayOfTemperatures)){
	$configOK = false;
	if(!$simulation_mode){
		array_push($errors, "ProjectIsOutOfDate");						//	Значения, вычитанные из Термосервера не соответствуют текущим настройкам. Обновите проект
	} else {
		ddl_debug_drop_all($dbh);										//	Выполняем инициализацию отладочных таблиц исходя из текущего состояния таблицы sensors
		ddl_debug_create_Silo($dbh);
		ddl_debug_create_Sensors($dbh);
	}
}

//	Данные в порядке. Теперь необходимо занести новые показания в БД и выполнить проверку на алармы
if($configOK){
	db_update_grainLevels($dbh, $arrayOfLevels);
	db_update_temperaturesAndSpeeds($dbh, $arrayOfTemperatures, $arrayOfTempSpeeds, $serverDate, $logFile);
}

exit_from_script:

//	Функция проверки таблицы sensors. Если она существует и не пуста => true
function isTableExistAndNotEmpty($dbh, $tableName){

	$sql = "SELECT * FROM $tableName;";
	$sth = $dbh->query($sql);

	if($sth==false){
		return false;
	}
	$rows = $sth->fetchAll();
	if(count($rows)==0){
		return false;
	}

	return true;
}
//	Функция для замены символов "(", ")", "off", ";". Необходима для корректной работы функции parse_ini_string()
function replaceForbiddenChars($str){
	$str = str_replace("(", "_", $str);	$str = str_replace(")", "_", $str);	$str = str_replace("off", "off_", $str); $str = str_replace(";", "%", $str);
	return $str;
}
//	Проверка файла TermoServer.ini на наличие всех необходимых ключей
function isIniFileTermoServerOK($termoServerINI){
	$siloKeyCount=0;
	foreach ($termoServerINI as $key => $value) {
		if(preg_match('/Silos[0-9]+/',$key)){
			if( !isset($termoServerINI[$key]['SilosName'])		||			//	имя силоса
				!isset($termoServerINI[$key]['PodvCount'])		||			//	количество подвесок
				!isset($termoServerINI[$key]['SensorsStr'])		||			//	строка с количеством датчиков по подвескам
				!isset($termoServerINI[$key]['DeviceAddress'])	||			//	адрес Блока Сбора информации
				!isset($termoServerINI[$key]['FirstPodvShift'])	||			//	смещение первой подвески в ПЗУ Блока Сбора
				!isset($termoServerINI[$key]['off_']) ){					//	силос отключен на сервере
				return false;
			}
			$siloKeyCount++;
		}
	}
	if($siloKeyCount==0){		//	Если ключей Silos[0-9]+ нет вообще
		return false;
	}
	return true;
}
//	Проверка файла TermoClient.ini на наличие всех необходимых ключей
function isIniFileTermoClientOK($termoClientINI){
	$siloKeyCount=0;
	foreach ($termoClientINI as $key => $value) {
		if(preg_match('/Silos[0-9]+/',$key)){
			if( !isset($termoClientINI[$key]['Left'])	||					//	столбец
				!isset($termoClientINI[$key]['Top'])	||					//	строка
				!isset($termoClientINI[$key]['Size'])	||					//	размер
				!isset($termoClientINI[$key]['sType'])	||					//	тип (квадратный, круглый)
				!isset($termoClientINI[$key]['Group'])	){					//	номер группы силосов
				return false;
			}
			$siloKeyCount++;
		}
	}
	if($siloKeyCount==0){		//	Если ключей Silos[0-9]+ нет вообще
		return false;
	}
	return true;
}
//	Проверка ini-файлов на соответствие друг другу
function areIniFilesConsistent($termoServerINI,$termoClientINI){
	foreach ($termoServerINI as $key => $value) {
		if(preg_match('/Silos[0-9]+/',$key)){
			if(!isset($termoClientINI[$key])){
				return false;
			}
		}
	}
	foreach ($termoClientINI as $key => $value) {
		if(preg_match('/Silos[0-9]+/',$key)){
			if(!isset($termoServerINI[$key])){
				return false;
			}
		}
	}
	return true;
}

//	Обновление проекта. Инициализация всех таблиц в Базе Данных
function projectUpdate($dbh, $termoClientINI, $termoServerINI){

	ddl_execute_statement($dbh, sql_statement_drop_all_tables);

	ddl_execute_statement($dbh, sql_statement_create_users);
	ddl_execute_statement($dbh, sql_staement_create_errors);
	ddl_execute_statement($dbh, sql_statement_create_dates);
	ddl_execute_statement($dbh, sql_statement_create_prodtypes);
	ddl_execute_statement($dbh, sql_statement_create_silosesgroups);
	ddl_execute_statement($dbh, sql_statement_create_prodtypesbysilo);
	ddl_execute_statement($dbh, sql_statement_create_sensors);
	ddl_execute_statement($dbh, sql_statement_create_measurements);

	date_default_timezone_set('Europe/Kiev'); $date = date('d.m.Y H:i:s', time()); $serverDate = $date;

	ddl_init_Users($dbh);
	ddl_init_Errors($dbh);
	ddl_init_Dates($dbh, $serverDate);
	ddl_init_Prodtypes($dbh);
	ddl_init_SilosesGroups($dbh, $termoClientINI);
	ddl_init_Prodtypesbysilo($dbh, $termoClientINI, $termoServerINI);
	ddl_init_Sensors($dbh, $termoClientINI, $termoServerINI, $serverDate);

	ddl_debug_drop_all($dbh);
	ddl_debug_create_Silo($dbh);
	ddl_debug_create_Sensors($dbh);

	setcookie("popupProjectWasUpdated", "OK", time()+60);

	return;
	
}

//	Проверка содержимого таблицы sensors конфигурации файла TermoServer.ini
//$sortedIniArr = getTermoServerIniSortedBySiloName($termoClientINI,$termoServerINI);
function areIniFilesConsistentToDB($dbh, $tableSensors, $sortedIniArr){

	$sql = "SELECT s.silo_id, pbs.silo_name, podv_id, count(sensor_num)
				FROM $tableSensors AS s inner JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
				GROUP BY silo_id, podv_id
				ORDER BY CAST(pbs.silo_name AS unsigned), podv_id;";
	$sth = $dbh->query($sql);

	if($sth==false){
		return false;
	}
	$rows = $sth->fetchAll();

	$sensorsNumberCounter = 0;
	foreach($sortedIniArr as $currIniString){
		$sensorsArr = preg_split('/,/',$currIniString['SensorsStr'],-1,PREG_SPLIT_NO_EMPTY);
		foreach($sensorsArr as $currSensorsNumber){
			if(!isset($rows[$sensorsNumberCounter])){
				return false;
			}
			if( ($rows[$sensorsNumberCounter]['silo_name'] != $currIniString['SilosName']) ||
				($rows[$sensorsNumberCounter]['count(sensor_num)'] != $currSensorsNumber) ){
				return false;
			}
			$sensorsNumberCounter++;
		}
	}

	if( $sensorsNumberCounter != count($rows) ){
		return false;
	}

	return true;
}

//	Функции для работы с программой TermoServer -----------------------------------------------------------------------------------------------------
//	Функция для получения строки с текущими значениями
//	В случае, если термосервер не запущен, происходит запись в глобальную переменную $errors
function getFromTS_inputString($IPAddr, $port){
	//http://docs.php.net/fsockopen
	$fp = @fsockopen($IPAddr, $port, $errno, $errstr, 30);

	if($fp == false){
		return false;
	}

	if (!$fp) {
		echo "$errstr ($errno)<br />\n";    
	} else {
		$out = "GET / HTTP/1.1\r\n"; $out .= "Host: $IPAddr\r\n"; $out .= "Connection: Close\r\n\r\n";
		fwrite($fp, $out);
		//	читаем данные по http в строку
		$inputStr = "";
		while(true){
			$inputStr .= fgets($fp,2);
			if(strlen($inputStr)<4){
				continue;
			}
			if(substr($inputStr,-4,4)==="####"){					//	Если #### - конец данных
				break;
			}
		}
		fclose($fp);
	}
	return $inputStr;
}
//	Функция для преобразования строки с текущими значениями в массив
function getFromTS_inputArray($inputString){
	//Получаем массив строк [массив_текущих температур, массив_скоростей_изменения_температура, массив_уровней, текущая дата]
	$inputArray = preg_split('/##/', $inputString, -1, PREG_SPLIT_NO_EMPTY);
	return $inputArray;
}
//	Функция для получения трехмерного массива текущих значений температур или скоростей их изменения
function getFromTS_arrayOfValues3d($inputArray){
	//	Получаем трехмерный массив [силос][подвеска][датчик]
	$arrayOfCurVals = preg_split('/::/', $inputArray, -1, PREG_SPLIT_NO_EMPTY);
	for($i = 0; $i < count($arrayOfCurVals); $i++){
		$arrayOfCurVals[$i] = preg_split('/:/', $arrayOfCurVals[$i], -1, PREG_SPLIT_NO_EMPTY);
		for($j = 0; $j < count($arrayOfCurVals[$i]); $j++){
			$arrayOfCurVals[$i][$j] = preg_split('/ /', $arrayOfCurVals[$i][$j], -1, PREG_SPLIT_NO_EMPTY);
		}
	}
	return $arrayOfCurVals;
}
//	Функция для получения массива уровней
function getFromTS_grainLevels($inputArray){
	$arrayOfLevels = preg_split('/:/', $inputArray, -1, PREG_SPLIT_NO_EMPTY);
	return $arrayOfLevels;
}
//	Проверка вычитанных из Термосервера или отладочных таблиц значений содержимому таблицы sensors
function checkReadValsToDBSensors($dbh, $arrayOfTemperatures){

	$sql = "SELECT sensor_id, silo_id, podv_id, count(sensor_num)
			FROM sensors
			GROUP BY silo_id, podv_id
			ORDER BY silo_id;";
	$sth = $dbh->query($sql);

	if($sth==false){
		return false;
	}

	$rows = $sth->fetchAll();
	if(count($rows)==0){
		return false;
	}

	if(!is_array($arrayOfTemperatures)){
		return false;
	}

	$arrayOfTemperaturesSize=0; $rowsShift=0;
	for($i=0; $i<count($arrayOfTemperatures); $i++){
		if($i>0){
			$rowsShift += count($arrayOfTemperatures[$i-1]);
		}
		if(!isset($arrayOfTemperatures[$i])){
			return false;
		}
		if(!is_array($arrayOfTemperatures[$i])){
			return false;
		}
		for($j=0; $j<count($arrayOfTemperatures[$i]); $j++){

			if(!isset($rows[$rowsShift+$j]['count(sensor_num)'])){
				return false;
			}

			if(count($arrayOfTemperatures[$i][$j]) != $rows[$rowsShift+$j]['count(sensor_num)'] ){
				return false;
			}
			$arrayOfTemperaturesSize++;
		}
	}

	if($arrayOfTemperaturesSize!=count($rows)){
		return false;
	}

	return true;

}

//	Перечень функций для выдачи конфигурационных массивов в JavaScript для повышения интерактивности.------------------------------------------------------------
//  Вызываются при переходе на новую страницу

//	Выход: трехмерный массив [массив имен силосов][массив подвесок][массив датчиков]
function getConfForVisu_ProjectConfig($dbh){

    $projectConfArr = array();

    $sql = "SELECT s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num, s.sensor_id
				FROM zernoib.sensors AS s INNER JOIN zernoib.prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id
				ORDER BY silo_id, silo_name, sensor_id, sensor_num;";
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();
	if(count($rows)==0){
		return false;
	}

    $currSiloName="";
    $currPodvNum="";

    for ($i=0; $i<count($rows); $i++){

        if($currSiloName != $rows[$i]['silo_name']){
            $currSiloName = $rows[$i]['silo_name'];
            $currPodvNum="";
            $projectConfArr[$currSiloName]=array();
        }

        if($currPodvNum != ($rows[$i]['podv_id'] + 1) ){
            $currPodvNum = $rows[$i]['podv_id'] + 1;
            $projectConfArr[$currSiloName][$currPodvNum]=array();
        }

        $projectConfArr[$currSiloName][$currPodvNum][$rows[$i]['sensor_num'] + 1] = $rows[$i]['sensor_num'] + 1;

    }

    return $projectConfArr;
}
//	Выход: массив с именами силосов, при этом индекс элемента равен id силоса в БД
function getConfForVisu_SiloNames($dbh){

    $arrayOfSiloNames = array();

    $sql = "SELECT silo_id, silo_name FROM zernoib.prodtypesbysilo ORDER BY silo_id;";
    $sth = $dbh->query($sql);
    
    $rows = $sth->fetchAll();

	foreach($rows as $row){
		array_push($arrayOfSiloNames, $row['silo_name']);
	}

    return $arrayOfSiloNames;
}
//	Выход: название силоса с максимальным количеством подвесок. Необходимо для страницы "Отчет" (печатные формы)
function getConfForVisu_SiloNameWithMaxPodvNumber($dbh){

    $sql = "SELECT s.silo_id, pbs.silo_name, count(distinct (s.podv_id)), count(distinct(s.sensor_num))
			FROM sensors AS s INNER JOIN prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id 
			GROUP BY s.silo_id
			ORDER BY count(distinct (s.podv_id)) DESC, count(distinct(s.sensor_num)) DESC, pbs.silo_name";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();
    
    return $rows[0]['silo_name'];
}

//  AJAX --------------------------------------------------------------------------------------------------------------------------------------------------------
if( isset($_POST['POST_currValsFromTS_get_number_of_new_alarms']) ) {
	if(count($errors)>0){
		echo json_encode($errors);
	} else {
		echo alarms_get_nack_number($dbh);
	}
}

if( isset($_POST['POST_currValsFromTS_acknowledge_alarms']) ) {
	echo alarms_ack($dbh,$serverDate, $logFile);
    //echo "Произведено подтверждение сигналов АПС" ;
}

//	Получение текущих значений
if( isset($_POST['POST_currValsFromTS_get_server_date']) ) {
    echo $serverDate;
}

if( isset($_POST['POST_currValsFromTS_get_array_of_levels']) ) {
    echo json_encode( $arrayOfLevels ) ;
}

//	Получение конфигурационных массивов
if( isset($_POST['POST_currValsFromTS_get_project_conf_array']) ) {
    echo json_encode( getConfForVisu_ProjectConfig($dbh) ) ;
}

if( isset($_POST['POST_currValsFromTS_get_silo_names_array']) ) {
    echo json_encode( getConfForVisu_SiloNames($dbh) ) ;
}

if( isset($_POST['POST_currValsFromTS_get_silo_number_with_max_podv_number']) ) {
    echo getConfForVisu_SiloNameWithMaxPodvNumber($dbh);
}

?>