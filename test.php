<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Page</title>
</head>
<body>

    <?php
        //require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/currValsFromTS.php');
        /*
        echo "<br>";
        print_r($arrayOfTemperatures);
        echo "<br>";
        echo "<br>";
        print_r($arrayOfTempSpeeds);
        echo "<br>";
        echo "<br>";
        print_r($arrayOfLevels);
        echo "<br>";
        echo "<br>";
        print_r($serverDate);
        echo "<br>";
        */
        //	Необходимые параметры для подключения к БД
        $servername = '127.0.0.1'; $username = 'root'; $password = ''; $dbname = 'zernoib';
        //	Создание объекта PDO для работы с Базой Данных
        $dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);	//[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        $serverDate="19.10.2021 14:05:00";




        $query = "	SELECT s.sensor_id, s.is_enabled, pbs.grain_level, s.current_temperature, pr.t_min, pr.t_max, pr.v_min, pr.v_max, e.error_desc_short
        FROM sensors AS s
        JOIN prodtypesbysilo AS pbs
            ON s.silo_id = pbs.silo_id
        JOIN prodtypes AS pr
            ON pbs.product_id = pr.product_id
        LEFT JOIN errors AS e
            ON s.error_id = e.error_id ";

$arrayOfTemperatures=array(array(array(1,1,1,1),array(1,1,1,1),array(1,1,1,1)),array(array(1,1,1,1)),array(array(1,1,1,1)));
$arrayOfTempSpeeds=array(array(array(1,1,1,1),array(1,1,1,1),array(1,1,1,1)),array(array(1,1,1,1)),array(array(1,1,1,1)));

$sth = $dbh->query($query);
$rows = $sth->fetchAll();

$query="UPDATE sensors SET current_temperature = ( CASE ";

$sensor_id = 0;
for($i = 0; $i < count($arrayOfTemperatures); $i++){
for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
for($k = 0; $k < count($arrayOfTemperatures[$i][$j]); $k++){
            $query.="WHEN sensor_id = ".$sensor_id." THEN ".$arrayOfTemperatures[$i][$j][$k] * 0.1 ." ";
            $sensor_id++;
}
}
}

$query.=" END), current_speed = ( CASE ";
$sensor_id = 0;
for($i = 0; $i < count($arrayOfTempSpeeds); $i++){
for($j = 0; $j < count($arrayOfTempSpeeds[$i]); $j++){
for($k = 0; $k < count($arrayOfTempSpeeds[$i][$j]); $k++){
            $current_temperature_speed = str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k]);
            $query.="WHEN sensor_id = ".$sensor_id." THEN '".$current_temperature_speed."' ";
            $sensor_id++;
}
}
}
//	Текст, отображаемый в ячейке
$query.=" END), curr_t_text = ( CASE ";
$sensor_id = 0;
for($i = 0; $i < count($arrayOfTemperatures); $i++){
for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
for($k = 0; $k < count($arrayOfTemperatures[$i][$j]); $k++){

    $curr_t_text = "''";

    if($rows[$sensor_id]['is_enabled']==0){

        $curr_t_text = "'ОТКЛ.'";

    } elseif ($arrayOfTemperatures[$i][$j][$k] < 850 ){

        $curr_t_text = sprintf('\'%01.1f\'', $arrayOfTemperatures[$i][$j][$k] * 0.1);

    } else {
        $curr_t_text = "'".$rows[$sensor_id]['error_desc_short']."'";
    }

    $query.="WHEN sensor_id = ".$sensor_id." THEN "
    .$curr_t_text." ";
    $sensor_id++;
}
}
}


