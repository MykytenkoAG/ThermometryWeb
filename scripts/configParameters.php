<?php
$simulation_mode = true;
$debugPageDisableElements = $simulation_mode ? "" : "disabled";
//	Набор инициализационных параметров для работы с термосервером и БД
$errCodesINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/ts_errors.ini'), true);
$initProductsINI =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_products.ini'), true);
$initUsersINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_users.ini'), true);
$settingsINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/settings.ini'), true);
//	Параметры подключения к TermoServer
$IPAddr		= $settingsINI['TermoServerIPAddr'];
$port		= $settingsINI['TermoServerPort'];
//	Необходимые параметры для подключения к БД
$servername	= $settingsINI['DBServerIPAddr'];
$username	= $settingsINI['DBUserName'];
$password	= $settingsINI['DBPassword'];
$dbname		= $settingsINI['DBName'];
//	Создание объекта PDO для работы с Базой Данных
$dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);	//[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
?>