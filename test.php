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


        $arrayOfSilos  = array(1,2,3);
        $arrayOfLayers = array(1,2,3,4,5);
        $arrayOfDates  = array('22.10.2021 10:10:32','22.10.2021 11:10:40');

        $sql = "";
        $outObj=[];
        for($i=0; $i<count($arrayOfLayers);$i++){
            $arrayOfLayers[$i]-=1;                                      //  Приведение номера датчика к id датчика в силосе
        }
        $strArrayOfLayers="(".implode(",",$arrayOfLayers).")";          //  Преобразование массива в строку для корректного формирования sql-запроса
    
        foreach($arrayOfSilos as $currSiloName){
            foreach($arrayOfDates as $currDate){
    
                $strDate = "STR_TO_DATE('$currDate', '%d.%m.%Y %H:%i:%s')";
    
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

            }
        }

    ?>

</body>
</html>