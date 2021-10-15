<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/php/configFromINI.php');

//  Получить все даты измерений
//  out = [дата => массив времен измерений]
function getAllMeasurementDates(){

    global $dbh;

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
    $daysArr=array(); $timesArr=array(); $currDay='';
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

    $outStr="";

    if(count($measurementArray)>1){
        $outStr.= "
        <div class=\"form-check mt-2 mb-2\">
            <input class=\"form-check-input\" type=\"checkbox\" id=\"prfchballdates\" onchange=\"prf_chb()\">
            <label class=\"form-check-label\">
                Все
            </label>
        </div>";
    }

    foreach($measurementArray as $date => $time){

        $outStr.= " <p>
                        <button class=\"btn btn-secondary\" type=\"button\" data-bs-toggle=\"collapse\"
                                data-bs-target=\".prfchbmc-$date\" aria-expanded=\"false\">
                            $date
                        </button>
                    </p>";

        if(count($measurementArray[$date])>1){
            $outStr.= "
            <div class=\"form-check mt-2 mb-2 collapse multi-collapse prfchbmc-$date\">
                <input class=\"form-check-input\" type=\"checkbox\" id=\"prfchball-$date\" onchange=\"prf_chb()\">
                <label class=\"form-check-label\">
                    Все
                </label>
            </div>";
        }

        foreach($time as $measTime){
            $outStr.= "
                    <div class=\"form-check mt-2 mb-2 collapse multi-collapse prfchbmc-$date\">
                        <input class=\"form-check-input\" type=\"checkbox\" id=\"prfchb-$date-$measTime\" onchange=\"prf_chb()\">
                        <label class=\"form-check-label\">
                            $measTime
                        </label>
                    </div>";
        }

    }

    return $outStr;
}

//  Средние температуры в слоях
function getAvgTemperaturesByLayer($arrayOfSilos, $arrayOfLayers, $arrayOfDates){

    global $dbh;
    $sql = "";  $outStr="";
    $outStr .= "<style>table, td, th {border: 1px solid black; border-collapse: collapse;}</style>";
    $strArrayOfLayers="(".implode(",",$arrayOfLayers).")";

    foreach($arrayOfSilos as $silo){
        foreach($arrayOfDates as $date){

            $strDate = "STR_TO_DATE('$date', '%d.%m.%Y %H:%i:%s')";

            $sql = "SELECT d.date, p.silo_name, s.sensor_num, ROUND(AVG(temperature),2)
                    FROM measurements m
                    INNER JOIN dates d ON m.date_id = d.date_id
                    INNER JOIN sensors s ON m.sensor_id = s.sensor_id 
                    INNER JOIN prodtypesbysilo p ON s.silo_id = p.silo_id 
                    GROUP BY sensor_num, s.silo_id, date
                    HAVING  d.date = $strDate AND
                            silo_id = $silo AND
                            s.sensor_num IN $strArrayOfLayers
                    ORDER BY d.date, p.silo_name, s.sensor_num;";

            $sth = $dbh->query($sql);

            if($sth==false){
                return false;
            }

            $layersArr = $sth->fetchAll();

            $outStr .= "<div style=\"float: left;\">";
            
            $outStr .= "<table><tr><td colspan=\"2\">Силос ".$layersArr[0]['silo_name']."<br>"
                        ."Дата ".$date."</td></tr> <tr><td>Слой</td><td>Средняя<br>температура</td></tr>";

            foreach($layersArr as $layer){

                $outStr .= "<tr>";
                $outStr .= "<td align=center>";
                $outStr .= $layer['sensor_num']+1;
                $outStr .= "</td>";
                $outStr .= "<td align=center>";
                $outStr .= $layer['ROUND(AVG(temperature),2)'];
                $outStr .= "</td>";
                $outStr .= "</tr>";

            }

            $outStr .= "</table></div>";

        }
    }

    return $outStr;
}

