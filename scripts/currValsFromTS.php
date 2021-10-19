<?php
/*
	Процедура запуска:
	1.	Проверка наличия файлов TermoClient.ini и TermoServer.ini в папке settings проекта. Если нету => выход
	2.	Проверка соответствия TermoServer.ini и TermoClient.ini. Если нет => Выдача предупреждающего сообщения и выход
	3.	Проверка TermoServer.ini тому, что шлет TermoServer. Если нет соответствия =>	Выдача предупреждающего сообщения с просьбой обновить файл и выход
		DEBUG. 	Проверка TermoServer.ini тому, что записано в таблицах debug_sensors и debug_silo. Если нет соответствия => автоматическая инициализация таблиц
	4.	Проверка содержимого TermoServer.ini тому, что записано в dbSensors и prodtypesbysilo. Если нет соответствия => предупреждающее сообщение и
			автоматическая инициализация таблиц
*/
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/debugScript.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbCreateTables.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbCurrVals.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbAlarms.php');

$simulation_mode = true;
$error="";
//	Функция для замены символов "(", ")", "off". Необходима для корректной работы функции parse_ini_string()
function replaceForbiddenChars($str){
	$str = str_replace("(", "_", $str);	$str = str_replace(")", "_", $str);	$str = str_replace("off", "off_", $str);
	return $str;
}
//	Чтение главных конфигурационных файлов проекта
$termoServerINI  =	@parse_ini_string(replaceForbiddenChars(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/TermoServer.ini')), true);
if( count($termoServerINI)==0 ){	$error .= "Файл TermoServer.ini отсутствует в каталоге webTermometry/settings;";}
$termoClientINI  =	@parse_ini_string(replaceForbiddenChars(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/TermoClient.ini')), true);
if( count($termoClientINI)==0 ){	$error .= "Файл TermoClient.ini отсутствует в каталоге webTermometry/settings;";}

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

if( ! doINIFilesMatchEachOther($termoServerINI,$termoClientINI) ){
	$error .= "Файлы TermoServer.ini и TermoClient.exe не соответствуют друг другу;";
}

//	Набор инициализационных параметров для работы с термосервером и БД
	$errCodesINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/ts_errors.ini'), true);
	$initProductsINI =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_products.ini'), true);
	$initUsersINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_users.ini'), true);
	$settingsINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/settings.ini'), true);
//	Параметры подключения к TermoServer
	$IPAddr		= $settingsINI['TermoServerIPAddr'];
	$port		= $settingsINI['TermoServerPort'];
//	Необходимые параметры для подключения к БД
	$servername	= $settingsINI['DBServerIPAddr'];
	$username	= $settingsINI['DBUserName'];
	$password	= $settingsINI['DBPassword'];
	$dbname		= $settingsINI['DBName'];
//	Создание объекта PDO для работы с Базой Данных
$dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);	//[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]

if($error!=""){
	die(require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/error_page.php'));
}

//	Функция для получения строки с текущими значениями
function getInputString($IPAddr, $port){
	global $error;
	$error = "Проверьте, запущен ли термосервер";
	//http://docs.php.net/fsockopen
	$fp = @fsockopen($IPAddr, $port, $errno, $errstr, 30)
		or die(require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/error_page.php'));	//	Перенаправление на страницу с описанием ошибки
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
function getInputArray($inputString){
	//Получаем массив строк [массив_текущих температур, массив_скоростей_изменения_температура, массив_уровней, текущая дата]
	$inputArray = preg_split('/##/', $inputString, -1, PREG_SPLIT_NO_EMPTY);
	return $inputArray;
}
//	Функция для получения трехмерного массива текущих значений температур или скоростей их изменения
function getArrayOfValues3d($inputArray){
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
function getLevels($inputArray){
	$arrayOfLevels = preg_split('/:/', $inputArray, -1, PREG_SPLIT_NO_EMPTY);
	return $arrayOfLevels;
}

//	Анализ значений, которые шлет термосервер на соответствие INI-файлу
function createTermoServerAssocArray($termoServerINI){
	$outArr=array();
	foreach ($termoServerINI as $key => $value) {
		if(preg_match('/Silos[0-9]+/',$key)){
			$sensorsArr = preg_split('/,/',$termoServerINI[$key]['SensorsStr'],-1,PREG_SPLIT_NO_EMPTY);
			$outArr[$key]=$sensorsArr;
		}
	}
	return $outArr;
}

function createArrOfTemperaturesAssocArr($arrayOfTemperatures){
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
function createdbTableAssocArr($dbTable){
	$outArr=array();
	global $dbh;
	
	$sql = "SELECT sensor_id, silo_id, podv_id, count(sensor_num)
			FROM $dbTable
			GROUP BY silo_id, podv_id;";
	$sth = $dbh->query($sql);
	if($sth==false){
		return $outArr;
	}
	$rows = $sth->fetchAll();

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

if( ! $simulation_mode) {
	//	Файлы прошли проверку, можно считывать значения из термосервера
	$inputValsArr		 = getInputArray ( getInputString($IPAddr, $port) );	//	[температуры][скорости][уровни][дата]
	$arrayOfTemperatures = getArrayOfValues3d($inputValsArr[0]);
	$arrayOfTempSpeeds   = getArrayOfValues3d($inputValsArr[1]);
	$arrayOfLevels       = getLevels($inputValsArr[2]);
	$serverDate			 = $inputValsArr[3];
	//	Проверка содержимого TermoServer.ini тому, что шлет сам Термосервер
	if( ! ( count( arrayRecursiveDiff( createTermoServerAssocArray($termoServerINI) , createArrOfTemperaturesAssocArr($arrayOfTemperatures)   ) )==0 &&
		    count( arrayRecursiveDiff( createArrOfTemperaturesAssocArr($arrayOfTemperatures) , createTermoServerAssocArray($termoServerINI)   ) )==0 )  ){
		$error = "Текущий файл TermoServer.ini не соответствует настрокам термосервера. Скопируйте актуальный файл TermoServer.ini в папку settings;";
		die(require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/error_page.php'));
	}

} else {
	//	Если включен режим отладки
	if( ! ( count( arrayRecursiveDiff( createTermoServerAssocArray($termoServerINI) , createdbTableAssocArr("zernoib.debug_sensors")   ) )==0 &&
			count( arrayRecursiveDiff( createdbTableAssocArr("zernoib.debug_sensors") , createTermoServerAssocArray($termoServerINI)   ) )==0 ) ){
			//	Создаем новые отладочные таблицы исходя из значений в ini-файле
			debug_drop_all_tables();
			debug_create_silo_table($termoServerINI);
			debug_create_sensors_table($termoServerINI);
	}
	//	Считывание парметров температур, скоростей и уровней в виде, аналогичном тому, который видает термосервер
	$arrayOfTemperatures = debug_update_temperature_values();
	$arrayOfTempSpeeds   = debug_update_temperature_speeds_values();
	$arrayOfLevels       = debug_update_level_values();
	date_default_timezone_set('Europe/Kiev');
	$date = date('d.m.Y H:i:s', time());
	$serverDate			 = $date;
}

//	Проверка таблицы dbSensors содержимому TermoServer.ini
if( ! ( count( arrayRecursiveDiff( createTermoServerAssocArray($termoServerINI) , createdbTableAssocArr("zernoib.sensors")   ) )==0 &&
		count( arrayRecursiveDiff( createdbTableAssocArr("zernoib.sensors") , createTermoServerAssocArray($termoServerINI)   ) )==0 ) ){

	//	Файл TermoServer.ini был обновлен. Необходимо выполнить автоматическую инициализацию всех таблиц в БД
	echo "Файл TermoServer.ini был обновлен. Выполняем автоматическую инициализацию всех таблиц в БД";

	deleteAllTables();

	createTableUsers();				initTableUsers($initUsersINI);
	createTableErrors();			initTableErrors($errCodesINI);
	createTableDates();				initTableDates($serverDate);
	createTableProdtypes();			initTableProdtypes($initProductsINI);
	createTableProdtypesbysilo();	initTableProdbysilo($termoClientINI,$termoServerINI);
	createTableSensors();			initTableSensors($termoServerINI,$serverDate);
	createTableMeasurements();

}

//	Запись текущих измеренных значений в БД
update_t_v($arrayOfTemperatures,$arrayOfTempSpeeds,$serverDate);
update_lvl($arrayOfLevels);

//	Проверка на алармы
setNACK();
//resetACK();

//	Функция выдачи главного конфигурационного массива проекта
function getProjectConfArr(){

    global $dbh;
    $projectConfArr = array();

    $sql = "SELECT s.sensor_id, s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num
            	FROM zernoib.sensors AS s INNER JOIN zernoib.prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id;";
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

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

if( isset($_POST['get_project_conf_array']) ) {
    echo json_encode( getProjectConfArr() ) ;
}


if( isset($_POST['is_sound_on']) ) {
    echo isSoundOn();
	//echo "Данные успешно прочитаны" ;
}

if( isset($_POST['read_vals']) ) {
    //echo isSoundOn();
	echo "Данные успешно прочитаны" ;
}

if( isset($_POST['acknowledge']) ) {
	setACK();
    echo "Произведено подтверждение сигналов АПС" ;
}

?>