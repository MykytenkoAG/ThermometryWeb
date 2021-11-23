<?php

//	Запись строки в журнал
function writeToLog($logFile, $loggingString){
    // Write the contents to the file, 
    // using the FILE_APPEND flag to append the content to the end of the file
    // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
    @file_put_contents($logFile, $loggingString, FILE_APPEND | LOCK_EX);
    return;
}
//	Очистка журнала
function logClear($logFile){
    file_put_contents($logFile, "");
    return;
}
//	Функция для записи значений из массива $arrayOfLevels в Базу Данных
function db_update_grainLevels($dbh, $arrayOfLevels){

	$query = "	SELECT	silo_id, silo_name, bs_addr, product_id,
						grain_level_fromTS, grain_level,
						is_square, size, position_col, position_row, silo_group
					FROM ".DBNAME.".prodtypesbysilo;";

	$sth = $dbh->query($query);
	$rows = $sth->fetchAll();

	$query = "INSERT INTO ".DBNAME.".prodtypesbysilo
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
//	Функция для обновления значений ассоциативного массива с текущими сигналами АПС
function db_update_curr_alarm_state($dbh){

    $outArr = array();

    //  Определяем проблемы, связанные с работой БС
    $sql = "SELECT	s.silo_id, pbs.silo_name,
					COUNT(s.sensor_id) AS count_sensor_id,
					SUM(IF(s.error_id = 253, 1, 0)) AS silo_sum_err_253,
					SUM(IF(s.error_id = 254, 1, 0)) AS silo_sum_err_254,
					SUM(IF(s.error_id = 256, 1, 0)) AS silo_sum_err_256,
					s.NACK_err, DATE_FORMAT(s.TIME_NACK_err,'%d.%m.%Y %H:%i:%s') AS f_TIME_NACK_err,
					s.ACK_err,  DATE_FORMAT(s.TIME_ACK_err,'%d.%m.%Y %H:%i:%s')  AS f_TIME_ACK_err,
					s.RST_err,  DATE_FORMAT(s.TIME_RST_err,'%d.%m.%Y %H:%i:%s')  AS f_TIME_RST_err,
					s.error_id, e.error_description, e.error_desc_for_visu
            FROM sensors AS s
            INNER JOIN prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id 
            LEFT JOIN errors AS e ON s.error_id = e.error_id
            GROUP BY silo_id
            HAVING (count_sensor_id=silo_sum_err_253 OR
                    count_sensor_id=silo_sum_err_254 OR
                    count_sensor_id=silo_sum_err_256);";

    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $defected_silo_rows = $sth->fetchAll();

	$defected_silo = array();
    foreach($defected_silo_rows as $defected_silo_row){

		$outArr[] = array(  'silo_id'				=>	$defected_silo_row["silo_id"],
							'silo_name'				=>	$defected_silo_row["silo_name"],
							'podv_id'				=>	"",
							'sensor_num'			=>	"",
							'is_enabled'			=>	"",

							'NACK_Tmax'				=>	"",
							'TIME_NACK_Tmax'		=>	"",
							'ACK_Tmax'				=>	"",
							'TIME_ACK_Tmax'			=>	"",
							'RST_Tmax'				=>	"",
							'TIME_RST_Tmax'			=>	"",

							'NACK_Vmax'        		=>	"",
							'TIME_NACK_Vmax'        =>	"",
							'ACK_Vmax'        		=>	"",
							'TIME_ACK_Vmax'        	=>	"",
							'RST_Vmax'        		=>	"",
							'TIME_RST_Vmax'        	=>	"",
							
							'NACK_err'        		=>	$defected_silo_row["NACK_err"],
							'TIME_NACK_err'        	=>	$defected_silo_row["f_TIME_NACK_err"],
							'ACK_err'        		=>	$defected_silo_row["ACK_err"],
							'TIME_ACK_err'        	=>	$defected_silo_row["f_TIME_ACK_err"],
							'RST_err'        		=>	$defected_silo_row["RST_err"],
							'TIME_RST_err'        	=>	$defected_silo_row["f_TIME_RST_err"],

							'error_id'				=>	$defected_silo_row["error_id"],
							'error_description'		=>	$defected_silo_row["error_description"],
							'error_desc_for_visu'	=>	$defected_silo_row["error_desc_for_visu"]	);

        array_push($defected_silo, $defected_silo_row["silo_name"]);
        
    }

    //  Определяем проблемы, связанные с работой ТП
    $sql = "SELECT	s.silo_id, pbs.silo_name, s.podv_id,
					COUNT(sensor_id) AS count_sensor_id,
					SUM(IF(s.error_id = 251, 1, 0)) AS silo_sum_err_251,
					SUM(IF(s.error_id = 252, 1, 0)) AS silo_sum_err_252,
					s.NACK_err, DATE_FORMAT(s.TIME_NACK_err,'%d.%m.%Y %H:%i:%s') AS f_TIME_NACK_err,
					s.ACK_err,  DATE_FORMAT(s.TIME_ACK_err,'%d.%m.%Y %H:%i:%s')  AS f_TIME_ACK_err,
					s.RST_err,  DATE_FORMAT(s.TIME_RST_err,'%d.%m.%Y %H:%i:%s')  AS f_TIME_RST_err,
					s.error_id, e.error_description, e.error_desc_for_visu
            FROM sensors AS s
            INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
            LEFT JOIN errors AS e ON s.error_id = e.error_id
            GROUP BY silo_id, podv_id
            HAVING (count_sensor_id=silo_sum_err_251 OR
                    count_sensor_id=silo_sum_err_252);";

    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $defected_podv_rows = $sth->fetchAll();

	$defected_podv = array();
    foreach($defected_podv_rows as $defected_podv_row){

		$outArr[] = array(  'silo_id'				=>	$defected_podv_row["silo_id"],
							'silo_name'				=>	$defected_podv_row["silo_name"],
							'podv_id'				=>	$defected_podv_row["podv_id"]+1,
							'sensor_num'			=>	"",
							'is_enabled'			=>	"",

							'NACK_Tmax'				=>	"",
							'TIME_NACK_Tmax'		=>	"",
							'ACK_Tmax'				=>	"",
							'TIME_ACK_Tmax'			=>	"",
							'RST_Tmax'				=>	"",
							'TIME_RST_Tmax'			=>	"",

							'NACK_Vmax'        		=>	"",
							'TIME_NACK_Vmax'        =>	"",
							'ACK_Vmax'        		=>	"",
							'TIME_ACK_Vmax'        	=>	"",
							'RST_Vmax'        		=>	"",
							'TIME_RST_Vmax'        	=>	"",
							
							'NACK_err'        		=>	$defected_podv_row["NACK_err"],
							'TIME_NACK_err'        	=>	$defected_podv_row["f_TIME_NACK_err"],
							'ACK_err'        		=>	$defected_podv_row["ACK_err"],
							'TIME_ACK_err'        	=>	$defected_podv_row["f_TIME_ACK_err"],
							'RST_err'        		=>	$defected_podv_row["RST_err"],
							'TIME_RST_err'        	=>	$defected_podv_row["f_TIME_RST_err"],

							'error_id'				=>	$defected_podv_row["error_id"],
							'error_description'		=>	$defected_podv_row["error_description"],
							'error_desc_for_visu'	=>	$defected_podv_row["error_desc_for_visu"]	);

        array_push($defected_podv, strval($defected_podv_row["silo_name"]).",".strval($defected_podv_row["podv_id"]));
    }

    //  Определяем алармы датчиков
    $sql = "SELECT  s.sensor_id, s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num, s.is_enabled,
                    s.NACK_Tmax, DATE_FORMAT(s.TIME_NACK_Tmax,'%d.%m.%Y %H:%i:%s') AS f_TIME_NACK_Tmax,
					s.ACK_Tmax,  DATE_FORMAT(s.TIME_ACK_Tmax,'%d.%m.%Y %H:%i:%s')  AS f_TIME_ACK_Tmax,
					s.RST_Tmax,  DATE_FORMAT(s.TIME_RST_Tmax,'%d.%m.%Y %H:%i:%s')  AS f_TIME_RST_Tmax,
                    s.NACK_Vmax, DATE_FORMAT(s.TIME_NACK_Vmax,'%d.%m.%Y %H:%i:%s') AS f_TIME_NACK_Vmax,
					s.ACK_Vmax,  DATE_FORMAT(s.TIME_ACK_Vmax,'%d.%m.%Y %H:%i:%s')  AS f_TIME_ACK_Vmax,
					s.RST_Vmax,  DATE_FORMAT(s.TIME_RST_Vmax,'%d.%m.%Y %H:%i:%s')  AS f_TIME_RST_Vmax,
                    s.NACK_err,  DATE_FORMAT(s.TIME_NACK_err,'%d.%m.%Y %H:%i:%s')  AS f_TIME_NACK_err,
					s.ACK_err,   DATE_FORMAT(s.TIME_ACK_err,'%d.%m.%Y %H:%i:%s')   AS f_TIME_ACK_err,
					s.RST_err,   DATE_FORMAT(s.TIME_RST_err,'%d.%m.%Y %H:%i:%s')   AS f_TIME_RST_err,
                    s.error_id, e.error_description, e.error_desc_for_visu
			FROM sensors AS s
				LEFT JOIN errors AS e ON s.error_id=e.error_id
				INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
			WHERE s.NACK_Tmax=1 OR s.ACK_Tmax=1 OR s.RST_Tmax=1 OR s.NACK_Vmax=1 OR s.ACK_Vmax=1 OR s.RST_Vmax=1 OR s.NACK_err=1 OR s.ACK_err=1 OR s.RST_err=1;";
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $sensor_alarm_rows = $sth->fetchAll();

    foreach($sensor_alarm_rows as $sensor_alarm_row){

        if( in_array($sensor_alarm_row["silo_name"], $defected_silo) ){
            continue;
        }

        if( in_array($sensor_alarm_row["silo_name"].",".$sensor_alarm_row["podv_id"], $defected_podv) ){
            continue;
        }

		$outArr[] = array(  'silo_id'				=>	$sensor_alarm_row["silo_id"],
							'silo_name'				=>	$sensor_alarm_row["silo_name"],
							'podv_id'				=>	$sensor_alarm_row["podv_id"]+1,
							'sensor_num'			=>	$sensor_alarm_row["sensor_num"]+1,
							'is_enabled'			=>	$sensor_alarm_row["is_enabled"],

							'NACK_Tmax'				=>	$sensor_alarm_row["NACK_Tmax"],
							'TIME_NACK_Tmax'		=>	$sensor_alarm_row["f_TIME_NACK_Tmax"],
							'ACK_Tmax'				=>	$sensor_alarm_row["ACK_Tmax"],
							'TIME_ACK_Tmax'			=>	$sensor_alarm_row["f_TIME_ACK_Tmax"],
							'RST_Tmax'				=>	$sensor_alarm_row["RST_Tmax"],
							'TIME_RST_Tmax'			=>	$sensor_alarm_row["f_TIME_RST_Tmax"],

							'NACK_Vmax'        		=>	$sensor_alarm_row["NACK_Vmax"],
							'TIME_NACK_Vmax'        =>	$sensor_alarm_row["f_TIME_NACK_Vmax"],
							'ACK_Vmax'        		=>	$sensor_alarm_row["ACK_Vmax"],
							'TIME_ACK_Vmax'        	=>	$sensor_alarm_row["f_TIME_ACK_Vmax"],
							'RST_Vmax'        		=>	$sensor_alarm_row["RST_Vmax"],
							'TIME_RST_Vmax'        	=>	$sensor_alarm_row["f_TIME_RST_Vmax"],
							
							'NACK_err'        		=>	$sensor_alarm_row["NACK_err"],
							'TIME_NACK_err'        	=>	$sensor_alarm_row["f_TIME_NACK_err"],
							'ACK_err'        		=>	$sensor_alarm_row["ACK_err"],
							'TIME_ACK_err'        	=>	$sensor_alarm_row["f_TIME_ACK_err"],
							'RST_err'        		=>	$sensor_alarm_row["RST_err"],
							'TIME_RST_err'        	=>	$sensor_alarm_row["f_TIME_RST_err"],

							'error_id'				=>	$sensor_alarm_row["error_id"],
							'error_description'		=>	$sensor_alarm_row["error_description"],
							'error_desc_for_visu'	=>	$sensor_alarm_row["error_desc_for_visu"]	);

    }

    //  Сортируем выходной ассоциативный массив по времени появления сигнала АПС
	$c_TIME_NACK_err = array_column($outArr, "TIME_NACK_err");
	$c_TIME_NACK_Tmax = array_column($outArr, "TIME_NACK_Tmax");
	$c_TIME_NACK_Vmax = array_column($outArr, "TIME_NACK_Vmax");

    array_multisort($c_TIME_NACK_err, SORT_ASC, $c_TIME_NACK_Tmax, SORT_ASC, $c_TIME_NACK_Vmax, SORT_ASC, $outArr);

	return $outArr;

}
//	Отправка Телеграм уведомлений
function send_Telegram_notifications($dbh, $alarmStateArray, $serverDate){

	$notificationMessage="";
	$notificationMessageCount = 0;

	foreach($alarmStateArray as $alarmState){

		if($alarmState["TIME_NACK_err"]===$serverDate){

			$notificationMessage .= "Силос ".$alarmState["silo_name"].". ";
			$notificationMessage .= $alarmState["podv_id"]==="" ? "" : "НП ".$alarmState["podv_id"].". ";
			$notificationMessage .= $alarmState["sensor_num"]==="" ? "" : "НД ".$alarmState["sensor_num"].". ";
			$notificationMessage .= $alarmState["error_description"].";%0A";
			$notificationMessageCount++;

		}

		if($alarmState["TIME_NACK_Tmax"]===$serverDate){

			$notificationMessage .= "Силос ".$alarmState["silo_name"].". ";
			$notificationMessage .= "НП ".$alarmState["podv_id"].". ";
			$notificationMessage .= "НД ".$alarmState["sensor_num"].". ";
			$notificationMessage .= "Tmax;%0A";
			$notificationMessageCount++;

		}

		if($alarmState["TIME_NACK_Vmax"]===$serverDate){

			$notificationMessage .= "Силос ".$alarmState["silo_name"].". ";
			$notificationMessage .= "НП ".$alarmState["podv_id"].". ";
			$notificationMessage .= "НД ".$alarmState["sensor_num"].". ";
			$notificationMessage .= "Vmax;%0A";
			$notificationMessageCount++;

		}

		if($notificationMessageCount>0 && $notificationMessageCount%10==0){
			$notificationMessage .= "\n";
		}

	}

	if(strlen($notificationMessage)>0){
		require_once(substr(__DIR__,0,-6)."/telegram/index.php");

		$query = "SELECT user_id FROM ".DBNAME.".telegram_users WHERE notifications_on=1;";
		$sth = $dbh->query($query);

		if($sth==false){
			return false;
		}

		$telegram_users = $sth->fetchAll();
		foreach($telegram_users as $telegram_user){
			sendMessage($telegram_user["user_id"], explode("\n",$notificationMessage));
		}
	}

	return;
}
//	
function log_events($dbh, $logFile, $serverDate, $alarmStateArray){

	$loggingString="";
	
	foreach($alarmStateArray as $alarmState){

		if( in_array( $serverDate, array($alarmState["TIME_NACK_err"], $alarmState["TIME_ACK_err"], $alarmState["TIME_RST_err"]) ) ){

			$loggingString .= "Силос ".$alarmState["silo_name"].". ";
			$loggingString .= $alarmState["podv_id"]==="" ? "" : "НП ".$alarmState["podv_id"].". ";
			$loggingString .= $alarmState["sensor_num"]==="" ? "" : "НД ".$alarmState["sensor_num"].". ";
			$loggingString .= $alarmState["error_description"].". ";

			$loggingString .= $alarmState["NACK_err"]==='1' ? "Возникновение сигнала АПС" : "";
			$loggingString .= $alarmState["ACK_err"]==='1'  ? "Подтверждение сигнала АПС" : "";
			$loggingString .= $alarmState["RST_err"]==='1'  ? "Исчезновение сигнала АПС"  : "";
			$loggingString .= ";\n";
		}

		if( in_array( $serverDate, array($alarmState["TIME_NACK_Tmax"], $alarmState["TIME_ACK_Tmax"], $alarmState["TIME_RST_Tmax"]) ) ){

			$loggingString .= "Силос ".$alarmState["silo_name"].". ";
			$loggingString .= "НП ".$alarmState["podv_id"].". ";
			$loggingString .= "НД ".$alarmState["sensor_num"].". ";
			$loggingString .= "Tmax. ";

			$loggingString .= $alarmState["NACK_Tmax"]==='1' ? "Возникновение сигнала АПС" : "";
			$loggingString .= $alarmState["ACK_Tmax"]==='1'  ? "Подтверждение сигнала АПС" : "";
			$loggingString .= $alarmState["RST_Tmax"]==='1'  ? "Исчезновение сигнала АПС"  : "";
			$loggingString .= ";\n";

		}

		if( in_array( $serverDate, array($alarmState["TIME_NACK_Vmax"], $alarmState["TIME_ACK_Vmax"], $alarmState["TIME_RST_Vmax"]) ) ){

			$loggingString .= "Силос ".$alarmState["silo_name"].". ";
			$loggingString .= "НП ".$alarmState["podv_id"].". ";
			$loggingString .= "НД ".$alarmState["sensor_num"].". ";
			$loggingString .= "Vmax. ";

			$loggingString .= $alarmState["NACK_Vmax"]==='1' ? "Возникновение сигнала АПС" : "";
			$loggingString .= $alarmState["ACK_Vmax"]==='1'  ? "Подтверждение сигнала АПС" : "";
			$loggingString .= $alarmState["RST_Vmax"]==='1'  ? "Исчезновение сигнала АПС"  : "";
			$loggingString .= ";\n";

		}

	}

	$query = "	UPDATE ".DBNAME.".sensors
					SET RST_err=0, TIME_RST_err=NULL, error_id=NULL
					WHERE RST_err=1;

				UPDATE ".DBNAME.".sensors
					SET RST_Tmax=0, TIME_RST_Tmax=NULL
					WHERE RST_Tmax=1;

				UPDATE ".DBNAME.".sensors
					SET RST_Vmax=0, TIME_RST_Vmax=NULL
					WHERE RST_Vmax=1;";

				$stmt = $dbh->prepare($query);
				$stmt->execute();

	writeToLog($logFile, $loggingString);

	return;
}

//	Функция получения массива кодов ошибок
function db_get_error_codes($dbh){

	$query = "SELECT error_id, error_description, error_desc_short, error_desc_for_visu
				FROM errors
				ORDER BY error_id;";

	$sth = $dbh->query($query);
	$error_codes = $sth->fetchAll();
	//	Создание двумерного ассоциативного массива из БД для удобства и скорости доступа к таблице "errors"
	$error_codes_arr = array();
	$j=0;
	for($i=0; $i<$error_codes[count($error_codes)-1]['error_id'];$i++){
		if($i==$error_codes[$j]['error_id']){
			array_push($error_codes_arr, $error_codes[$j]);
			$j++;
		} else {
			array_push($error_codes_arr, array());
		}
	}

	return $error_codes_arr;

}
//	Функция для записи значений из массивов $arrayOfTemperatures и $arrayOfTempSpeeds в Базу Данных от времени $serverDate
function db_update_temperaturesAndSpeeds($dbh, $arrayOfTemperatures, $arrayOfTempSpeeds, $serverDate, $logFile){

	$error_codes_arr = db_get_error_codes($dbh);

	$query = "	SELECT  s.sensor_id, s.silo_id, s.podv_id, s.sensor_num, s.is_enabled, s.current_temperature, s.current_speed,
						s.curr_t_text, s.curr_v_text, s.curr_t_colour, s.curr_v_colour, s.server_date,
						s.NACK_Tmax, s.TIME_NACK_Tmax, s.ACK_Tmax, s.TIME_ACK_Tmax, s.RST_Tmax, s.TIME_RST_Tmax,
						s.NACK_Vmax, s.TIME_NACK_Vmax, s.ACK_Vmax, s.TIME_ACK_Vmax, s.RST_Vmax, s.TIME_RST_Vmax,
						s.NACK_err, s.TIME_NACK_err, s.ACK_err, s.TIME_ACK_err, s.RST_err, s.TIME_RST_err,
						s.error_id,
						pbs.grain_level, pbs.silo_name, pr.t_min, pr.t_max, pr.v_min, pr.v_max, e.error_desc_short
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

	$query = "INSERT INTO ".DBNAME.".sensors
			   (sensor_id, silo_id, podv_id, sensor_num, is_enabled, current_temperature, current_speed,
				curr_t_text, curr_v_text, curr_t_colour, curr_v_colour, server_date,
				NACK_Tmax, TIME_NACK_Tmax, ACK_Tmax, TIME_ACK_Tmax, RST_Tmax, TIME_RST_Tmax,
				NACK_Vmax, TIME_NACK_Vmax, ACK_Vmax, TIME_ACK_Vmax, RST_Vmax, TIME_RST_Vmax,
				NACK_err, TIME_NACK_err, ACK_err, TIME_ACK_err, RST_err, TIME_RST_err,
				error_id)
			  VALUES ";

	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTemperatures); $i++){
		for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
			for($k = 0; $k < count($arrayOfTemperatures[$i][$j]); $k++){

				$query_RST_err = "'".$rows[$sensor_id]['RST_err']."', ";
				$query_TIME_RST_err = is_null($rows[$sensor_id]['TIME_RST_err']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_RST_err']."', ";

				//	error NACK
				if( $rows[$sensor_id]['is_enabled']==1 && is_null($rows[$sensor_id]['error_id']) &&
					$arrayOfTemperatures[$i][$j][$k]>=850 ){
						$query_NACK_err = "1, ";
						$query_TIME_NACK_err = "STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'), ";
						$query_RST_err = "0, ";
						$query_TIME_RST_err = "NULL, ";
						$query_error_id = "'".($arrayOfTemperatures[$i][$j][$k]*0.1)."'),";
				} else {
						$query_NACK_err = "'".$rows[$sensor_id]['NACK_err']."', ";
						$query_TIME_NACK_err = is_null($rows[$sensor_id]['TIME_NACK_err']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_NACK_err']."', ";
						$query_error_id = is_null($rows[$sensor_id]['error_id']) ? "NULL)," : "'".$rows[$sensor_id]['error_id']."'),";
				}

				//	error reset
				if( $rows[$sensor_id]['ACK_err']==1 &&
					($rows[$sensor_id]['is_enabled']==0 || $arrayOfTemperatures[$i][$j][$k]<850) ){
						$query_NACK_err = "0, ";
						$query_TIME_NACK_err = "NULL, ";
						$query_ACK_err = "0, ";
						$query_TIME_ACK_err = "NULL, ";
						$query_RST_err = "1, ";
						$query_TIME_RST_err = "STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'), ";

						//$query_error_id = "NULL),";

				} else {
						$query_ACK_err = "'".$rows[$sensor_id]['ACK_err']."', ";
						$query_TIME_ACK_err = is_null($rows[$sensor_id]['TIME_ACK_err']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_ACK_err']."', ";
						if($query_NACK_err!="1, "){
							//$query_error_id = is_null($rows[$sensor_id]['error_id']) ? "NULL)," : "'".$rows[$sensor_id]['error_id']."'),";
						}
				}


				$query_RST_Tmax = "'".$rows[$sensor_id]['RST_Tmax']."', ";
				$query_TIME_RST_Tmax = is_null($rows[$sensor_id]['TIME_RST_Tmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_RST_Tmax']."', ";

				//	Tmax NACK
				if( is_null($rows[$sensor_id]['error_id']) && $rows[$sensor_id]['is_enabled']==1 &&		//	датчик исправен и включен
					$rows[$sensor_id]['NACK_Tmax']==0 && $rows[$sensor_id]['ACK_Tmax']==0 &&			//	нет текущего аларма
					($rows[$sensor_id]['sensor_num'] < $rows[$sensor_id]['grain_level']) &&				//	датчик находится в зерне
					($arrayOfTemperatures[$i][$j][$k]<850) &&											//	датчик выдает корректные показания
					($arrayOfTemperatures[$i][$j][$k]*0.1 > $rows[$sensor_id]['t_max']) ){				//	температура выше критической для данного продукта
						$query_NACK_Tmax = "1, ";
						$query_TIME_NACK_Tmax = "STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'), ";
						$query_RST_Tmax = "0, ";
						$query_TIME_RST_Tmax = "NULL, ";

				} else {
						$query_NACK_Tmax = "'".$rows[$sensor_id]['NACK_Tmax']."', ";
						$query_TIME_NACK_Tmax = is_null($rows[$sensor_id]['TIME_NACK_Tmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_NACK_Tmax']."', ";
				}
				
				//	Tmax reset
				if( $rows[$sensor_id]['ACK_Tmax']==1 &&													//	АПС была установлена и квитирована
					( $rows[$sensor_id]['is_enabled']==0 || !is_null($rows[$sensor_id]['error_id']) ||	//	датчик вышел из строя или был отключен
						$arrayOfTemperatures[$i][$j][$k]>=850 ||											//	датчик стал выдавать некорректные показания
					($rows[$sensor_id]['sensor_num'] >= $rows[$sensor_id]['grain_level']) ||			//	датчик ниже уровня заполнения
					($arrayOfTemperatures[$i][$j][$k]*0.1 <= $rows[$sensor_id]['t_max'])  ) ){			//	температура меньше критической
						$query_NACK_Tmax = "0, ";
						$query_TIME_NACK_Tmax = "NULL, ";
						$query_ACK_Tmax = "0, ";
						$query_TIME_ACK_Tmax = "NULL, ";
						$query_RST_Tmax = "1, ";
						$query_TIME_RST_Tmax = "STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'), ";

				} else {
						$query_ACK_Tmax = "'".$rows[$sensor_id]['ACK_Tmax']."', ";
						$query_TIME_ACK_Tmax = is_null($rows[$sensor_id]['TIME_ACK_Tmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_ACK_Tmax']."', ";
				}


				$query_RST_Vmax = "'".$rows[$sensor_id]['RST_Vmax']."', ";
				$query_TIME_RST_Vmax = is_null($rows[$sensor_id]['TIME_RST_Vmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_RST_Vmax']."', ";

				//	Vmax NACK
				if(  is_null($rows[$sensor_id]['error_id']) && $rows[$sensor_id]['is_enabled']==1 &&	//	датчик исправен и включен
					 $rows[$sensor_id]['NACK_Vmax']==0 && $rows[$sensor_id]['ACK_Vmax']==0 &&			//	нет текущего аларма
					($rows[$sensor_id]['sensor_num'] < $rows[$sensor_id]['grain_level']) &&				//	датчик находится в зерне
					($arrayOfTemperatures[$i][$j][$k]<850) &&											//	датчик выдает корректные показания
					($arrayOfTempSpeeds[$i][$j][$k] > $rows[$sensor_id]['v_max']) ){					//	скорость изменения температуры выше критической
						$query_NACK_Vmax = "1, ";
						$query_TIME_NACK_Vmax = "STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'), ";
						$query_RST_Vmax = "0, ";
						$query_TIME_RST_Vmax = "NULL, ";

				} else {
						$query_NACK_Vmax = "'".$rows[$sensor_id]['NACK_Vmax']."', ";
						$query_TIME_NACK_Vmax = is_null($rows[$sensor_id]['TIME_NACK_Vmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_NACK_Vmax']."', ";
				}
				
				//	Vmax reset
				if(  $rows[$sensor_id]['ACK_Vmax']==1 &&												//	АПС была установлена и квитирована
					( $rows[$sensor_id]['is_enabled']==0 || !is_null($rows[$sensor_id]['error_id']) ||	//	датчик вышел из строя или был отключен
					  $arrayOfTemperatures[$i][$j][$k]>=850 ||											//	датчик стал выдавать некорректные показания
					($rows[$sensor_id]['sensor_num'] >= $rows[$sensor_id]['grain_level']) ||			//	датчик ниже уровня заполнения
					($arrayOfTempSpeeds[$i][$j][$k] <= $rows[$sensor_id]['v_max'])  ) ){				//	скорость меньше критической
						$query_NACK_Vmax = "0, ";
						$query_TIME_NACK_Vmax = "NULL, ";
						$query_ACK_Vmax = "0, ";
						$query_TIME_ACK_Vmax = "NULL, ";
						$query_RST_Vmax = "1, ";
						$query_TIME_RST_Vmax = "STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'), ";

				} else {
						$query_ACK_Vmax = "'".$rows[$sensor_id]['ACK_Vmax']."', ";
						$query_TIME_ACK_Vmax = is_null($rows[$sensor_id]['TIME_ACK_Vmax']) ? "NULL, " : "'".$rows[$sensor_id]['TIME_ACK_Vmax']."', ";
				}

				//	Отображение параметров в таблице

				//	Текст
				if($query_error_id!="NULL),"){

					if(isset( $error_codes_arr [ $rows[$sensor_id]['error_id'] ] ['error_desc_short'] ) ) {

						$query_curr_t_text = "'".$error_codes_arr[$rows[$sensor_id]['error_id']]['error_desc_short']."', ";
						$query_curr_v_text = "'".$error_codes_arr[$rows[$sensor_id]['error_id']]['error_desc_short']."', ";

					} else if (isset($error_codes_arr[$arrayOfTemperatures[$i][$j][$k] * 0.1]['error_desc_short'])) {
						
						$query_curr_t_text = "'".$error_codes_arr[ $arrayOfTemperatures[$i][$j][$k] * 0.1 ]['error_desc_short']."', ";
						$query_curr_v_text = "'".$error_codes_arr[ $arrayOfTemperatures[$i][$j][$k] * 0.1 ]['error_desc_short']."', ";

					}

				} else if ($rows[$sensor_id]['is_enabled']==0) {
					$query_curr_t_text = "'откл.', ";
					$query_curr_v_text = "'откл.', ";
				} else {
					$query_curr_t_text = sprintf('\'%01.1f\'', $arrayOfTemperatures[$i][$j][$k] * 0.1).", ";
					$query_curr_v_text = sprintf('\'%01.1f\'', $arrayOfTempSpeeds[$i][$j][$k]).", ";
				}

				//	Цвет
				if($query_error_id!="NULL),"){
					$query_curr_t_colour="'#FF0000', ";
					$query_curr_v_colour="'#FF0000', ";
				} else if ($rows[$sensor_id]['is_enabled']==0) {
					$query_curr_t_colour="'#616161', ";
					$query_curr_v_colour="'#616161', ";
				} else if ($k>=$rows[$sensor_id]['grain_level']) {
					$query_curr_t_colour="'#E5E5E5', ";
					$query_curr_v_colour="'#E5E5E5', ";
				} else {
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

					$query_curr_t_colour=sprintf('\'#%02X%02X00\'',$red, $green).", ";

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

					$query_curr_v_colour=sprintf('\'#%02X%02X00\'',$red, $green).", ";
				}

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
				$query .= "'". str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k]) ."', ";
				//	curr_t_text
				$query .= $query_curr_t_text;
				//	curr_v_text
				$query .= $query_curr_v_text;
				//	curr_t_colour
				$query .= $query_curr_t_colour;
				//	curr_v_colour
				$query .= $query_curr_v_colour;
				//	server_date
				$query .= "STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'), ";

				//	NACK_Tmax
				$query .= $query_NACK_Tmax;
				//	TIME_NACK_Tmax
				$query .= $query_TIME_NACK_Tmax;
				//	ACK_Tmax
				$query .= $query_ACK_Tmax;
				//	TIME_ACK_Tmax
				$query .= $query_TIME_ACK_Tmax;
				//	RST_Tmax
				$query .= $query_RST_Tmax;
				//	TIME_RST_Tmax
				$query .= $query_TIME_RST_Tmax;

				//	NACK_Vmax
				$query .= $query_NACK_Vmax;
				//	TIME_NACK_Vmax
				$query .= $query_TIME_NACK_Vmax;
				//	ACK_Vmax
				$query .= $query_ACK_Vmax;
				//	TIME_ACK_Vmax
				$query .= $query_TIME_ACK_Vmax;
				//	RST_Vmax
				$query .= $query_RST_Vmax;
				//	TIME_RST_Vmax
				$query .= $query_TIME_RST_Vmax;

				//	NACK_err
				$query .= $query_NACK_err;
				//	TIME_NACK_err
				$query .= $query_TIME_NACK_err;
				//	ACK_err
				$query .= $query_ACK_err;
				//	TIME_ACK_err
				$query .= $query_TIME_ACK_err;
				//	RST_err
				$query .= $query_RST_err;
				//	TIME_RST_err
				$query .= $query_TIME_RST_err;

				//	error_id
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
										RST_Tmax=VALUES(RST_Tmax),
										TIME_RST_Tmax=VALUES(TIME_RST_Tmax),
										NACK_Vmax=VALUES(NACK_Vmax),
										TIME_NACK_Vmax=VALUES(TIME_NACK_Vmax),
										ACK_Vmax=VALUES(ACK_Vmax),
										TIME_ACK_Vmax=VALUES(TIME_ACK_Vmax),
										RST_Vmax=VALUES(RST_Vmax),
										TIME_RST_Vmax=VALUES(TIME_RST_Vmax),
										NACK_err=VALUES(NACK_err),
										TIME_NACK_err=VALUES(TIME_NACK_err),
										ACK_err=VALUES(ACK_err),
										TIME_ACK_err=VALUES(TIME_ACK_err),
										RST_err=VALUES(RST_err),
										TIME_RST_err=VALUES(TIME_RST_err),
										error_id=VALUES(error_id);";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	file_put_contents(__DIR__.'/debug.txt', "");
	file_put_contents(__DIR__.'/debug.txt', print_r(db_update_curr_alarm_state($dbh),1), FILE_APPEND);

	$alarmStateArray = db_update_curr_alarm_state($dbh);

	send_Telegram_notifications($dbh, $alarmStateArray,$serverDate);
	log_events($dbh, $logFile, $serverDate, $alarmStateArray);

	return;
}
//	Квитирование алармов
function alarms_ack($dbh, $serverDate, $logFile){

	$query = "	UPDATE ".DBNAME.".sensors
					SET NACK_err=0, ACK_err=1, TIME_ACK_err=STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s')
					WHERE NACK_err=1;

				UPDATE ".DBNAME.".sensors
					SET NACK_Tmax=0, ACK_Tmax=1, TIME_ACK_Tmax=STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s')
					WHERE NACK_Tmax=1;

				UPDATE ".DBNAME.".sensors
					SET NACK_Vmax=0, ACK_Vmax=1, TIME_ACK_Vmax=STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s')
					WHERE NACK_Vmax=1;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	log_events($dbh, $logFile, $serverDate, db_update_curr_alarm_state($dbh));

	return $query;

}
//	Функция определения того, есть ли неквитированные алармы
function alarms_get_nack_number($dbh){
	$sql = "SELECT count(sensor_id) FROM ".DBNAME.".sensors WHERE (NACK_err=1 OR NACK_Tmax=1 OR NACK_Vmax=1)";
	$sth = $dbh->query($sql);
	if($sth!=false){
		return $sth->fetchAll()[0]['count(sensor_id)'];
	}
}

?>