//  Температуры каждого датчика в слоях
function getSensorTemperaturesByLayer($arrayOfSilos, $arrayOfLayers, $arrayOfDates){

    global $dbh;
    $sql = "";  $outStr="";
    $outStr .= "<style>table, td, th {border: 1px solid black; border-collapse: collapse;}</style>";

    foreach($arrayOfDates as $date){
        foreach($arrayOfSilos as $silo){
            foreach($arrayOfLayers as $layer){
        
                $strDate = "STR_TO_DATE('$date', '%d.%m.%Y %H:%i:%s')";

                $sql = "SELECT d.date, p.silo_name, s.sensor_num, s.podv_id, temperature
                        FROM measurements m
                        INNER JOIN dates d ON m.date_id = d.date_id
                        INNER JOIN sensors s ON m.sensor_id = s.sensor_id 
                        INNER JOIN prodtypesbysilo p ON s.silo_id = p.silo_id 
                        WHERE d.date = $strDate AND s.silo_id = $silo AND s.sensor_num = $layer
                        ORDER BY d.date, p.silo_name, s.sensor_num, podv_id;";

                $sth = $dbh->query($sql);

                if($sth==false){
                    return false;
                }

                $podvArr = $sth->fetchAll();

                $outStr .= "<div style=\"float: left;\">";
                
                $outStr .= "<table><tr><td colspan=\"2\">Силос ".$podvArr[0]['silo_name']."<br>"
                            ."Дата ".$date."<br>Слой ".($layer+1)."</td></tr> <tr><td>Подв.</td><td>Температура</td></tr>";

                foreach($podvArr as $podv){

                    $outStr .= "<tr>";
                    $outStr .= "<td align=center>";
                    $outStr .= $podv['podv_id']+1;
                    $outStr .= "</td>";
                    $outStr .= "<td align=center>";
                    $outStr .= $podv['temperature'];
                    $outStr .= "</td>";
                    $outStr .= "</tr>";

                }

                $outStr .= "</table></div>";

            }
        }
    }

    return $outStr;
}

//  Температуры каждого датчика в подвеске
function getSensorTemperaturesByPodv($arrayOfSilos, $arrayOfPodv, $arrayOfSensors, $arrayOfDates){

    global $dbh;
    $sql = "";  $outStr="";
    $outStr .= "<style>table, td, th {border: 1px solid black; border-collapse: collapse;}</style>";
    $strArrayOfSensors="(".implode(",",$arrayOfSensors).")";
    
    foreach($arrayOfDates as $date){
        foreach($arrayOfSilos as $silo){
            foreach($arrayOfPodv as $podv){
        
                $strDate = "STR_TO_DATE('$date', '%d.%m.%Y %H:%i:%s')";

                $sql = "SELECT d.date, p.silo_name, s.podv_id, s.sensor_num, temperature
                        FROM measurements m
                        INNER JOIN dates d ON m.date_id = d.date_id
                        INNER JOIN sensors s ON m.sensor_id = s.sensor_id 
                        INNER JOIN prodtypesbysilo p ON s.silo_id = p.silo_id 
                        WHERE   d.date = $strDate AND
                                s.silo_id = $silo AND
                                s.podv_id = $podv AND
                                s.sensor_num IN $strArrayOfSensors
                        ORDER BY d.date, p.silo_name, podv_id, s.sensor_num;";
                        
                $sth = $dbh->query($sql);
                
                if($sth==false){
                    return false;
                }

                $podvArr = $sth->fetchAll();

                $outStr .= "<div style=\"float: left; page-break-after: always;\">";
                
                $outStr .= "<table><tr><td colspan=\"2\">Силос ".$podvArr[0]['silo_name']."<br>"
                            ."Дата ".$date."<br>Подвеска ".($podv+1)."</td></tr> <tr><td>Дат.</td><td>Температура</td></tr>";

                foreach($podvArr as $podv){

                    $outStr .= "<tr>";
                    $outStr .= "<td align=center>";
                    $outStr .= $podv['sensor_num']+1;
                    $outStr .= "</td>";
                    $outStr .= "<td align=center>";
                    $outStr .= $podv['temperature'];
                    $outStr .= "</td>";
                    $outStr .= "</tr>";

                }

                $outStr .= "</table></div>";

            }
        }
    }

    return $outStr;


    return;
}


