<?php

require_once ('configParameters.php');

//	Функция для записи значений из массива $arrayOfLevels в Базу Данных
function db_update_grainLevels($dbh, $arrayOfLevels){
	//	Определяем, есть ли силоса с автоматическим определением уровня
	$query = "SELECT count(silo_id) FROM prodtypesbysilo WHERE grain_level_fromTS=1";
	$sth = $dbh->query($query);

	if($sth->fetch()['count(silo_id)']==0){				
		return;
	}
	//	Если есть, обновляем значения из $arrayOfLevels
	$query = "SELECT silo_id, grain_level_fromTS
				FROM prodtypesbysilo;";

	$sth = $dbh->query($query);
	$rows = $sth->fetchAll();

	$silo_id_arr=array();

	$query="UPDATE prodtypesbysilo SET grain_level = (CASE ";

	for($i=0;$i<count($rows);$i++){
		if($rows[$i]['grain_level_fromTS']==1){
			$query.="WHEN silo_id = ".$i." THEN ".$arrayOfLevels[$i]." ";
			array_push($silo_id_arr,$i);
		}
	}

	$query.=" END) WHERE silo_id IN (".implode(",", $silo_id_arr).");";

	$stmt = $dbh->prepare($query);
	$stmt->execute();
	
	return;
}
//	Функция для записи значений из массивов $arrayOfTemperatures и $arrayOfTempSpeeds в Базу Данных от времени $serverDate
function db_update_temperaturesAndSpeeds($dbh, $arrayOfTemperatures, $arrayOfTempSpeeds, $serverDate){

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

	$query = "	SELECT s.sensor_id, s.is_enabled, pbs.grain_level, s.current_temperature, pr.t_min, pr.t_max, pr.v_min, pr.v_max, e.error_desc_short
					FROM sensors AS s
					JOIN prodtypesbysilo AS pbs
						ON s.silo_id = pbs.silo_id
					JOIN prodtypes AS pr
						ON pbs.product_id = pr.product_id
					LEFT JOIN errors AS e
						ON s.error_id = e.error_id 
					ORDER BY s.sensor_id;";

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

				if($rows[$sensor_id]['is_enabled']==0){

					$curr_t_text = "'off'";

				} else if ($arrayOfTemperatures[$i][$j][$k] < 850 ){

					$curr_t_text = sprintf('\'%01.1f\'', $arrayOfTemperatures[$i][$j][$k] * 0.1);

				} else {
					$curr_t_text = "'".$rows[$sensor_id]['error_desc_short']."'";
				}

				$query.="WHEN sensor_id = ".$sensor_id." THEN "
				."$curr_t_text"." ";
				$sensor_id++;
			}
		}
	}

	$query.=" END), curr_v_text = ( CASE ";
	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTempSpeeds); $i++){
		for($j = 0; $j < count($arrayOfTempSpeeds[$i]); $j++){
			for($k = 0; $k < count($arrayOfTempSpeeds[$i][$j]); $k++){

				$curr_v_text = "''";

				if($rows[$sensor_id]['is_enabled']==0){

					$curr_v_text = "'off'";

				} else if($arrayOfTemperatures[$i][$j][$k] < 850 ){

					$curr_v_text = sprintf('\'%01.1f\'', $arrayOfTempSpeeds[$i][$j][$k]);

				} else {
					$curr_v_text = "'".$rows[$sensor_id]['error_desc_short']."'";
				}
				

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

				$curr_t_colour="'#E5E5E5'";

				if($rows[$sensor_id]['is_enabled']==0){

					$curr_t_colour="'#616161'";

				} else if($arrayOfTemperatures[$i][$j][$k] < 850 and $k<$rows[$sensor_id]['grain_level']){

					$green = ($rows[$sensor_id]['t_max'] - $arrayOfTemperatures[$i][$j][$k] * 0.1) / ($rows[$sensor_id]['t_max'] - $rows[$sensor_id]['t_min']) * 255;
					if($green>255){
						$green=255;
					} else if ($green < 0) {
						$green = 0;
					}

					$red = (1 - ($rows[$sensor_id]['t_max'] - $arrayOfTemperatures[$i][$j][$k] * 0.1) / ($rows[$sensor_id]['t_max'] - $rows[$sensor_id]['t_min'])) * 255;
					if($red > 255){
						$red = 255;
					} else if ($red < 0) {
						$red = 0;
					}

					$curr_t_colour=sprintf('\'#%02X%02X00\'',$red, $green);

				} else if( in_array($arrayOfTemperatures[$i][$j][$k],array(850,1270,2510,2520,2530,2540))){

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

				$curr_v_colour="'#E5E5E5'";

				if($rows[$sensor_id]['is_enabled']==0){

					$curr_v_colour="'#616161'";

				} else if($arrayOfTemperatures[$i][$j][$k] < 850 and $k<$rows[$sensor_id]['grain_level']){

					$green = ($rows[$sensor_id]['v_max'] - str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k])) / ($rows[$sensor_id]['v_max'] - $rows[$sensor_id]['v_min']) * 255;
					if($green>255){
						$green=255;
					} else if ($green < 0) {
						$green = 0;
					}

					$red = (1 - ($rows[$sensor_id]['v_max'] - str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k])) / ($rows[$sensor_id]['v_max'] - $rows[$sensor_id]['v_min'])) * 255;
					if($red > 255){
						$red = 255;
					} else if ($red < 0) {
						$red = 0;
					}

					$curr_v_colour=sprintf('\'#%02X%02X00\'',$red, $green);

				} else if( in_array($arrayOfTemperatures[$i][$j][$k],array(850,1270,2510,2520,2530,2540))){
					$curr_v_colour="'#FF0000'";
				}

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

	return $query;
	return;
}

?>