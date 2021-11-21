<?php

//	Функция для получения строки с текущими значениями
function getFromTS_inputString($IPAddr, $port){
	//http://docs.php.net/fsockopen
	$fp = @fsockopen($IPAddr, $port, $errno, $errstr, 30);

	if($fp == false){
		return false;
	}

	if (!$fp) {
		echo "$errstr ($errno)<br />\n";    
	} else {
		$out = "GET / HTTP/1.1\r\n"; $out .= "Host: $IPAddr\r\n"; $out .= "Connection: Close\r\n\r\n";
		fwrite($fp, $out);
		//	читаем данные по http в строку
		$inputStr = "";
		while(true){
			$inputStr .= fgets($fp,2);
			if(strlen($inputStr)<4){
				continue;
			}
			if(substr($inputStr,-4,4)==="####"){					//	Если #### - конец данных
				break;
			}
		}
		fclose($fp);
	}
	return $inputStr;
}
//	Функция для преобразования строки с текущими значениями в массив
function getFromTS_inputArray($inputString){
	//Получаем массив строк [массив_текущих температур, массив_скоростей_изменения_температура, массив_уровней, текущая дата]
	$inputArray = preg_split('/##/', $inputString, -1, PREG_SPLIT_NO_EMPTY);
	return $inputArray;
}
//	Функция для получения трехмерного массива текущих значений температур или скоростей их изменения
function getFromTS_arrayOfValues3d($inputArray){
	//	Получаем трехмерный массив [силос][подвеска][датчик]
	$arrayOfCurVals = preg_split('/::/', $inputArray, -1, PREG_SPLIT_NO_EMPTY);
	for($i = 0; $i < count($arrayOfCurVals); $i++){
		$arrayOfCurVals[$i] = preg_split('/:/', $arrayOfCurVals[$i], -1, PREG_SPLIT_NO_EMPTY);
		for($j = 0; $j < count($arrayOfCurVals[$i]); $j++){
			$arrayOfCurVals[$i][$j] = preg_split('/ /', $arrayOfCurVals[$i][$j], -1, PREG_SPLIT_NO_EMPTY);
		}
	}
	return $arrayOfCurVals;
}
//	Функция для получения массива уровней
function getFromTS_grainLevels($inputArray){
	$arrayOfLevels = preg_split('/:/', $inputArray, -1, PREG_SPLIT_NO_EMPTY);
	return $arrayOfLevels;
}
//	Проверка вычитанных из Термосервера или отладочных таблиц значений содержимому таблицы sensors
function checkReadValsToDBSensors($dbh, $arrayOfTemperatures){

	$sql = "SELECT sensor_id, silo_id, podv_id, count(sensor_num)
			FROM sensors
			GROUP BY silo_id, podv_id
			ORDER BY silo_id;";
	$sth = $dbh->query($sql);

	if($sth==false){
		return false;
	}

	$rows = $sth->fetchAll();
	if(count($rows)==0){
		return false;
	}

	if(!is_array($arrayOfTemperatures)){
		return false;
	}

	$arrayOfTemperaturesSize=0; $rowsShift=0;
	for($i=0; $i<count($arrayOfTemperatures); $i++){
		if($i>0){
			$rowsShift += count($arrayOfTemperatures[$i-1]);
		}
		if(!isset($arrayOfTemperatures[$i])){
			return false;
		}
		if(!is_array($arrayOfTemperatures[$i])){
			return false;
		}
		for($j=0; $j<count($arrayOfTemperatures[$i]); $j++){

			if(!isset($rows[$rowsShift+$j]['count(sensor_num)'])){
				return false;
			}

			if(count($arrayOfTemperatures[$i][$j]) != $rows[$rowsShift+$j]['count(sensor_num)'] ){
				return false;
			}
			$arrayOfTemperaturesSize++;
		}
	}

	if($arrayOfTemperaturesSize!=count($rows)){
		return false;
	}

	return true;

}

?>