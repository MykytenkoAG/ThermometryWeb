<?php

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
//	Проверка содержимого таблицы sensors конфигурации файла TermoServer.ini
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
//	Обновление проекта. Инициализация всех таблиц в Базе Данных
function projectUpdate($dbh, $termoClientINI, $termoServerINI){

	ddl_execute_statement($dbh, SQL_STATEMENT_DROP_ALL_TABLES);

	ddl_execute_statement($dbh, SQL_STATEMENT_CREATE_USERS);
	ddl_execute_statement($dbh, SQL_STATEMENT_CREATE_ERRORS);
	ddl_execute_statement($dbh, SQL_STATEMENT_CREATE_DATES);
	ddl_execute_statement($dbh, SQL_STATEMENT_CREATE_PRODTYPES);
	ddl_execute_statement($dbh, SQL_STATEMENT_CREATE_SILOSESGROUPS);
	ddl_execute_statement($dbh, SQL_STATEMENT_CREATE_PRODTYPESBYSILO);
	ddl_execute_statement($dbh, SQL_STATEMENT_CREATE_SENSORS);
	ddl_execute_statement($dbh, SQL_STATEMENT_CREATE_MEASUREMENTS);
	ddl_execute_statement($dbh, SQL_STATEMENT_CREATE_TELEGRAM_USERS);

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

//	Перечень функций для выдачи конфигурационных массивов в JavaScript для повышения интерактивности
//  Вызываются при переходе на новую страницу
//	Выход: трехмерный массив [массив имен силосов][массив подвесок][массив датчиков]
function getConfForVisu_ProjectConfig($dbh){

    $projectConfArr = array();

    $sql = "SELECT s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num, s.sensor_id
				FROM ".DBNAME.".sensors AS s INNER JOIN ".DBNAME.".prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id
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

    $sql = "SELECT silo_id, silo_name FROM ".DBNAME.".prodtypesbysilo ORDER BY silo_id;";
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

//	Функции для отображения ошибок
//	Функция для отрисовки сообщений с ошибками
function draw_errors($POSSIBLE_ERRORS, $errors){
	$outStr = "";
	foreach($errors as $error){
		$outStr .= $POSSIBLE_ERRORS[$error]['message'];
	}
	return $outStr;
}
function draw_error_images($POSSIBLE_ERRORS, $errors){
	$outStr = "";
	if(	array_search("NoTermoServer.ini", $errors)!=false		||
		array_search("NoTermoClient.ini", $errors)!=false		||
		array_search("DamagedTermoServer.ini", $errors)!=false	||
		array_search("DamagedTermoClient.ini", $errors)!=false	||
		array_search("IniFilesInconsistent", $errors)!=false	||
		array_search("ProjectIsOutOfDate", $errors)!=false ){
			$outStr .= $POSSIBLE_ERRORS["SolutionINIFilesAreDamaged"]['image'];
	}
	if(array_search("TermoServerIsOff", $errors)){
			$outStr .= $POSSIBLE_ERRORS["SolutionTermoServerIsOff"]['image'];
	}
	return $outStr;
}

?>