<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/configParameters.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbDebugTables.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbCreateTables.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/checkCurrConfig.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbCurrVals.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbAlarms.php');

update_lvl($dbh, $arrayOfLevels);
update_t_v($dbh, $arrayOfTemperatures, $arrayOfTempSpeeds, $serverDate);

setNACK ($dbh, $serverDate);
resetACK($dbh, $serverDate);

if( isset($_POST['read_vals']) ) {
	echo "Данные успешно прочитаны" ;
}

if( isset($_POST['is_sound_on']) ) {

    $dbBackupFile = @parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/dbBackups/dbbackup 29.10.2021 21.24.58.ini'), true);

    //echo restoreFromBackup($dbh, $dbBackupFile);

    echo isSoundOn($dbh);
}

if( isset($_POST['acknowledge']) ) {
	setACK($dbh,$serverDate);
    echo "Произведено подтверждение сигналов АПС" ;
}

//	Функция выдачи главного конфигурационного массива проекта
function getProjectConfArr($dbh){

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

function getArrayOfSiloNames($dbh){

    $arrayOfSiloNames = array();

    $sql = "SELECT silo_id, silo_name FROM zernoib.prodtypesbysilo ORDER BY silo_id;";
    $sth = $dbh->query($sql);
    
    $rows = $sth->fetchAll();

	foreach($rows as $row){
		array_push($arrayOfSiloNames, $row['silo_name']);
	}

    return $arrayOfSiloNames;
}

if( isset($_POST['get_server_date']) ) {
    echo $serverDate;
}

if( isset($_POST['get_array_of_levels']) ) {
    echo json_encode( $arrayOfLevels ) ;
}

if( isset($_POST['get_project_conf_array']) ) {
    echo json_encode( getProjectConfArr($dbh) ) ;
}

if( isset($_POST['get_silo_names_array']) ) {
    echo json_encode( getArrayOfSiloNames($dbh) ) ;
}

function getSiloNameWithID0($dbh){

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

if( isset( $_POST['get_silo_name_with_id_0']) ) {
    echo getSiloNameWithID0($dbh);
}

function getSiloNameWithMaxPodvNumber($dbh){

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

if( isset( $_POST['get_silo_number_with_max_podv_number']) ) {
    echo getSiloNameWithMaxPodvNumber($dbh);
}

?>