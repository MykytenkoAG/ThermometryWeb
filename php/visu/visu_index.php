<?php

require_once (substr(__DIR__,0,-4).'/auth/auth.php');
require_once (substr(__DIR__,0,-4).'/ts/currValsFromTS.php');

// Левый сайтбар ------------------------------------------------------------------------------------------------------------------------------------------------------
//  OUT = html table < NACK, time, silo_name, podv_num, sensor_num, reason >
function vInd_getCurrAlarms($dbh){

    $outArr = array();
    $NACK = 0; $timeOfAlarm=""; $silo_name=""; $podv_num=0; $sensor_num=0; $reason="";

    //  Определяем проблемы, связанные с работой БС
    $defected_silo = array();

    $sql = "SELECT	s.silo_id, pbs.silo_name,
                COUNT(s.sensor_id) AS count_sensor_id,
                SUM(IF(s.error_id = 253, 1, 0)) AS silo_sum_err_253,
                SUM(IF(s.error_id = 254, 1, 0)) AS silo_sum_err_254,
                SUM(IF(s.error_id = 256, 1, 0)) AS silo_sum_err_256,
		        s.NACK_err, s.TIME_NACK_err,
                e.error_description, e.error_desc_for_visu
            FROM sensors AS s
            INNER JOIN prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id 
            LEFT JOIN errors AS e ON s.error_id = e.error_id
            GROUP BY silo_id
            HAVING (count_sensor_id=silo_sum_err_253 OR
                    count_sensor_id=silo_sum_err_254 OR
                    count_sensor_id=silo_sum_err_256);";

    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $defected_silo_rows = $sth->fetchAll();

    foreach($defected_silo_rows as $defected_silo_row){

        $outArr[] = array(  'NACK'          => $defected_silo_row["NACK_err"],
                            'timeOfAlarm'   => $defected_silo_row["TIME_NACK_err"],
                            'silo_name'     => $defected_silo_row["silo_name"],
                            'podv_num'      => "",
                            'sensor_num'    => "",
                            'reason'        => $defected_silo_row["error_desc_for_visu"]);

        array_push($defected_silo, $defected_silo_row["silo_name"]);
        
    }

    //  Определяем проблемы, связанные с работой ТП
    $defected_podv = array();

    $sql = "SELECT	s.silo_id, pbs.silo_name, s.podv_id,
                COUNT(sensor_id) AS count_sensor_id,
                SUM(IF(s.error_id = 251, 1, 0)) AS silo_sum_err_251,
                SUM(IF(s.error_id = 252, 1, 0)) AS silo_sum_err_252,
		        s.NACK_err, s.TIME_NACK_err,
                e.error_description, e.error_desc_for_visu
            FROM sensors AS s
            INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
            LEFT JOIN errors AS e ON s.error_id = e.error_id
            GROUP BY silo_id, podv_id
            HAVING (count_sensor_id=silo_sum_err_251 OR
                    count_sensor_id=silo_sum_err_252);";

    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $defected_podv_rows = $sth->fetchAll();

    foreach($defected_podv_rows as $defected_podv_row){

        $outArr[] = array(  'NACK'          => $defected_podv_row["NACK_err"],
                            'timeOfAlarm'   => $defected_podv_row["TIME_NACK_err"],
                            'silo_name'     => $defected_podv_row["silo_name"],
                            'podv_num'      => ($defected_podv_row["podv_id"]+1),
                            'sensor_num'    => "",
                            'reason'        => $defected_podv_row["error_desc_for_visu"]);

        array_push($defected_podv, strval($defected_podv_row["silo_name"]).",".strval($defected_podv_row["podv_id"]));
    }

    //  Определяем алармы датчиков
    $sql = "SELECT  s.sensor_id, s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num,
                    s.NACK_Tmax, s.TIME_NACK_Tmax, s.ACK_Tmax, s.TIME_ACK_Tmax,
                    s.NACK_Vmax, s.TIME_NACK_Vmax, s.ACK_Vmax, s.TIME_ACK_Vmax,
                    s.NACK_err, s.TIME_NACK_err, s.ACK_err, s.TIME_ACK_err,
                    s.error_id, e.error_description, e.error_desc_for_visu
                    FROM sensors AS s LEFT JOIN errors AS e ON s.error_id=e.error_id INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
                    WHERE s.NACK_Tmax=1 OR s.ACK_Tmax=1 OR s.NACK_Vmax=1 OR s.ACK_Vmax=1 OR s.NACK_err=1 OR s.ACK_err=1;";
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $sensor_alarm_rows = $sth->fetchAll();

    foreach($sensor_alarm_rows as $sensor_alarm_row){

        if( in_array($sensor_alarm_row["silo_name"], $defected_silo) ){
            continue;
        }

        if( in_array($sensor_alarm_row["silo_name"].",".$sensor_alarm_row["podv_id"], $defected_podv) ){
            continue;
        }

        if($sensor_alarm_row['NACK_Tmax']==1 or $sensor_alarm_row['NACK_Vmax']==1 or $sensor_alarm_row['NACK_err']==1){
            $NACK = 1;
        } else {
            $NACK = 0;
        }

        if($sensor_alarm_row['TIME_NACK_Tmax']!=null){
            $timeOfAlarm = $sensor_alarm_row['TIME_NACK_Tmax'];
            $reason = "T крит.";
        } else  if ($sensor_alarm_row['TIME_NACK_Vmax']!=null){
            $timeOfAlarm = $sensor_alarm_row['TIME_NACK_Vmax'];
            $reason = "V крит.";
        } else  if ($sensor_alarm_row['TIME_NACK_err']!=null){
            $timeOfAlarm = $sensor_alarm_row['TIME_NACK_err'];
            $reason = $sensor_alarm_row['error_desc_for_visu'];
        }

        $silo_name = $sensor_alarm_row['silo_name'];
        $podv_num = $sensor_alarm_row['podv_id'] + 1;
        $sensor_num = $sensor_alarm_row['sensor_num'] + 1;

        $outArr[] = array(  'NACK'          => $NACK,
                            'timeOfAlarm'   => $timeOfAlarm,
                            'silo_name'     => $silo_name,
                            'podv_num'      => $podv_num,
                            'sensor_num'    => $sensor_num,
                            'reason'        => $reason);

    }

    //  Сортируем выходной ассоциативный массив по времени появления сигнала АПС
    $c_NACK  = array_column($outArr, 'NACK');
    $c_timeOfAlarm  = array_column($outArr, 'timeOfAlarm');
    $c_silo_name  = array_column($outArr, 'silo_name');
    $c_podv_num  = array_column($outArr, 'podv_num');
    $c_sensor_num  = array_column($outArr, 'sensor_num');
    $c_reason  = array_column($outArr, 'reason');

    array_multisort($c_timeOfAlarm, SORT_ASC, $c_silo_name, SORT_ASC, $c_podv_num, SORT_ASC, $c_sensor_num, SORT_ASC, $c_NACK, SORT_ASC, $outArr);

    //  Формируем выходную таблицу
    $outStr = "<table class=\"table table-striped\">";
    //$outStr .= "<tr><th>Время</th><th>Силос</th><th>ТП</th><th>Датчик</th><th>Тип АПС</th></tr>";

    foreach($outArr as $val){
        $outStr .= "<tr ";

        if($val['NACK']){
            $outStr .= "style=\"font-weight: bold;\"";
        }

        $outStr .= "><td style=\"width: 140px; margin:0px; padding: 0px;\">";

        $outStr .= (new \DateTime($val['timeOfAlarm']))->format("d.m.Y H:i")
                ."</td><td style=\"width: 60px; margin:0px; padding: 0px; text-align:center\">".$val['silo_name']
                ."</td><td style=\"width: 30px; margin:0px; padding: 0px;\">".$val['podv_num']
                ."</td><td style=\"width: 40px; margin:0px; padding: 0px;\">".$val['sensor_num']
                ."</td><td style=\"width: 140px; margin:0px; padding: 0px;\">".$val['reason'];

                $outStr .= "</td></tr>";
    }

    $outStr .= "</tr></table>";
    
    return $outStr;
}

