<?php
/*  Перечень команд:
    siloinfo - краткая информация по текущему состоянию. Формат ответа: Силос, Продукт, Уровень, Ткр, Tmax, Tmin, Vкр, Vmax
    alarms - получение текущих неисправностей
    lvl1 - получение уровня заполнения силоса. Формат команды: /lvl [силос]
    temp1 - получение текущей температуры. Формат команды: /temp [силос].[подвеска].[датчик]
    speed1 - получение текущей скорости изменения температуры. Формат команды: /speed [силос].[подвеска].[датчик]
    conf - получение конфигурации системы. Формат ответа: [силос]:НП[номер подвески]/[количество датчиков]
    notificationson - включить уведомления о возникновении новых АПС
    notificationsoff - выключить уведомления о возникновении новых АПС

    set webhook: https://api.telegram.org/bot2123872619:AAENLR1KZVjWBmeOP8vcqHM39KPZOPX9OW4/setWebhook?url=https://nethermometrytest.ddns.net:8443/Thermometry/telegram/
*/
require_once( substr(__DIR__,0,-8)."/php/ts/currValsFromTS.php" );              //  Получаем всю необходимую информацию

const BASE_URL = "https://api.telegram.org/bot"; const TOKEN = "2123872619:AAENLR1KZVjWBmeOP8vcqHM39KPZOPX9OW4"; ini_set("allow_url_fopen", true);

$newMessage = json_decode(file_get_contents('php://input'));            //  Получаем сообщение от Телеграм Бота
//file_put_contents(__DIR__.'/debug.txt', print_r($newMessage,1), FILE_APPEND);

if(!is_null($newMessage)>0){                                            //  Если пришло новое сообщение
    recognizeCmd($dbh, $newMessage);                                    //  Распознаем команду и отправляем ответ
}

function sendMessage($sender_id, $arrayOfMessages){
    foreach($arrayOfMessages as $currMess){
        file_get_contents(BASE_URL.TOKEN."/sendMessage?chat_id=".$sender_id."&text=".$currMess."&parse_mode=HTML");
    }
    return;
}

function recognizeCmd($dbh, $newMessage){

    $command = $newMessage->message->text;
    $sender_id = $newMessage->message->from->id;
    $messageToSend = array("Неопознанная команда");

    if( preg_match('/\/?silo\s*info\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*\.?\s*\.?\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*/ui',$command,$matches) ||
        preg_match('/\/?инфо\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*\.?\s*\.?\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*/ui',$command,$matches) ){

        $messageToSend = cmdGetSiloInfo($dbh, $matches[1], $matches[2]);

    } else if ( preg_match('/\/?alarms\s*/ui',$command,$matches) ||
                preg_match('/\/?апс\s*/ui',$command,$matches)){

        $messageToSend = cmdGetAlarms($dbh);

    } else if ( preg_match('/\/?lvl\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*/ui',$command,$matches) ||
                preg_match('/\/?уровень\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*/ui',$command,$matches)){

        $messageToSend = cmdGetGrainLevels($dbh, $matches[1]);
        
    } else if ( preg_match('/\/?temp\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})\s*\.?\s*(\d{0,2})?\s*\.?\s*(\d{0,2})?\s*/ui',$command,$matches) ||
                preg_match('/\/?температура\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})\s*\.?\s*(\d{0,2})?\s*\.?\s*(\d{0,2})?\s*/ui',$command,$matches) ) {

        $messageToSend = cmdGetTemperatures($dbh, $matches[1], $matches[2], $matches[3]);

    } else if ( preg_match('/\/?speed\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})\s*\.?\s*(\d{0,2})?\s*\.?\s*(\d{0,2})?\s*/ui',$command,$matches) ||
                preg_match('/\/?скорость\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})\s*\.?\s*(\d{0,2})?\s*\.?\s*(\d{0,2})?\s*/ui',$command,$matches) ) {

        $messageToSend = cmdGetTemperatureSpeeds($dbh, $matches[1], $matches[2], $matches[3]);

    } else if(  preg_match('/\/?conf\s*/ui',$command,$matches) ||
                preg_match('/\/?конфигурация\s*/ui',$command,$matches) ){

        $messageToSend = cmdGetConfiguration($dbh);

    }else if (  preg_match('/\/?notificationson\s*/ui',$command,$matches) ||
                preg_match('/\/?уведомления\s*вкл\s*/ui',$command,$matches) ){

        $query="REPLACE INTO telegram_users (user_id, notifications_on) VALUES ('$sender_id', '1');";
        $stmt = $dbh->prepare($query);
        $stmt->execute();
        $messageToSend = array("Уведомления о возникновении АПС включены.");
    
    }else if (  preg_match('/\/?notificationsoff\s*/ui',$command,$matches) ||
                preg_match('/\/?уведомления\s*выкл\s*/ui',$command,$matches) ){

        $query="REPLACE INTO telegram_users (user_id, notifications_on) VALUES ('$sender_id', '0');";
        $stmt = $dbh->prepare($query);
        $stmt->execute();
        $messageToSend = array("Уведомления о возникновении АПС выключены");

    } else if (preg_match('/\/?start/ui',$command,$matches)) {

        $messageToSend = array("Здравствуйте, ".$newMessage->message->from->first_name."!");

    }

    sendMessage($sender_id, $messageToSend);

    return;
}

