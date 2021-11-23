<?php
/*  Перечень команд:
    i - (и) краткая информация по текущему состоянию
    a - (а) текущие неисправности
    l1 - (у) уровень заполнения силоса. Формат: /l [Силос]
    t1 - (т) температура. Формат: /t [Силос].[НП].[НД]
    v1 - (с) скорость изм. температуры. Формат: /v [Силос].[НП].[НД]
    conf - (конф) получение конфигурации системы
    notificationson - (увед вкл) вкл. уведомления о новых АПС
    notificationsoff - (увед выкл) выкл. уведомления о новых АПС

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
        if(strlen($currMess)>0){
            file_get_contents(BASE_URL.TOKEN."/sendMessage?chat_id=".$sender_id."&text=".$currMess);
        }
    }
    return;
}

function recognizeCmd($dbh, $newMessage){

    $command = $newMessage->message->text;
    $sender_id = $newMessage->message->from->id;
    $messageToSend = array("Неопознанная команда");

    if( preg_match('/^\/?i\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*\.?\s*\.?\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*/ui',$command,$matches) ||
        preg_match('/^\/?и\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*\.?\s*\.?\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*/ui',$command,$matches) ){

        $messageToSend = cmdGetSiloInfo($dbh, $matches[1], $matches[2]);

    } else if ( preg_match('/^\/?a\s*/ui',$command,$matches) ||
                preg_match('/^\/?а\s*/ui',$command,$matches)){

        $messageToSend = cmdGetAlarms($dbh);

    } else if ( preg_match('/^\/?l\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*/ui',$command,$matches) ||
                preg_match('/^\/?у\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})?\s*/ui',$command,$matches)){

        $messageToSend = cmdGetGrainLevels($dbh, $matches[1]);
        
    } else if ( preg_match('/^\/?t\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})\s*\.?\s*(\d{0,2})?\s*\.?\s*(\d{0,2})?\s*/ui',$command,$matches) ||
                preg_match('/^\/?т\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})\s*\.?\s*(\d{0,2})?\s*\.?\s*(\d{0,2})?\s*/ui',$command,$matches) ) {

        $messageToSend = cmdGetTemperatures($dbh, $matches[1], $matches[2], $matches[3]);

    } else if ( preg_match('/^\/?v\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})\s*\.?\s*(\d{0,2})?\s*\.?\s*(\d{0,2})?\s*/ui',$command,$matches) ||
                preg_match('/^\/?с\s*([A-Za-z0-9\x{0400}-\x{04FF}]{0,5})\s*\.?\s*(\d{0,2})?\s*\.?\s*(\d{0,2})?\s*/ui',$command,$matches) ) {

        $messageToSend = cmdGetTemperatureSpeeds($dbh, $matches[1], $matches[2], $matches[3]);

    } else if(  preg_match('/^\/?conf\s*/ui',$command,$matches) ||
                preg_match('/^\/?конф\s*/ui',$command,$matches) ){

        $messageToSend = cmdGetConfiguration($dbh);

    }else if (  preg_match('/^\/?notificationson\s*/ui',$command,$matches) ||
                preg_match('/^\/?увед\s*вкл\s*/ui',$command,$matches) ){

        $query="REPLACE INTO telegram_users (user_id, notifications_on) VALUES ('$sender_id', '1');";
        $stmt = $dbh->prepare($query);
        $stmt->execute();
        $messageToSend = array("Уведомления о возникновении АПС включены.");
    
    }else if (  preg_match('/^\/?notificationsoff\s*/ui',$command,$matches) ||
                preg_match('/^\/?увед\s*выкл\s*/ui',$command,$matches) ){

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

    $sql = "SELECT  s.sensor_id, s.silo_id, s.podv_id, s.sensor_num, pbs.silo_name,
                    p.product_name, p.t_max, p.t_min, p.v_max,
                    max( IF(s.sensor_num<pbs.grain_level, s.current_temperature, NULL) ) AS max_temp,
                    min( IF(s.sensor_num<pbs.grain_level, s.current_temperature, NULL) ) AS min_temp,
                    max( IF(s.sensor_num<pbs.grain_level, s.current_speed, NULL) ) AS max_temp_speed,
                    pbs.grain_level,
                    max(s.sensor_num) AS lvl,
                    sum( IF(s.is_enabled = 1, 1, 0) ) as num_of_en_sensors,
                    sum( IF(s.error_id = 254, 1, 0) ) as num_of_crc,
                    sum( IF(s.error_id = 253, 1, 0) ) as num_of_eeprom,
                    sum( IF(s.error_id is not null, 1, 0) ) as num_of_errors,
                    sum( IF((s.NACK_Tmax=1 or s.ACK_Tmax=1), 1, 0) ) as num_of_Tmax,
                    sum( IF((s.NACK_Vmax=1 or s.ACK_Vmax=1), 1, 0) ) as num_of_Vmax
            FROM sensors AS s
            INNER JOIN prodtypesbysilo AS pbs ON s.silo_id=pbs.silo_id
            INNER JOIN prodtypes AS p ON pbs.product_id=p.product_id ";

    if( $silo_name_1 != "" && $silo_name_2 != "" ){
        $sql .= " WHERE silo_name BETWEEN $silo_name_1 AND $silo_name_2 ";
    } else if ( $silo_name_1 != "" ){
        $sql .= " WHERE silo_name = $silo_name_1 ";
    }

    $sql .= " GROUP BY s.silo_id; ";
    
    $sth = $dbh->query($sql);
    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    $outArr = array(); $outStr = "";
    
    for($i=0; $i<count($rows); $i++){

        $grainLevel = round((($rows[$i]["grain_level"]/($rows[$i]["lvl"]+1))*100),1);

        $outStr .=  "Силос "    .$rows[$i]["silo_name"].";%0A";

        if( $rows[$i]["num_of_en_sensors"] == 0 ){
            $outStr .=  "Силос отключен;%0A";
        } else {
            
            $outStr .=  "Продукт: " .$rows[$i]["product_name"].";%0A";
            $outStr .=  "Уровень: " .$grainLevel." %;";

            if($grainLevel>0){
                if( ! is_null($rows[$i]["max_temp"]) ){
                    $outStr .=  "%0ATкр: "     .round($rows[$i]["t_max"],1)." %C2%B0C, ";
                    $outStr .=  "Tmax: " .round($rows[$i]["max_temp"],1)." %C2%B0C, ";
                    $outStr .=  "Tmin: " .round($rows[$i]["min_temp"],1)." %C2%B0C;%0A";
                    $outStr .=  "Vкр: " .round($rows[$i]["v_max"],1)." %C2%B0C/сут., ";
                    $outStr .=  "Vmax: " .round($rows[$i]["max_temp_speed"],1)." %C2%B0C/сут.;";
                } else {
                    $outStr .=  "%0AПоказания температуры в уровне заполнения отсутствуют;";
                }
                
                if( $rows[$i]["num_of_en_sensors"] == $rows[$i]["num_of_crc"] ){
                    $outStr .=  "%0AНеисправность: CRC;";
                } else if ( $rows[$i]["num_of_en_sensors"] == $rows[$i]["num_of_eeprom"] ){
                    $outStr .=  "%0AНеисправность: ПЗУ;";
                } else if ( $rows[$i]["num_of_errors"]>0 || $rows[$i]["num_of_Tmax"]>0 || $rows[$i]["num_of_Vmax"]>0 ){
                    $outStr .=  "%0AАПС: ";
                    $outStr .=  $rows[$i]["num_of_errors"]." неиспр., ";
                    $outStr .=  $rows[$i]["num_of_Tmax"]." Tmax, ";
                    $outStr .=  $rows[$i]["num_of_Vmax"]." Vmax;";
                }
            }

        }

        array_push($outArr, $outStr);

        $outStr = "";
        
    }

    return $outArr;
}

