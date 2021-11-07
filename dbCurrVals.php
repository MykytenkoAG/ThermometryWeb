<?php

require_once ('configParameters.php');
require_once('currValsFromTS.php');

//	Функция для записи значений из массива $arrayOfLevels в Базу Данных
function db_update_grainLevels($dbh, $arrayOfLevels){

	$query = "	SELECT	silo_id, silo_name, bs_addr, product_id,
						grain_level_fromTS, grain_level,
						is_square, size, position_col, position_row, silo_group
					FROM zernoib.prodtypesbysilo;";

	$sth = $dbh->query($query);
	$rows = $sth->fetchAll();

	$query = "INSERT INTO zernoib.prodtypesbysilo
			   		(silo_id, silo_name, bs_addr, product_id,
					 grain_level_fromTS, grain_level,
					 is_square, size, position_col, position_row, silo_group)
			  VALUES ";

	for($i=0; $i<count($arrayOfLevels); $i++){
				//	silo_id
		$query .= "(".$rows[$i]['silo_id'].", ";
				//	silo_name
				$query .= "'".$rows[$i]['silo_name']."', ";
				//	bs_addr
				$query .= "'".$rows[$i]['bs_addr']."', ";
				//	product_id
				$query .= "'".$rows[$i]['product_id']."', ";
				//	grain_level_fromTS
				$query .= "'".$rows[$i]['grain_level_fromTS']."', ";
				//	grain_level
				if($rows[$i]['grain_level_fromTS']==1){
					$query .= "'".$arrayOfLevels[$i]."', ";
				} else {
					$query .= "'".$rows[$i]['grain_level']."', ";
				}				
				//	is_square
				$query .= "'".$rows[$i]['is_square']."', ";
				//	size
				$query .= "'".$rows[$i]['size']."', ";
				//	position_col
				$query .= "'".$rows[$i]['position_col']."', ";
				//	position_row
				$query .= "'".$rows[$i]['position_row']."', ";
				//	silo_group
				$query .= "'".$rows[$i]['silo_group']."'),";
	}

	$query = substr($query,0,-1)
	." ON DUPLICATE KEY UPDATE	silo_id=VALUES(silo_id),
								silo_name=VALUES(silo_name),
								bs_addr=VALUES(bs_addr),
								product_id=VALUES(product_id),
								grain_level_fromTS=VALUES(grain_level_fromTS),
								grain_level=VALUES(grain_level),
								is_square=VALUES(is_square),
								size=VALUES(size),
								position_col=VALUES(position_col),
								position_row=VALUES(position_row),
								silo_group=VALUES(silo_group);";
	$stmt = $dbh->prepare($query);
	$stmt->execute();
	
	return;
}