function cmdGetSiloInfo($dbh, $silo_name_1, $silo_name_2){

    $sql = "SELECT silo_id FROM prodtypesbysilo ";

    if( $silo_name_1 != "" && $silo_name_2 != "" ){
        $sql .= " WHERE silo_name BETWEEN $silo_name_1 AND $silo_name_2 ";
    } else if ( $silo_name_1 != "" ){
        $sql .= " WHERE silo_name = $silo_name_1 ";
    }
    
    $sth = $dbh->query($sql);
    if($sth==false){
        return false;
    }
    $silo_id_rows = $sth->fetchAll();

    $outArr = array(); $outStr = "";
    
    for($i=0; $i<count($silo_id_rows); $i++){

        $sql = "SELECT s.sensor_id, s.silo_id, s.podv_id, s.sensor_num,
                pbs.silo_name,
                p.product_name, p.t_max, p.t_min, p.v_max,
                max(s.current_temperature),
                min(s.current_temperature),
                max(s.current_speed),
                pbs.grain_level,
                max(s.sensor_num)
                FROM sensors AS s
                INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
                INNER JOIN prodtypes AS p ON pbs.product_id=p.product_id
                where s.silo_id=".$silo_id_rows[$i]["silo_id"]." and s.sensor_num<pbs.grain_level";
        
        $sth = $dbh->query($sql);
        if($sth==false){
            return false;
        }
        $curr_row = $sth->fetchAll();

        $outStr .= "Силос "         .$curr_row[0]["silo_name"]
                .",%0AПродукт: "    .$curr_row[0]["product_name"]
                .",%0AУровень: "    .round((($curr_row[0]["grain_level"]/($curr_row[0]["max(s.sensor_num)"]+1))*100),1)." %"
                .",%0ATкр: "        .round($curr_row[0]["t_max"],1)." %C2%B0C"
                .", Tmax: "         .round($curr_row[0]["max(s.current_temperature)"],1)." %C2%B0C"
                .", Tmin: "         .round($curr_row[0]["min(s.current_temperature)"],1)." %C2%B0C"
                .",%0AVкр: "        .round($curr_row[0]["v_max"],1)." %C2%B0C/сут."
                .", Vmax: "         .round($curr_row[0]["max(s.current_speed)"],1)." %C2%B0C/сут."
                .";";
        array_push($outArr, $outStr);
        $outStr = "";
        
    }

    return $outArr;
}

