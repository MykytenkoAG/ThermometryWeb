<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/configParameters.php');

$logFile = $_SERVER['DOCUMENT_ROOT'].'/webTermometry/logs/log.txt';

//	Запись строки в журнал
function writeToLog($loggingString){
	global $logFile;
    // Write the contents to the file, 
    // using the FILE_APPEND flag to append the content to the end of the file
    // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
    file_put_contents($logFile, $loggingString, FILE_APPEND | LOCK_EX);
    return;
}

function logClear(){
	global $logFile;
    file_put_contents($logFile, "");
    return;
}
/*	Функция для определения появившихся алармов
	Сначала определяются датчики с неисправностями
	затем датчики с превышением температуры
	затем датчики с превышением скорости изменения температуры
	а затем происходит логирование
	Вызывается 1 раз в 10 секунд
*/
//	! Можно модифицировать, чтобы она возвращала количество новых алармов
//	Если количество больше нуля, необходимо включать звук
function alarms_set($dbh, $serverDate){

	//	sensors JOIN productsbysilo JOIN products
	$sql_joinedTable = "SELECT s.sensor_id, s.server_date, s.current_temperature, pbs.silo_name, s.podv_id, s.sensor_num, e.error_description
                                FROM sensors AS s
                                JOIN prodtypesbysilo AS pbs
                                    ON s.silo_id=pbs.silo_id
                                JOIN prodtypes AS pr
                                    ON pbs.product_id=pr.product_id
                                LEFT JOIN errors AS e ON s.error_id = e.error_id ";

	$cond_sens_errors="WHERE (s.is_enabled=1 AND "													//	условие для неисправных датчиков
							."(s.current_temperature BETWEEN 85 AND 254) AND "
							."s.NACK_err=0 AND s.ACK_err=0)";
	$cond_tmax="WHERE (s.is_enabled=1 AND "															//	условия для АПС о превышении температуры
							."(s.current_temperature < 85) AND "
							."s.NACK_err=0 AND s.ACK_err=0 AND "
							."s.NACK_Tmax=0 AND s.ACK_Tmax=0 AND "
							."(s.sensor_num < pbs.grain_level) AND"
							."(s.current_temperature > pr.t_max))";
	$cond_vmax="WHERE (s.is_enabled=1 AND "															//	условия для АПС о превышении скорости изменения температуры
							."(s.current_temperature < 85) AND "
							."s.NACK_err=0 AND s.ACK_err=0 AND "
							."s.NACK_Vmax=0 AND s.ACK_Vmax=0 AND "
							."(s.sensor_num < pbs.grain_level) AND"
							."(s.current_speed > pr.v_max))";

    $sql = $sql_joinedTable.$cond_sens_errors;														//	Сначала ищем неисправные датчики
    $sth = $dbh->query($sql);
	
    if($sth!=false){																				//	Если обнаружены неисправные датчики
		
		$rows = $sth->fetchAll();

		//	необходимо произвести запрос в таблцу errors

		if(count($rows)>0){

			$sensor_ids=array();
			foreach($rows as $row){
				array_push( $sensor_ids, $row['sensor_id']);
			}

			$query="UPDATE sensors SET NACK_err = ( CASE ";					//	NACK=1

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN "."1"." ";
			}

			$query.=" END), error_id = ( CASE ";							//	Записываем код ошибки

			for($i=0; $i<count($sensor_ids); $i++){
				$error_id=$rows[$i]['current_temperature'];
				$query.="WHEN sensor_id = ".$sensor_ids[$i]." THEN ".$error_id." ";
			}
			
			$query.=" END), TIME_NACK_err = STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s') WHERE sensor_id IN (";	//	Сохраняем дату возникновения АПС

			foreach($sensor_ids as $s_id){
				$query.= "$s_id,";
			}

			$query = substr($query,0,-1);
			$query.= ");";

			$stmt = $dbh->prepare($query);
			$stmt->execute();
		}

	}

    $sql = $sql_joinedTable.$cond_tmax;
    $sth = $dbh->query($sql);

    if($sth!=false){																				//	Затем ищем датчики с температурой выше критической
			
		$rows = $sth->fetchAll();

		if(count($rows)>0){

			$sensor_ids=array();																		//	Сохраняем id в массив и формируем запрос
			foreach($rows as $row){
				array_push( $sensor_ids, $row['sensor_id']);
			}

			$query="UPDATE sensors SET NACK_Tmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN "."1"." ";										//	NACK = 1
			}
			
			$query.=" END), TIME_NACK_Tmax = STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s') WHERE sensor_id IN (";	//	Сохраняем дату

			foreach($sensor_ids as $s_id){
				$query.= "$s_id,";
			}

			$query = substr($query,0,-1);
			$query.= ");";

			$stmt = $dbh->prepare($query);
			$stmt->execute();
		}
	}

	$sql = $sql_joinedTable.$cond_vmax;
	$sth = $dbh->query($sql);

	if($sth!=false){																				//	А затем находим датчики со скоростью выше критической
			
		$rows = $sth->fetchAll();

		if(count($rows)>0){
			$sensor_ids=array();
			foreach($rows as $row){
				array_push( $sensor_ids, $row['sensor_id']);
			}

			$query="UPDATE sensors SET NACK_Vmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN "."1"." ";										//	NACK = 1
			}

			$query.=" END), TIME_NACK_Vmax = STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s') WHERE sensor_id IN (";	//	Сохраняем дату

			foreach($sensor_ids as $s_id){
				$query.= "$s_id,";
			}

			$query = substr($query,0,-1);
			$query.= ");";

			$stmt = $dbh->prepare($query);
			$stmt->execute();
		}
	}


	//	Logging
    $loggingString = "";

	$sql = "SELECT s.server_date, pbs.silo_name, s.podv_id, s.sensor_num, s.NACK_err, s.NACK_Tmax, s.NACK_Vmax, e.error_description
				FROM sensors AS s
					JOIN prodtypesbysilo AS pbs
						ON s.silo_id=pbs.silo_id
					LEFT JOIN errors AS e ON s.error_id = e.error_id
				WHERE ((s.NACK_err=1 OR s.NACK_Tmax=1 OR s.NACK_Vmax=1) AND s.server_date='$serverDate')";
	$sth = $dbh->query($sql);

	if($sth!=false){
			
		$rows = $sth->fetchAll();

		foreach($rows as $row){

			if($row['NACK_err']==1){
				$loggingString .= $row['server_date'].": Силос ".$row['silo_name']." ТП ".($row['podv_id'] + 1)
								." НД ".($row['sensor_num'] + 1).$row['error_description']." Появление сигнала АПС\n";
			}
			if($row['NACK_Tmax']==1){
				$loggingString .= $row['server_date'].": Силос ".$row['silo_name']." ТП ".($row['podv_id'] + 1)
				." НД ".($row['sensor_num'] + 1)."Tmax"." Появление сигнала АПС\n";
			}
			if($row['NACK_Vmax']==1){
				$loggingString .= $row['server_date'].": Силос ".$row['silo_name']." ТП ".($row['podv_id'] + 1)
				." НД ".($row['sensor_num'] + 1)."Vmax"." Появление сигнала АПС\n";
			}

		}

	}

    writeToLog($loggingString);

	return;
}
/*	Функция квитирования алармов
	Вызывается пользователем путем нажатия на кнопку
	Сбрасывает флаг NACK и устанавливает флаг ACK
*/
function alarms_ack($dbh, $serverDate){
	
	$sql_joinedTable = "SELECT s.sensor_id, s.current_temperature, s.current_speed
						FROM sensors AS s
						JOIN prodtypesbysilo AS pbs
							ON s.silo_id=pbs.silo_id
						JOIN prodtypes AS pr
							ON pbs.product_id=pr.product_id	";

	$cond_NACK_sens_errors = "WHERE (s.NACK_err=1 AND s.ACK_err=0)";									//	Неквитированные неисправные датчики
	$cond_NACK_tmax =		 "WHERE (s.NACK_Tmax=1 AND s.ACK_Tmax=0)";									//	Неквитированные датчики с превышением температуры
	$cond_NACK_vmax =		 "WHERE (s.NACK_Vmax=1 AND s.ACK_Vmax=0)";									//	Неквитированные датчики с превышением скорости

	$sql = $sql_joinedTable.$cond_NACK_sens_errors;
	$sth = $dbh->query($sql);
	
	if($sth!=false){
		   
		$rows = $sth->fetchAll();

		if(count($rows)>0){

			$sensor_ids = array();
			foreach($rows as $row){
				array_push( $sensor_ids, $row['sensor_id']);
			}

			$query="UPDATE sensors SET NACK_err = ( CASE ";						//	NACK=0

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 0 ";
			}

			$query.=" END), ACK_err = ( CASE ";									//	ACK=1

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 1 ";
			}
		
			$query.=" END), TIME_ACK_err = STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s') WHERE sensor_id IN (";

			foreach($sensor_ids as $s_id){
				$query.= "$s_id,";
			}

			$query = substr($query,0,-1).");";

			$stmt = $dbh->prepare($query);
			$stmt->execute();
		}
	}

	$sql = $sql_joinedTable.$cond_NACK_tmax;
	$sth = $dbh->query($sql);
	
	if($sth!=false){
		   
		$rows = $sth->fetchAll();

		if(count($rows)>0){

			$sensor_ids = array();

			foreach($rows as $row){
				array_push( $sensor_ids, $row['sensor_id']);
			}

			$query="UPDATE sensors SET NACK_Tmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 0 ";				//	NACK=0
			}

			$query.=" END), ACK_Tmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 1 ";				//	ACK=1
			}
		
			$query.=" END), TIME_ACK_Tmax = STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s') WHERE sensor_id IN (";

			foreach($sensor_ids as $s_id){
				$query.= "$s_id,";
			}

			$query = substr($query,0,-1).");";

			$stmt = $dbh->prepare($query);
			$stmt->execute();
		}
	}

	$sql = $sql_joinedTable.$cond_NACK_vmax;
	$sth = $dbh->query($sql);
	
	if($sth!=false){
		   
		$rows = $sth->fetchAll();

		if(count($rows)>0){

			$sensor_ids = array();

			foreach($rows as $row){
				array_push( $sensor_ids, $row['sensor_id']);
			}

			$query="UPDATE sensors SET NACK_Vmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 0 ";				//	NACK=0
			}

			$query.=" END), ACK_Vmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 1 ";				//	NACK=1
			}

			$query.=" END), TIME_ACK_Vmax = STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s') WHERE sensor_id IN (";

			foreach($sensor_ids as $s_id){
				$query.= "$s_id,";
			}

			$query = substr($query,0,-1).");";

			$stmt = $dbh->prepare($query);
			$stmt->execute();
		}
	}

	//	Logging
    $loggingString = "";
	
	$sql = "SELECT s.server_date, pbs.silo_name, s.podv_id, s.sensor_num, s.NACK_err, s.NACK_Tmax, s.NACK_Vmax, e.error_description
				FROM sensors AS s
					JOIN prodtypesbysilo AS pbs
						ON s.silo_id=pbs.silo_id
					LEFT JOIN errors AS e ON s.error_id = e.error_id
				WHERE ((s.ACK_err=1 OR s.ACK_Tmax=1 OR s.ACK_Vmax=1) AND s.server_date='$serverDate')";
	$sth = $dbh->query($sql);

	if($sth!=false){
			
		$rows = $sth->fetchAll();

		foreach($rows as $row){

			if($row['ACK_err']==1){
				$loggingString .= $row['server_date'].": Силос ".$row['silo_name']." ТП ".($row['podv_id'] + 1)
								." НД ".($row['sensor_num'] + 1).$row['error_description']." Квитирование сигнала АПС\n";
			}
			if($row['ACK_Tmax']==1){
				$loggingString .= $row['server_date'].": Силос ".$row['silo_name']." ТП ".($row['podv_id'] + 1)
				." НД ".($row['sensor_num'] + 1)."Tmax"." Квитирование сигнала АПС\n";
			}
			if($row['ACK_Vmax']==1){
				$loggingString .= $row['server_date'].": Силос ".$row['silo_name']." ТП ".($row['podv_id'] + 1)
				." НД ".($row['sensor_num'] + 1)."Vmax"." Квитирование сигнала АПС\n";
			}

		}

	}

    writeToLog($loggingString);

	return;
}
/*	Функция для сброса сигналов АПС
	Сначала происходит сброс АПС о неисправности
	затем АПС о превышении температуры
	и затем АПС о превышении скорости ее изменения
*/
function alarms_reset($dbh, $serverDate){

	$loggingString = "";

	$sql_joinedTable = "SELECT s.sensor_id, s.silo_id, s.podv_id, s.sensor_num, pbs.silo_name, s.current_temperature, e.error_description
							FROM sensors AS s
								JOIN prodtypesbysilo AS pbs
									ON s.silo_id=pbs.silo_id
								JOIN prodtypes AS pr
									ON pbs.product_id=pr.product_id
								LEFT JOIN errors AS e
									ON s.error_id = e.error_id ";

	$cond_RST_sens_errors="WHERE (s.NACK_err=0 AND s.ACK_err=1 AND (s.is_enabled=0 OR current_temperature < 84))";		// неисправные датчики
	$cond_RST_tmax="WHERE (s.NACK_Tmax=0 AND s.ACK_Tmax=1 AND
							(s.is_enabled=0 OR s.sensor_num >= pbs.grain_level OR s.current_temperature < pr.t_max))";	// датчики с превышением температуры
	$cond_RST_vmax="WHERE (s.NACK_Vmax=0 AND s.ACK_Vmax=1 AND
							(s.is_enabled=0 OR s.sensor_num >= pbs.grain_level OR s.current_speed < pr.v_max))";	// датчики с превышением скорости
   
	$sql = $sql_joinedTable.$cond_RST_sens_errors;
	$sth = $dbh->query($sql);

	if($sth != false){
		   
		$rows = $sth->fetchAll();

		if(count($rows)>0){

			$sensor_ids = array();

			foreach($rows as $row){
				array_push( $sensor_ids, $row['sensor_id']);

		//		$loggingString .= /*$row['server_date'].*/": Силос ".$row['silo_name']." ТП ".($row['podv_id'] + 1)			//	Logging
		//       ." НД ".($row['sensor_num'] + 1).". ".$row['error_description'].". Исчезновение сигнала АПС\n";

			}

			$query="UPDATE sensors SET NACK_err = ( CASE ";						//	NACK=0

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 0 ";
			}

			$query.=" END), ACK_err = ( CASE ";									//	ACK=0

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 0 ";
			}
		
			$query.=" END), TIME_NACK_err = NULL, TIME_ACK_err = NULL, error_id = NULL WHERE sensor_id IN (";

			foreach($sensor_ids as $s_id){
				$query.= "$s_id,";
			}

			$query = substr($query,0,-1).");";

			$stmt = $dbh->prepare($query);
			$stmt->execute();
		}
	}

	$sql = $sql_joinedTable.$cond_RST_tmax;
	$sth = $dbh->query($sql);
	
	if($sth!=false){
		   
		$rows = $sth->fetchAll();

		if(count($rows)>0){

			$sensor_ids = array();

			foreach($rows as $row){
				array_push( $sensor_ids, $row['sensor_id']);

	//           $loggingString .= $row['server_date'].": Силос ".$row['silo_name']." ТП ".($row['podv_id'] + 1)		//	Logging
	//              				." НД ".($row['sensor_num'] + 1).". Tмакс. Исчезновение сигнала АПС\n";
			}

			$query="UPDATE sensors SET NACK_Tmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 0 ";				//	NACK = 0
			}

			$query.=" END), ACK_Tmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 0 ";				//	ACK = 0
			}
		
			$query.=" END), TIME_NACK_Tmax = NULL, TIME_ACK_Tmax = NULL WHERE sensor_id IN (";

			foreach($sensor_ids as $s_id){
				$query.= "$s_id,";
			}

			$query = substr($query,0,-1).");";

			$stmt = $dbh->prepare($query);
			$stmt->execute();
		}
	}

	$sql = $sql_joinedTable.$cond_RST_vmax;
	$sth = $dbh->query($sql);
	
	if($sth!=false){
		   
		$rows = $sth->fetchAll();

		if(count($rows)>0){

			$sensor_ids=array();
			foreach($rows as $row){
				array_push( $sensor_ids, $row['sensor_id']);

		//      $loggingString .= $row['server_date'].": Силос ".$row['silo_name']." ТП ".($row['podv_id'] + 1)		//	Logging
		//          				." НД ".($row['sensor_num'] + 1).". Vмакс. Исчезновение сигнала АПС\n";

			}

			$query="UPDATE sensors SET NACK_Vmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 0 ";				//	NACK=0
			}

			$query.=" END), ACK_Vmax = ( CASE ";

			foreach($sensor_ids as $s_id){
				$query.="WHEN sensor_id = ".$s_id." THEN 0 ";				//	NACK=1
			}

			$query.=" END), TIME_NACK_Vmax = NULL, TIME_ACK_Vmax = NULL WHERE sensor_id IN (";

			foreach($sensor_ids as $s_id){
				$query.= "$s_id,";
			}

			$query = substr($query,0,-1);
			$query.= ");";

			$stmt = $dbh->prepare($query);
			$stmt->execute();
		}
	}

	writeToLog($loggingString);

	return;
}
//	Функция определения того, есть ли неквитированные алармы
function isSoundOn($dbh){

    $sql = "SELECT sensor_id FROM sensors WHERE NACK_Tmax=1 OR NACK_Vmax=1 OR NACK_err=1;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

	$rows = $sth->fetchAll();

	if(count($rows)>0){
		return "YES";
	}

    return "NO";
}

?>