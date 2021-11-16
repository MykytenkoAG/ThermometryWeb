<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/Thermometry/currValsFromTS.php');

header('Content-type: json/application');

$method = $_SERVER['REQUEST_METHOD'];
$params = isset($_GET['q']) ? explode("/", $_GET['q']) : "";
$param_name = isset($params[0]) ? $params[0] : "";
$param0 = isset($params[1]) ? $params[1] : ""; $param1 = isset($params[2]) ? $params[2] : ""; $param2 = isset($params[3]) ? $params[3] : "";

//print_r($params);
echo "param_name: $param_name; param0: $param0; param1: $param1; param2: $param2;";

//  Функции
function apiGetSiloConfiguration($dbh, $silo_id=""){
    $query = "SELECT silo_id, silo_name, pbs.product_id,
                grain_level_fromTS, grain_level, is_square, size, position_col, position_row, silo_group, p.product_name, p.t_min, p.t_max, p.v_min, p.v_max 
              FROM prodtypesbysilo AS pbs INNER JOIN prodtypes AS p ON pbs.product_id = p.product_id ";
    if($silo_id!==""){
        $query .= " WHERE silo_id=".$silo_id;
    }
	$sth = $dbh->query($query);
    $rows = $sth->fetchAll();
    $outArr=[];
    foreach($rows as $row){
        $outArr[] = array(  "silo_id"=>$row["silo_id"],
                            "silo_name"=>$row["silo_name"],
                            "product_id"=>$row["product_id"],
                            "grain_level_fromTS"=>$row["grain_level_fromTS"],
                            "grain_level"=>$row["grain_level"],
                            "is_square"=>$row["is_square"],
                            "size"=>$row["size"],
                            "position_col"=>$row["position_col"],
                            "position_row"=>$row["position_row"],
                            "silo_group"=>$row["silo_group"],
                            "product_name"=>$row["product_name"],
                            "t_min"=>$row["t_min"],
                            "t_max"=>$row["t_max"],
                            "v_min"=>$row["v_min"],
                            "v_max"=>$row["v_max"]);
    }
    return $outArr;
}

function apiGetSensorsState($dbh, $silo_id="", $podv_id="", $sensor_num=""){
    $query = "  SELECT  sensor_id, s.silo_id, pbs.silo_name, podv_id, sensor_num, is_enabled,
                        current_temperature, current_speed,
                        curr_t_text, curr_v_text, curr_t_colour, curr_v_colour,
                        server_date,
                        NACK_Tmax, TIME_NACK_Tmax, ACK_Tmax, TIME_ACK_Tmax,
                        NACK_Vmax, TIME_NACK_Vmax, ACK_Vmax, TIME_ACK_Vmax,
                        NACK_err, TIME_NACK_err, ACK_err, TIME_ACK_err,
                        s.error_id, e.error_description, e.error_desc_for_visu
                    FROM sensors AS s
                    INNER JOIN prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id 
                    LEFT JOIN errors AS e ON s.error_id = e.error_id";
    if($silo_id!==""){
        $query .= " WHERE s.silo_id=".$silo_id;
    }
    if($podv_id!==""){
        $query .= " AND s.podv_id=".$podv_id;
    }
    if($sensor_num!==""){
        $query .= " AND s.sensor_num=".$sensor_num;
    }
	$sth = $dbh->query($query);
    $rows = $sth->fetchAll();
    $outArr=[];
    foreach($rows as $row){
        $outArr[] = array(  "sensor_id"=>$row["sensor_id"],
                            "silo_id"=>$row["silo_id"],
                            "silo_name"=>$row["silo_name"],
                            "podv_id"=>$row["podv_id"],
                            "sensor_num"=>$row["sensor_num"],
                            "is_enabled"=>$row["is_enabled"],
                            "current_temperature"=>$row["current_temperature"],
                            "current_speed"=>$row["current_speed"],
                            "curr_t_text"=>$row["curr_t_text"],
                            "curr_v_text"=>$row["curr_v_text"],
                            "curr_t_colour"=>$row["curr_t_colour"],
                            "curr_v_colour"=>$row["curr_v_colour"],
                            "server_date"=>$row["server_date"],
                            "NACK_Tmax"=>$row["NACK_Tmax"],
                            "TIME_NACK_Tmax"=>$row["TIME_NACK_Tmax"],
                            "ACK_Tmax"=>$row["ACK_Tmax"],
                            "TIME_ACK_Tmax"=>$row["TIME_ACK_Tmax"],
                            "NACK_Vmax"=>$row["NACK_Vmax"],
                            "TIME_NACK_Vmax"=>$row["TIME_NACK_Vmax"],
                            "ACK_Vmax"=>$row["ACK_Vmax"],
                            "TIME_ACK_Vmax"=>$row["TIME_ACK_Vmax"],
                            "NACK_err"=>$row["NACK_err"],
                            "TIME_NACK_err"=>$row["TIME_NACK_err"],
                            "ACK_err"=>$row["ACK_err"],
                            "TIME_ACK_err"=>$row["TIME_ACK_err"],
                            "error_id"=>$row["error_id"],
                            "error_description"=>$row["error_description"],
                            "error_desc_for_visu"=>$row["error_desc_for_visu"]);
    }
    return $outArr;
}