function cmdGetAlarms($dbh){
    
    $sql = "SELECT s.sensor_id, s.silo_id, s.podv_id, s.sensor_num, pbs.silo_name, s.NACK_Tmax, s.ACK_Tmax, s.NACK_Vmax, s.ACK_Vmax, s.NACK_err, s.ACK_Vmax, e.error_id, e.error_desc_for_visu 
            FROM sensors AS s
            INNER JOIN prodtypesbysilo AS pbs ON s.silo_id = pbs.silo_id
            LEFT JOIN errors AS e ON s.error_id = e.error_id
            WHERE s.NACK_Tmax=1 OR s.ACK_Tmax=1 OR s.NACK_Vmax=1 OR s.ACK_Vmax=1 OR s.NACK_err=1 OR s.ACK_err=1 ";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    $outArr = array();  $outStr = "";

    if(count($rows)==0){
        array_push($outArr, "На данный момент в системе отсутствуют неисправности.");
    }

    $i=0;
    foreach($rows as $row){

        $outStr .= "Силос ".$row["silo_name"].". НП".($row["podv_id"]+1).". НД".($row["sensor_num"]+1).". ";
        if(!is_null($row["error_desc_for_visu"])){
            $outStr .= $row["error_desc_for_visu"];
        } else if ($row["NACK_Tmax"]||$row["ACK_Tmax"]){
            $outStr .= "Tmax";
        } else if ($row["NACK_Vmax"]||$row["ACK_Vmax"]){
            $outStr .= "Vmax";
        }
        
        $outStr .= ";%0A";
        
        if( ($i>0 && $i%10==0) || $i==(count($rows)-1) ){
            array_push($outArr, $outStr);
            $outStr = "";
        }
        $i++;
    }

    return $outArr;
}

function cmdGetGrainLevels($dbh, $silo_name){
    
    $sql = "SELECT pbs.silo_id, pbs.silo_name, s.podv_id, count(s.sensor_num), pbs.grain_level
            FROM prodtypesbysilo AS pbs
            LEFT JOIN sensors AS s
            ON pbs.silo_id = s.silo_id ";

    if($silo_name!=""){
        $sql .= " WHERE pbs.silo_name = '$silo_name' ";
    }

    $sql .= "  GROUP BY s.silo_id, s.podv_id
               HAVING s.podv_id = 0 ";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    $outArr = array(); $outStr = "";

    if(count($rows)==0){
        if($silo_name!=""){
            array_push($outArr, "Запрашиваемый силос отсутствует в текущем проекте;");
        }
    }

    $i=0;
    foreach($rows as $row){
        $outStr .= "Силос ".$row["silo_name"].". ";
        $outStr .= "Уровень заполнения: ".(($row["grain_level"]/$row["count(s.sensor_num)"])*100)." %";
        $outStr .= ";%0A";

        if( ($i>0 && $i%10==0) || $i==(count($rows)-1) ){
            array_push($outArr, $outStr);
            $outStr = "";
        }
        $i++;
    }

    return $outArr;
}

function cmdGetTemperatures($dbh, $silo_name, $podv_id, $sensor_num){

    $sql = "SELECT s.sensor_id, s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num, s.is_enabled, s.current_temperature, s.current_speed, e.error_desc_for_visu 
            FROM sensors AS s
            INNER JOIN prodtypesbysilo pbs ON s.silo_id = pbs.silo_id
            LEFT JOIN errors AS e ON s.error_id = e.error_id ";
    if($silo_name!=""){
        $sql .= " WHERE pbs.silo_name = '$silo_name' ";
    }
    if($podv_id!=""){
        $sql .= " AND s.podv_id = ".($podv_id-1);
    }
    if($sensor_num!=""){
        $sql .= " AND s.sensor_num = ".($sensor_num-1);
    }
    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    $outArr = array();  $outStr = "";
    
    if(count($rows)==0){
        if($sensor_num!=""){            array_push($outArr, "Запрашиваемый датчик отсутствует в текущем проекте;");
        } else if($podv_id!=""){        array_push($outArr, "Запрашиваемая подвеска отсутствует в текущем проекте;");
        } else if($silo_name!=""){      array_push($outArr, "Запрашиваемый силос отсутствует в текущем проекте;");
        }
    }

    $i=0;
    foreach($rows as $row){
        $outStr .= "Силос ".$row["silo_name"].". НП".($row["podv_id"]+1).". НД".($row["sensor_num"]+1).". ";
        if(!is_null($row["error_desc_for_visu"])){
            $outStr .= $row["error_desc_vor_visu"];
        } else if ($row["is_enabled"]==0) {
            $outStr .= "Датчик отключен";
        } else {
            $outStr .= "Температура: ".$row["current_temperature"]." %C2%B0C";
        }

        $outStr .= ";%0A";
        
        if( ($i>0 && $i%10==0) || $i==(count($rows)-1) ){
            array_push($outArr, $outStr);
            $outStr = "";
        }
        $i++;
    }

    return $outArr;
}

