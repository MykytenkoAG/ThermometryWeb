<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/currValsFromTS.php');

//  Получить все даты измерений
//  out = [дата => массив времен измерений]
function getAllMeasurementDates($dbh){

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

function createMeasurementCheckboxes($measurementArray){

    $outStr = "<table>";

    if(count($measurementArray)>1){
        $outStr.= "
                    <tr>
                        <tr>
                            <div class=\"form-check mt-0 mb-0\" style=\"margin-left: 3px; text-align: left\">
                                <input class=\"form-check-input\" type=\"checkbox\" id=\"prfchballdates\" onchange=\"prfChbAllDates();rprtprf_checkDatesAndBlockDownloadButtons();\">
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
                                    <input class=\"form-check-input\" type=\"checkbox\" id=\"prfchball_$date\" onchange=\"prfChbCurrDate('prfchball_$date');rprtprf_checkDatesAndBlockDownloadButtons();\">
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
                                    <input class=\"form-check-input\" type=\"checkbox\" id=\"prfchb_".$date."_".$measTime."\"  onchange=\"rprtprf_checkDatesAndBlockDownloadButtons();\">
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

//  Средние температуры в слояхprfrb_avg_t_by_layer_arrayOfSilos
function getAvgTemperaturesByLayer($dbh, $arrayOfSilos, $arrayOfLayers, $arrayOfDates){

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
            
            $layersObj=[];
            foreach($layersArr as $layer){
                $layersObj[]=array($layer['sensor_num']+1=>$layer['ROUND(AVG(temperature),2)']);
            }
            $outObj[]=array('date'=>$layersArr[0]['date'], 'silo'=>$layersArr[0]['silo_name'], 'layerTemperatures'=>$layersObj);
        }
    }
    return $outObj;
}

if( isset($_POST['prfrb_avg_t_by_layer_arrayOfSilos']) && isset($_POST['prfrb_avg_t_by_layer_arrayOfLayers']) && isset($_POST['prfrb_avg_t_by_layer_arrayOfDates']) ) {
    echo json_encode (getAvgTemperaturesByLayer($dbh, $_POST['prfrb_avg_t_by_layer_arrayOfSilos'], $_POST['prfrb_avg_t_by_layer_arrayOfLayers'], $_POST['prfrb_avg_t_by_layer_arrayOfDates']));
}

//  Температуры каждого датчика в слоях
function getSensorTemperaturesByLayer($dbh, $arrayOfSilos, $arrayOfLayers, $arrayOfDates){

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

                //return $sql;

                if($sth==false){
                    return false;
                }

                $podvArr = $sth->fetchAll();

                if(count($podvArr)==0){
                    continue;
                }

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

if( isset($_POST['prfrb_t_by_layer_arrayOfSilos']) && isset($_POST['prfrb_t_by_layer_arrayOfLayers']) && isset($_POST['prfrb_t_by_layer_arrayOfDates']) ) {
    echo json_encode( getSensorTemperaturesByLayer($dbh, $_POST['prfrb_t_by_layer_arrayOfSilos'], $_POST['prfrb_t_by_layer_arrayOfLayers'], $_POST['prfrb_t_by_layer_arrayOfDates']) );
}

//  Температуры каждого датчика в подвеске
function getSensorTemperaturesByPodv($dbh, $arrayOfSilos, $arrayOfPodv, $arrayOfSensors, $arrayOfDates){

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
                
                if($sth==false){
                    return false;
                }

                $sensorArr = $sth->fetchAll();

                if(count($sensorArr)==0){
                    continue;
                }

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

if( isset($_POST['prfrb_t_by_sensor_arrayOfSilos']) && isset($_POST['prfrb_t_by_sensor_arrayOfPodv']) && isset($_POST['prfrb_t_by_sensor_arrayOfSensors']) && isset($_POST['prfrb_t_by_sensor_arrayOfDates']) ) {
    echo json_encode( getSensorTemperaturesByPodv($dbh, $_POST['prfrb_t_by_sensor_arrayOfSilos'], $_POST['prfrb_t_by_sensor_arrayOfPodv'], $_POST['prfrb_t_by_sensor_arrayOfSensors'], $_POST['prfrb_t_by_sensor_arrayOfDates']) );
}

function getTimeTemperatureTable($dbh,$silo_name, $podv_id, $sens_num, $dateStart, $dateEnd){
    
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

if( isset($_POST['get_t_chart_silo_id']) && isset($_POST['get_t_chart_podv_id']) && isset($_POST['get_t_chart_sensor_num']) && isset($_POST['get_t_chart_period']) ) {

    $dateEnd = DateTime::createFromFormat('d.m.Y H:i:s', $serverDate);
    $dateStart = DateTime::createFromFormat('d.m.Y H:i:s', $serverDate);
    $dateStart->modify("-1 ".$_POST['get_t_chart_period']);

    echo json_encode( getTimeTemperatureTable( $dbh, $_POST['get_t_chart_silo_id'], $_POST['get_t_chart_podv_id'], $_POST['get_t_chart_sensor_num'], $dateStart->format('Y-m-d H:i:s'), $dateEnd->format('Y-m-d H:i:s') ) );
}

?>