if( isset($_POST['POST_vInd_get_current_alarms']) ) {
    echo vInd_getCurrAlarms($dbh);
}
//  Выключить все неисправные датчики
function vInd_disAllDefectiveSensors($dbh){
	
	$query="UPDATE sensors SET is_enabled=0 WHERE current_temperature > 84";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['POST_vInd_dis_all_defective_sensors']) ) {
	vInd_disAllDefectiveSensors($dbh);
    echo "Датчики включены";
}
//  Включить все отключенные датчики
function vInd_enAllSensors($dbh){
	
	$query="UPDATE sensors SET is_enabled=1";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['POST_vInd_enable_all_sensors']) ) {
	vInd_enAllSensors($dbh);
    echo "датчики отключены";
}
//  Включить автоопределение уровня на всех силосах
function vInd_enAutoLvlOnAllSilo($dbh){
    $query="UPDATE prodtypesbysilo SET grain_level_fromTS = 1;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['POST_vInd_enable_auto_lvl_mode_on_all_silo']) ) {
    vInd_enAutoLvlOnAllSilo($dbh);
}

//  Основная часть ----------------------------------------------------------------------------------------------------------------------------------------------------
//  Функция отрисовки главного плана расположения силосов
function vInd_drawSiloPlan($dbh){ 

    $sql = "SELECT  pbs.silo_id, pbs.silo_name, pbs.grain_level_fromTS, pbs.grain_level,
                pbs.is_square, pbs.size, pbs.position_col, pbs.position_row,
                pt.product_name, pt.t_max, pt.t_min, pt.v_max, pt.v_min,
                MAX(s.current_temperature), MIN(s.current_temperature), MAX(s.current_speed),
                pbs.silo_group, sg.silo_group_name, sg.silo_group_col, sg.silo_group_row, sg.silo_group_size,
                (pbs.position_col + sg.silo_group_col) AS table_pos_col, (pbs.position_row + sg.silo_group_row) AS table_pos_row
            FROM prodtypesbysilo AS pbs INNER JOIN prodtypes AS pt ON pbs.product_id=pt.product_id
            INNER JOIN sensors AS s ON pbs.silo_id = s.silo_id
            INNER JOIN silosesgroups AS sg ON pbs.silo_group = sg.silo_group 
            GROUP BY s.silo_id
            ORDER BY silo_group, position_row, position_col;";

    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $siloConfigRows = $sth->fetchAll();
    //  Определяем количество строк таблицы
    $sql = "SELECT MAX(pbs.position_row + sg.silo_group_row) FROM prodtypesbysilo AS pbs INNER JOIN silosesgroups AS sg ON pbs.silo_group = sg.silo_group;";
    $sth = $dbh->query($sql);
    $rowsNumber = $sth->fetch()['MAX(pbs.position_row + sg.silo_group_row)'];
    //  Определяем количество столбцов таблицы
    $sql = "SELECT MAX(pbs.position_col + sg.silo_group_col) FROM prodtypesbysilo AS pbs INNER JOIN silosesgroups AS sg ON pbs.silo_group = sg.silo_group;";
    $sth = $dbh->query($sql);
    $colsNumber = $sth->fetch()['MAX(pbs.position_col + sg.silo_group_col)'];

    $outStr = "<table style=\"width:100%;\">";

    for($i = 0; $i <= $rowsNumber; $i++){
        $outStr .= "<tr>";
        for($j = 0; $j <= $colsNumber; $j++){
            $outStr .= "<td class=\"silo\">";
            foreach($siloConfigRows as $siloConfigRow){
                if($siloConfigRow['table_pos_col']==($j+1) and $siloConfigRow['table_pos_row']==$i){

                    //  Всплывающая подсказка
                    $siloTooltip = " Тип продукта : ".$siloConfigRow['product_name']."; 
 Tmax : ".$siloConfigRow['t_max']."&deg;C"." ;
 Tmin : ".$siloConfigRow['t_min']."&deg;C"." ;
 Vmax : ".$siloConfigRow['v_max']."&deg;C/сут."." ;
 Vmin : ".$siloConfigRow['v_min']."&deg;C/сут."." ;";

                    $sql = "SELECT COUNT(DISTINCT(sensor_num))
                            FROM sensors
                            WHERE silo_id=".$siloConfigRow['silo_id'];
                    $sth = $dbh->query($sql);
                    $maxSensorNumber = $sth->fetch()['COUNT(DISTINCT(sensor_num))'];

                $siloTooltip .= "
 Уровень заполнения: ".(round(($siloConfigRow['grain_level']/$maxSensorNumber)*100))." % ;";

    if($siloConfigRow['MIN(s.current_temperature)']<85 and $siloConfigRow['MAX(s.current_temperature)']<85){
 $siloTooltip .= "
 Диапазон температур : ".$siloConfigRow['MIN(s.current_temperature)']."&deg;C"." .. ".$siloConfigRow['MAX(s.current_temperature)']."&deg;C ;
 Максимальная скорость : ".$siloConfigRow['MAX(s.current_speed)']."&deg;C/сут.; ";
                    }

                    //  Имя силоса
                    $fontSize= round(200/$colsNumber);
                    if($fontSize>24){
                        $fontSize=24;
                    }
                    $outStr .= "<div class=\"d-inline silo-number\" style=\"padding: 5px; font-size: $fontSize px;\">"
                    .$siloConfigRow['silo_name']."</div>";


                    if($siloConfigRow['is_square']){
                        //  Если силос круглый
                        $outStr .= "<img src=\"/Thermometry/assets/img/silo_square_OK.png\"
                        id=\"silo-".$siloConfigRow['silo_id']."\" onclick=\"vIndOnClickOnSilo(event.target.id)\"

                        data-bs-toggle=\"tooltip\" data-bs-placement=\"right\" title=\"$siloTooltip\"

                        style=\"display: block; margin-left: auto; margin-right: auto; width: ".($siloConfigRow['size']*100)."%;\"/>";
                    } else{
                        //  Если силос квадратный
                        $outStr .= "<img src=\"/Thermometry/assets/img/silo_round_OK.png\"
                        id=\"silo-".$siloConfigRow['silo_id']."\" onclick=\"vIndOnClickOnSilo(event.target.id)\"

                        data-bs-toggle=\"tooltip\" data-bs-placement=\"right\" title=\"$siloTooltip\"

                        style=\"display: block; margin-left: auto; margin-right: auto; width: ".($siloConfigRow['size']*100)."%;\"/>";
                    }

                }
            }
            $outStr .= "</td>";
		}
        $outStr .= "</tr>";
	}
    $outStr .= "</table>";

    $outStr .= "

    ";

    return $outStr;
}

