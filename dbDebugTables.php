<?php

function ddl_debug_drop_all($dbh){

	$query = 
	   "DROP TABLE IF EXISTS zernoib.debug_sensors;
		DROP TABLE IF EXISTS zernoib.debug_silo;";

	$stmt = $dbh->prepare($query);

	$stmt->execute();

    return;
}
//  silo_id, grain_level
function ddl_debug_create_Silo($dbh){
	
	$sql = "CREATE TABLE IF NOT EXISTS zernoib.debug_silo
			 (silo_id INT NOT NULL,
              grain_level INT NOT NULL,
			  PRIMARY KEY (silo_id))
			  ENGINE = InnoDB;";
			  
	$stmt = $dbh->prepare($sql);
	$stmt->execute();

    $sql = "SELECT silo_id, grain_level FROM prodtypesbysilo;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $prodtypesbysiloRows = $sth->fetchAll();

    $sql = "INSERT INTO debug_silo (silo_id, grain_level) VALUES ";

    foreach($prodtypesbysiloRows as $prodtypesbysiloRow){
        $sql .= "(" . $prodtypesbysiloRow['silo_id'] . "," . "0" . "),";
    }

	$sql = substr($sql,0,-1).";";

	$stmt = $dbh->prepare($sql);
	$stmt->execute();

    return;
}
//  sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_temperature_speed
function ddl_debug_create_Sensors($dbh){

	$sql = "CREATE TABLE IF NOT EXISTS zernoib.debug_sensors
			 (sensor_id INT NOT NULL,
              silo_id INT NOT NULL,
              podv_id INT NOT NULL,
              sensor_num INT NOT NULL,
              current_temperature INT NOT NULL,
              current_temperature_speed INT NOT NULL,
			  PRIMARY KEY (sensor_id),
              CONSTRAINT debug_sensors_fk FOREIGN KEY (silo_id) REFERENCES debug_silo(silo_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
			  ENGINE = InnoDB;";
			  
	$stmt = $dbh->prepare($sql);
	$stmt->execute();


    $sql = "SELECT sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_speed FROM sensors;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $sensorsRows = $sth->fetchAll();


    $sql = "INSERT INTO debug_sensors (sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_temperature_speed) VALUES ";

    foreach($sensorsRows as $sensorsRow){
        $sql .= "(".$sensorsRow['sensor_id'].",".$sensorsRow['silo_id'].",".$sensorsRow['podv_id'].",".$sensorsRow['sensor_num'].","."0".","."0"."),";
    }

	$sql = substr($sql,0,-1).";";

	$stmt = $dbh->prepare($sql);
	$stmt->execute();

    return;
}

//  Получение текущих значений параметров из Базы Данных
//  БД => $arrayOfTemperatures
function db_debug_update_temperatures($dbh){

    $arrayOfTemperatures=array();

    $sql = "SELECT sensor_id, silo_id, podv_id, sensor_num, current_temperature
                FROM debug_sensors;";
 
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();
    foreach($rows as $row){
        $arrayOfTemperatures[$row['silo_id']][$row['podv_id']][$row['sensor_num']]=$row['current_temperature'];
    }

    return $arrayOfTemperatures;
}
//  БД => $arrayOfTempSpeeds
function db_debug_update_temperatureSpeeds($dbh){

    $arrayOfTempSpeeds=array();

    $sql = "SELECT sensor_id, silo_id, podv_id, sensor_num, current_temperature_speed
                FROM debug_sensors;";
 
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();
    foreach($rows as $row){
        $arrayOfTempSpeeds[$row['silo_id']][$row['podv_id']][$row['sensor_num']]=$row['current_temperature_speed'];
    }

    return $arrayOfTempSpeeds;
}
//  БД => $arrayOfLevels
function db_debug_update_grainLevels($dbh){

    $arrayOfLevels=array();

    $sql = "SELECT silo_id, grain_level
                FROM debug_silo;";
 
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();
    foreach($rows as $row){
        $arrayOfLevels[$row['silo_id']]=$row['grain_level'];
    }

    return $arrayOfLevels;
}

?>