function cmdGetTemperatureSpeeds($dbh, $silo_name, $podv_id, $sensor_num){

    $sql = "SELECT s.sensor_id, s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num, s.is_enabled, s.current_temperature, s.current_speed, e.error_desc_for_visu 
            FROM sensors AS s
            INNER JOIN prodtypesbysilo pbs ON s.silo_id = pbs.silo_id
            LEFT JOIN errors AS e ON s.error_id = e.error_id ";
    if($silo_name!=""){
        $sql .= " WHERE pbs.silo_name = '$silo_name' ";
    }
    if($podv_id!=""){
        $sql .= " AND s.podv_id = ".($podv_id-1);
    }
    if($sensor_num!=""){
        $sql .= " AND s.sensor_num = ".($sensor_num-1);
    }
    $sth = $dbh->query($sql);

    if($sth==false){
    return false;
    }
    $rows = $sth->fetchAll();

    $outArr = array(); $outStr = "";

    if(count($rows)==0){
        if($sensor_num!=""){            array_push($outArr, "Запрашиваемый датчик отсутствует в текущем проекте;");
        } else if($podv_id!=""){        array_push($outArr, "Запрашиваемая подвеска отсутствует в текущем проекте;");
        } else if($silo_name!=""){      array_push($outArr, "Запрашиваемый силос отсутствует в текущем проекте;");
        }
    }

    $i=0;
    foreach($rows as $row){
        $outStr .= "Силос ".$row["silo_name"].". НП".($row["podv_id"]+1).". НД".($row["sensor_num"]+1).". ";
        if(!is_null($row["error_desc_for_visu"])){
            $outStr .= $row["error_desc_vor_visu"];
        } else if ($row["is_enabled"]==0) {
            $outStr .= "Датчик отключен";
        } else {
            $outStr .= "Скорость: ".$row["current_speed"]." %C2%B0C/сут.";
        }

        $outStr .= ";%0A";
        
        if( ($i>0 && $i%10==0) || $i==(count($rows)-1) ){
            array_push($outArr, $outStr);
            $outStr = "";
        }
        $i++;
    }

    return $outArr;
}

function cmdGetConfiguration($dbh){

    $sql = "SELECT s.silo_id, s.podv_id, count(s.sensor_num), pbs.silo_name
            FROM sensors AS s
            INNER JOIN prodtypesbysilo pbs ON s.silo_id = pbs.silo_id
            GROUP BY s.silo_id, s.podv_id";
    
    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    $outArr = array(); $outStr = ""; $currentSilo="";
    
    for($i=0; $i<count($rows); $i++){

        if($currentSilo != $rows[$i]["silo_name"]){
            
            if($i>0){
               array_push($outArr, substr($outStr,0,-2).";");
            }

            $outStr = "";
            $currentSilo = $rows[$i]["silo_name"];
            $outStr .= "Силос ".$rows[$i]["silo_name"].": ";
        }

        $outStr .= "НП".($rows[$i]["podv_id"]+1)."/".$rows[$i]["count(s.sensor_num)"].", ";

        if($i==count($rows)-1){
            array_push($outArr, substr($outStr,0,-2).";");
        }
        
    }

    return $outArr;
}

?>