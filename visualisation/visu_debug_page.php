<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/currValsFromTS.php');

function debug_get_debug_table($dbh){

    $sql = "SELECT s.sensor_id, pbs.silo_name, s.podv_id, s.sensor_num, s.current_temperature, s.current_speed, pbs.grain_level 
            FROM zernoib.sensors s INNER JOIN prodtypesbysilo pbs ON s.silo_id = pbs.silo_id";

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
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">". $row['current_temperature']."</td>";
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">". $row['current_speed']."</td>";
        $outStr .= "<td style=\"text-align: center; padding-right: 10px;\">". $row['grain_level']."</td>";

        $outStr .= "</tr>";

    }

    $outStr .= "</table>";

    $outStr .= "
      <div class=\"modal fade\" id=\"dbg-main-modal\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\" tabindex=\"-1\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
        <div class=\"modal-dialog modal-dialog-centered\">
          <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title\" id=\"staticBackdropLabel\">Debug</h5>
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
            </div>
            <div class=\"modal-body\"><h5 id=\"dbg-modal-body-message\"></h5></div>
            <div class=\"modal-footer\">
                <div style=\"margin: auto;\">
                    <button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\">OK</button>
                </div>
            </div>
          </div>
        </div>
      </div>
    ";

    return $outStr;
}

if( isset( $_POST['dbg_refresh'] ) ) {
    echo debug_get_debug_table($dbh);
}

function debug_set_silo_temperature($dbh, $silo_name, $value){
	
	$query="UPDATE debug_sensors SET current_temperature = $value WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name);";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['dbg_1_silo_name']) && isset($_POST['dbg_1_temperature'])) {
    debug_set_silo_temperature($dbh, $_POST['dbg_1_silo_name'], $_POST['dbg_1_temperature']);
    echo "Температура всех датчиков силоса ".$_POST['dbg_1_silo_name']." установлена в ".$_POST['dbg_1_temperature'];
}

function debug_set_silo_temperature_speed($dbh, $silo_name, $value){
	
	$query="UPDATE debug_sensors SET current_temperature_speed = $value WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name);";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['dbg_2_silo_name']) && isset($_POST['dbg_2_t_speed'])) {
    debug_set_silo_temperature_speed($dbh, $_POST['dbg_2_silo_name'], $_POST['dbg_2_t_speed']);
    echo "Скорость всех датчиков силоса ".$_POST['dbg_2_silo_name']." установлена в ".$_POST['dbg_2_t_speed'];
}

function debug_set_silo_level($dbh, $silo_name, $value){
	
	$query="UPDATE debug_silo SET grain_level = $value WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name);";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['dbg_3_silo_name']) && isset($_POST['dbg_3_grain_level'])) {
    debug_set_silo_level($dbh, $_POST['dbg_3_silo_name'], $_POST['dbg_3_grain_level']);
    echo "Уровень заполнения силоса ".$_POST['dbg_3_silo_name']." установлен в ".$_POST['dbg_3_grain_level'];
}

function debug_set_podv_temperature($dbh, $silo_name, $podv_id, $value){
	
	$query="UPDATE debug_sensors SET current_temperature = $value
            WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name) AND podv_id=".($podv_id-1).";";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['dbg_4_silo_name']) && isset($_POST['dbg_4_podv_num']) && isset($_POST['dbg_4_temperature'])) {
    debug_set_podv_temperature($dbh, $_POST['dbg_4_silo_name'], $_POST['dbg_4_podv_num'], $_POST['dbg_4_temperature']);
    echo "Температура всех датчиков силоса ".$_POST['dbg_4_silo_name']," подвески ".$_POST['dbg_4_podv_num']." установлена в ".$_POST['dbg_4_temperature'];
}

function debug_set_podv_temperature_speed($dbh, $silo_name, $podv_id, $value){
	
	$query="UPDATE debug_sensors SET current_temperature_speed = $value
            WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name) AND podv_id=".($podv_id-1).";";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['dbg_5_silo_name']) && isset($_POST['dbg_5_podv_num']) && isset($_POST['dbg_5_t_speed'])) {
    debug_set_podv_temperature_speed($dbh, $_POST['dbg_5_silo_name'], $_POST['dbg_5_podv_num'], $_POST['dbg_5_t_speed']);
    echo "Скорость всех датчиков силоса ".$_POST['dbg_5_silo_name']," подвески ".$_POST['dbg_5_podv_num']." установлена в ".$_POST['dbg_5_t_speed'];
}

function debug_set_sensor_temperature($dbh, $silo_name, $podv_id, $sensor_num, $value){
	
	$query="UPDATE debug_sensors SET current_temperature = $value
            WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name) AND podv_id=".($podv_id-1)." AND sensor_num=".($sensor_num-1).";";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['dbg_6_silo_name']) && isset($_POST['dbg_6_podv_num']) && isset($_POST['dbg_6_sensor_num']) && isset($_POST['dbg_6_temperature'])) {
    debug_set_sensor_temperature($dbh, $_POST['dbg_6_silo_name'], $_POST['dbg_6_podv_num'], $_POST['dbg_6_sensor_num'], $_POST['dbg_6_temperature']);
    echo "Температура датчика ".$_POST['dbg_6_sensor_num']." подвески ".$_POST['dbg_6_podv_num']." силоса ".$_POST['dbg_6_silo_name']." установлена в ".$_POST['dbg_6_temperature'];
}

function debug_set_sensor_temperature_speed($dbh, $silo_name, $podv_id, $sensor_num, $value){
	
	$query="UPDATE debug_sensors SET current_temperature_speed = $value
            WHERE silo_id=(SELECT silo_id FROM prodtypesbysilo WHERE silo_name=$silo_name) AND podv_id=".($podv_id-1)." AND sensor_num=".($sensor_num-1).";";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['dbg_7_silo_name']) && isset($_POST['dbg_7_podv_num']) && isset($_POST['dbg_7_sensor_num']) && isset($_POST['dbg_7_t_speed'])) {
    debug_set_sensor_temperature_speed($dbh, $_POST['dbg_7_silo_name'], $_POST['dbg_7_podv_num'], $_POST['dbg_7_sensor_num'], $_POST['dbg_7_t_speed']);
    echo "Скорость датчика ".$_POST['dbg_7_sensor_num']." подвески ".$_POST['dbg_7_podv_num']." силоса ".$_POST['dbg_7_silo_name']." установлена в ".$_POST['dbg_7_t_speed'];
}

function debug_set_all_parameters_to_0($dbh){
	
	$query="UPDATE debug_sensors SET current_temperature=0, current_temperature_speed = 0;";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['dbg_8_set_all_params_to_0']) ) {
    debug_set_all_parameters_to_0($dbh);
    echo "Отладочные параметры установлены в ноль";
}



if( isset( $_POST['write_measurements_to_db'] ) ) {
    require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbMeasurements.php');
    addNewMeasurement($dbh, $arrayOfTemperatures, $serverDate);
    echo "Текущие параметры занесены в БД";
}

?>