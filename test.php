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
        $servername	= '127.0.0.1';
        $username	= 'root';
        $password	= '';
        $dbname		= 'zernoib';
        //	Создание объекта PDO для работы с Базой Данных
        $dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);	//[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]

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
                    //array_push($outArr, $curr_silo_type.$curr_silo_status);
                    array_push($outArr, array($curr_silo_type, $curr_silo_status) );
                }
                $curr_silo_status="";
            }
    
            if($row['is_square']==1){
                //$curr_silo_type="S_";                                   //  square
                $curr_silo_type = 1;
            } else {
                //$curr_silo_type="R_";                                   //  round
                $curr_silo_type = 0;
            }
    
            if( $row['error_id']==255 or $row['error_id']==256){
                //$curr_silo_status="OFF";                            //  OFF
                $curr_silo_status = 0;
                continue;
            }
    
            if( $row['error_id']==253 or $row['error_id']==254){
                //$curr_silo_status="CRC";                            //  CRC
                $curr_silo_status = 1;
                continue;
            }
    
            if( $row['NACK_Tmax']==1 or $row['NACK_Vmax']==1 or $row['NACK_err']==1){
                //$curr_silo_status="NACK";                           //  NACK
                $curr_silo_status = 2;
                continue;
            }
    
            if( $curr_silo_status!=3 and
                ($row['ACK_Tmax']==1 or $row['ACK_Vmax']==1 or $row['ACK_err']==1)){
                //$curr_silo_status="ACK";                            //  ACK
                $curr_silo_status = 3;
                continue;
            }
    
            if( $curr_silo_status!=0 and $curr_silo_status!=1 and $curr_silo_status!=2 and $curr_silo_status!=3 and
                ($row['NACK_Tmax']==0 and $row['NACK_Vmax']==0 and $row['NACK_err']==0 and
                 $row['ACK_Tmax']==0 and $row['ACK_Vmax']==0 and $row['ACK_err']==0)){
                //$curr_silo_status="OK";                             //  OK
                $curr_silo_status = 4;
                continue;
            }
    
        }
    
        //array_push($outArr, $curr_silo_type.$curr_silo_status);
        array_push($outArr, array($curr_silo_type, $curr_silo_status) );
    
        return $outArr;

    ?>

</body>
</html>