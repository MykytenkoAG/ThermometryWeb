<?php

require_once (substr(__DIR__,0,-4).'/ts/currValsFromTS.php');
//  Получение главной отладочной таблицы
function vDbg_draw_debugParamsTable($dbh){

    $sql = "SELECT s.sensor_id, pbs.silo_name, s.podv_id, s.sensor_num, s.current_temperature, s.current_speed, pbs.grain_level, e.error_desc_short
            FROM ".DBNAME.".sensors AS s
            INNER JOIN prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id
            LEFT JOIN errors AS e ON s.error_id = e.error_id;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();

    $outStr = "<table>";

    $outStr .= "<tr>";

    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Силос"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Подвеска"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Датчик"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Температура"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Скорость"."</td>";
    $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">"."Уровень"."</td>";

    $outStr .= "</tr>";

    foreach($rows as $row){

        $outStr .= "<tr>";

        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">". $row['silo_name']."</td>";
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">".($row['podv_id']+1)."</td>";
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">".($row['sensor_num']+1)."</td>";

        if( ! is_null($row['current_temperature']) ){
            $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">". $row['current_temperature']."</td>";
            $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">". $row['current_speed']."</td>";
        } else if ( ! is_null($row['error_desc_short']) ){
            $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">". $row['error_desc_short']."</td>";
            $outStr .= "<td style=\"text-align: center; padding-right: 10px;\"></td>";
        } else {
            $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">?</td>";
            $outStr .= "<td style=\"text-align: center; padding-right: 10px;\"></td>";
        }

        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">". $row['grain_level']."</td>";

        $outStr .= "</tr>";

    }

    $outStr .= "</table>";

    return $outStr;
}

