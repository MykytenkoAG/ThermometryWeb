<?php

$simulation_mode = true;
$debugPageDisableElements = $simulation_mode ? "" : "disabled";
//	Набор инициализационных параметров для работы с термосервером и БД
$errCodesINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/ts_errors.ini'), true);
$initProductsINI =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_products.ini'), true);
$initUsersINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_users.ini'), true);
//	Параметры подключения к TermoServer
$IPAddr		= '127.0.0.1';
$port		= 200;
//	Необходимые параметры для подключения к БД
$servername	= "localhost";
$username	= "root";
$password	= "";
$dbname		= "zernoib";
//	Создание объекта PDO для работы с Базой Данных
$dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);	//[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]

?>