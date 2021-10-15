<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/php/currValsFromTS.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/php/dbSensors.php');

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
function debug_create_silo_table($arrayOfLevels){

    global $dbh;
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.debug_silo
			 (silo_id INT NOT NULL,
              grain_level INT NOT NULL,
			  PRIMARY KEY (silo_id))
			  ENGINE = InnoDB;";
			  
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    $query = "INSERT INTO debug_silo (silo_id, grain_level) VALUES ";

    for ($i=0; $i<count($arrayOfLevels); $i++){

        $query .= "(".$i.",".$arrayOfLevels[$i]."),";

    }

	$query = substr($query,0,-1).";";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

//  sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_temperature_speed
function debug_create_sensors_table($arrayOfTemperatures, $arrayOfTempSpeeds){

    global $dbh;
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.debug_sensors
			 (sensor_id INT NOT NULL,
              silo_id INT NOT NULL,
              podv_id INT NOT NULL,
              sensor_num INT NOT NULL,
              current_temperature INT NOT NULL,
              current_temperature_speed INT NOT NULL,
			  PRIMARY KEY (sensor_id),
              CONSTRAINT debug_sensors_fk FOREIGN KEY (silo_id) REFERENCES debug_silo(silo_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
			  ENGINE = InnoDB;";
			  
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    $query = "INSERT INTO debug_sensors (sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_temperature_speed) VALUES ";

    $sensor_id = 0;
	for($i = 0; $i < count($arrayOfTemperatures); $i++){
        for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
			$sensor_num = 0;
    		for($k=0; $k < count($arrayOfTemperatures[$i][$j]); $k++){

                $query .= "(".$sensor_id.",".$i.",".$j.",".$k.","
                             .$arrayOfTemperatures[$i][$j][$k].","
                             .str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k])."),";

                $sensor_num++;
				$sensor_id++;
    		}
        }
    }

	$query = substr($query,0,-1).";";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

function update_temperature_values($arrayOfTemperatures){

    global $dbh;

    $sql = "SELECT sensor_id, current_temperature
                FROM debug_sensors;";
 
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    $sensor_id = 0;
	for($i = 0; $i < count($arrayOfTemperatures); $i++){
        for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
    		for($k=0; $k < count($arrayOfTemperatures[$i][$j]); $k++){
                $arrayOfTemperatures[$i][$j][$k]=$rows[$sensor_id]['current_temperature'];
				$sensor_id++;
    		}
        }
    }

    return $arrayOfTemperatures;
}

function update_temperature_speeds_values($arrayOfTempSpeeds){

    global $dbh;

    $sql = "SELECT sensor_id, current_temperature_speed
                FROM debug_sensors;";
 
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    $sensor_id = 0;
	for($i = 0; $i < count($arrayOfTempSpeeds); $i++){
        for($j = 0; $j < count($arrayOfTempSpeeds[$i]); $j++){
    		for($k=0; $k < count($arrayOfTempSpeeds[$i][$j]); $k++){
                $arrayOfTempSpeeds[$i][$j][$k]=$rows[$sensor_id]['current_temperature_speed'];
				$sensor_id++;
    		}
        }
    }

    return $arrayOfTempSpeeds;
}

function update_level_values($arrayOfLevels){

    global $dbh;

    $sql = "SELECT silo_id, grain_level
                FROM debug_silo;";
 
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

	for($i = 0; $i < count($arrayOfLevels); $i++){
        $arrayOfLevels[$i] = $rows[$i]['grain_level'];
    }

    return $arrayOfLevels;
}