//  out: = [silo_id=>[{round,square},img_index]]
function vInd_getSiloCurrStatus($dbh){

    $outArr = array();    
    
    $sql = "SELECT  sensor_id, s.silo_id,
                    NACK_Tmax, ACK_Tmax, NACK_Vmax, ACK_Vmax, NACK_err, ACK_err,
                    error_id, pbs.is_square
            FROM sensors AS s inner join prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id;";

    $sth = $dbh->query($sql);

    if($sth==false){
    return false;
    }
    $rows = $sth->fetchAll();

    $curr_silo_id=""; $curr_silo_status=""; $curr_silo_type="";

    foreach($rows as $row){

        if($curr_silo_id!=$row['silo_id']){
            $curr_silo_id = $row['silo_id'];
            if($curr_silo_status!=""){
                array_push($outArr, array($curr_silo_type, $curr_silo_status) );
            }
            $curr_silo_status=5;
        }

        $curr_silo_type = $row['is_square'];                                                                        //  0: round, 1: square


        if( in_array($row['error_id'],array(255,256))){
            $curr_silo_status = 0;                                                                                  //  OFF
            continue;
        }

        if( in_array($row['error_id'],array(253,254))){
            $curr_silo_status = 1;                                                                                  //  CRC
            continue;
        }

        if( $row['NACK_Tmax']==1 or $row['NACK_Vmax']==1 or $row['NACK_err']==1){
            $curr_silo_status = 2;                                                                                  //  NACK
            continue;
        }

        if( $curr_silo_status!=3 and
            ($row['ACK_Tmax']==1 or $row['ACK_Vmax']==1 or $row['ACK_err']==1)){
            $curr_silo_status = 3;                                                                                  //  ACK
            continue;
        }

        if( !in_array($curr_silo_status,array(0,1,2,3)) and
            ($row['NACK_Tmax']==0 and $row['NACK_Vmax']==0 and $row['NACK_err']==0 and
             $row['ACK_Tmax']==0 and $row['ACK_Vmax']==0 and $row['ACK_err']==0)){
            $curr_silo_status = 4;                                                                                  //  OK
            continue;
        }

    }

    //array_push($outArr, $curr_silo_type.$curr_silo_status);
    array_push($outArr, array($curr_silo_type, $curr_silo_status) );

    return $outArr;
}