$query.=" END), curr_v_text = ( CASE ";
$sensor_id = 0;
for($i = 0; $i < count($arrayOfTempSpeeds); $i++){
for($j = 0; $j < count($arrayOfTempSpeeds[$i]); $j++){
for($k = 0; $k < count($arrayOfTempSpeeds[$i][$j]); $k++){

    $curr_v_text = "''";

    if($rows[$sensor_id]['is_enabled']==0){

        $curr_v_text = "'ОТКЛ.'";

    }elseif($arrayOfTemperatures[$i][$j][$k] < 850 ){

        $curr_v_text = sprintf('\'%01.1f\'', $arrayOfTempSpeeds[$i][$j][$k]);

    } else {
        $curr_v_text = "'".$rows[$sensor_id]['error_desc_short']."'";
    }
    

    $query.="WHEN sensor_id = ".$sensor_id." THEN "
    .$curr_v_text." ";
    $sensor_id++;
}
}
}

//	Определение цвета для ячейки с текущей температурой
$query.=" END), curr_t_colour = ( CASE ";
$sensor_id = 0;
for($i = 0; $i < count($arrayOfTemperatures); $i++){
for($j = 0; $j < count($arrayOfTemperatures[$i]); $j++){
for($k = 0; $k < count($arrayOfTemperatures[$i][$j]); $k++){

    $curr_t_colour="'#E5E5E5'";

    if($arrayOfTemperatures[$i][$j][$k] < 850 and $k<$rows[$sensor_id]['grain_level'] ){

        $green = ($rows[$sensor_id]['t_max'] - $arrayOfTemperatures[$i][$j][$k] * 0.1) / ($rows[$sensor_id]['t_max'] - $rows[$sensor_id]['t_min']) * 255;
        if($green>255){
            $green=255;
        } elseif ($green < 0) {
            $green = 0;
        }

        $red = (1 - ($rows[$sensor_id]['t_max'] - $arrayOfTemperatures[$i][$j][$k] * 0.1) / ($rows[$sensor_id]['t_max'] - $rows[$sensor_id]['t_min'])) * 255;
        if($red > 255){
            $red = 255;
        } elseif ($red < 0) {
            $red = 0;
        }

        $curr_t_colour=sprintf('\'#%02X%02X00\'',$red, $green);

    } else if( in_array($arrayOfTemperatures[$i][$j][$k],array(850,1270,2510,2520,2530,2540))){
        $curr_t_colour="'#FF0000'";
    }

    $query.="WHEN sensor_id = ".$sensor_id." THEN "
    .$curr_t_colour." ";
    $sensor_id++;
}
}
}

$query.=" END), curr_v_colour = ( CASE ";
$sensor_id = 0;
for($i = 0; $i < count($arrayOfTempSpeeds); $i++){
for($j = 0; $j < count($arrayOfTempSpeeds[$i]); $j++){
for($k = 0; $k < count($arrayOfTempSpeeds[$i][$j]); $k++){

    $curr_v_colour="'#E5E5E5'";

    if($arrayOfTemperatures[$i][$j][$k] < 850 and $k<$rows[$sensor_id]['grain_level'] ){

        $green = ($rows[$sensor_id]['v_max'] - str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k])) / ($rows[$sensor_id]['v_max'] - $rows[$sensor_id]['v_min']) * 255;
        if($green>255){
            $green=255;
        } elseif ($green < 0) {
            $green = 0;
        }

        $red = (1 - ($rows[$sensor_id]['v_max'] - str_replace(",", ".", $arrayOfTempSpeeds[$i][$j][$k])) / ($rows[$sensor_id]['v_max'] - $rows[$sensor_id]['v_min'])) * 255;
        if($red > 255){
            $red = 255;
        } elseif ($red < 0) {
            $red = 0;
        }

        $curr_v_colour=sprintf('\'#%02X%02X00\'',$red, $green);

    } else if( in_array($arrayOfTemperatures[$i][$j][$k],array(850,1270,2510,2520,2530,2540))){
        $curr_v_colour="'#FF0000'";
    }

    $query.="WHEN sensor_id = ".$sensor_id." THEN "
    .$curr_v_colour." ";
    $sensor_id++;
}
}
}

$sensor_id--;
$query.=" END), server_date = STR_TO_DATE('$serverDate','%d.%m.%Y %H:%i:%s') WHERE sensor_id BETWEEN 0 AND $sensor_id;";
echo $query;
$stmt = $dbh->prepare($query);
$stmt->execute();

    ?>

</body>
</html>