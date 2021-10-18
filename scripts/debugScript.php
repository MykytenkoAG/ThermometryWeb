<?php

function debug_drop_all_tables(){

	global $dbh;

	$query = 
	   "DROP TABLE IF EXISTS zernoib.debug_sensors;
		DROP TABLE IF EXISTS zernoib.debug_silo;";

	$stmt = $dbh->prepare($query);

	$stmt->execute();

    return;
}
//  silo_id, grain_level
function debug_create_silo_table($termoServerINI){

    global $dbh;
	
	$sql = "CREATE TABLE IF NOT EXISTS zernoib.debug_silo
			 (silo_id INT NOT NULL,
              grain_level INT NOT NULL,
			  PRIMARY KEY (silo_id))
			  ENGINE = InnoDB;";
			  
	$stmt = $dbh->prepare($sql);
	$stmt->execute();

    $sql = "INSERT INTO debug_silo (silo_id, grain_level) VALUES ";

    foreach ($termoServerINI as $key => $value) {
		if( preg_match('/Silos([0-9]+)/',$key,$matches) ){
            $sql .= "(" . ($matches[1]-1) . "," . "0" . "),";
		}
	}

	$sql = substr($sql,0,-1).";";

	$stmt = $dbh->prepare($sql);
	$stmt->execute();

    return;
}
//  sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_temperature_speed
function debug_create_sensors_table($termoServerINI){

    global $dbh;

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

    $sql = "INSERT INTO debug_sensors (sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_temperature_speed) VALUES ";

    $sensor_id = 0;
    foreach ($termoServerINI as $key => $value) {
		if(preg_match('/Silos([0-9]+)/',$key,$matches)){
            $silo_id=$matches[1]-1;
			$sensorsArr = preg_split('/,/',$termoServerINI[$key]['SensorsStr'],-1,PREG_SPLIT_NO_EMPTY);
            $podv_id=0;
			foreach($sensorsArr as $podvSensorsNumber){
                $sensor_num=0;
                for($i=0;$i<$podvSensorsNumber;$i++){
                    $sql .= "(".$sensor_id.",".$silo_id.",".$podv_id.",".$sensor_num.","."0".","."0"."),";
                    $sensor_num++;
                    $sensor_id++;
                }
                $podv_id++;
            }
		}
	}

	$sql = substr($sql,0,-1).";";

	$stmt = $dbh->prepare($sql);
	$stmt->execute();

    return;
}

//  Получение текущих значений параметров
function debug_update_temperature_values(){

    global $dbh;
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

function debug_update_temperature_speeds_values(){

    global $dbh;
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

function debug_update_level_values(){

    global $dbh;
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