if( isset($_POST['POST_vInd_get_curr_silo_status']) ) {
    echo json_encode(vInd_getSiloCurrStatus($dbh));
}

//  Правый сайтбар -----------------------------------------------------------------------------------------------------------------------------------------------------
//  Получение текущих параметров продукта для текущего силоса
//  out: [название продукта, Tmax, Vmax, ProdTmin, ProdTavg, ProdTmax, ProdVmin, ProdVavg, ProdVmax, RngTmin, RngTmax, RngVmax]
function vInd_getSiloProductParams($dbh, $silo_id){

    $silo_id = preg_split('/-/',$silo_id,-1,PREG_SPLIT_NO_EMPTY)[count(preg_split('/-/',$silo_id,-1,PREG_SPLIT_NO_EMPTY))-1];

    $sql = "SELECT  pbs.silo_id,
                    pt.product_name, pt.t_max, pt.t_min, pt.v_max, pt.v_min,
                    MAX(s.current_temperature), MIN(s.current_temperature), MAX(s.current_speed)
            FROM prodtypesbysilo AS pbs INNER JOIN prodtypes AS pt ON pbs.product_id=pt.product_id INNER JOIN sensors AS s ON pbs.silo_id=s.silo_id
            GROUP BY s.silo_id
            HAVING silo_id=$silo_id";

    $sth = $dbh->query($sql);
    if($sth==false){
        return false;
    }
    $row = $sth->fetch();

    $prodName = $row['product_name'];
    $prodTmin = $row['t_min']."&deg;C";
    $prodTavg = (($row['t_min']+$row['t_max'])/2)."&deg;C";
    $prodTmax = $row['t_max']."&deg;C";
    $prodVmin = $row['v_min']."&deg;C/сут.";
    $prodVavg = (($row['v_min']+$row['v_max'])/2)."&deg;C/сут.";
    $prodVmax = $row['v_max']."&deg;C/сут.";
    $rngTmin  = $row['MIN(s.current_temperature)']."&deg;C";
    $rngTmax  = $row['MAX(s.current_temperature)']."&deg;C";
    $rngVmax  = $row['MAX(s.current_speed)']."&deg;C/сут.";

    //return $sql;
    return array($prodName, $prodTmax, $prodVmax, $prodTmin, $prodTavg, $prodTmax, $prodVmin, $prodVavg, $prodVmax, $rngTmin, $rngTmax, $rngVmax);
}