function apiGetAlarms($dbh){
    $query = "  SELECT  sensor_id, s.silo_id, pbs.silo_name, podv_id, sensor_num, is_enabled,
                    current_temperature, current_speed,
                    NACK_Tmax, DATE_FORMAT(TIME_NACK_Tmax,'%d.%m.%Y %H:%i:%s') AS f_TIME_NACK_Tmax, ACK_Tmax, DATE_FORMAT(TIME_ACK_Tmax,'%d.%m.%Y %H:%i:%s') AS f_TIME_ACK_Tmax,
                    NACK_Vmax, DATE_FORMAT(TIME_NACK_Vmax,'%d.%m.%Y %H:%i:%s') AS f_TIME_NACK_Vmax, ACK_Vmax, DATE_FORMAT(TIME_ACK_Vmax,'%d.%m.%Y %H:%i:%s') AS f_TIME_ACK_Vmax,
                    NACK_err, DATE_FORMAT(TIME_NACK_err,'%d.%m.%Y %H:%i:%s')   AS f_TIME_NACK_err,  ACK_err,  DATE_FORMAT(TIME_ACK_err,'%d.%m.%Y %H:%i:%s')  AS f_TIME_ACK_err,
                    s.error_id, e.error_description, e.error_desc_for_visu
                FROM sensors AS s
                INNER JOIN prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id
                LEFT JOIN errors AS e ON s.error_id = e.error_id
                where not isnull(s.error_id) or s.NACK_Tmax or s.ACK_Tmax or s.NACK_Vmax or s.ACK_Vmax
                order by TIME_NACK_err ASC, TIME_NACK_Tmax ASC, TIME_NACK_Vmax ASC, TIME_ACK_err ASC, TIME_ACK_Tmax ASC, TIME_ACK_Vmax ASC";

    $sth = $dbh->query($query);
    $rows = $sth->fetchAll();
    $outArr=[];
    foreach($rows as $row){
        if( !is_null($row['error_id']) ){
            $alarmType = $row['error_description'];
            $timeNACK = $row['f_TIME_NACK_err'];
            $timeACK = $row['f_TIME_ACK_err'];
        }
        if( $row["NACK_Tmax"]==1 || $row["ACK_Tmax"]==1 ){
            $alarmType = "Tmax";
            $timeNACK = $row['f_TIME_NACK_Tmax'];
            $timeACK = $row['f_TIME_ACK_Tmax'];
        }
        if( $row["NACK_Vmax"]==1 || $row["ACK_Vmax"]==1 ){
            $alarmType = "Vmax";
            $timeNACK = $row['f_TIME_NACK_Vmax'];
            $timeACK = $row['f_TIME_ACK_Vmax'];
        }
        $outArr[] = array(  "sensor_id"=>$row["sensor_id"],
                            "silo_id"=>$row["silo_id"],
                            "silo_name"=>$row["silo_name"],
                            "podv_id"=>$row["podv_id"],
                            "sensor_num"=>$row["sensor_num"],
                            "is_enabled"=>$row["is_enabled"],
                            "current_temperature"=>$row["current_temperature"],
                            "current_speed"=>$row["current_speed"],
                            "alarm_type"=>$alarmType,
                            "time_NACK"=>$timeNACK,
                            "time_ACK"=>$timeACK);
    }
    return $outArr;
}

function apiGetProdtypes($dbh){
    $query = "SELECT product_id, product_name, t_min, t_max, v_min, v_max FROM prodtypes;";
    $sth = $dbh->query($query);
    $rows = $sth->fetchAll();
    $outArr=[];
    foreach($rows as $row){
        $outArr[] = array(  "product_id"=>$row["product_id"],
                            "product_name"=>$row["product_name"],
                            "t_min"=>$row["t_min"],
                            "t_max"=>$row["t_max"],
                            "v_min"=>$row["v_min"],
                            "v_max"=>$row["v_max"]);
    }
    return $outArr;
}

function apiGetProdtypesbysilo($dbh){
    $query = "  SELECT silo_id, silo_name, bs_addr, grain_level_fromTS, grain_level,
                pbs.product_id, p.product_name FROM prodtypesbysilo AS pbs INNER JOIN prodtypes AS p ON pbs.product_id=p.product_id;";
    $sth = $dbh->query($query);
    $rows = $sth->fetchAll();
    $outArr=[];
    foreach($rows as $row){
        $outArr[] = array(  "silo_id"=>$row["silo_id"],
                            "silo_name"=>$row["silo_name"],
                            "bs_addr"=>$row["bs_addr"],
                            "grain_level_fromTS"=>$row["grain_level_fromTS"],
                            "grain_level"=>$row["grain_level"],
                            "product_id"=>$row["product_id"],
                            "product_name"=>$row["product_name"]);
    }
    return $outArr;
}

//  конфигурация силосов
if($param_name === "silo_configuration"){
    echo json_encode(apiGetSiloConfiguration($dbh, $param0));
}

//  состояние датчиков
if($param_name === "sensors"){
    echo json_encode(apiGetSensorsState($dbh, $param0, $param1, $param2));
}

//  текущее время сервера
if($param_name === "servertime"){
    echo $serverDate;
}

//  текущие сигналы АПС
if($param_name === "alarms"){
    echo json_encode(apiGetAlarms($dbh));
}

//  печатные формы
if($param_name === ""){

}

//  графики температуры
if($param_name === ""){

}

//  таблица "Типы продукта"
if($param_name === "prodtypes"){
    echo json_encode(apiGetProdtypes($dbh));
}

//  таблица "Загрузка силосов"
if($param_name === "prodtypesbysilo"){
    echo json_encode(apiGetProdtypesbysilo($dbh));
}

?>