<?php
require_once 'constants.php';
//	Подключение к Базе Данных ----------------------------------------------------------------------------------------------------------
$dbh = new PDO("mysql:host=".SERVER_NAME."; charset=utf8;", USERNAME, password, $pdo_options);
$dbh->query("CREATE DATABASE IF NOT EXISTS ".DBNAME." CHARACTER SET utf8 COLLATE utf8_general_ci;"); $dbh->query("use ".DBNAME);
//	Подключение к ПО TermoServer -------------------------------------------------------------------------------------------------------
$IPAddr; $port;
$sql = SQL_STATEMENT_SELECT_TS_CONN_SETTINGS;
$sth = $dbh->query($sql);
if($sth==false){
    $query = SQL_STATEMENT_CREATE_TS_CONN_SETTINGS.SQL_STATEMENT_INIT_TS_CONN_SETTINGS;     //  Если таблица была удалена, создаем новую
    $stmt = $dbh->prepare($query);
    $stmt->execute();
    $IPAddr = '127.0.0.1'; $port = 200;
 } else {
    $ts_conn_settings_row = $sth->fetchAll();
    $IPAddr = $ts_conn_settings_row[0]['ts_ip'];
    $port   = $ts_conn_settings_row[0]['ts_port'];
}

?>