//  Отрисовка текущих значений параметров силоса
if( isset($_POST['POST_vInd_silo_id_for_product_parameters']) ) {
    echo json_encode( vInd_getSiloProductParams($dbh, $_POST['POST_vInd_silo_id_for_product_parameters']) );
}

//  Функции для отрисовки таблиц с измеренными значениями
function vInd_getRowsNumberForSiloCurrValuesTable($dbh, $siloNum){

    $sql = "SELECT MAX(csn) FROM
            (SELECT COUNT(sensor_num) AS csn
                FROM sensors s 
                GROUP BY silo_id, podv_id 
                HAVING silo_id = $siloNum) AS mcsn;";
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    return $rows[0]['MAX(csn)'];
}

function vInd_getColsNumberForSiloCurrValuesTable($dbh, $siloNum){

    $sql = "SELECT COUNT(DISTINCT(podv_id))
    FROM sensors s
    GROUP BY silo_id
    HAVING silo_id = $siloNum;";
    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();
    if(count($rows)==0){
        return false;
    }

    return $rows[0]['COUNT(DISTINCT(podv_id))'];
}

function vInd_getShiftArrayForSiloCurrValuesTable($dbh, $siloNum){
    $sql = "SELECT COUNT(sensor_num) AS csn
                FROM sensors s 
                GROUP BY silo_id, podv_id 
                HAVING silo_id=$siloNum;";
    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    return $sth->fetchAll();
}

