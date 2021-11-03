<?php

require_once ('currValsFromTS.php');

//  Печатные формы -------------------------------------------------------------------------------------------------------------------------------------------------------
//  Получить все даты измерений
//  out = [дата => массив времен измерений]
function vRep_getAllMeasDates($dbh){

    $sql = "SELECT DISTINCT (d.date)
                FROM measurements m
                INNER JOIN dates d ON m.date_id = d.date_id
                ORDER BY d.date";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();

    $outArr = array();
    $daysArr = array(); $timesArr = array(); $currDay = '';
    $daysIndArr = array();

    for($i=0; $i<count($rows); $i++){
        $day    = preg_split('/ /', $rows[$i]['date'], -1, PREG_SPLIT_NO_EMPTY)[0];
        $time   = preg_split('/ /', $rows[$i]['date'], -1, PREG_SPLIT_NO_EMPTY)[1];
        if($currDay!=$day){
            $currDay=$day;
            array_push($daysIndArr,$i);
            array_push($daysArr,$day);
        }
        array_push($timesArr,$time);
    }

    for($i=0; $i<count($daysIndArr); $i++){
        if( $i == (count($daysIndArr)-1) ){
            $outArr[$daysArr[$i]] = array_slice($timesArr, $daysIndArr[$i]);
            break;
        }
        $outArr[$daysArr[$i]] = array_slice( $timesArr, $daysIndArr[$i], ($daysIndArr[$i+1]-$daysIndArr[$i] ) );
    }

    return $outArr;
}
//  Отрисовка кнопок с датами и чекбоксов с конкретным временем измерения
function vRep_drawMeasCheckboxes($measurementArray){

    $outStr = "<table>";

    if(count($measurementArray)>1){
        $outStr.= "
                    <tr>
                        <tr>
                            <div class=\"form-check mt-0 mb-0\" style=\"margin-left: 3px; text-align: left\">
                                <input class=\"form-check-input\" type=\"checkbox\" id=\"prfchballdates\" onchange=\"vRep_prfChbAllDates();vRep_rprtprf_checkDatesAndBlockDownloadButtons();\">
                                <label class=\"form-check-label\">
                                    Все даты
                                </label>
                            </div>
                        </td>
                    </tr>";
    }

    foreach($measurementArray as $date => $time){

        $outStr.= " <tr>
                        <td colspan=\"2\" style=\"margin: 0px;\">
                            <p style=\"margin-bottom: 0px; padding: 0px;\">
                                <button class=\"btn btn-secondary mt-0 mb-1\" type=\"button\" data-bs-toggle=\"collapse\"
                                        data-bs-target=\".prfchbmc_$date\" aria-expanded=\"false\">
                                    $date
                                </button>
                            </p>
                        </td>
                    </tr>
                    ";

        if(count($measurementArray[$date])>1){
            $outStr.= "<tr>
                            <td>
                                <div class=\"form-check mt-0 mb-1 collapse multi-collapse prfchbmc_$date\" style=\"margin-left: 3px; text-align: left\">
                                    <input class=\"form-check-input\" type=\"checkbox\" id=\"prfchball_$date\" onchange=\"vRep_prfChbCurrDate('prfchball_$date');vRep_rprtprf_checkDatesAndBlockDownloadButtons();\">
                                    <label class=\"form-check-label\">
                                        Все
                                    </label>
                                </div>
                            </td>
                        </tr>
                            ";
        }

        foreach($time as $measTime){
            $outStr.= "
                        <tr>
                            <td>
                                <div class=\"form-check mt-0 mb-1 collapse multi-collapse prfchbmc_$date\" style=\"margin-left: 3px; text-align: left\">
                                    <input class=\"form-check-input\" type=\"checkbox\" id=\"prfchb_".$date."_".$measTime."\"  onchange=\"vRep_rprtprf_checkDatesAndBlockDownloadButtons();\">
                                    <label class=\"form-check-label\">
                                        $measTime
                                    </label>
                                </div>
                            </td>
                        </tr>";
        }

    }

    $outStr .= "</table>";

    return $outStr;
}

