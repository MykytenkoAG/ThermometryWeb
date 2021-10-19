<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/currValsFromTS.php');

//  OUT = html table < NACK, time, silo_name, podv_num, sensor_num, reason >
function getCurrentAlarms(){

    global $dbh;
    $outArr = array();

    $sql = "SELECT  s.sensor_id, s.silo_id, s.podv_id, s.sensor_num,
                    s.NACK_Tmax, s.TIME_NACK_Tmax, s.ACK_Tmax, s.TIME_ACK_Tmax,
                    s.NACK_Vmax, s.TIME_NACK_Vmax, s.ACK_Vmax, s.TIME_ACK_Vmax,
                    s.NACK_err, s.TIME_NACK_err, s.ACK_err, s.TIME_ACK_err,
                    s.error_id, e.error_description, pbs.silo_name
                    FROM sensors AS s LEFT JOIN errors AS e ON s.error_id=e.error_id INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
                    WHERE s.NACK_Tmax=1 OR s.ACK_Tmax=1 OR s.NACK_Vmax=1 OR s.ACK_Vmax=1 OR s.NACK_err=1 OR s.ACK_err=1;";
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    $NACK = 0; $timeOfAlarm=""; $silo_name=""; $podv_num=0; $sensor_num=0; $reason="";
    foreach($rows as $row){

        if($row['NACK_Tmax']==1 or $row['NACK_Vmax']==1 or $row['NACK_err']==1){
            $NACK = 1;
        } else {
            $NACK = 0;
        }

        if($row['TIME_NACK_Tmax']!=null){
            $timeOfAlarm = $row['TIME_NACK_Tmax'];
            $reason = "T крит.";

            $silo_name = $row['silo_name'];
            $podv_num = $row['podv_id'] + 1;
            $sensor_num = $row['sensor_num'] + 1;
            $outArr[] = array('NACK'=>$NACK,'timeOfAlarm'=>$timeOfAlarm,'silo_name'=>$silo_name,'podv_num'=>$podv_num,'sensor_num'=>$sensor_num,'reason'=>$reason);

        }
        
        if ($row['TIME_NACK_Vmax']!=null){
            $timeOfAlarm = $row['TIME_NACK_Vmax'];
            $reason = "V крит.";

            $silo_name = $row['silo_name'];
            $podv_num = $row['podv_id'] + 1;
            $sensor_num = $row['sensor_num'] + 1;
            $outArr[] = array('NACK'=>$NACK,'timeOfAlarm'=>$timeOfAlarm,'silo_name'=>$silo_name,'podv_num'=>$podv_num,'sensor_num'=>$sensor_num,'reason'=>$reason);
        }
        
        if ($row['TIME_NACK_err']!=null){
            $timeOfAlarm = $row['TIME_NACK_err'];
            $reason = $row['error_description'];

            $silo_name = $row['silo_name'];
            $podv_num = $row['podv_id'] + 1;
            $sensor_num = $row['sensor_num'] + 1;
            $outArr[] = array('NACK'=>$NACK,'timeOfAlarm'=>$timeOfAlarm,'silo_name'=>$silo_name,'podv_num'=>$podv_num,'sensor_num'=>$sensor_num,'reason'=>$reason);
        }

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

//  out: = [silo_id=>[{round,square},img_index]]
function getSiloCurrentStatus(){

    global $dbh;
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

        if($row['is_square']==1){
            $curr_silo_type = 1;                                                                                    //  square
        } else {
            $curr_silo_type = 0;                                                                                    //  round
        }

        if( $row['error_id']==255 or $row['error_id']==256){
            $curr_silo_status = 0;                                                                                  //  OFF
            continue;
        }

        if( $row['error_id']==253 or $row['error_id']==254){
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

        if( $curr_silo_status!=0 and $curr_silo_status!=1 and $curr_silo_status!=2 and $curr_silo_status!=3 and
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

if( isset($_POST['get_silo_current_status']) ) {
    echo json_encode(getSiloCurrentStatus());
}

//  Функция отрисовки главного плана расположения силосов
function createSiloPlan(){ 

    global $dbh;

    $sql = "SELECT  pbs.silo_id, pbs.silo_name,
                    pbs.is_square, pbs.size, pbs.position_col, pbs.position_row,
                    pt.product_name, pt.t_max, pt.t_min, pt.v_max, pt.v_min,
                    MAX(s.current_temperature), MIN(s.current_temperature), MAX(s.current_speed)
            FROM prodtypesbysilo AS pbs INNER JOIN prodtypes AS pt ON pbs.product_id=pt.product_id INNER JOIN sensors AS s ON pbs.silo_id=s.silo_id
            GROUP BY s.silo_id;";

    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $siloConfigRows = $sth->fetchAll();
    //  Определяем количество строк таблицы
    $sql = "SELECT  MAX(position_row)
                    FROM prodtypesbysilo;";
    $sth = $dbh->query($sql);
    $rowsNumber = $sth->fetch()['MAX(position_row)'];
    //  Определяем количество столбцов таблицы
    $sql = "SELECT  MAX(position_col)
                    FROM prodtypesbysilo;";
    $sth = $dbh->query($sql);
    $colsNumber = $sth->fetch()['MAX(position_col)'];

    $outStr = "<table>";

    for($i = 0; $i <= $rowsNumber; $i++){
        $outStr .= "<tr>";
        for($j = 0; $j <= $colsNumber; $j++){
            $outStr .= "<td class=\"silo\">";
            foreach($siloConfigRows as $siloConfigRow){
                if($siloConfigRow['position_col']==($j+1) and $siloConfigRow['position_row']==$i){

                    //  Всплывающая подсказка
                    $siloTooltip = " Тип продукта : ".$siloConfigRow['product_name']."; 
 Tmax : ".$siloConfigRow['t_max']."&deg;C"." ;
 Tmin : ".$siloConfigRow['t_min']."&deg;C"." ;
 Vmax : ".$siloConfigRow['v_max']."&deg;C/сут."." ;
 Vmin : ".$siloConfigRow['v_min']."&deg;C/сут."." ;
 Диапазон температур : ".$siloConfigRow['MIN(s.current_temperature)']."&deg;C"." .. ".$siloConfigRow['MAX(s.current_temperature)']."&deg;C ;
 Максимальная скорость : ".$siloConfigRow['MAX(s.current_speed)']."&deg;C/сут.; ";

                    //  Имя силоса
                    $outStr .= "<div class=\"d-inline silo-number\" style=\"padding: 5px; font-size: 20px;\">"
                    .$siloConfigRow['silo_name']."</div>";


                    if($siloConfigRow['is_square']){
                        //  Если силос круглый
                        $outStr .= "<img src=\"/webTermometry/img/silo_square_OK.png\"
                        id=\"silo-".$siloConfigRow['silo_id']."\" onclick=\"onSiloClicked(event.target.id)\"

                        data-bs-toggle=\"tooltip\" data-bs-placement=\"right\" title=\"$siloTooltip\"

                        style=\"display: block; margin-left: auto; margin-right: auto; width: ".($siloConfigRow['size']*100)."%;\"/>";
                    } else{
                        //  Если силос квадратный
                        $outStr .= "<img src=\"/webTermometry/img/silo_round_OK.png\"
                        id=\"silo-".$siloConfigRow['silo_id']."\" onclick=\"onSiloClicked(event.target.id)\"

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

    return $outStr;
}

//  Функция для определения массива отключенных силосов
//  ! ВОЗМОЖНО, СЛЕДУЕТ ИЗМЕНИТЬ ЗАПРОС НА ОПРЕДЕЛЕНИЕ СИЛОСОВ, В КОТОРЫХ ОШИБКУ ВЫДАЮТ ВООБЩЕ ВСЕ ДАТЧИКИ
function getAllSiloDisabled(){

    global $dbh;

    $sql = "SELECT DISTINCT silo_id FROM sensors WHERE error_id=256";
    $sth = $dbh->query($sql);
    if($sth==false){
        return false;
    }
    $siloIDs = $sth->fetchAll();
    $outArr = array();
    foreach($siloIDs as $siloID){
        array_push($outArr,$siloID['silo_id']);
    }

    return $outArr;
}

function getAllSlioCRC(){

    global $dbh;

    $sql = "SELECT DISTINCT silo_id FROM sensors WHERE error_id=253 OR error_id=254";
    $sth = $dbh->query($sql);
    if($sth==false){
        return false;
    }
    $siloIDs = $sth->fetchAll();
    $outArr = array();
    foreach($siloIDs as $siloID){
        array_push($outArr,$siloID['silo_id']);
    }

    return $outArr;
}

function getAllSiloNACK(){

    global $dbh;

    $sql = "SELECT DISTINCT silo_id FROM sensors WHERE (NACK_Tmax=1 AND ACK_Tmax=0) OR (NACK_Vmax=1 AND ACK_Vmax=0) OR (NACK_err=1 AND NACK_err=0)";
    $sth = $dbh->query($sql);
    if($sth==false){
        return false;
    }
    $siloIDs = $sth->fetchAll();
    $outArr = array();
    foreach($siloIDs as $siloID){
        array_push($outArr,$siloID['silo_id']);
    }

    return $outArr;
}

function getAllSiloACK(){

    global $dbh;

    $sql = "SELECT DISTINCT silo_id FROM sensors WHERE (NACK_Tmax=0 AND NACK_Vmax=0 AND NACK_err=0 AND (ACK_Tmax=1 OR ACK_Vmax=1 OR ACK_err=1))";
    $sth = $dbh->query($sql);
    if($sth==false){
        return false;
    }
    $siloIDs = $sth->fetchAll();
    $outArr = array();
    foreach($siloIDs as $siloID){
        array_push($outArr,$siloID['silo_id']);
    }

    return $outArr;
}

//  Функции для отрисовки таблиц параметров
function getRowsNumberForSiloTables($siloNum){
    global $dbh;

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

function getColsNumberForSiloTables($siloNum){
    global $dbh;

    $sql = "SELECT COUNT(DISTINCT(podv_id))
    FROM sensors s
    GROUP BY silo_id
    HAVING silo_id = $siloNum;";
    $sth = $dbh->query($sql);

    if($sth==false){
    return false;
    }
    $rows = $sth->fetchAll();

    return $rows[0]['COUNT(DISTINCT(podv_id))'];
}

function getShiftArrayForSiloTables($siloNum){
    global $dbh;
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

function createTemperaturesTable($siloNum){

    global $dbh;
    $rows_number = getRowsNumberForSiloTables($siloNum);
    $cols_number = getColsNumberForSiloTables($siloNum);
    $shifts_array = getShiftArrayForSiloTables($siloNum);

    //  Находим главный массив
    $sql = "SELECT curr_t_text, curr_t_colour
                FROM sensors s
                WHERE silo_id = $siloNum;";
    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $main_array = $sth->fetchAll();

    $outStr = "<table>";

    for($i = $rows_number-1; $i >= 0; $i--){
        
        $outStr .= "<tr style=\"height: 15px; \">";

        //  Отображение уровня
        if($i==($rows_number-1)){
            $outStr .= "<td rowspan=\"".($rows_number)."\">
                    <input type=\"range\" id=\"\" name=\"\" 
                            min=\"0\" max=\"$rows_number\" value=\"9\" step=\"1\" style=\"width: 15px; --tdHeight: calc(26px * $rows_number); height: var( --tdHeight );
                            padding-right: 0px; margin-right: 0px;
                            -webkit-appearance: slider-vertical;\">
            </td>";
        }

        $outStr .= "<td style=\"text-align: right; padding-right: 10px;\">".($i+1)."</td>";

        for($j = 0; $j < $cols_number; $j++){
            $outStr .= "<td >";

            if($i<$shifts_array[$j]['csn']){

                $curr_ind = 0;
                for($k=0; $k<$j; $k++){
                    $curr_ind += $shifts_array[$k]['csn'];
                }
                
                $curr_ind += $i;  
                
                //  DROPDOWN!!
                $outStr .= "
                    <div class=\"dropdown\" style=\"margin:0px; padding:0px;\">
                        <button class=\"\" type=\"button\" id=\"sensor-t-$siloNum-$j-$i\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\"
                        style=\"border: none; width: 40px; height: 25px; padding: 0px 0px 0px 0px; text-align: center; font-weight: bold; background-color: ".$main_array[$curr_ind]['curr_t_colour'].";\"
                        >"
                            .$main_array[$curr_ind]['curr_t_text'].
                        "</button>
                        <ul class=\"dropdown-menu\" aria-labelledby=\"dropdownMenu2\">
                        <li><button class=\"dropdown-item\" type=\"button\">Отключить выбранный датчик</button></li>
                        <li><button class=\"dropdown-item\" type=\"button\">Отключить выбранную подвеску</button></li>
                        </ul>
                    </div>";

            }

            $outStr .= "</td>";
        }
        $outStr .= "</tr>";
    }

    //	Нумерация
    $outStr .= "<tr style=\"height: 15px; text-align: center; \">";

    $outStr .= "<td><div style=\"width:30px;\"></div></td>";    //

    $outStr .= "<td></td>";
    for($j = 1; $j <= $cols_number; $j++){
        $outStr .= "<td >".$j."</td>";
    }
    $outStr .= "</tr>";

    $outStr .= "</table>";

    return $outStr;

}

function createTemperatureSpeedsTable($siloNum){

    global $dbh;
    $rows_number = getRowsNumberForSiloTables($siloNum);
    $cols_number = getColsNumberForSiloTables($siloNum);
    $shifts_array = getShiftArrayForSiloTables($siloNum);

    //  Находим главный массив
    $sql = "SELECT curr_v_text, curr_v_colour
                FROM sensors s
                WHERE silo_id = $siloNum;";
    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $main_array = $sth->fetchAll();

    $outStr = "<table>";

    for($i = $rows_number-1; $i >= 0; $i--){
        
        $outStr .= "<tr style=\"height: 15px; \">";

        $outStr .= "<td style=\"text-align: right; padding-right: 10px;\">".($i+1)."</td>";

        for($j = 0; $j < $cols_number; $j++){
            $outStr .= "<td >";

            if($i<$shifts_array[$j]['csn']){

                $curr_ind = 0;
                for($k=0; $k<$j; $k++){
                    $curr_ind += $shifts_array[$k]['csn'];
                }
                
                $curr_ind += $i;

                $currSensV = $main_array[$curr_ind]['curr_v_text'];
                if($currSensV>100){
                    $currSensV=100;
                } elseif ($currSensV<-100){
                    $currSensV=-100;
                }
                
                $outStr .= "<div id=\"sensor-v-$siloNum-$j-$i\" onclick=\"alert(event.target.id)\"
                        style=\"width: 40px; text-align: center; font-weight: bold; background-color: ".$main_array[$curr_ind]['curr_v_colour'].";\">"
                        .$currSensV."</div>";
            }

            $outStr .= "</td>";
        }
        $outStr .= "</tr>";
    }

    //	Нумерация
    $outStr .= "<tr style=\"height: 15px; text-align: center; \">";
    $outStr .= "<td></td>";
    for($j = 1; $j <= $cols_number; $j++){
        $outStr .= "<td >".$j."</td>";
    }
    $outStr .= "</tr>";

    $outStr .= "</table>";

    return $outStr;

}

//  Получение текущих параметров для силоса
//  out: [название продукта, Tmax, Vmax, ProdTmin, ProdTavg, ProdTmax, ProdVmin, ProdVavg, ProdVmax, RngTmin, RngTmax, RngVmax]
function getSiloParameters($silo_id){

    global $dbh;
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

//  AJAX

//  Отрисовка текущих значений температур
if(isset($_POST['silo_id_for_temperature_table']) && !empty($_POST['silo_id_for_temperature_table'])) {
    echo createTemperaturesTable(preg_split('/-/', $_POST['silo_id_for_temperature_table'], -1, PREG_SPLIT_NO_EMPTY)[1]);
}

//  Отрисовка текущих значений скоростей
if(isset($_POST['silo_id_for_speeds_table']) && !empty($_POST['silo_id_for_speeds_table'])) {
    echo createTemperatureSpeedsTable(preg_split('/-/', $_POST['silo_id_for_speeds_table'], -1, PREG_SPLIT_NO_EMPTY)[1]);
}

//  Отрисовка текста названия силоса
if(isset($_POST['silo_id_forText']) && !empty($_POST['silo_id_forText'])) {
    $sql = "SELECT silo_name
                FROM prodtypesbysilo
                WHERE silo_id=".(preg_split('/-/', $_POST['silo_id_forText'], -1, PREG_SPLIT_NO_EMPTY)[1]).";";
    $sth = $dbh->query($sql);
    $silo_name = $sth->fetch()['silo_name'];
    echo "Силос $silo_name";
}

//  Отрисовка текущих значений параметров силоса
if( isset($_POST['silo_id_for_silo_parameters']) ) {
    echo json_encode(getSiloParameters($_POST['silo_id_for_silo_parameters']));
}

//  Получение текущих алармов
if( isset($_POST['get_current_alarms']) ) {
    echo getCurrentAlarms();
}

?>