<?php

require_once ('configParameters.php');		//	Параметры подключения к БД и связи с Термосервером
require_once ('dbDebugTables.php');			//	Создание, удаление и изменение отладочных таблиц в БД
require_once ('dbDDL.php');					//	Создание и инициализация всех таблиц в БД
require_once ('dbCurrVals.php');			//	Запись текущих измеренных значений в БД
require_once ('dbAlarms.php');				//	Работа с сигналами АПС

/*
	Процедура запуска:
	1.	Проверка наличия файлов TermoClient.ini и TermoServer.ini в папке settings проекта. Если нету => выход
	2.	Проверка соответствия TermoServer.ini и TermoClient.ini. Если нет => Выдача предупреждающего сообщения и выход
	3.	Проверка TermoServer.ini тому, что шлет TermoServer. Если нет соответствия =>	Выдача предупреждающего сообщения с просьбой обновить файл и выход
		DEBUG. 	Проверка TermoServer.ini тому, что записано в таблицах debug_sensors и debug_silo. Если нет соответствия => автоматическая инициализация таблиц
	4.	Проверка содержимого TermoServer.ini тому, что записано в dbSensors и prodtypesbysilo. Если нет соответствия => предупреждающее сообщение и
			автоматическая инициализация таблиц
*/
$error="";
//	Функция для замены символов "(", ")", "off". Необходима для корректной работы функции parse_ini_string()
function replaceForbiddenChars($str){
	$str = str_replace("(", "_", $str);	$str = str_replace(")", "_", $str);	$str = str_replace("off", "off_", $str);
	return $str;
}
//	Чтение главных конфигурационных файлов проекта
$termoServerINI  =	@parse_ini_string(replaceForbiddenChars(file_get_contents('settings/TermoServer.ini')), true);
if( count($termoServerINI)==0 ){	$error .= "Файл TermoServer.ini отсутствует в каталоге webTermometry/settings;".getcwd();}
$termoClientINI  =	@parse_ini_string(replaceForbiddenChars(file_get_contents('settings/TermoClient.ini')), true);
if( count($termoClientINI)==0 ){	$error .= "Файл TermoClient.ini отсутствует в каталоге webTermometry/settings;".getcwd();}

/*	Проверка соответствия файлов TermoServer.ini и TermoClient.ini
	Функция производит проверку наличия всех силосов из TermoServer.ini в TermoClient.ini
*/
function doINIFilesMatchEachOther($termoServerINI,$termoClientINI){
	$termoServerSiloArray=array();$termoClientSiloArray=array();
	foreach ($termoServerINI as $key => $value) {
		if(preg_match('/Silos[0-9]+/',$key)){
			array_push($termoServerSiloArray, $key);
		}
	}
	foreach ($termoClientINI as $key => $value) {
		if(preg_match('/Silos[0-9]+/',$key)){
			array_push($termoClientSiloArray, $key);
		}
	}
	foreach($termoServerSiloArray as $currSilo){
		if(!in_array($currSilo, $termoClientSiloArray)){
			return false;
		}
	}
	return true;
}

//	Если обнаружено несоответствие => Выход
if( ! doINIFilesMatchEachOther($termoServerINI,$termoClientINI) ){
	$error .= "Файлы TermoServer.ini и TermoClient.exe не соответствуют друг другу;";
}

if($error!=""){	die(require_once('error_page.php'));}

