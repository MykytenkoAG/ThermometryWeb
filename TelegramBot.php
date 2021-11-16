<?php
/*  Перечень команд:
    conf - получение конфигурации системы. Формат ответа: [силос]:НП[номер подвески]/[количество датчиков]
    temp1 - получение текущей температуры. Формат команды: /temp [силос].[подвеска].[датчик]
    speed1 - получение текущей скорости изменения температуры. Формат команды: /speed [силос].[подвеска].[датчик]
    lvl1 - получение уровня заполнения силоса. Формат команды: /lvl [силос]
    alarms - получение текущих неисправностей
*/
require_once('currValsFromTS.php');
//  Работа ведется по методу getUpdates. Каждые 3с JS код вызывает данную секцию (т.е. до получения SSL бот будет работать только при подкл. кого-либо из клиентов)
const TOKEN = "2123872619:AAENLR1KZVjWBmeOP8vcqHM39KPZOPX9OW4";     //  Токен, уникальный для каждого бота
const BASE_URL = "https://api.telegram.org/bot";

//print_r( json_decode(file_get_contents(BASE_URL.TOKEN."/getUpdates"))->result );

$updatesArray = getUpdates(BASE_URL.TOKEN."/getUpdates");
$newUpdatesArray = getNewUpdates($dbh, getUpdates(BASE_URL.TOKEN."/getUpdates"));
foreach($updatesArray as $update){

    if(in_array($update["update_id"],$newUpdatesArray)){
        print_r($update);
        print_r("<br>");
        recognizeCmd($dbh, $update);
    }

}

//  Функция отправляет запрос и получает JSON-объект с текущими обновлениями
function getUpdates($url){
    $jsobObject = json_decode(file_get_contents($url));
    if($jsobObject->ok==false){
        return false;
    }
    $outArr=array();
    foreach($jsobObject->result as $currMessage){
        $outArr[] = array(  "update_id" => $currMessage->update_id,
                            "sender_id" => $currMessage->message->from->id,
                            "date"      => $currMessage->message->date,
                            "text"      => $currMessage->message->text);
    }
    return $outArr;
}

//  Функция принимает JSON-объект и делает запрос в БД, чтобы проверить, какие сообщения еще не обрабатывались на данный момент
//  Возвращает объекты с новыми сообщениями
function getNewUpdates($dbh, $updates){

    $sql_statement_create_table_telegram_bot =
        "CREATE TABLE IF NOT EXISTS `zernoib`.`telegram_bot`
            ( `update_id` INT(32) NOT NULL, `sender_id` INT(32) NOT NULL,
              `date` INT(32) NOT NULL, `text` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
              PRIMARY KEY (`update_id`))
              ENGINE = InnoDB
              CHARSET = utf8 COLLATE utf8_general_ci;";

    $stmt = $dbh->prepare($sql_statement_create_table_telegram_bot);
	$stmt->execute();

    $updatesArr=array();
    foreach($updates as $update){
        array_push($updatesArr,$update["update_id"]);
    }

    $sql_statement_select_new_updates = "SELECT update_id FROM zernoib.telegram_bot;";
	$sth = $dbh->query($sql_statement_select_new_updates);
    $rows = $sth->fetchAll();

    $updatesFromDBArr=array();
    foreach($rows as $row){
        array_push($updatesFromDBArr, $row["update_id"]);
    }

    $newUpdatesArr = array_diff($updatesArr, $updatesFromDBArr);

    return $newUpdatesArr;
}

