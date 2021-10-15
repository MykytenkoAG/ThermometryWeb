<?php

//	Файлы конфигурации
$errCodesINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/ts_errors.ini'), true);
$initProductsINI =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_products.ini'), true);
$initUsersINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_users.ini'), true);
$settingsINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/settings.ini'), true);
$termoServerINI  =	parse_ini_string(replaceForbiddenChars(file_get_contents($settingsINI['pathToTermoServer'])), true);
$termoClientINI  =	parse_ini_string(replaceForbiddenChars(file_get_contents($settingsINI['pathToTermoClient'])), true);
//	Параметры подключения к TermoServer
$IPAddr		= $settingsINI['TermoServerIPAddr'];
$port		= $settingsINI['TermoServerPort'];
//	Необходимые параметры для подключения к БД
$servername	= $settingsINI['DBServerIPAddr'];
$username	= $settingsINI['DBUserName'];
$password	= $settingsINI['DBPassword'];
$dbname		= $settingsINI['DBName'];
//	Создание объекта PDO для работы с Базой Данных
$dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//	Функция для замены символов "(", ")", "off". Необходима для корректной работы функции parse_ini_string()
function replaceForbiddenChars($str){
	$str = str_replace("(", "_", $str);	$str = str_replace(")", "_", $str);	$str = str_replace("off", "off_", $str);
	return $str;
}

//	Функция проверки конфигурации в INI-файлах
function isConfigOK(){

	global $dbh;
	global $termoServerINI;
	
	$sql = "SELECT silo_id, podv_id, COUNT(sensor_id) FROM sensors GROUP BY silo_id, podv_id";
	$sth = $dbh->query($sql);
	
	if($sth==false){
		return false;
	}
	$rows = $sth->fetchAll();

	$rowsIterator=0;
	foreach ($termoServerINI as $key => $value) {
		if(preg_match('/Silos[0-9]+/',$key)){
			$sensorsArr = preg_split('/,/',$termoServerINI[$key]['SensorsStr'],-1,PREG_SPLIT_NO_EMPTY);     //  Получаем массив количеств датчиков
			foreach ($sensorsArr as $sensorsNum){                                                           //  Проходим по нему
				if($rowsIterator>(count($rows)-1)){
					return false;
				}
				if($sensorsNum != $rows[$rowsIterator]['COUNT(sensor_id)']){
					return false;
				}
				preg_match('/[0-9]+/',$key, $matches);
				$siloIDfromINI = $matches[0]-1;
				$siloIDfromDB = $rows[$rowsIterator]['silo_id'];
				if( ($siloIDfromINI) != $siloIDfromDB){
					return false;
				}
				$rowsIterator++;
			}
		}
	}
	if($rowsIterator!=count($rows)){
		return false;
	}
	return true;
}

//	Функция выдачи главного конфигурационного массива проекта
function getProjectConfArr(){

    global $dbh;
    $projectConfArr = array();

    $sql = "SELECT s.sensor_id, s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num
            FROM zernoib.sensors AS s INNER JOIN zernoib.prodtypesbysilo AS pbs
            ON s.silo_id = pbs.silo_id;";
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

?>