function vInd_drawTemperaturesTable($dbh, $siloID){

    global $accessLevel;
    $rows_number  = vInd_getRowsNumberForSiloCurrValuesTable($dbh, $siloID);
    $cols_number  = vInd_getColsNumberForSiloCurrValuesTable($dbh, $siloID);
    $shifts_array = vInd_getShiftArrayForSiloCurrValuesTable($dbh, $siloID);

    //  Находим главный массив
    $sql = "SELECT curr_t_text, curr_t_colour, s.is_enabled, pbs.grain_level_fromTS, pbs.grain_level
            FROM sensors AS s INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
            WHERE s.silo_id = $siloID;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $dbRowsArr = $sth->fetchAll();

    return vInd_drawCurrValuesTable($accessLevel, $rows_number, $cols_number, $shifts_array, $dbRowsArr, 't', $siloID);

}

function vInd_drawTemperatureSpeedsTable($dbh, $siloID){

    global $accessLevel;
    $rows_number  = vInd_getRowsNumberForSiloCurrValuesTable($dbh, $siloID);
    $cols_number  = vInd_getColsNumberForSiloCurrValuesTable($dbh, $siloID);
    $shifts_array = vInd_getShiftArrayForSiloCurrValuesTable($dbh, $siloID);

    //  Находим главный массив
    $sql = "SELECT curr_v_text, curr_v_colour, s.is_enabled, pbs.grain_level_fromTS, pbs.grain_level
            FROM sensors AS s INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
            WHERE s.silo_id = $siloID;";
    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $dbRowsArr = $sth->fetchAll();

    return vInd_drawCurrValuesTable($accessLevel, $rows_number, $cols_number, $shifts_array, $dbRowsArr, 'v', $siloID);

}

