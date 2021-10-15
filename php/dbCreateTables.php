<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/php/currValsFromTS.php');

//	!!!
function createDBBackup(){

	//global $servername;	global $username;	global $password;	global $dbname;
	//exec('c:\wamp64\bin\mysql\mysql5.7.31\bin\mysqldump --user=root --password=newpassword --host=localhost --fields-terminated-by="," zernoib > c:/wamp64/www/webTermometry/backup3.sql');

	return;
}

//	Удаление/очистка таблиц
function deleteAllTables(){

	global $dbh;

	$query = 
	   "DROP TABLE IF EXISTS zernoib.measurements;
		DROP TABLE IF EXISTS zernoib.sensors;
		DROP TABLE IF EXISTS zernoib.prodtypesbysilo;
		DROP TABLE IF EXISTS zernoib.prodtypes;
		DROP TABLE IF EXISTS zernoib.dates;
		DROP TABLE IF EXISTS zernoib.errors;
		DROP TABLE IF EXISTS zernoib.users;";

	$stmt = $dbh->prepare($query);

	$stmt->execute();

	return;
}

function truncateTableMeasurements(){

	global $dbh;

	$query = 
	   "TRUNCATE zernoib.measurements;";

	$stmt = $dbh->prepare($query);

	$stmt->execute();

	return;
}

//	Создание таблиц

