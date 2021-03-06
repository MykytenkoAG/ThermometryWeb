<?php
//  Данный скрипт вызывается 1 раз в 30 минут в фоне для записи показаний температуры в базу данных
require_once(__DIR__.'/php/ts/currValsFromTS.php');

add_new_measurement($dbh, $arrayOfTemperatures, $serverDate);

function add_new_measurement($dbh, $arrayOfTemperatures,$serverDate){

	$query="INSERT INTO dates (date) VALUES (STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'));";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    $query = "SELECT date_id FROM ".DBNAME.".dates ORDER BY date_id DESC LIMIT 1";
    $sth = $dbh->query($query);
	$last_date_id=($sth->fetchAll())[0]['date_id'];									//	Выбираем id последней даты

	$query="INSERT INTO measurements (date_id, sensor_id, temperature) VALUES ";	//	Записываем измеренные температуры в базу

	$sensor_id = 0;

	for($i = 0; $i < count($arrayOfTemperatures); $i++){
		for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
			for($k = 0; $k < count($arrayOfTemperatures[$i][$j]); $k++){
				$currentTemperature = $arrayOfTemperatures[$i][$j][$k]<850 ? $arrayOfTemperatures[$i][$j][$k] * 0.1: "NULL" ;
				$query.="(".$last_date_id.","."'".$sensor_id."'".",".$currentTemperature."),";
				$sensor_id++;
			}
		}
	}

	$query = substr($query,0,-1).";";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

?>