//	Функция для записи значений из массивов $arrayOfTemperatures и $arrayOfTempSpeeds в Базу Данных от времени $serverDate
function db_update_temperaturesAndSpeeds($dbh, $arrayOfTemperatures, $arrayOfTempSpeeds, $serverDate){

	$query = "	SELECT  s.sensor_id, s.silo_id, s.podv_id, s.sensor_num, s.is_enabled, s.current_temperature, s.current_speed,
						s.curr_t_text, s.curr_v_text, s.curr_t_colour, s.curr_v_colour, s.server_date,
						s.NACK_Tmax, s.TIME_NACK_Tmax, s.ACK_Tmax, s.TIME_ACK_Tmax, s.NACK_Vmax, s.TIME_NACK_Vmax, s.ACK_Vmax, s.TIME_ACK_Vmax,
						s.NACK_err, s.TIME_NACK_err, s.ACK_err, s.TIME_ACK_err, s.error_id,
						pbs.grain_level, pr.t_min, pr.t_max, pr.v_min, pr.v_max, e.error_desc_short
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

	$query = "INSERT INTO zernoib.sensors
			   (sensor_id, silo_id, podv_id, sensor_num, is_enabled, current_temperature, current_speed,
				curr_t_text, curr_v_text, curr_t_colour, curr_v_colour, server_date,
				NACK_Tmax, TIME_NACK_Tmax, ACK_Tmax, TIME_ACK_Tmax, NACK_Vmax, TIME_NACK_Vmax, ACK_Vmax, TIME_ACK_Vmax,
				NACK_err, TIME_NACK_err, ACK_err, TIME_ACK_err, error_id)
			  VALUES ";

	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTemperatures); $i++){
		for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
			for($k = 0; $k < count($arrayOfTemperatures[$i][$j]); $k++){

				//	sensor_id
				$query .= "(".$rows[$sensor_id]['sensor_id'].", ";
				//	silo_id
				$query .= "'".$rows[$sensor_id]['silo_id']."', ";
				//	podv_id
				$query .= "'".$rows[$sensor_id]['podv_id']."', ";
				//	sensor_num
				$query .= "'".$rows[$sensor_id]['sensor_num']."', ";
				//	is_enabled
				$query .= "'".$rows[$sensor_id]['is_enabled']."', ";
				//	current_temperature
				$query .= "'". ($arrayOfTemperatures[$i][$j][$k] * 0.1) ."', ";
				//	current_speed
				$current_temperature_speed = str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k]);
				$query .= "'". $current_temperature_speed ."', ";
				//	curr_t_text
				$curr_t_text = "''";

				if($rows[$sensor_id]['is_enabled']==0){

					$curr_t_text = "'откл.'";
					//$curr_t_text = "'off.'";

				} else if ($arrayOfTemperatures[$i][$j][$k] < 850 ){

					$curr_t_text = sprintf('\'%01.1f\'', $arrayOfTemperatures[$i][$j][$k] * 0.1);

				} else {
					$curr_t_text = "'".$rows[$sensor_id]['error_desc_short']."'";
				}
				$query .= $curr_t_text .", ";
				//	curr_v_text
				$curr_v_text = "''";

				if($rows[$sensor_id]['is_enabled']==0){

					$curr_v_text = "'откл.'";
					//$curr_v_text = "'off.'";

				} else if($arrayOfTemperatures[$i][$j][$k] < 850 ){

					$curr_v_text = sprintf('\'%01.1f\'', $arrayOfTempSpeeds[$i][$j][$k]);

				} else {
					$curr_v_text = "'".$rows[$sensor_id]['error_desc_short']."'";
				}
				$query .= $curr_v_text.", ";
				//	curr_t_colour
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
				$query .= $curr_t_colour.", ";
				//	curr_v_colour
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
				$query .= $curr_v_colour.", ";
				//	server_date
				$query .= "STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'), ";
				//	NACK_Tmax
				$query_NACK_Tmax = is_null($rows[$sensor_id]['NACK_Tmax']) ? "NULL, " : "'".$rows[$sensor_id]['NACK_Tmax']."', ";
				$query .= $query_NACK_Tmax;
				//	TIME_NACK_Tmax
				$query_TIME_NACK_Tmax = is_null($rows[$sensor_id]['TIME_NACK_Tmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_NACK_Tmax']."', ";
				$query .= $query_TIME_NACK_Tmax;
				//	ACK_Tmax
				$query_ACK_Tmax = is_null($rows[$sensor_id]['ACK_Tmax']) ? "NULL, " : "'".$rows[$sensor_id]['ACK_Tmax']."', ";
				$query .= $query_ACK_Tmax;
				//	TIME_ACK_Tmax
				$query_TIME_ACK_Tmax = is_null($rows[$sensor_id]['TIME_ACK_Tmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_ACK_Tmax']."', ";
				$query .= $query_TIME_ACK_Tmax;
				//	NACK_Vmax
				$query_TIME_NACK_Vmax = is_null($rows[$sensor_id]['NACK_Vmax']) ? "NULL, " : "'".$rows[$sensor_id]['NACK_Vmax']."', ";
				$query .= $query_TIME_NACK_Vmax;
				//	TIME_NACK_Vmax
				$query_TIME_NACK_Vmax = is_null($rows[$sensor_id]['TIME_NACK_Vmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_NACK_Vmax']."', ";
				$query .= $query_TIME_NACK_Vmax;
				//	ACK_Vmax
				$query_ACK_Vmax = is_null($rows[$sensor_id]['ACK_Vmax']) ? "NULL, " : "'".$rows[$sensor_id]['ACK_Vmax']."', ";
				$query .= $query_ACK_Vmax;
				//	TIME_ACK_Vmax
				$query_TIME_ACK_Vmax = is_null($rows[$sensor_id]['TIME_ACK_Vmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_ACK_Vmax']."', ";
				$query .= $query_TIME_ACK_Vmax;
				//	NACK_err
				$query_NACK_err = is_null($rows[$sensor_id]['NACK_err']) ? "NULL, " : "'".$rows[$sensor_id]['NACK_err']."', ";
				$query .= $query_NACK_err;
				//	TIME_NACK_err
				$query_TIME_NACK_err = is_null($rows[$sensor_id]['TIME_NACK_err']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_NACK_err']."', ";
				$query .= $query_TIME_NACK_err;
				//	ACK_err
				$query_ACK_err = is_null($rows[$sensor_id]['ACK_err']) ? "NULL, " : "'".$rows[$sensor_id]['ACK_err']."', ";
				$query .= $query_ACK_err;
				//	TIME_ACK_err
				$query_TIME_ACK_err = is_null($rows[$sensor_id]['TIME_ACK_err']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_ACK_err']."', ";
				$query .= $query_TIME_ACK_err;
				//	error_id
				$query_error_id = is_null($rows[$sensor_id]['error_id']) ? "NULL)," : "'".$rows[$sensor_id]['error_id']."'),";
				$query .= $query_error_id;

				$sensor_id++;
			}
		}
	}

	$query = substr($query,0,-1)
			." ON DUPLICATE KEY UPDATE	sensor_id=VALUES(sensor_id),
										silo_id=VALUES(silo_id),
										podv_id=VALUES(podv_id),
										sensor_num=VALUES(sensor_num),
										is_enabled=VALUES(is_enabled),
										current_temperature=VALUES(current_temperature),
										current_speed=VALUES(current_speed),
										curr_t_text=VALUES(curr_t_text),
										curr_v_text=VALUES(curr_v_text),
										curr_t_colour=VALUES(curr_t_colour),
										curr_v_colour=VALUES(curr_v_colour),
										server_date=VALUES(server_date),
										NACK_Tmax=VALUES(NACK_Tmax),
										TIME_NACK_Tmax=VALUES(TIME_NACK_Tmax),
										ACK_Tmax=VALUES(ACK_Tmax),
										TIME_ACK_Tmax=VALUES(TIME_ACK_Tmax),
										NACK_Vmax=VALUES(NACK_Vmax),
										TIME_NACK_Vmax=VALUES(TIME_NACK_Vmax),
										ACK_Vmax=VALUES(ACK_Vmax),
										TIME_ACK_Vmax=VALUES(TIME_ACK_Vmax),
										NACK_err=VALUES(NACK_err),
										TIME_NACK_err=VALUES(TIME_NACK_err),
										ACK_err=VALUES(ACK_err),
										TIME_ACK_err=VALUES(TIME_ACK_err),
										error_id=VALUES(error_id);";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

?>