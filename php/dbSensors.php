<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/php/currValsFromTS.php');

function writeCurrentReadingsToDB(){

	global $dbh;
	global $arrayOfTemperatures; global $arrayOfTempSpeeds; global $serverDate;

	/*
		Реализуем запись типа:
			UPDATE sensors
					SET current_temperature = (CASE WHEN sensor_id=0 THEN 18.5
													WHEN sensor_id=1 THEN 19
													...
													END),
							current_speed = (CASE WHEN sensor_id=0 THEN 1
													WHEN sensor_id=1 THEN 2
													...
													END),
						server_date = STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s') WHERE sensor_id BETWEEN 0 AND $sensor_id;
	*/

	$query = "	SELECT s.sensor_id, s.current_temperature, pr.t_min, pr.t_max, pr.v_min, pr.v_max, e.error_desc_short
					FROM sensors AS s
					JOIN prodtypesbysilo AS pbs
						ON s.silo_id = pbs.silo_id
					JOIN prodtypes AS pr
						ON pbs.product_id = pr.product_id
					LEFT JOIN errors AS e
						ON s.error_id = e.error_id ";

	$sth = $dbh->query($query);
	$rows = $sth->fetchAll();

	$query="UPDATE sensors SET current_temperature = ( CASE ";

	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTemperatures); $i++){
		for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
			for($k = 0; $k < count($arrayOfTemperatures[$i][$j]); $k++){
						$query.="WHEN sensor_id = ".$sensor_id." THEN ".$arrayOfTemperatures[$i][$j][$k] * 0.1 ." ";
						$sensor_id++;
			}
		}
	}

	$query.=" END), current_speed = ( CASE ";
	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTempSpeeds); $i++){
		for($j = 0; $j < count($arrayOfTempSpeeds[$i]); $j++){
			for($k = 0; $k < count($arrayOfTempSpeeds[$i][$j]); $k++){
						$current_temperature_speed = str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k]);
						$query.="WHEN sensor_id = ".$sensor_id." THEN '".$current_temperature_speed."' ";
						$sensor_id++;
			}
		}
	}
	//	Текст, отображаемый в ячейке
	$query.=" END), curr_t_text = ( CASE ";
	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTemperatures); $i++){
		for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
			for($k = 0; $k < count($arrayOfTemperatures[$i][$j]); $k++){

				$curr_t_text = "''";

				if($arrayOfTemperatures[$i][$j][$k] < 850 ){

					$curr_t_text = sprintf('\'%01.1f\'', $arrayOfTemperatures[$i][$j][$k] * 0.1);

				} else {
					$curr_t_text = "'".$rows[$sensor_id]['error_desc_short']."'";
				}

				$query.="WHEN sensor_id = ".$sensor_id." THEN "
				.$curr_t_text." ";
				$sensor_id++;
			}
		}
	}

	$query.=" END), curr_v_text = ( CASE ";
	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTempSpeeds); $i++){
		for($j = 0; $j < count($arrayOfTempSpeeds[$i]); $j++){
			for($k = 0; $k < count($arrayOfTempSpeeds[$i][$j]); $k++){

				$curr_v_text = sprintf('\'%01.1f\'', $arrayOfTempSpeeds[$i][$j][$k]);

				$query.="WHEN sensor_id = ".$sensor_id." THEN "
				.$curr_v_text." ";
				$sensor_id++;
			}
		}
	}

	//	Определение цвета для ячейки с текущей температурой
	$query.=" END), curr_t_colour = ( CASE ";
	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTemperatures); $i++){
		for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
			for($k = 0; $k < count($arrayOfTemperatures[$i][$j]); $k++){

				$curr_t_colour="'#FFFFFF'";

				if($arrayOfTemperatures[$i][$j][$k] < 850 ){

					$green = ($rows[$sensor_id]['t_max'] - $arrayOfTemperatures[$i][$j][$k] * 0.1) / ($rows[$sensor_id]['t_max'] - $rows[$sensor_id]['t_min']) * 255;
					if($green>255){
						$green=255;
					} elseif ($green < 0) {
						$green = 0;
					}

					$red = (1 - ($rows[$sensor_id]['t_max'] - $arrayOfTemperatures[$i][$j][$k] * 0.1) / ($rows[$sensor_id]['t_max'] - $rows[$sensor_id]['t_min'])) * 255;
					if($red > 255){
						$red = 255;
					} elseif ($red < 0) {
						$red = 0;
					}

					$curr_t_colour=sprintf('\'#%02X%02X00\'',$red, $green);

				} else if(	$arrayOfTemperatures[$i][$j][$k]==850 or
							$arrayOfTemperatures[$i][$j][$k]==1270 or
							$arrayOfTemperatures[$i][$j][$k]==2510 or
							$arrayOfTemperatures[$i][$j][$k]==2520 or
							$arrayOfTemperatures[$i][$j][$k]==2520 or
							$arrayOfTemperatures[$i][$j][$k]==2530 or
							$arrayOfTemperatures[$i][$j][$k]==2540){

					$curr_t_colour="'#FF0000'";
				}

				$query.="WHEN sensor_id = ".$sensor_id." THEN "
				.$curr_t_colour." ";
				$sensor_id++;
			}
		}
	}

	$query.=" END), curr_v_colour = ( CASE ";
	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTempSpeeds); $i++){
		for($j = 0; $j < count($arrayOfTempSpeeds[$i]); $j++){
			for($k = 0; $k < count($arrayOfTempSpeeds[$i][$j]); $k++){

				$curr_v_colour="'#FFFFFF'";

				$green = ($rows[$sensor_id]['v_max'] - str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k])) / ($rows[$sensor_id]['v_max'] - $rows[$sensor_id]['v_min']) * 255;
				if($green>255){
					$green=255;
				} elseif ($green < 0) {
					$green = 0;
				}

				$red = (1 - ($rows[$sensor_id]['v_max'] - str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k])) / ($rows[$sensor_id]['v_max'] - $rows[$sensor_id]['v_min'])) * 255;
				if($red > 255){
					$red = 255;
				} elseif ($red < 0) {
					$red = 0;
				}

				$curr_v_colour=sprintf('\'#%02X%02X00\'',$red, $green);

				$query.="WHEN sensor_id = ".$sensor_id." THEN "
				.$curr_v_colour." ";
				$sensor_id++;
			}
		}
	}

	$sensor_id--;
	$query.=" END), server_date = STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s') WHERE sensor_id BETWEEN 0 AND $sensor_id;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

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