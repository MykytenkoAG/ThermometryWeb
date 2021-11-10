<?php
require_once ('constants.php');
//  Выбор режима работы ----------------------------------------------------------------------------------------------------------------
$simulation_mode = true;
                   //false;
$debugPageDisableElements = $simulation_mode ? "" : "disabled";     //  Отключение элементов на странице отладки
//	Подключение к Базе Данных ----------------------------------------------------------------------------------------------------------
const servername = "localhost"; const username = "root"; const password = ""; const dbname = "zernoib"; mb_internal_encoding("UTF-8");
$pdo_options = [
    PDO::ATTR_TIMEOUT => 5//,
    //PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION//,
    //PDO::ATTR_CASE => PDO::CASE_NATURAL//,
    //PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING
];
$dbh = new PDO("mysql:host=".servername, username, password, $pdo_options);
$dbh->query("CREATE DATABASE IF NOT EXISTS ".dbname); $dbh->query("use ".dbname);
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