<?php
//	Скрипт для вычитывания текущих значений температур, скоростей их изменения, уровней и текущей даты из TermoServer'а по протоколу http
//	Используется AJAX'ом каждые 10 секунд и скриптом dbWriteParameters для записи полученных значений температур в базу данных 1 раз в 30 минут в фоновом режиме
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/php/configFromINI.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/php/debugScript.php');

$inputValsArr		 = getInputArray ( getInputString($IPAddr, $port) );	//	[температуры][скорости][уровни][дата]
$arrayOfTemperatures = getArrayOfValues3d($inputValsArr[0]);
$arrayOfTempSpeeds   = getArrayOfValues3d($inputValsArr[1]);
$arrayOfLevels       = getLevels($inputValsArr[2]);
$serverDate			 = $inputValsArr[3];

//	Функция для получения строки с текущими значениями
function getInputString($IPAddr, $port){
	//http://docs.php.net/fsockopen
	$fp = @fsockopen($IPAddr, $port, $errno, $errstr, 30)
		or die(require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/ts_conn_Error.php'));	//	Перенаправление на страницу с описанием ошибки
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
function getInputArray($inputString){
	//Получаем массив строк [массив_текущих температур, массив_скоростей_изменения_температура, массив_уровней, текущая дата]
	$inputArray = preg_split('/##/', $inputString, -1, PREG_SPLIT_NO_EMPTY);
	return $inputArray;
}

//	Функция для получения трехмерного массива текущих значений температур или скоростей их изменения
function getArrayOfValues3d($inputArray){
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
function getLevels($inputArray){
	//	Получаем массив уровней
	$arrayOfLevels = preg_split('/:/', $inputArray, -1, PREG_SPLIT_NO_EMPTY);
	return $arrayOfLevels;
}

$simulation_mode = true;

if($simulation_mode){
	/*debug_drop_all_tables();
	debug_create_silo_table($arrayOfLevels);
	debug_create_sensors_table($arrayOfTemperatures, $arrayOfTempSpeeds);*/
	$arrayOfTemperatures	= update_temperature_values($arrayOfTemperatures);
	$arrayOfTempSpeeds		= update_temperature_speeds_values($arrayOfTempSpeeds);
	$arrayOfLevels			= update_level_values($arrayOfLevels);	
}

?>