<?php
//	Включение/Отключение датчиков
//	Вызов только по команде пользователя
function sensorEnable($silo_id, $podv_id, $sensor_num){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=1 WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function sensorDisable($silo_id, $podv_id, $sensor_num){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=0 WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function podvEnable($silo_id, $podv_id){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=1 WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function podvDisable($silo_id, $podv_id){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=0 WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function enableAllSensors(){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=1";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function disableAllDefectiveSensors(){

	global $dbh;
	
	$query="UPDATE sensors SET is_enabled=0 WHERE current_temperature > 84";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

?>