function createTableUsers(){

	global $dbh;
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.users
			 (user_id INT NOT NULL AUTO_INCREMENT,
			  user_name VARCHAR(20) NOT NULL,
			  password VARCHAR(32) NOT NULL,
			  access_level INT NOT NULL DEFAULT 0,
			  PRIMARY KEY (user_id))
			  ENGINE = InnoDB;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function createTableErrors(){

	global $dbh;
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.errors
			 (error_id INT NOT NULL,
			  error_description VARCHAR(60) NOT NULL,
			  error_desc_short VARCHAR(5) NOT NULL,
			  error_desc_for_visu VARCHAR(50) NOT NULL,
			  PRIMARY KEY (error_id))
			  ENGINE = InnoDB;";
			  
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function createTableDates(){

	global $dbh;
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.dates
			 (date_id INT NOT NULL AUTO_INCREMENT,
			  date TIMESTAMP NOT NULL,
			  PRIMARY KEY (date_id))
			  ENGINE = InnoDB;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function createTableProdtypes(){

	global $dbh;
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.prodtypes
			 (product_id INT NOT NULL AUTO_INCREMENT,
			  product_name VARCHAR(60) NOT NULL,
			  t_min FLOAT NOT NULL, t_max FLOAT NOT NULL,
			  v_min FLOAT NOT NULL, v_max FLOAT NOT NULL,
			  PRIMARY KEY (product_id))
			  ENGINE = InnoDB;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function createTableProdtypesbysilo(){

	global $dbh;
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.prodtypesbysilo
			 (silo_id INT NOT NULL,
			  silo_name VARCHAR(20) NOT NULL,
			  bs_addr INT NOT NULL DEFAULT 1,
			  product_id INT NOT NULL,
			  grain_level_fromTS BOOLEAN NOT NULL DEFAULT TRUE,
			  grain_level INT NOT NULL,
			  is_square BOOLEAN NOT NULL DEFAULT TRUE,
			  size FLOAT NOT NULL DEFAULT 1,
			  position_col INT NOT NULL DEFAULT 0,
			  position_row INT NOT NULL DEFAULT 0,
			  PRIMARY KEY (silo_id),
			  CONSTRAINT prodtypesbysilo_fk FOREIGN KEY (product_id) REFERENCES prodtypes(product_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
			  ENGINE = InnoDB;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function createTableSensors(){

	global $dbh;
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.sensors
			 (sensor_id INT NOT NULL,
			  silo_id INT NOT NULL, podv_id INT NOT NULL, sensor_num INT NOT NULL,
			  is_enabled BOOLEAN NOT NULL DEFAULT TRUE,
			  current_temperature FLOAT NOT NULL,
			  current_speed FLOAT NOT NULL,
			  curr_t_text VARCHAR(7) NOT NULL DEFAULT '0.0',
			  curr_v_text VARCHAR(7) NOT NULL DEFAULT '0.0',
			  curr_t_colour VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
			  curr_v_colour VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
			  server_date TIMESTAMP NULL DEFAULT NULL,
			  NACK_Tmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_NACK_Tmax TIMESTAMP NULL DEFAULT NULL,
			  ACK_Tmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_ACK_Tmax TIMESTAMP NULL DEFAULT NULL,
			  NACK_Vmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_NACK_Vmax TIMESTAMP NULL DEFAULT NULL,
			  ACK_Vmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_ACK_Vmax TIMESTAMP NULL DEFAULT NULL,
			  NACK_err BOOLEAN NOT NULL DEFAULT FALSE,	TIME_NACK_err TIMESTAMP NULL DEFAULT NULL,
			  ACK_err BOOLEAN NOT NULL DEFAULT FALSE,	TIME_ACK_err TIMESTAMP NULL DEFAULT NULL,
			  error_id INT NULL DEFAULT NULL,
			  PRIMARY KEY (sensor_id),
			  CONSTRAINT sensors_fk FOREIGN KEY (silo_id) REFERENCES prodtypesbysilo(silo_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
			  CONSTRAINT sens_err_fk FOREIGN KEY (error_id) REFERENCES errors(error_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
			  ENGINE = InnoDB;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function createTableMeasurements(){

	global $dbh;
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.measurements
			 (date_id INT NOT NULL,
			  sensor_id INT NOT NULL,
			  temperature FLOAT NOT NULL,
			  INDEX (date_id),
			  CONSTRAINT measurements_fk_date_id FOREIGN KEY (date_id) REFERENCES dates(date_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
			  CONSTRAINT measurements_fk_sensor_id FOREIGN KEY (sensor_id) REFERENCES sensors(sensor_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
			  ENGINE = InnoDB;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

//	Инициализация таблиц

function initTableUsers(){
	
	global $dbh;
	global $initUsersINI;

	$query="INSERT INTO users (user_name, password, access_level) VALUES ";

	foreach ($initUsersINI as $key => $value) {
		$query.="('".$initUsersINI[$key]['user_name']."',"
				."'".$initUsersINI[$key]['password']."'".","
				    .$initUsersINI[$key]['access_level']."),";
	}

	$query = substr($query,0,-1).";";
	
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function initTableErrors(){

	global $dbh;
	global $errCodesINI;

	$query="INSERT INTO errors (error_id, error_description, error_desc_short, error_desc_for_visu) VALUES ";

	foreach ($errCodesINI as $key => $value) {
		$query.="('".$errCodesINI[$key]['code']."',"
				."'".$errCodesINI[$key]['desc_full']."',"
				."'".$errCodesINI[$key]['desc_short']."',"
				."'".$errCodesINI[$key]['desc_for_visu']."'),";
	}

	$query = substr($query,0,-1).";";
	
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function initTableDates(){

	global $dbh;
	global $serverDate;

	$query="INSERT INTO dates (date) VALUES (STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'));";
	$stmt = $dbh->prepare($query);

	$stmt->execute();

	return;
}

function initTableProdtypes(){

	global $dbh;
	global $initProductsINI;

	$query="INSERT INTO prodtypes (product_name, t_min, t_max, v_min, v_max) VALUES ";

	foreach ($initProductsINI as $key => $value) {
		$query.="('".$initProductsINI[$key]['product_name']."',"
				    .$initProductsINI[$key]['t_min'].","
				    .$initProductsINI[$key]['t_max'].","
				    .$initProductsINI[$key]['v_min'].","
				    .$initProductsINI[$key]['v_max']."),";
	}

	$query = substr($query,0,-1).";";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function initTableProdbysilo(){

	global $dbh; global $termoClientINI; global $termoServerINI; global $arrayOfLevels;

    $query = "SELECT product_id FROM zernoib.prodtypes ORDER BY product_id ASC LIMIT 1";
    $sth = $dbh->query($query);
    // fetch all rows into array, by default PDO::FETCH_BOTH is used
	$product_id=($sth->fetchAll())[0]['product_id'];					//	Выбираем продукт с id=1 для заполенения им всех силосов (только при инициализации)

	$query="INSERT INTO prodtypesbysilo (silo_id, silo_name, bs_addr, product_id, grain_level_fromTS, grain_level, is_square, size, position_col, position_row) VALUES ";
	for($i = 0; $i < count($arrayOfLevels); $i++){
		$query.="(".$i.","
			  ."'".$termoClientINI['Silos'.($i+1)]['Name']."'".","
			  .$termoServerINI['Silos'.($i+1)]['DeviceAddress'].","
			  ."'".$product_id."'".","
			  ."TRUE".","
			  .$arrayOfLevels[$i].","
			  .$termoClientINI['Silos'.($i+1)]['sType'].","
			  .str_replace(",", ".", $termoClientINI['Silos'.($i+1)]['Size']).","
			  .$termoClientINI['Silos'.($i+1)]['Left'].","
			  .$termoClientINI['Silos'.($i+1)]['Top']
			  ."),";
	}

	$query = substr($query,0,-1).";";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function initTableSensors(){

	global $dbh;

	$query="INSERT INTO sensors (sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_speed, server_date) VALUES ";
	$query.=getInitParamsForTableSensors();
	$query.=";";
	
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function getInitParamsForTableSensors(){

	global $arrayOfTemperatures; global $arrayOfTempSpeeds; global $serverDate;
	//	sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_speed, server_date
	$paramsArray = array(array(),array(),array(),array(),array(),array(),array());	
	
	$sensor_id = 0;
	for($i = 0; $i < count($arrayOfTemperatures); $i++){
        for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
			$sensor_num = 0;
    		for($k=0; $k < count($arrayOfTemperatures[$i][$j]); $k++){
				array_push( $paramsArray[0], $sensor_id);										//	sensor_id
				array_push( $paramsArray[1], $i);												//	silo_id
				array_push( $paramsArray[2], $j);												//	podv_id
				array_push( $paramsArray[3], $sensor_num);										//	sensor_num
				array_push( $paramsArray[4], $arrayOfTemperatures[$i][$j][$k] * 0.1 );			//	current_temperature
				$temperSpeed = str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k]);
				array_push( $paramsArray[5], $temperSpeed);										//	current_speed
				array_push( $paramsArray[6], "STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s')");	//	server_date

				$sensor_num++;
				$sensor_id++;
    		}
        }
    }

	$outStr="";
	for($i = 0; $i < count($paramsArray[0]); $i++){
		$outStr.="(".$paramsArray[0][$i].",".$paramsArray[1][$i].",".$paramsArray[2][$i].","
					.$paramsArray[3][$i].",".$paramsArray[4][$i].','.$paramsArray[5][$i].','.$paramsArray[6][$i]."),";
	}
	$outStr = substr($outStr,0,-1);
	
	return $outStr;
}


?>