<?php

$simulation_mode = true;
$debugPageDisableElements = $simulation_mode ? "" : "disabled";
//	Необходимые параметры для подключения к БД
const servername = "localhost"; const username = "root"; const password = ""; const dbname = "zernoib";
//	Создание объекта PDO для работы с Базой Данных
$dbh = new PDO("mysql:host=".servername.";dbname=".dbname, username, password/*, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]*/);
//	Подключение к TermoServer
$sql = "SELECT ts_ip, ts_port FROM zernoib.ts_conn_settings;";
$sth = $dbh->query($sql);
if($sth==false){
    $query = "CREATE TABLE IF NOT EXISTS `zernoib`.`ts_conn_settings`
                ( `ts_ip` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '127.000.000.1' , `ts_port` SMALLINT NOT NULL DEFAULT '200' )
                ENGINE = InnoDB;
              INSERT INTO zernoib.ts_conn_settings (ts_ip, ts_port) VALUES ('127.000.000.1', '200');";
    $stmt = $dbh->prepare($query);
    $stmt->execute();

    $IPAddr = '127.0.0.1'; $port = 200;
} else {
    $ts_conn_settings_row = $sth->fetchAll();
    $IPAddr = $ts_conn_settings_row[0]['ts_ip'];
    $port = $ts_conn_settings_row[0]['ts_port'];
}

//	Набор инициализационных параметров для работы с термосервером и БД
//  ! Все ini-файлы кроме конфигурационных необходимо убрать
$errCodesINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/ts_errors.ini'),      true);
$initProductsINI =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_products.ini'),  true);
$initUsersINI	 =	parse_ini_string(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/webTermometry/settings/init_users.ini'),     true);

?>