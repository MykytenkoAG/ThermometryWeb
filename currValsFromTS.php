<?php

require_once ('configParameters.php');		//	Параметры подключения к БД и связи с Термосервером
require_once ('checkProjectConfig.php');	//	Функции для проверки текущей конфигурации проекта
require_once ('dbDebugTables.php');			//	Создание, удаление и изменение отладочных таблиц в БД
require_once ('dbDDL.php');					//	Создание и инициализация всех таблиц в БД
require_once ('TermoServer.php');			//	Функции для работы с программой Термосервер
require_once ('dbCurrVals.php');			//	Запись текущих измеренных значений в БД

$configOK=true;
$errors=array();
$arrayOfTemperatures=array(); $arrayOfTempSpeeds=array(); $arrayOfLevels=array(); $serverDate="";

//	ПРОЦЕДУРА ПРОВЕРКИ -------------------------------------------------------------------------------------------------------------------------------------------

//	Проверка существования таблицы sensors в базе данных проекта
if( isTableExistAndNotEmpty($dbh,"".DBNAME.".sensors") ){
	goto tableSensorsOK;
}

//	Преобразовываем файлы из кодировки WINDOWS-1251 в UTF-8
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

if( count($errors)>0 ){
	goto exit_from_script;
}

if( !isIniFileTermoServerOK($termoServerINI) ){
	$configOK=false;
	array_push($errors, "DamagedTermoServer.ini");			//	Файл TermoServer.ini поврежден
}

if( !isIniFileTermoClientOK($termoClientINI) ){
	$configOK=false;
	array_push($array, "DamagedTermoClient.ini");			//	Файл TermoClient.ini поврежден
}

if( count($errors)>0 ){
	goto exit_from_script;
}

if( !areIniFilesConsistent($termoServerINI,$termoClientINI) ){
	$configOK=false;
	array_push($errors, "IniFilesInconsistent");				//	Файлы TermoServer.ini и TermoClient.ini не соответствуют друг другу
}

if( count($errors)>0 ){
	goto exit_from_script;
}

//	Конфигурационные файлы прошли проверку
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

//	Функция для замены символов "(", ")", "off", ";". Необходима для корректной работы функции parse_ini_string()
function replaceForbiddenChars($str){
	$str = str_replace("(", "_", $str);	$str = str_replace(")", "_", $str);	$str = str_replace("off", "off_", $str); $str = str_replace(";", "%", $str);
	return $str;
}

//  AJAX --------------------------------------------------------------------------------------------------------------------------------------------------------
//	Работа с сигналами АПС
if( isset($_POST['POST_currValsFromTS_get_number_of_new_alarms']) ) {
	if(count($errors)>0){
		echo json_encode($errors);				//	Ошибка конфигурации возвращаем в JSON формате
	} else {
		echo alarms_get_nack_number($dbh);		//	Если все ОК, выдаем количество неквитированных алармов
	}
}

if( isset($_POST['POST_currValsFromTS_acknowledge_alarms']) ) {
	echo alarms_ack($dbh,$serverDate, $logFile);		//	Квитирование сигналов АПС
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