//  Получение параметров для печатных форм -------------------------------------------------------------------------------------------------------------------------------
//  Средние температуры в слоях
function vRep_getAvgTemperByLayer($dbh, $arrayOfSilos, $arrayOfLayers, $arrayOfDates){

    $outObj=[];

    for($i=0; $i<count($arrayOfLayers);$i++){
        $arrayOfLayers[$i]-=1;                                      //  Приведение номера датчика к id датчика в силосе
    }
    $strArrayOfLayers="(".implode(",",$arrayOfLayers).")";          //  Преобразование массива в строку для корректного формирования sql-запроса

    foreach($arrayOfSilos as $currSiloName){
        foreach($arrayOfDates as $currDate){

            $strDate = "STR_TO_DATE('$currDate', '%Y-%m-%d %H:%i:%s')";
    
            $sql = "SELECT d.date, pbs.silo_name, s.sensor_num, ROUND(AVG(temperature),2)
                        FROM measurements m
                        INNER JOIN dates d ON m.date_id = d.date_id
                        INNER JOIN sensors s ON m.sensor_id = s.sensor_id 
                        INNER JOIN prodtypesbysilo pbs ON s.silo_id = pbs.silo_id 
                        GROUP BY sensor_num, s.silo_id, date
                        HAVING  d.date = $strDate AND
                            pbs.silo_name = $currSiloName AND
                            s.sensor_num IN $strArrayOfLayers
                    ORDER BY d.date, pbs.silo_name, s.sensor_num;";

            $sth = $dbh->query($sql);

            if($sth==false){
                return false;
            }

            $layersArr = $sth->fetchAll();

            if(count($layersArr)==0){
                continue;
            }
            
            $layersObj=[];
            foreach($layersArr as $layer){
                $layersObj[]=array($layer['sensor_num']+1=>$layer['ROUND(AVG(temperature),2)']);
            }
            $outObj[]=array('date'=>$layersArr[0]['date'], 'silo'=>$layersArr[0]['silo_name'], 'layerTemperatures'=>$layersObj);
        }
    }
    return $outObj;
}

if( isset($_POST['POST_vRep_getAvgTemperByLayer_arrayOfSilos']) && isset($_POST['POST_vRep_getAvgTemperByLayer_arrayOfLayers']) && isset($_POST['POST_vRep_getAvgTemperByLayer_arrayOfDates']) ) {
    echo json_encode (vRep_getAvgTemperByLayer($dbh, $_POST['POST_vRep_getAvgTemperByLayer_arrayOfSilos'], $_POST['POST_vRep_getAvgTemperByLayer_arrayOfLayers'], $_POST['POST_vRep_getAvgTemperByLayer_arrayOfDates']));
}

//  Температуры каждого датчика в слоях
function vRep_getSensorTemperByLayer($dbh, $arrayOfSilos, $arrayOfLayers, $arrayOfDates){

    $outObj=[];

    for($i=0; $i<count($arrayOfLayers);$i++){
        $arrayOfLayers[$i]-=1;                                      //  Приведение номера датчика к id датчика в силосе
    }

    foreach($arrayOfDates as $currDate){
        foreach($arrayOfSilos as $currSiloName){
            foreach($arrayOfLayers as $currLayer){
        
                $strDate = "STR_TO_DATE('$currDate', '%Y-%m-%d %H:%i:%s')";

                $sql = "SELECT d.date, pbs.silo_name, s.sensor_num, s.podv_id, temperature
                                FROM measurements m
                                INNER JOIN dates d ON m.date_id = d.date_id
                                INNER JOIN sensors s ON m.sensor_id = s.sensor_id 
                                INNER JOIN prodtypesbysilo pbs ON s.silo_id = pbs.silo_id 
                                WHERE d.date = $strDate AND pbs.silo_name = $currSiloName AND s.sensor_num = $currLayer
                                ORDER BY d.date, pbs.silo_name, s.sensor_num, podv_id;";

                $sth = $dbh->query($sql);

                if($sth==false){ return false; }

                $podvArr = $sth->fetchAll();

                if(count($podvArr)==0){ continue; }

                $podvObj=[];
                foreach($podvArr as $podv){
                    $podvObj[]=array($podv['podv_id']+1=>$podv['temperature']);
                }
                $outObj[]=array('date'=>$podvArr[0]['date'], 'silo'=>$podvArr[0]['silo_name'], 'layer'=>$podvArr[0]['sensor_num']+1, 'sensorTemperatures'=>$podvObj);

            }
        }
    }

    return $outObj;
}

if( isset($_POST['POST_vRep_getSensorTemperByLayer_arrayOfSilos']) && isset($_POST['POST_vRep_getSensorTemperByLayer_arrayOfLayers']) && isset($_POST['POST_vRep_getSensorTemperByLayer_arrayOfDates']) ) {
    echo json_encode( vRep_getSensorTemperByLayer($dbh, $_POST['POST_vRep_getSensorTemperByLayer_arrayOfSilos'], $_POST['POST_vRep_getSensorTemperByLayer_arrayOfLayers'], $_POST['POST_vRep_getSensorTemperByLayer_arrayOfDates']) );
}

vRep_getSensorTemperByLayer($dbh, array('1'), array('1'), array('2021-10-31 22:05:26'));