//	Функции для работы с программой TermoServer -----------------------------------------------------------------------------------------------------
//	Функция для получения строки с текущими значениями
//	В случае, если термосервер не запущен, происходит запись в глобальную переменную $error
function getFromTS_inputString($IPAddr, $port){
	global $error;
	$error = "Проверьте, запущен ли термосервер $IPAddr, $port";
	//http://docs.php.net/fsockopen
	$fp = @fsockopen($IPAddr, $port, $errno, $errstr, 30)
		or die(require_once('error_page.php'));	//	Перенаправление на страницу с описанием ошибки
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
	$error = "";
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

//	Функции для создания ассоциативных массивов для выявления соответствия значений, которые шлет Термосервер ini-файлу конфигурации -----------------
function createAssocArr_TermoServer($termoServerINI){
	$outArr=array();
	foreach ($termoServerINI as $key => $value) {
		if(preg_match('/Silos[0-9]+/',$key)){
			$sensorsArr = preg_split('/,/',$termoServerINI[$key]['SensorsStr'],-1,PREG_SPLIT_NO_EMPTY);
			$outArr[$key]=$sensorsArr;
		}
	}
	return $outArr;
}

function createAssocArr_ArrayOfTemperatures($arrayOfTemperatures){
	$outArr=array();
	for($i=0; $i<count($arrayOfTemperatures); $i++){
		$outArr["Silos".($i+1)]=array();
		for($j=0; $j<count($arrayOfTemperatures[$i]); $j++){
			$outArr["Silos".($i+1)][$j]=count($arrayOfTemperatures[$i][$j]);
		}
	}
	return $outArr;
}
//	В случае проблем с БД возвращаем пустой массив
function createAssocArr_dbTable($dbh, $dbTable){
	$outArr=array();
	
	$sql = "SELECT sensor_id, silo_id, podv_id, count(sensor_num)
			FROM $dbTable
			GROUP BY silo_id, podv_id;";
	$sth = $dbh->query($sql);
	if($sth==false){
		return $outArr;
	}
	$rows = $sth->fetchAll();

	if(count($rows)==0){
		return $outArr;
	}

	$currentSilo=$rows[0]['silo_id']; $sensorsArr=array();

	for($i=0; $i<count($rows); $i++){
		if($rows[$i]['silo_id']!=$currentSilo){
			$currentSilo=$rows[$i]['silo_id'];
			$outArr["Silos".($rows[$i]['silo_id'])]=$sensorsArr;
			$sensorsArr=array();
		}
		array_push($sensorsArr,$rows[$i]['count(sensor_num)']);
	}
	$outArr["Silos".($rows[count($rows)-1]['silo_id']+1)]=$sensorsArr;

	return $outArr;
}
//	Функция для сравнения ассоциативных массивов
function arrayRecursiveDiff($aArray1, $aArray2) {
    $aReturn = array();
  
    foreach ($aArray1 as $mKey => $mValue) {
        if (array_key_exists($mKey, $aArray2)) {
            if (is_array($mValue)) {
                $aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
            } else {
                if ($mValue != $aArray2[$mKey]) {
                    $aReturn[$mKey] = $mValue;
                }
            }
        } else {
            $aReturn[$mKey] = $mValue;
        }
    }
  
    return $aReturn;
}

$arrayOfTemperatures=array();$arrayOfTempSpeeds=array();$arrayOfLevels=array();$serverDate="";

//	Заполнение массивов $arrayOfTemperatures; $arrayOfTempSpeed; $arrayOfLevels и переменной $serverDate
//	РАБОЧИЙ РЕЖИМ:	значения шлет Термосервер
//	РЕЖИМ ОТЛАДКИ:	значения вычитываются из БД (запись значений производится из отладочной страницы визуализации)
if( ! $simulation_mode) {
	//	Файлы прошли проверку, можно считывать значения из термосервера
	$inputValsArr		 = getFromTS_inputArray ( getFromTS_inputString($IPAddr, $port) );	//	[температуры][скорости][уровни][дата]
	$arrayOfTemperatures = getFromTS_arrayOfValues3d($inputValsArr[0]);
	$arrayOfTempSpeeds   = getFromTS_arrayOfValues3d($inputValsArr[1]);
	$arrayOfLevels       = getFromTS_grainLevels($inputValsArr[2]);
	$serverDate			 = $inputValsArr[3];
	//	Проверка содержимого TermoServer.ini тому, что шлет сам Термосервер
	if( ! ( count( arrayRecursiveDiff( createAssocArr_TermoServer($termoServerINI) , createAssocArr_ArrayOfTemperatures($arrayOfTemperatures)   ) )==0 &&
		    count( arrayRecursiveDiff( createAssocArr_ArrayOfTemperatures($arrayOfTemperatures) , createAssocArr_TermoServer($termoServerINI)   ) )==0 )  ){
		$error = "Текущий файл TermoServer.ini не соответствует настрокам термосервера. Скопируйте актуальный файл TermoServer.ini в папку settings;";
		die(require_once('error_page.php'));
	}

 } else {
	//	Если включен режим отладки
	if( ! ( count( arrayRecursiveDiff( createAssocArr_TermoServer($termoServerINI) , createAssocArr_dbTable($dbh, "zernoib.debug_sensors")   ) )==0 &&
			count( arrayRecursiveDiff( createAssocArr_dbTable($dbh, "zernoib.debug_sensors") , createAssocArr_TermoServer($termoServerINI)   ) )==0 ) ){
			//	Создаем новые отладочные таблицы исходя из значений в ini-файле
			ddl_debug_drop_all($dbh);
			ddl_debug_create_Silo($dbh, $termoServerINI);
			ddl_debug_create_Sensors($dbh, $termoServerINI);
	}
	//	Считывание парметров температур, скоростей и уровней в виде, аналогичном тому, который видает термосервер
	$arrayOfTemperatures = db_debug_update_temperatures($dbh);
	$arrayOfTempSpeeds   = db_debug_update_temperatureSpeeds($dbh);
	$arrayOfLevels       = db_debug_update_grainLevels($dbh);
	date_default_timezone_set('Europe/Kiev');
	$date = date('d.m.Y H:i:s', time());
	$serverDate			 = $date;
}

//	Проверка таблицы dbSensors содержимому TermoServer.ini
if( ! ( count( arrayRecursiveDiff( createAssocArr_TermoServer($termoServerINI) , createAssocArr_dbTable($dbh, "zernoib.sensors")   ) )==0 &&
		count( arrayRecursiveDiff( createAssocArr_dbTable($dbh, "zernoib.sensors") , createAssocArr_TermoServer($termoServerINI)   ) )==0 ) ){

	echo "Файл TermoServer.ini был обновлен. Выполняем автоматическую инициализацию всех таблиц в БД";

	ddl_drop_all($dbh);

	ddl_create_Users($dbh);				ddl_init_Users($dbh);
	ddl_create_Errors($dbh);			ddl_init_Errors($dbh);
	ddl_create_Dates($dbh);				ddl_init_Dates($dbh, $serverDate);
	ddl_create_Prodtypes($dbh);			ddl_init_Prodtypes($dbh);
	ddl_create_Prodtypesbysilo($dbh);	ddl_init_Prodtypesbysilo($dbh, $termoClientINI,$termoServerINI);
	ddl_create_Sensors($dbh);			ddl_init_Sensors($dbh, $termoServerINI,$serverDate);
	ddl_create_Measurements($dbh);

}

//  После успешного прохождения всех проверочных операций необходимо записать текущие измеренные значения в Базу Данных
db_update_grainLevels($dbh, $arrayOfLevels);
db_update_temperaturesAndSpeeds($dbh, $arrayOfTemperatures, $arrayOfTempSpeeds, $serverDate);

//  После записи всех измеренных значений в БД необходимо выполнить проверку на повление новых сигналов АПС и сброс уже существующих
alarms_set  ($dbh, $serverDate);
alarms_reset($dbh, $serverDate);

//	Перечень функций для выдачи конфигурационных массивов в JavaScript для повышения интерактивности.------------------------------------------------------------
//  Вызываются при переходе на новую страницу
//	Выход: трехмерный массив [массив имен силосов][массив подвесок][массив датчиков]
function getConfForVisu_ProjectConfig($dbh){

    $projectConfArr = array();

    $sql = "SELECT s.sensor_id, s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num
            	FROM zernoib.sensors AS s INNER JOIN zernoib.prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id;";
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
//	Выход: название силоса с id==0. Необходимо для страницы "Отчет" (формирование графиков)
function getConfForVisu_SiloNameWith_id_0($dbh){

    $sql = "SELECT silo_name
            FROM prodtypesbysilo
            WHERE silo_id=0";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();
    
    return $rows[0]['silo_name'];
}
//	Выход: название силоса с максимальным количеством подвесок. Необходимо для страницы "Отчет" (печатные формы)
function getConfForVisu_SiloNameWithMaxPodvNumber($dbh){

    $sql = "SELECT s.silo_id, pbs.silo_name, count(distinct (s.podv_id))
            FROM sensors AS s INNER JOIN prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id 
            GROUP BY s.silo_id
            ORDER BY count(distinct (s.podv_id)) DESC";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();
    
    return $rows[0]['silo_name'];
}

//  AJAX --------------------------------------------------------------------------------------------------------------------------------------------------------
if( isset($_POST['POST_currValsFromTS_get_number_of_new_alarms']) ) {
    echo alarms_get_nack_number($dbh);
}

if( isset($_POST['POST_currValsFromTS_acknowledge_alarms']) ) {
	alarms_ack($dbh,$serverDate);
    echo "Произведено подтверждение сигналов АПС" ;
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

if( isset($_POST['POST_currValsFromTS_get_silo_name_with_id_0']) ) {
    echo getConfForVisu_SiloNameWith_id_0($dbh);
}

if( isset($_POST['POST_currValsFromTS_get_silo_number_with_max_podv_number']) ) {
    echo getConfForVisu_SiloNameWithMaxPodvNumber($dbh);
}

?>