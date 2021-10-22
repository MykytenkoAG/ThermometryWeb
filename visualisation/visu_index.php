<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/currValsFromTS.php');   //  ! Можно оптимизировать

//  OUT = html table < NACK, time, silo_name, podv_num, sensor_num, reason >
function getCurrentAlarms($dbh){

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

//  Получение текущих алармов
if( isset($_POST['get_current_alarms']) ) {
    echo getCurrentAlarms($dbh);
}

//  out: = [silo_id=>[{round,square},img_index]]
function getSiloCurrentStatus($dbh){

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

if( isset($_POST['get_silo_current_status']) ) {
    echo json_encode(getSiloCurrentStatus($dbh));
}

//  Функция отрисовки главного плана расположения силосов
function drawSiloPlan($dbh){ 

    $sql = "SELECT  pbs.silo_id, pbs.silo_name, pbs.grain_level_fromTS, pbs.grain_level,
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

    $outStr .= "
      <div class=\"modal fade\" id=\"ind-lvl-auto-all-silo-enable\" data-bs-backdrop=\"static\" data-bs-keyboard=\"false\" tabindex=\"-1\" aria-labelledby=\"staticBackdropLabel\" aria-hidden=\"true\">
        <div class=\"modal-dialog modal-dialog-centered\">
          <div class=\"modal-content\">
            <div class=\"modal-header\">
                <h5 class=\"modal-title\" id=\"staticBackdropLabel\">Автоматическое определение уровня</h5>
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>
            </div>
            <div class=\"modal-body\">
                Установить автоопределение уровня на всех силосах?
            </div>
            <div class=\"modal-footer\">
                <div style=\"margin: auto;\">
                    <button type=\"button\" class=\"btn btn-primary\" data-bs-dismiss=\"modal\" onclick=\"enable_all_auto_lvl_mode()\">Да</button>
                    <button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Отмена</button>
                </div>
            </div>
          </div>
        </div>
      </div>
    ";

    return $outStr;
}

//  Функции для отрисовки таблиц параметров
function getRowsNumberForSiloTables($dbh, $siloNum){

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

function getColsNumberForSiloTables($dbh, $siloNum){

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

function getShiftArrayForSiloTables($dbh, $siloNum){
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

function drawTemperaturesTable($dbh, $siloID){

    $rows_number  = getRowsNumberForSiloTables($dbh, $siloID);
    $cols_number  = getColsNumberForSiloTables($dbh, $siloID);
    $shifts_array = getShiftArrayForSiloTables($dbh, $siloID);

    //  Находим главный массив
    $sql = "SELECT curr_t_text, curr_t_colour, s.is_enabled, pbs.grain_level_fromTS, pbs.grain_level
            FROM sensors AS s INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
            WHERE s.silo_id = $siloID;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $dbRowsArr = $sth->fetchAll();

    return drawParametersTable($rows_number, $cols_number, $shifts_array, $dbRowsArr, 't', $siloID);

}

function drawTemperatureSpeedsTable($dbh, $siloID){

    $rows_number  = getRowsNumberForSiloTables($dbh, $siloID);
    $cols_number  = getColsNumberForSiloTables($dbh, $siloID);
    $shifts_array = getShiftArrayForSiloTables($dbh, $siloID);

    //  Находим главный массив
    $sql = "SELECT curr_v_text, curr_v_colour, s.is_enabled, pbs.grain_level_fromTS, pbs.grain_level
            FROM sensors AS s INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
            WHERE s.silo_id = $siloID;";
    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $dbRowsArr = $sth->fetchAll();

    return drawParametersTable($rows_number, $cols_number, $shifts_array, $dbRowsArr, 'v', $siloID);

}

/*  Вспомогательная функция для построения таблицы с параметрами
    (количество строк, количество столбцов, массив сдвигов(таблица с параметрами практически всегда не полная), массив из БД, параметр(t,v), id силоса)
*/
function drawParametersTable($rowsNumber, $colsNumber, $shiftsArr, $dbRowsArr, $parameter, $siloID){

    $trHeight = 15; $divWidth = 40; $divHeight = 25;

    $outStr = "<table>";

    for($i = $rowsNumber; $i >= 0; $i--){

        //  Кнопка для переключения режима определения уровня
        if( $i==($rowsNumber) ){
            $outStr .= "<tr style=\"height: ".$trHeight."px; \"><td>";

            if($dbRowsArr[0]['grain_level_fromTS']){
                $lvlModeText="A";
                $lvlModeColour="green";
                $lvlModeButton="<li><button class=\"dropdown-item\" type=\"button\" onclick=\"change_grain_level_mode($siloID, '0')\">Переключить в ручной режим</button></li>";
                $lvlSliderDisabled="disabled";
            } else {
                $lvlModeText="M";
                $lvlModeColour="orange";
                $lvlModeButton="<li><button class=\"dropdown-item\" type=\"button\" onclick=\"change_grain_level_mode($siloID, '1')\">Переключить в автоматический режим</button></li>";
                $lvlSliderDisabled="";
            }

            $outStr .= "
                    <div class=\"dropdown\" style=\"margin: 0px; padding: 0px;\">
                        <button class=\"\" type=\"button\" id=\"lvl-mode-$parameter-$siloID\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\"
                        style=\"border: none; width: ".$divWidth."px; height: ".$divHeight."px;
                        padding: 0px 0px 0px 0px; text-align: center; font-weight: bold; background-color: $lvlModeColour;\" >
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
            $lvlSlider_style ="width: 15px; --tdHeight: calc(27px * $rowsNumber); height: var( --tdHeight );
                            padding-right: 0px; margin-right: 0px;
                            -webkit-appearance: slider-vertical;";

            $outStr .= "<td rowspan=\"$lvlSlider_rowspan\">
                    <input type=\"range\" id=\"lvl-slider-$parameter-$siloID\"
                    name=\"\" min=\"0\" max=\"$lvlSlider_max\" value=\"$lvlSlider_value\" step=\"1\"
                    onchange=\"change_grain_level_from_slider($siloID)\" style=\"$lvlSlider_style\" $lvlSliderDisabled>
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
                    $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"selectedSensorDisable($siloID,$j,$i)\">Отключить выбранный датчик</button></li>";
                    $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"selectedPodvDisable($siloID,$j)\">Отключить выбранную подвеску</button></li>";
                } else {
                    $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"selectedSensorEnable($siloID,$j,$i)\">Включить выбранный датчик</button></li>";
                    $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"selectedPodvEnable($siloID,$j)\">Включить выбранную подвеску</button></li>";
                }
                $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"selectedSensorDrawChart($siloID,$j,$i,'month')\">Отобразить график температуры за месяц</button></li>";
                $outStr .= "<li><button class=\"dropdown-item\" type=\"button\" onclick=\"selectedSensorDrawChart($siloID,$j,$i,'day')\">Отобразить график температуры за сутки</button></li>";
                    
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
if(isset($_POST['silo_id_for_temperature_table']) && !empty($_POST['silo_id_for_temperature_table'])) {
    echo drawTemperaturesTable($dbh, preg_split('/-/', $_POST['silo_id_for_temperature_table'], -1, PREG_SPLIT_NO_EMPTY)[1]);
}

//  Отрисовка текущих значений скоростей
if(isset($_POST['silo_id_for_speeds_table']) && !empty($_POST['silo_id_for_speeds_table'])) {
    echo drawTemperatureSpeedsTable($dbh, preg_split('/-/', $_POST['silo_id_for_speeds_table'], -1, PREG_SPLIT_NO_EMPTY)[1]);
}

//  Получение текущих параметров для силоса
//  out: [название продукта, Tmax, Vmax, ProdTmin, ProdTavg, ProdTmax, ProdVmin, ProdVavg, ProdVmax, RngTmin, RngTmax, RngVmax]
function getSiloParameters($dbh, $silo_id){

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
if( isset($_POST['silo_id_for_silo_parameters']) ) {
    echo json_encode(getSiloParameters($dbh, $_POST['silo_id_for_silo_parameters']));
}

function changeLevelFromSlider($dbh, $silo_id, $grainLevel){
    $query="UPDATE prodtypesbysilo SET grain_level = $grainLevel WHERE silo_id=$silo_id;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;

}

//  Изменение уровня из главной страницы
if( isset($_POST['change_level_from_slider_silo_id']) && isset($_POST['change_level_from_slider_grain_level']) ) {
    changeLevelFromSlider($dbh, $_POST['change_level_from_slider_silo_id'], $_POST['change_level_from_slider_grain_level']);
}

function changeLevelMode($dbh, $silo_id, $levelMode){
    $query="UPDATE prodtypesbysilo SET grain_level_fromTS = $levelMode WHERE silo_id=$silo_id;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['change_level_mode_silo_id']) && isset($_POST['change_level_mode_level_mode']) ) {
    changeLevelMode($dbh, $_POST['change_level_mode_silo_id'], $_POST['change_level_mode_level_mode']);
}

function enableAutoLvlOnAllSilo($dbh){
    $query="UPDATE prodtypesbysilo SET grain_level_fromTS = 1;";

	$stmt = $dbh->prepare($query);
	$stmt->execute();

    return;
}

if( isset($_POST['enable_auto_lvl_mode']) ) {
    enableAutoLvlOnAllSilo($dbh);
}

function sensorDisable($dbh, $silo_id, $podv_id, $sensor_num){
	
	$query="UPDATE sensors SET is_enabled=0 WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['sensor_disable_silo_id']) && isset($_POST['sensor_disable_podv_num']) && isset($_POST['sensor_disable_sensor_num']) ) {
	sensorDisable($dbh, $_POST['sensor_disable_silo_id'], $_POST['sensor_disable_podv_num'], $_POST['sensor_disable_sensor_num']);
    echo "Выбранный датчик отключен";
}

function sensorEnable($dbh, $silo_id, $podv_id, $sensor_num){
	
	$query="UPDATE sensors SET is_enabled=1 WHERE silo_id=$silo_id AND podv_id=$podv_id AND sensor_num=$sensor_num";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['sensor_enable_silo_id']) && isset($_POST['sensor_enable_podv_num']) && isset($_POST['sensor_enable_sensor_num']) ) {
	sensorEnable($dbh, $_POST['sensor_enable_silo_id'], $_POST['sensor_enable_podv_num'], $_POST['sensor_enable_sensor_num']);
    echo "Выбранный датчик включен";
}

function podvDisable($dbh, $silo_id, $podv_id){
	
	$query="UPDATE sensors SET is_enabled=0 WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['podv_disable_silo_id']) && isset($_POST['podv_disable_podv_num']) ) {
	podvDisable($dbh, $_POST['podv_disable_silo_id'], $_POST['podv_disable_podv_num']);
}

function podvEnable($dbh, $silo_id, $podv_id){
	
	$query="UPDATE sensors SET is_enabled=1 WHERE silo_id=$silo_id AND podv_id=$podv_id";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['podv_enable_silo_id']) && isset($_POST['podv_enable_podv_num']) ) {
	podvEnable($dbh, $_POST['podv_enable_silo_id'], $_POST['podv_enable_podv_num']);
}

function disableAllDefectiveSensors($dbh){
	
	$query="UPDATE sensors SET is_enabled=0 WHERE current_temperature > 84";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['disable_all_defective_sensors']) ) {
	disableAllDefectiveSensors($dbh);
    echo "Датчики включены";
}

function enableAllSensors($dbh){
	
	$query="UPDATE sensors SET is_enabled=1";
	$stmt = $dbh->prepare($query);
	$stmt->execute();

	return;
}

if( isset($_POST['enable_all_sensors']) ) {
	enableAllSensors($dbh);
    echo "датчики отключены";
}

//  Отрисовка текста названия силоса
//  Необходимо заменить!
if(isset($_POST['silo_id_forText']) && !empty($_POST['silo_id_forText'])) {
    $sql = "SELECT silo_name
                FROM prodtypesbysilo
                WHERE silo_id=".(preg_split('/-/', $_POST['silo_id_forText'], -1, PREG_SPLIT_NO_EMPTY)[1]).";";
    $sth = $dbh->query($sql);
    $silo_name = $sth->fetch()['silo_name'];
    echo "Силос $silo_name";
}

?>