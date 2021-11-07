<?php

//require_once ('currValsFromTS.php');
require_once ('configParameters.php');

//	Удаление/очистка таблиц
function ddl_drop_all($dbh){

	$query = 
	   "DROP TABLE IF EXISTS zernoib.measurements;
		DROP TABLE IF EXISTS zernoib.dates;
		DROP TABLE IF EXISTS zernoib.sensors;
		DROP TABLE IF EXISTS zernoib.prodtypesbysilo;
		DROP TABLE IF EXISTS zernoib.silosesgroups;
		DROP TABLE IF EXISTS zernoib.prodtypes;
		DROP TABLE IF EXISTS zernoib.errors;
		DROP TABLE IF EXISTS zernoib.users;";

	$stmt = $dbh->prepare($query);

	$stmt->execute();

	return;
}

function ddl_truncate_Measurements($dbh){

	$query = 
	   "TRUNCATE zernoib.measurements;";

	$stmt = $dbh->prepare($query);

	$stmt->execute();

	return;
}

//	Создание таблиц
function ddl_create_Users($dbh){
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.users
			 (user_id INT NOT NULL AUTO_INCREMENT,
			  user_name VARCHAR(20) NOT NULL,
			  password VARCHAR(32) NOT NULL,
			  access_level INT NOT NULL DEFAULT 0,
			  PRIMARY KEY (user_id))
			  ENGINE = InnoDB
			  CHARSET=utf8 COLLATE utf8_general_ci;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_create_Errors($dbh){
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.errors
			 (error_id INT NOT NULL,
			  error_description VARCHAR(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  error_desc_short VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  error_desc_for_visu VARCHAR(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  PRIMARY KEY (error_id))
			  ENGINE = InnoDB
			  CHARSET=utf8 COLLATE utf8_general_ci;";
			  
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_create_Dates($dbh){
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.dates
			 (date_id INT NOT NULL AUTO_INCREMENT,
			  date TIMESTAMP NOT NULL,
			  PRIMARY KEY (date_id))
			  ENGINE = InnoDB
			  CHARSET=utf8 COLLATE utf8_general_ci;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_create_Prodtypes($dbh){

	$query = "CREATE TABLE IF NOT EXISTS zernoib.prodtypes
			 (product_id INT NOT NULL AUTO_INCREMENT,
			  product_name VARCHAR(60) NOT NULL,
			  t_min FLOAT NOT NULL, t_max FLOAT NOT NULL,
			  v_min FLOAT NOT NULL, v_max FLOAT NOT NULL,
			  PRIMARY KEY (product_id))
			  ENGINE = InnoDB
			  CHARSET=utf8 COLLATE utf8_general_ci;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_create_SilosesGroups($dbh){
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.silosesgroups
			 (silo_group INT NOT NULL,
			  silo_group_name VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  silo_group_col INT NULL DEFAULT NULL,
			  silo_group_row INT NULL DEFAULT NULL,
			  silo_group_size FLOAT NULL DEFAULT NULL,
			  PRIMARY KEY (silo_group))
			  ENGINE = InnoDB
			  CHARSET=utf8 COLLATE utf8_general_ci;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_create_Prodtypesbysilo($dbh){
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.prodtypesbysilo
			 (silo_id INT NOT NULL,
			  silo_name VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
			  bs_addr INT NOT NULL DEFAULT 1,
			  product_id INT NOT NULL,
			  grain_level_fromTS BOOLEAN NOT NULL DEFAULT TRUE,
			  grain_level INT NOT NULL,
			  is_square BOOLEAN NOT NULL DEFAULT TRUE,
			  size FLOAT NOT NULL DEFAULT 1,
			  position_col INT NOT NULL DEFAULT 0,
			  position_row INT NOT NULL DEFAULT 0,
			  silo_group INT NOT NULL DEFAULT 0,
			  PRIMARY KEY (silo_id),
			  CONSTRAINT prodtypesbysilo_fk FOREIGN KEY (product_id) REFERENCES prodtypes(product_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
			  CONSTRAINT prodtypesbysilo_fk_sg FOREIGN KEY (silo_group) REFERENCES silosesgroups(silo_group) ON DELETE RESTRICT ON UPDATE RESTRICT)
			  ENGINE = InnoDB
			  CHARSET=utf8 COLLATE utf8_general_ci;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_create_Sensors($dbh){
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.sensors
			 (sensor_id INT NOT NULL,
			  silo_id INT NOT NULL, podv_id INT NOT NULL, sensor_num INT NOT NULL,
			  is_enabled BOOLEAN NOT NULL DEFAULT TRUE,
			  current_temperature FLOAT NOT NULL,
			  current_speed FLOAT NOT NULL,
			  curr_t_text VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0',
			  curr_v_text VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0',
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
			  ENGINE = InnoDB
			  CHARSET=utf8 COLLATE utf8_general_ci;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_create_Measurements($dbh){
	
	$query = "CREATE TABLE IF NOT EXISTS zernoib.measurements
			 (date_id INT NOT NULL,
			  sensor_id INT NOT NULL,
			  temperature FLOAT NULL,
			  INDEX (date_id),
			  CONSTRAINT measurements_fk_date_id FOREIGN KEY (date_id) REFERENCES dates(date_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
			  CONSTRAINT measurements_fk_sensor_id FOREIGN KEY (sensor_id) REFERENCES sensors(sensor_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
			  ENGINE = InnoDB
			  CHARSET=utf8 COLLATE utf8_general_ci;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

//	Инициализация таблиц
function ddl_init_Users($dbh){
	
	$query="INSERT INTO users (user_name, password, access_level) VALUES
			('oper', 'c5e9da289c72211431256b6ddf36b57b', 1),
			('tehn', '218044cc646f586c34149a8efeefd843', 2)";
	
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_init_Errors($dbh){

	$query="REPLACE INTO errors (error_id, error_description, error_desc_short, error_desc_for_visu) VALUES 
			(85,  'Обрыв плюсового провода', '85', 'Обрыв плюсового провода'),
			(127, 'Неисправность датчика температуры', 'Дат-', 'Неисправность дат. температуры'),
			(128, 'Температура не измерялась', '-', 'Температура не измерялась'),
			(251, 'Обрыв линии связи термпоподвески ТП', 'Обр.', 'Обрыв линии связи термпоподвески ТП'),
			(252, 'Короткое замыкание линии связи ТП', 'К.З.', 'Короткое замыкание линии связи ТП'),
			(253, 'Неисправность встроенного ПЗУ БС', 'ПЗУ', 'Ошибка ПЗУ БС'),
			(254, 'Отсутствие связи с блоком сбора БС', 'CRC', 'Отсутствие связи с блоком сбора БС'),
			(255, 'Датчик отключен оператором', 'Откл.', 'Датчик отключен оператором'),
			(256, 'Силос отключен на сервере', 'Х', 'Силос отключен');";
	
	$stmt = $dbh->prepare($query);

	$stmt->execute();
	
	return;
}

function ddl_init_Dates($dbh, $serverDate){

	$query="INSERT INTO dates (date) VALUES (STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s'));";
	$stmt = $dbh->prepare($query);

	$stmt->execute();

	return;
}

function ddl_init_Prodtypes($dbh){

	$query="INSERT INTO prodtypes (product_name, t_min, t_max, v_min, v_max) VALUES 
			('Пшеница-кл.1 вл.10% сорн.1%','20.0','30.0','0.0','3.0'),
			('Пшеница-кл.2 вл.18% сорн.3%','20.0','35.0','0.0','2.0'),
			('Пшеница-кл.3 вл.20% сорн.4%','20.0','30.0','0.0','2.0'),
			('Ячмень-кл.1 вл.10% сорн.1%','20.0','35.0','0.0','2.0'),
			('Ячмень-кл.2 вл.20% сорн.6%','20.0','35.0','0.0','2.0'),
			('Гречка-кл.1 вл.10% сорн.3%','20.0','35.0','0.0','2.0'),
			('Продукт 7','20.0','30.0','0.0','10.0')";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_init_SilosesGroups($dbh, $termoClientINI){

	$query  = "INSERT INTO zernoib.silosesgroups (silo_group, silo_group_name, silo_group_col, silo_group_row, silo_group_size) VALUES ";
	$query .= "(0, 'default_group', 0, 0, 1),";

	if(isset($termoClientINI['SilosesGroups'])){
		foreach ($termoClientINI['SilosesGroups'] as $key => $value) {
			$groupCharacteristics = explode("%", str_replace(",", ".", $value));
			$query .= "('".$key."', '".$groupCharacteristics[0]."', ".$groupCharacteristics[1].", ".$groupCharacteristics[2].", ".$groupCharacteristics[3]."),";
		}
	}

	$query = substr($query,0,-1).";";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

//	Функция сортировки ini-файлов по именам силосов. Полезна в больших проектах, где имена в ini-файле идут в разнобой
function getTermoServerIniSortedBySiloName($termoClientINI,$termoServerINI){

	$outArr=array();

	foreach ($termoServerINI as $key => $value) {
		if( preg_match('/Silos([0-9]+)/',$key,$matches) ){

			$outArr[] = array(	'SiloNumInFile'	=> $matches[1],
				
								'SilosName'		=> $termoServerINI[$key]['SilosName'],
								'PodvCount'		=> $termoServerINI[$key]['PodvCount'],
								'SensorsStr'	=> $termoServerINI[$key]['SensorsStr'],
								'DeviceAddress'	=> $termoServerINI[$key]['DeviceAddress'],
								'FirstPodvShift'=> $termoServerINI[$key]['FirstPodvShift'],
								'off_'			=> $termoServerINI[$key]['off_'],

								'Left'			=> $termoClientINI[$key]['Left'],
								'Top'			=> $termoClientINI[$key]['Top'],
								'Size'			=> $termoClientINI[$key]['Size'],
								'sType'			=> $termoClientINI[$key]['sType'],
								'Group'			=> $termoClientINI[$key]['Group']);

		}
	}

	$arr_SiloNumInFile = array_column($outArr, 'SiloNumInFile');
	$arr_SilosName = array_column($outArr, 'SilosName');
	$arr_PodvCount = array_column($outArr, 'PodvCount');
	$arr_SensorsStr = array_column($outArr, 'SensorsStr');
	$arr_DeviceAddress = array_column($outArr, 'DeviceAddress');
	$arr_FirstPodvShift = array_column($outArr, 'FirstPodvShift');
	$arr_off = array_column($outArr, 'off_');
	$arr_Left = array_column($outArr, 'Left');
	$arr_Top = array_column($outArr, 'Top');
	$arr_Size = array_column($outArr, 'Size');
	$arr_sType = array_column($outArr, 'sType');
	$arr_Group = array_column($outArr, 'Group');


	array_multisort($arr_SilosName, SORT_ASC,
					$arr_SiloNumInFile, SORT_ASC,
					$arr_PodvCount, SORT_ASC,
					$arr_SensorsStr, SORT_ASC,
					$arr_DeviceAddress, SORT_ASC,
					$arr_FirstPodvShift, SORT_ASC,
					$arr_off, SORT_ASC,
					$arr_Left, SORT_ASC,
					$arr_Top, SORT_ASC,
					$arr_Size, SORT_ASC,
					$arr_sType, SORT_ASC,
					$arr_Group, SORT_ASC,
					$outArr);

	return $outArr;

}

function ddl_init_Prodtypesbysilo($dbh, $termoClientINI, $termoServerINI){

    $query = "SELECT product_id FROM zernoib.prodtypes ORDER BY product_id ASC LIMIT 1";
    $sth = $dbh->query($query);
    // fetch all rows into array, by default PDO::FETCH_BOTH is used
	$product_id=($sth->fetchAll())[0]['product_id'];					//	Выбираем продукт с id=1 для заполенения им всех силосов (только при инициализации)

	$query="INSERT INTO prodtypesbysilo (silo_id, silo_name, bs_addr, product_id, grain_level_fromTS, grain_level, is_square, size, position_col, position_row, silo_group) VALUES ";

	$ini_arr = getTermoServerIniSortedBySiloName($termoClientINI,$termoServerINI);

	for($i=0; $i<count($ini_arr); $i++){
		$query.="(".$i.","													//	silo_id
		."'".$ini_arr[$i]['SilosName']."'".","								//	silo_name
		."'".$ini_arr[$i]['DeviceAddress']."'".","							//	bs_addr
		."'".$product_id."'".","											//	product_id = 1
		."TRUE".","															//	grain_level_from_TS = 1
		."0".","															//	grain_level = 0
		."'".$ini_arr[$i]['sType']."'".","									//	is_square
		."'".str_replace(",", ".", $ini_arr[$i]['Size'])."'".","			//	size
		."'".str_replace(",", ".", $ini_arr[$i]['Left'])."'".","			//	position_col
		."'".str_replace(",", ".", $ini_arr[$i]['Top'])."'".","				//	position_row
		."'".str_replace(",", ".", $ini_arr[$i]['Group'])."'"
		."),";	
	}

	$query = substr($query,0,-1).";";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

function ddl_init_Sensors($dbh, $termoClientINI, $termoServerINI, $serverDate){

	$query="INSERT INTO sensors (sensor_id, silo_id, podv_id, sensor_num, current_temperature, current_speed, server_date) VALUES ";

	$ini_arr = getTermoServerIniSortedBySiloName($termoClientINI,$termoServerINI);

	$sensor_id = 0;
	for($i=0; $i<count($ini_arr); $i++){
		$sensorsArr = preg_split('/,/',$ini_arr[$i]['SensorsStr'],-1,PREG_SPLIT_NO_EMPTY);
		$podv_id=0;
		foreach($sensorsArr as $podvSensorsNumber){
			for($j=0;$j<$podvSensorsNumber;$j++){
				$query .= "(".$sensor_id.",".$i.",".$podv_id.",".$j.","."0".","."0".","."STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s')"."),";
				$sensor_id++;
			}
			$podv_id++;
		}
	}

	$query = substr($query,0,-1).";";
	
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

?>