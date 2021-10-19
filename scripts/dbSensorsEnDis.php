<?php

function sensorDisable($silo_id, $podv_id, $sensor_num){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=0 WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['sensor_disable_silo_id']) && isset($_POST['sensor_disable_podv_num']) && isset($_POST['sensor_disable_sensor_num']) ) {
	
}

function sensorEnable($silo_id, $podv_id, $sensor_num){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=1 WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['sensor_enable_silo_id']) && isset($_POST['sensor_enable_podv_num']) && isset($_POST['sensor_enable_sensor_num']) ) {
	
}

function podvDisable($silo_id, $podv_id){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=0 WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['podv_disable_silo_id']) && isset($_POST['podv_disable_podv_num']) ) {
	
}

function podvEnable($silo_id, $podv_id){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=1 WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['podv_enable_silo_id']) && isset($_POST['podv_enable_podv_num']) ) {
	
}

function disableAllDefectiveSensors(){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=0 WHERE current_temperature > 84";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['disable_all_defective_sensors']) ) {
	
}

function enableAllSensors(){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=1";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['enable_all_sensors']) ) {
	
}

?>