/*  Вспомогательная функция для построения таблицы с параметрами
    (количество строк, количество столбцов, массив сдвигов(таблица с параметрами практически всегда не полная), массив из БД, параметр(t,v), id силоса)
*/
function vInd_drawCurrValuesTable($accessLevel, $rowsNumber, $colsNumber, $shiftsArr, $dbRowsArr, $parameter, $siloID){

    $btnDisabled = $accessLevel<1 ? "disabled" : "";

    $trHeight = 15; $divWidth = 40; $divHeight = 25;

    $outStr = "<table>";

    for($i = $rowsNumber; $i >= 0; $i--){

        //  Кнопка для переключения режима определения уровня
        if( $i==($rowsNumber) ){
            $outStr .= "<tr style=\"height: ".$trHeight."px; \"><td>";

            if($dbRowsArr[0]['grain_level_fromTS']){
                $lvlModeText="A";
                $lvlModeColour="green";
                $lvlModeButton="<li><button class=\"dropdown-item\" type=\"button\" onclick=\"vIndChangeSourceOfLvl($siloID, '0')\">Переключить в ручной режим</button></li>";
            } else {
                $lvlModeText="M";
                $lvlModeColour="orange";
                $lvlModeButton="<li><button class=\"dropdown-item\" type=\"button\" onclick=\"vIndChangeSourceOfLvl($siloID, '1')\">Переключить в автоматический режим</button></li>";
            }

            $lvlSliderDisabled = $dbRowsArr[0]['grain_level_fromTS']==1 || $accessLevel<1 ? "disabled" : "";

            $outStr .= "
                    <div class=\"dropdown\" style=\"margin: 0px; padding: 0px;\">
                        <button class=\"\" type=\"button\" id=\"lvl-mode-$parameter-$siloID\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\"
                        style=\"border: none; width: ".$divWidth."px; height: ".$divHeight."px;
                        padding: 0px 0px 0px 0px; text-align: center; font-weight: bold; background-color: $lvlModeColour;\" $btnDisabled>
                            $lvlModeText
                        </button>
                        <ul class=\"dropdown-menu\" aria-labelledby=\"dropdownMenu2\">$lvlModeButton</ul></div>";

            $outStr .= "</td></tr>";
            continue;
        }
        
        $outStr .= "<tr style=\"height: $trHeight px; \">";

        //  Слайдер для отображения и выбора уровня
        if( $i==($rowsNumber-1) ){
            $lvlSlider_rowspan = $rowsNumber + 1;
            $lvlSlider_max   = $rowsNumber;
            $lvlSlider_value = $dbRowsArr[0]['grain_level'];
            $lvlSlider_style ="position: absolute;
                            display: block;
                            top: 30px;
                            width: 40px;
                            --tdHeight: calc(27px * $rowsNumber); height: var( --tdHeight );
                            margin-left: auto; margin-right: auto;
                            vertical-align: top;
                            -webkit-appearance: slider-vertical;";

            $outStr .= "<td rowspan=\"$lvlSlider_rowspan\">
                    <input type=\"range\" id=\"lvl-slider-$parameter-$siloID\"
                    name=\"\" min=\"0\" max=\"$lvlSlider_max\" value=\"$lvlSlider_value\" step=\"1\"
                    onchange=\"vIndWriteGrainLvlFromSlider($siloID)\" style=\"$lvlSlider_style\" $lvlSliderDisabled>
            </td>";
        }

        //  Номер слоя
        $outStr .= "<td style=\"text-align: right; padding-right: 10px;\">".($i+1)."</td>";

        for($j = 0; $j < $colsNumber; $j++){
            $outStr .= "<td >";

            if($i<$shiftsArr[$j]['csn']){

                //  Вычисляем индекс элемента в массиве из БД
                $curr_ind = 0;
                for($k=0; $k<$j; $k++){
                    $curr_ind += $shiftsArr[$k]['csn'];
                }
                $curr_ind += $i;
                
                $outStr .= "
                    <div class=\"dropdown\" style=\" width: $divWidth px; margin:0px; padding:0px; \">
                        <button class=\"\" type=\"button\" id=\"sensor-$parameter-$siloID-$j-$i\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\"
                        style=\"border: none; width: ".$divWidth."px; height: ".$divHeight."px; padding: 0px 0px 0px 0px; text-align: center; font-weight: bold; background-color: "
                            .$dbRowsArr[$curr_ind]["curr_".$parameter."_colour"].";\">"
                            .$dbRowsArr[$curr_ind]["curr_".$parameter."_text"].
                            "</button>
                        <ul class=\"dropdown-menu\" aria-labelledby=\"dropdownMenu2\">";

                if($dbRowsArr[$curr_ind]['is_enabled']){
                    $outStr .= "<li><button class=\"btn dropdown-item\" type=\"button\" onclick=\"vIndSelectedSensorDisable($siloID,$j,$i)\" $btnDisabled>Отключить выбранный датчик</button></li>";
                    $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"vIndSelectedPodvDisable($siloID,$j)\" $btnDisabled>Отключить выбранную подвеску</button></li>";
                } else {
                    $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"vIndSelectedSensorEnable($siloID,$j,$i)\" $btnDisabled>Включить выбранный датчик</button></li>";
                    $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"vIndSelectedPodvEnable($siloID,$j)\" $btnDisabled>Включить выбранную подвеску</button></li>";
                }
                $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"vIndDrawChartForSelectedSensor($siloID,$j,$i,'month')\">Отобразить график температуры за месяц</button></li>";
                $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"vIndDrawChartForSelectedSensor($siloID,$j,$i,'day')\">Отобразить график температуры за сутки</button></li>";
                    
                $outStr .= "</ul></div>";

            }
            $outStr .= "</td>";
        }
        $outStr .= "</tr>";
    }

    //	Нумерация
    $outStr .= "<tr style=\"height: 15px; text-align: center; \">";
    $outStr .= "<td><div style=\"width:30px;\"></div></td>";

    for($j = 1; $j <= $colsNumber; $j++){
        $outStr .= "<td >".$j."</td>";
    }
    $outStr .= "</tr>";
    $outStr .= "</table>";

    return $outStr;
}