function recognizeCmd($dbh, $update){

    //if( preg_match('/[Кк]онфигурация/',$update["text"],$matches) ){
    if( preg_match('/\/conf/',$update["text"],$matches) ){

        $arrayToSend = cmdGetConfiguration($dbh);
        foreach($arrayToSend as $currMess){
            file_get_contents(BASE_URL.TOKEN."/sendMessage?chat_id=".$update["sender_id"]."&text=".$currMess);
        }
        dbWriteUpdates($dbh, $update);

    //} else if (preg_match('/[Тт]емпература\s*(\d{0,5})\.?(\d{0,2})?\.?(\d{0,2})?/',$update["text"],$matches)) {
    } else if (preg_match('/\/temp\s*(\d{0,5})\.?(\d{0,2})?\.?(\d{0,2})?/',$update["text"],$matches)) {

        $arrayToSend = cmdGetTemperatures($dbh, $matches[1], $matches[2], $matches[3]);
        foreach($arrayToSend as $currMess){
            file_get_contents(BASE_URL.TOKEN."/sendMessage?chat_id=".$update["sender_id"]."&text=".$currMess);
        }
        dbWriteUpdates($dbh, $update);
        
    //} else if (preg_match('/[Сс]корость\s*(\d{0,5})\.?(\d{0,2})?\.?(\d{0,2})?/',$update["text"],$matches)) {
    } else if (preg_match('/\/speed\s*(\d{0,5})\.?(\d{0,2})?\.?(\d{0,2})?/',$update["text"],$matches)) {

        $arrayToSend = cmdGetTemperatureSpeeds($dbh, $matches[1], $matches[2], $matches[3]);
        foreach($arrayToSend as $currMess){
            file_get_contents(BASE_URL.TOKEN."/sendMessage?chat_id=".$update["sender_id"]."&text=".$currMess);
        }
        dbWriteUpdates($dbh, $update);

    //} else if (preg_match('/[Уу]ровень\s+(\d{0,5})?/',$update["text"],$matches)){
    } else if (preg_match('/\/lvl\s*(\d{0,5})?/',$update["text"],$matches)){

        $arrayToSend = cmdGetGrainLevels($dbh, $matches[1]);
        foreach($arrayToSend as $currMess){
            file_get_contents(BASE_URL.TOKEN."/sendMessage?chat_id=".$update["sender_id"]."&text=".$currMess);
        }
        dbWriteUpdates($dbh, $update);
        
    //} else if (preg_match('/[Аа]лармы/',$update["text"],$matches)){
    } else if (preg_match('/\/alarms/',$update["text"],$matches)){

        $arrayToSend = cmdGetAlarms($dbh);
        foreach($arrayToSend as $currMess){
            file_get_contents(BASE_URL.TOKEN."/sendMessage?chat_id=".$update["sender_id"]."&text=".$currMess);
        }
        dbWriteUpdates($dbh, $update);

    } else if (preg_match('/\/start/',$update["text"],$matches)) {
        
        dbWriteUpdates($dbh, $update);

    } else {

        file_get_contents(BASE_URL.TOKEN."/sendMessage?chat_id=".$update["sender_id"]."&text=Неопознанная команда");        
        dbWriteUpdates($dbh, $update);

    }

    echo "<br><br>";

    return;
}
//  Запись выполненных команд в БД
function dbWriteUpdates($dbh, $update){
    $query = "INSERT INTO zernoib.telegram_bot
			   		(update_id, sender_id, date, text)
			  VALUES ("
              ."'".$update["update_id"]."','".$update["sender_id"]."','".$update["date"]."','".$update["text"]."');";
    $stmt = $dbh->prepare($query);
    $stmt->execute();
    return;
}
//  Удаление из БД старых команд
function dbTruncateOldUpdates(){

    return;
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
        if($sensor_num!=""){
            array_push($outArr, "Запрашиваемый датчик отсутствует в текущем проекте;");
        } else if($podv_id!=""){
            array_push($outArr, "Запрашиваемая подвеска отсутствует в текущем проекте;");
        } else if($silo_name!=""){
            array_push($outArr, "Запрашиваемый силос отсутствует в текущем проекте;");
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
        if($sensor_num!=""){
            array_push($outArr, "Запрашиваемый датчик отсутствует в текущем проекте;");
        } else if($podv_id!=""){
            array_push($outArr, "Запрашиваемая подвеска отсутствует в текущем проекте;");
        } else if($silo_name!=""){
            array_push($outArr, "Запрашиваемый силос отсутствует в текущем проекте;");
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

exit_from_script:

?>