function debug_set_silo_temperature($silo_id, $value){

    global $dbh;
	
	$query="UPDATE debug_sensors SET current_temperature = $value WHERE silo_id=$silo_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

function debug_set_silo_temperature_speed($silo_id, $value){

    global $dbh;
	
	$query="UPDATE debug_sensors SET current_temperature_speed = $value WHERE silo_id=$silo_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

function debug_set_silo_level($silo_id, $value){

    global $dbh;
	
	$query="UPDATE debug_silo SET grain_level = $value WHERE silo_id=$silo_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

function debug_set_podv_temperature($silo_id, $podv_id, $value){

    global $dbh;
	
	$query="UPDATE debug_sensors SET current_temperature = $value WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

function debug_set_podv_temperature_speed($silo_id, $podv_id, $value){

    global $dbh;
	
	$query="UPDATE debug_sensors SET current_temperature_speed = $value WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

function debug_set_sensor_temperature($silo_id, $podv_id, $sensor_num, $value){

    global $dbh;
	
	$query="UPDATE debug_sensors SET current_temperature = $value WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

function debug_set_sensor_temperature_speed($silo_id, $podv_id, $sensor_num, $value){

    global $dbh;
	
	$query="UPDATE debug_sensors SET current_temperature_speed = $value WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

function debug_get_debug_table(){

    global $dbh;

    $sql = "SELECT  debug_sensors.silo_id AS silos,
                    debug_sensors.podv_id AS podv,
                    debug_sensors.sensor_num AS sensor,
                    debug_sensors.current_temperature AS t,
                    debug_sensors.current_temperature_speed AS v,
                    debug_silo.grain_level AS lvl
                FROM debug_sensors INNER JOIN debug_silo
                                    ON debug_sensors.silo_id = debug_silo.silo_id;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();

    $outStr = "<table>";

    $outStr .= "<tr>";

    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Силос"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Подвеска"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Датчик"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Температура"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Скорость"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Уровень"."</td>";

    $outStr .= "</tr>";

    foreach($rows as $row){

        $outStr .= "<tr>";

        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">".$row['silos']."</td>";
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">".$row['podv']."</td>";
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">".$row['sensor']."</td>";
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">".$row['t']."</td>";
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">".$row['v']."</td>";
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">".$row['lvl']."</td>";

        $outStr .= "</tr>";

    }

    $outStr .= "</table>";

    return $outStr;
}

if( isset($_POST['dbg_1_silo_num']) && isset($_POST['dbg_1_temperature'])) {
    debug_set_silo_temperature($_POST['dbg_1_silo_num'], $_POST['dbg_1_temperature']);
    echo "Температура всех датчиков силоса ".$_POST['dbg_1_silo_num']." установлена в ".$_POST['dbg_1_temperature'];
}

if( isset($_POST['dbg_2_silo_num']) && isset($_POST['dbg_2_t_speed'])) {
    debug_set_silo_temperature_speed($_POST['dbg_2_silo_num'], $_POST['dbg_2_t_speed']);
    echo "Скорость всех датчиков силоса ".$_POST['dbg_2_silo_num']." установлена в ".$_POST['dbg_2_t_speed'];
}

if( isset($_POST['dbg_3_silo_num']) && isset($_POST['dbg_3_grain_level'])) {
    debug_set_silo_level($_POST['dbg_3_silo_num'], $_POST['dbg_3_grain_level']);
    echo "Уровень заполнения силоса ".$_POST['dbg_3_silo_num']." установлен в ".$_POST['dbg_3_grain_level'];
}

if( isset($_POST['dbg_4_silo_num']) && isset($_POST['dbg_4_podv_num']) && isset($_POST['dbg_4_temperature'])) {
    debug_set_podv_temperature($_POST['dbg_4_silo_num'], $_POST['dbg_4_podv_num'], $_POST['dbg_4_temperature']);
    echo "Температура всех датчиков силоса ".$_POST['dbg_4_silo_num']," подвески ".$_POST['dbg_4_podv_num']." установлена в ".$_POST['dbg_4_temperature'];
}

if( isset($_POST['dbg_5_silo_num']) && isset($_POST['dbg_5_podv_num']) && isset($_POST['dbg_5_t_speed'])) {
    debug_set_podv_temperature_speed($_POST['dbg_5_silo_num'], $_POST['dbg_5_podv_num'], $_POST['dbg_5_t_speed']);
    echo "Скорость всех датчиков силоса ".$_POST['dbg_5_silo_num']," подвески ".$_POST['dbg_5_podv_num']." установлена в ".$_POST['dbg_5_t_speed'];
}

if( isset($_POST['dbg_6_silo_num']) && isset($_POST['dbg_6_podv_num']) && isset($_POST['dbg_6_sensor_num']) && isset($_POST['dbg_6_temperature'])) {
    debug_set_sensor_temperature($_POST['dbg_6_silo_num'], $_POST['dbg_6_podv_num'], $_POST['dbg_6_sensor_num'], $_POST['dbg_6_temperature']);
    echo "Температура датчика".$_POST['dbg_6_sensor_num']." подвески ".$_POST['dbg_6_podv_num']." силоса ".$_POST['dbg_6_silo_num']." установлена в ".$_POST['dbg_6_temperature'];
}

if( isset($_POST['dbg_7_silo_num']) && isset($_POST['dbg_7_podv_num']) && isset($_POST['dbg_7_sensor_num']) && isset($_POST['dbg_7_t_speed'])) {
    debug_set_sensor_temperature_speed($_POST['dbg_7_silo_num'], $_POST['dbg_7_podv_num'], $_POST['dbg_7_sensor_num'], $_POST['dbg_7_t_speed']);
    echo "Скорость датчика".$_POST['dbg_7_sensor_num']." подвески ".$_POST['dbg_7_podv_num']." силоса ".$_POST['dbg_7_silo_num']." установлена в ".$_POST['dbg_7_t_speed'];
}

if( isset( $_POST['write_measurements_to_db'] ) ) {
    require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/php/dbMeasurements.php');
    addNewMeasurement();
    echo "Текущие параметры занесены в БД";
}

if( isset( $_POST['dbg_refresh'] ) ) {
    echo debug_get_debug_table();
}

?>