//  Отрисовка текущих значений температур
if(isset($_POST['POST_vInd_temperature_table_silo_id']) && !empty($_POST['POST_vInd_temperature_table_silo_id'])) {
    echo vInd_drawTemperaturesTable($dbh, preg_split('/-/', $_POST['POST_vInd_temperature_table_silo_id'], -1, PREG_SPLIT_NO_EMPTY)[1]);
}

//  Отрисовка текущих значений скоростей
if(isset($_POST['POST_vInd_speeds_table_silo_id']) && !empty($_POST['POST_vInd_speeds_table_silo_id'])) {
    echo vInd_drawTemperatureSpeedsTable($dbh, preg_split('/-/', $_POST['POST_vInd_speeds_table_silo_id'], -1, PREG_SPLIT_NO_EMPTY)[1]);
}

function vInd_changeSourceOfLvlForCurrSilo($dbh, $silo_id, $levelMode){
    $query="UPDATE prodtypesbysilo SET grain_level_fromTS = $levelMode WHERE silo_id=$silo_id;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['POST_vInd_change_source_of_grain_level_silo_id']) && isset($_POST['POST_vInd_change_source_of_grain_level_source']) ) {
    vInd_changeSourceOfLvlForCurrSilo($dbh, $_POST['POST_vInd_change_source_of_grain_level_silo_id'], $_POST['POST_vInd_change_source_of_grain_level_source']);
}

//  Изменение уровня из главной страницы
function vInd_writeLevelFromSliderForCurrSilo($dbh, $silo_id, $grainLevel){
    $query="UPDATE prodtypesbysilo SET grain_level = $grainLevel WHERE silo_id=$silo_id;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;

}

if( isset($_POST['POST_vInd_writeLevelFromSliderForCurrSilo_silo_id']) && isset($_POST['POST_vInd_writeLevelFromSliderForCurrSilo_grainLevel']) ) {
    vInd_writeLevelFromSliderForCurrSilo($dbh, $_POST['POST_vInd_writeLevelFromSliderForCurrSilo_silo_id'], $_POST['POST_vInd_writeLevelFromSliderForCurrSilo_grainLevel']);
}

function vInd_sensorDisable($dbh, $silo_id, $podv_id, $sensor_num){
	
	$query="UPDATE sensors SET is_enabled=0 WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['POST_vInd_sensorDisable_silo_id']) && isset($_POST['POST_vInd_sensorDisable_podv_id']) && isset($_POST['POST_vInd_sensorDisable_sensor_num']) ) {
	vInd_sensorDisable($dbh, $_POST['POST_vInd_sensorDisable_silo_id'], $_POST['POST_vInd_sensorDisable_podv_id'], $_POST['POST_vInd_sensorDisable_sensor_num']);
    echo "Выбранный датчик отключен";
}

function vInd_sensorEnable($dbh, $silo_id, $podv_id, $sensor_num){
	
	$query="UPDATE sensors SET is_enabled=1 WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['POST_vInd_sensorEnable_silo_id']) && isset($_POST['POST_vInd_sensorEnable_podv_id']) && isset($_POST['POST_vInd_sensorEnable_sensor_num']) ) {
	vInd_sensorEnable($dbh, $_POST['POST_vInd_sensorEnable_silo_id'], $_POST['POST_vInd_sensorEnable_podv_id'], $_POST['POST_vInd_sensorEnable_sensor_num']);
    echo "Выбранный датчик включен";
}

function vInd_podvDisable($dbh, $silo_id, $podv_id){
	
	$query="UPDATE sensors SET is_enabled=0 WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['POST_vInd_podvDisable_silo_id']) && isset($_POST['POST_vInd_podvDisable_podv_id']) ) {
	vInd_podvDisable($dbh, $_POST['POST_vInd_podvDisable_silo_id'], $_POST['POST_vInd_podvDisable_podv_id']);
}

function vInd_podvEnable($dbh, $silo_id, $podv_id){
	
	$query="UPDATE sensors SET is_enabled=1 WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['POST_vInd_podvEnable_silo_id']) && isset($_POST['POST_vInd_podvEnable_podv_id']) ) {
	vInd_podvEnable($dbh, $_POST['POST_vInd_podvEnable_silo_id'], $_POST['POST_vInd_podvEnable_podv_id']);
}

?>