if( isset( $_POST['POST_vDbgPage_dbg_refresh'] ) ) {
    echo vDbg_draw_debugParamsTable($dbh);
}
//  Установка температуры для всех датчиков определенного силоса
function vDbg_set_temperatureForCurrSilo($dbh, $silo_name, $value){
	
	$query="UPDATE debug_sensors SET current_temperature = $value WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name);";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['POST_vDbgPage_dbg_1_silo_name']) && isset($_POST['POST_vDbgPage_dbg_1_temperature'])) {
    vDbg_set_temperatureForCurrSilo($dbh, $_POST['POST_vDbgPage_dbg_1_silo_name'], $_POST['POST_vDbgPage_dbg_1_temperature']);
    echo "Температура всех датчиков силоса ".$_POST['POST_vDbgPage_dbg_1_silo_name']." установлена в ".$_POST['POST_vDbgPage_dbg_1_temperature'];
}
//  Установка скорости изменения температуры для всех датчиков определенного силоса
function vDbg_set_temperatureSpeedForCurrSilo($dbh, $silo_name, $value){
	
	$query="UPDATE debug_sensors SET current_temperature_speed = $value WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name);";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['POST_vDbgPage_dbg_2_silo_name']) && isset($_POST['POST_vDbgPage_dbg_2_t_speed'])) {
    vDbg_set_temperatureSpeedForCurrSilo($dbh, $_POST['POST_vDbgPage_dbg_2_silo_name'], $_POST['POST_vDbgPage_dbg_2_t_speed']);
    echo "Скорость всех датчиков силоса ".$_POST['POST_vDbgPage_dbg_2_silo_name']." установлена в ".$_POST['POST_vDbgPage_dbg_2_t_speed'];
}
//  Установка уровня заполнения для определенного силоса
function vDbg_set_grainLevelForCurrSilo($dbh, $silo_name, $value){
	
	$query="UPDATE debug_silo SET grain_level = $value WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name);";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['POST_vDbgPage_dbg_3_silo_name']) && isset($_POST['POST_vDbgPage_dbg_3_grain_level'])) {
    vDbg_set_grainLevelForCurrSilo($dbh, $_POST['POST_vDbgPage_dbg_3_silo_name'], $_POST['POST_vDbgPage_dbg_3_grain_level']);
    echo "Уровень заполнения силоса ".$_POST['POST_vDbgPage_dbg_3_silo_name']." установлен в ".$_POST['POST_vDbgPage_dbg_3_grain_level'];
}
//  Установка температуры для всех датчиков определенной подвески
function vDbg_set_temperatureForCurrPodv($dbh, $silo_name, $podv_id, $value){
	
	$query="UPDATE debug_sensors SET current_temperature = $value
            WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name) AND podv_id=".($podv_id-1).";";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['POST_vDbgPage_dbg_4_silo_name']) && isset($_POST['POST_vDbgPage_dbg_4_podv_num']) && isset($_POST['POST_vDbgPage_dbg_4_temperature'])) {
    vDbg_set_temperatureForCurrPodv($dbh, $_POST['POST_vDbgPage_dbg_4_silo_name'], $_POST['POST_vDbgPage_dbg_4_podv_num'], $_POST['POST_vDbgPage_dbg_4_temperature']);
    echo "Температура всех датчиков силоса ".$_POST['POST_vDbgPage_dbg_4_silo_name']," подвески ".$_POST['POST_vDbgPage_dbg_4_podv_num']." установлена в ".$_POST['POST_vDbgPage_dbg_4_temperature'];
}
//  Установка скорости изменения температуры для всех датчиков определенной подвески
function vDbg_set_temperatureSpeedForCurrPodv($dbh, $silo_name, $podv_id, $value){
	
	$query="UPDATE debug_sensors SET current_temperature_speed = $value
            WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name) AND podv_id=".($podv_id-1).";";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['POST_vDbgPage_dbg_5_silo_name']) && isset($_POST['POST_vDbgPage_dbg_5_podv_num']) && isset($_POST['POST_vDbgPage_dbg_5_t_speed'])) {
    vDbg_set_temperatureSpeedForCurrPodv($dbh, $_POST['POST_vDbgPage_dbg_5_silo_name'], $_POST['POST_vDbgPage_dbg_5_podv_num'], $_POST['POST_vDbgPage_dbg_5_t_speed']);
    echo "Скорость всех датчиков силоса ".$_POST['POST_vDbgPage_dbg_5_silo_name']," подвески ".$_POST['POST_vDbgPage_dbg_5_podv_num']." установлена в ".$_POST['POST_vDbgPage_dbg_5_t_speed'];
}
//  Установка температуры для определенного датчика
function vDbg_set_temperatureForCurrSensor($dbh, $silo_name, $podv_id, $sensor_num, $value){
	
	$query="UPDATE debug_sensors SET current_temperature = $value
            WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name) AND podv_id=".($podv_id-1)." AND sensor_num=".($sensor_num-1).";";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['POST_vDbgPage_dbg_6_silo_name']) && isset($_POST['POST_vDbgPage_dbg_6_podv_num']) && isset($_POST['POST_vDbgPage_dbg_6_sensor_num']) && isset($_POST['POST_vDbgPage_dbg_6_temperature'])) {
    vDbg_set_temperatureForCurrSensor($dbh, $_POST['POST_vDbgPage_dbg_6_silo_name'], $_POST['POST_vDbgPage_dbg_6_podv_num'], $_POST['POST_vDbgPage_dbg_6_sensor_num'], $_POST['POST_vDbgPage_dbg_6_temperature']);
    echo "Температура датчика ".$_POST['POST_vDbgPage_dbg_6_sensor_num']." подвески ".$_POST['POST_vDbgPage_dbg_6_podv_num']." силоса ".$_POST['POST_vDbgPage_dbg_6_silo_name']." установлена в ".$_POST['POST_vDbgPage_dbg_6_temperature'];
}
//  Установка скорости изменения температуры для определенного датчика
function vDbg_set_temperatureSpeedForCurrSensor($dbh, $silo_name, $podv_id, $sensor_num, $value){
	
	$query="UPDATE debug_sensors SET current_temperature_speed = $value
            WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name) AND podv_id=".($podv_id-1)." AND sensor_num=".($sensor_num-1).";";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['POST_vDbgPage_dbg_7_silo_name']) && isset($_POST['POST_vDbgPage_dbg_7_podv_num']) && isset($_POST['POST_vDbgPage_dbg_7_sensor_num']) && isset($_POST['POST_vDbgPage_dbg_7_t_speed'])) {
    vDbg_set_temperatureSpeedForCurrSensor($dbh, $_POST['POST_vDbgPage_dbg_7_silo_name'], $_POST['POST_vDbgPage_dbg_7_podv_num'], $_POST['POST_vDbgPage_dbg_7_sensor_num'], $_POST['POST_vDbgPage_dbg_7_t_speed']);
    echo "Скорость датчика ".$_POST['POST_vDbgPage_dbg_7_sensor_num']." подвески ".$_POST['POST_vDbgPage_dbg_7_podv_num']." силоса ".$_POST['POST_vDbgPage_dbg_7_silo_name']." установлена в ".$_POST['POST_vDbgPage_dbg_7_t_speed'];
}
//  Установка всех отладочных параметров в 0
function vDbg_set_allValuesTo_0($dbh){
	
	$query="UPDATE debug_sensors SET current_temperature=0, current_temperature_speed = 0;";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset( $_POST['POST_vDbgPage_dbg_8_set_all_params_to_0']) ) {
    vDbg_set_allValuesTo_0($dbh);
    echo "Отладочные параметры установлены в ноль";
}
//  Сохранение текущих значений параметров в Базу Данных
if( isset( $_POST['POST_vDbgPage_write_measurements_to_db'] ) ) {
    require_once ('dbMeasurements.php');
    echo "Текущие параметры занесены в БД";
}

?>