/*
    // HMLT5 Parser
    require_once '../dompdf/lib/html5lib/Parser.php';

    // Sabberworm
    spl_autoload_register(function($class)
    {
        if (strpos($class, 'Sabberworm') !== false) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
            $file = realpath('../dompdf/lib/php-css-parser/lib/' . (empty($file) ? '' : DIRECTORY_SEPARATOR) . $file . '.php');
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }
        return false;
    });

    // php-font-lib
    require_once '../dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';

    //php-svg-lib
    require_once '../dompdf/lib/php-svg-lib/src/autoload.php';


    /*
    * New PHP 5.3.0 namespaced autoloader
    */
 /*   require_once '../dompdf/src/Autoloader.php';

    Dompdf\Autoloader::register();

    // reference the Dompdf namespace
    use Dompdf\Dompdf;

    // instantiate and use the dompdf class
    $dompdf = new Dompdf();


    require_once "../php/printedForms.php";

    $arrayOfSilos = array(0,1);
    $arrayOfLayers = array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17);
    $arrayOfDates = array('03.10.2021 09:49:43','03.10.2021 09:50:43');

    $arrayOfPodv=array(0,1,2,3,4,5,6,7,8,9,10,11);
    $arrayOfSensors=array(0,1,2,3,4,5,6,7);
    
    //echo getAvgTemperaturesByLayer($arrayOfSilos, $arrayOfLayers, $arrayOfDates);

    //echo getSensorTemperaturesByLayer($arrayOfSilos, $arrayOfLayers, $arrayOfDates);

    //echo getSensorTemperaturesByPodv($arrayOfSilos, $arrayOfPodv, $arrayOfSensors, $arrayOfDates);

    $htmlText="<html><head><style>body { font-family: DejaVu Sans }</style></head><body>"
    .getSensorTemperaturesByPodv($arrayOfSilos, $arrayOfPodv, $arrayOfSensors, $arrayOfDates)."</body></html>";

    $dompdf->loadHtml($htmlText);

    // (Optional) Setup the paper size and orientation
    $dompdf->setPaper('A4', 'landscape');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF to Browser
    $dompdf->stream();           
    
*/

/*
    function getSiloPodvSensAssocArray(){

        global $dbh; $outArr=array();

        $sql="SELECT max(silo_id) FROM prodtypesbysilo;";

        $sth = $dbh->query($sql);

        if($sth==false){
            return false;
        }

        $maxSilo_id = $sth->fetch()['max(silo_id)'];

        for($i=0; $i<=$maxSilo_id; $i++){

            $sql="SELECT pbs.silo_name, s.podv_id, count(s.sensor_num)
                    FROM zernoib.prodtypesbysilo AS pbs INNER JOIN zernoib.sensors AS s ON pbs.silo_id=s.silo_id
                    GROUP BY s.silo_id, s.podv_id
                    HAVING silo_id=$i;";

            $sth = $dbh->query($sql);
        
            if($sth==false){
                return false;
            }

            $rows = $sth->fetchAll();
            $currSiloName=$rows[0]['silo_name'];
            $currSiloArr=array();

            foreach($rows as $row){
                $currSiloArr[($row['podv_id']+1)]=$row['count(s.sensor_num)'];
            }
            
            $outArr[$currSiloName]=$currSiloArr;
        }

        return $outArr;
    }
*/

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/php/configFromINI.php');

function getTimeTemperatureTable($silo_id, $podv_id, $sens_num, $dateStart, $dateEnd){

    global $dbh; global $serverDate;
    
    $sql = "
            SELECT d.date, m.temperature
                FROM sensors AS s INNER JOIN measurements AS m ON s.sensor_id=m.sensor_id
                INNER JOIN dates AS d ON m.date_id=d.date_id
                WHERE (s.silo_id = $silo_id AND s.podv_id = $podv_id AND s.sensor_num = $sens_num AND d.date BETWEEN '$dateStart' AND '$dateEnd')
    ";
    
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

if( isset($_POST['silo_id']) && isset($_POST['podv_id']) && isset($_POST['sensor_num']) && isset($_POST['period']) ) {
    echo json_encode( getTimeTemperatureTable( $_POST['silo_id'], $_POST['podv_id'], $_POST['sensor_num'], "2021-10-07 10:00:00", "2021-10-07 11:00:00" ) );
}

/*    if( isset($_POST['get_silo_podv_arr']) ) {
        echo json_encode( getSiloPodvSensAssocArray());
    }   
*/

?>