function cmdGetAlarms($dbh){

    $alarmStateArray = db_update_curr_alarm_state($dbh);

    $outArr = array();  $outStr = "";

    if(count($alarmStateArray)==0){
        array_push($outArr, "На данный момент в системе отсутствуют неисправности.");
    }

    for($i=0; $i<count($alarmStateArray); $i++){

        $outStr .= "Силос ".$alarmStateArray[$i]["silo_name"].". ";
        $outStr .= is_null($alarmStateArray[$i]["podv_id"]) ? "" : "НП".$alarmStateArray[$i]["podv_id"].". ";
        $outStr .= is_null($alarmStateArray[$i]["sensor_num"]) ? "" : "НД".$alarmStateArray[$i]["sensor_num"].". ";

        if(!is_null($alarmStateArray[$i]["error_desc_for_visu"])){
            $outStr .=  $alarmStateArray[$i]["error_desc_for_visu"];
        } else if ( $alarmStateArray[$i]["NACK_Tmax"]==1 || $alarmStateArray[$i]["ACK_Tmax"]==1 ){
            $outStr .=  "Tmax";
        } else if ( $alarmStateArray[$i]["NACK_Vmax"]==1 || $alarmStateArray[$i]["ACK_Vmax"]==1  ){
            $outStr .=  "Vmax";
        }
        
        $outStr .= ";%0A";

        if( ($i>0 && $i%10==0) || $i==(count($alarmStateArray)-1) ){
            array_push($outArr, $outStr);
            $outStr = "";
        }
    }

    return $outArr;
}

function cmdGetGrainLevels($dbh, $silo_name){
    
    $sql = "SELECT pbs.silo_id, pbs.silo_name, s.podv_id,
                   (max(s.sensor_num)+1) as max_sensor_num, pbs.grain_level
            FROM prodtypesbysilo AS pbs
            LEFT JOIN sensors AS s
            ON pbs.silo_id = s.silo_id ";

    if($silo_name!=""){
        $sql .= " WHERE pbs.silo_name = '$silo_name' ";
    }

    $sql .= "  GROUP BY s.silo_id ";

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
        $outStr .= "Уровень заполнения: ".(($row["grain_level"]/$row["max_sensor_num"])*100)." %";
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

    $sql = "SELECT  s.sensor_id, s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num,
                    s.is_enabled, s.current_temperature, s.current_speed, e.error_desc_for_visu 
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