//  Температуры каждого датчика в подвеске
function vRep_getSensorTemperByPodv($dbh, $arrayOfSilos, $arrayOfPodv, $arrayOfSensors, $arrayOfDates){

    $outObj=[];

    for($i=0; $i<count($arrayOfPodv);$i++){
        $arrayOfPodv[$i]-=1;                                         //  Приведение номера подвески к id подвески в силосе
    }
    for($i=0; $i<count($arrayOfSensors);$i++){
        $arrayOfSensors[$i]-=1;                                      //  Приведение номера датчика к id датчика в силосе
    }
    $strArrayOfSensors="(".implode(",",$arrayOfSensors).")";

    foreach($arrayOfDates as $currDate){
        foreach($arrayOfSilos as $currSiloName){
            foreach($arrayOfPodv as $currPodv){
        
                $strDate = "STR_TO_DATE('$currDate', '%Y-%m-%d %H:%i:%s')";

                $sql = "SELECT d.date, pbs.silo_name, s.podv_id, s.sensor_num, temperature
                            FROM measurements m
                            INNER JOIN dates d ON m.date_id = d.date_id
                            INNER JOIN sensors s ON m.sensor_id = s.sensor_id 
                            INNER JOIN prodtypesbysilo pbs ON s.silo_id = pbs.silo_id 
                            WHERE   d.date = $strDate AND
                                    pbs.silo_name = $currSiloName AND
                                    s.podv_id = $currPodv AND
                                    s.sensor_num IN $strArrayOfSensors
                            ORDER BY d.date, pbs.silo_name, podv_id, s.sensor_num;";
                        
                $sth = $dbh->query($sql);
                
                if($sth==false){ return false; }

                $sensorArr = $sth->fetchAll();

                if(count($sensorArr)==0){ continue; }

                $sensorsObj=[];
                foreach($sensorArr as $sensor){
                    $sensorsObj[]=array($sensor['sensor_num']+1=>$sensor['temperature']);
                }
                $outObj[]=array('date'=>$sensorArr[0]['date'], 'silo'=>$sensorArr[0]['silo_name'], 'podv'=>$sensorArr[0]['podv_id']+1, 'sensorTemperatures'=>$sensorsObj);

            }
        }
    }

    return $outObj;
}

if( isset($_POST['POST_vRep_getSensorTemperByPodv_arrayOfSilos']) && isset($_POST['POST_vRep_getSensorTemperByPodv_arrayOfPodv']) && isset($_POST['POST_vRep_getSensorTemperByPodv_arrayOfSensors']) && isset($_POST['POST_vRep_getSensorTemperByPodv_arrayOfDates']) ) {
    echo json_encode( vRep_getSensorTemperByPodv($dbh, $_POST['POST_vRep_getSensorTemperByPodv_arrayOfSilos'], $_POST['POST_vRep_getSensorTemperByPodv_arrayOfPodv'], $_POST['POST_vRep_getSensorTemperByPodv_arrayOfSensors'], $_POST['POST_vRep_getSensorTemperByPodv_arrayOfDates']) );
}

//  Получение таблицы температур для графика -----------------------------------------------------------------------------------------------------------------------------
function vRep_getTableForChart($dbh, $silo_name, $podv_id, $sens_num, $dateStart, $dateEnd){
    
    $sql = "SELECT d.date, m.temperature
            FROM sensors AS s INNER JOIN measurements AS m ON s.sensor_id=m.sensor_id
            INNER JOIN dates AS d ON m.date_id=d.date_id
            WHERE (s.silo_id = (select silo_id from prodtypesbysilo where silo_name=$silo_name) AND s.podv_id = $podv_id AND s.sensor_num = $sens_num AND
                                d.date BETWEEN '$dateStart' AND '$dateEnd')";
    
    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
            
    $rows = $sth->fetchAll();

    $outArr=array();

    foreach($rows as $row){        
        $outArr[] = array('date' => $row['date'], 'temperature' => $row['temperature']);
    }

   return $outArr;
}

if( isset($_POST['POST_vRep_getTableForChart_silo_name']) && isset($_POST['POST_vRep_getTableForChart_podv_id']) && isset($_POST['POST_vRep_getTableForChart_sensor_num']) && isset($_POST['POST_vRep_getTableForChart_period']) ) {

    $dateEnd = DateTime::createFromFormat('d.m.Y H:i:s', $serverDate);
    $dateStart = DateTime::createFromFormat('d.m.Y H:i:s', $serverDate);
    $dateStart->modify("-1 ".$_POST['POST_vRep_getTableForChart_period']);

    echo json_encode( vRep_getTableForChart( $dbh, $_POST['POST_vRep_getTableForChart_silo_name'], $_POST['POST_vRep_getTableForChart_podv_id'], $_POST['POST_vRep_getTableForChart_sensor_num'], $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s') ) );
}

?>