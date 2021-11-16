<?php

require_once ("auth.php");
require_once ("currValsFromTS.php");

//  Отрисовка таблицы "Типы продукта"
function vSConf_draw_Prodtypes($dbh, $accessLevel){

    $inputsDisabled = $accessLevel<2 ? "disabled" : "";                 //  Данная таблица доступна только технологу

    $outStr="";

    $sql = "SELECT p.product_id, p.product_name, p.t_min, p.t_max, p.v_min, p.v_max, pbs.silo_id 
            FROM prodtypes AS p LEFT JOIN prodtypesbysilo AS pbs ON p.product_id = pbs.product_id
            GROUP BY p.product_id;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();

    $outStr = "<table class=\"table table-hover text-center\" id=\"table-prodtypes\">
                    <thead>
                        <tr>
                            <th scope=\"col\">Тип продукта</th><th scope=\"col\">T мин.</th><th scope=\"col\">Т кр.</th>
                            <th scope=\"col\">V мин.</th><th scope=\"col\">V кр.</th><th scope=\"col\"></th>
                        </tr>
                    </thead>
                    <tbody>";

    foreach($rows as $row){

        $outStr .= "
        <tr>
            <td>
                <input type=\"text\"

                    id=\"prodtypes-product-name-".$row['product_id']."\"
                    onchange=\"vSConf_tblProdtypesUpdateRow(".$row['product_id'].")\"
                    oninput=\"vSConf_checkProductNames()\"

                    class=\"form-control mx-auto productname\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 300px;\"
                    value=\"".$row['product_name']."\" $inputsDisabled></input>
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-t-min-".$row['product_id']."\"
                    onchange=\"vSConf_tblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 80px;\"
                    value=\"".$row['t_min']."\" $inputsDisabled></input>
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-t-max-".$row['product_id']."\"
                    onchange=\"vSConf_tblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 80px;\"
                    value=\"".$row['t_max']."\" $inputsDisabled></input>
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-v-min-".$row['product_id']."\"
                    onchange=\"vSConf_tblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 60px;\"
                    value=\"".$row['v_min']."\" $inputsDisabled></input>
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-v-max-".$row['product_id']."\"
                    onchange=\"vSConf_tblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 60px;\"
                    value=\"".$row['v_max']."\" $inputsDisabled></input>
            </td>
            <td>
                <button type=\"submit\" class=\"btn btn-danger mx-auto\"
                    id=\"prodtypes-remove-btn-".$row['product_id']."\" onclick=\"vSConf_tblProdtypesRemoveRow(".$row['product_id'].")\" ";
                    
            if( ! is_null($row['silo_id']) || count($rows)==1 || $accessLevel<2){
                $outStr .= "disabled";
            }

            $outStr .= ">
                    <img  src=\"assets/img/icon-remove.png\" width=\"20\" height=\"20\"/>
                </button>
            </td>
        </tr>";

    }

    $outStr .= "</tbody></table>";

    return $outStr;
}

if( isset($_POST['POST_vSConf_draw_Prodtypes']) ) {
    echo vSConf_draw_Prodtypes($dbh, $accessLevel);
}
//  Отрисовка таблицы "Загрузка силосов"
function vSConf_draw_Prodtypesbysilo($dbh, $accessLevel){

    $inputsDisabled = $accessLevel<1 ? "disabled" : "";                         //  Данная таблица доступна как оператора, так и технологу

    $sql = "SELECT silo_id, max(sensor_num) FROM sensors GROUP BY silo_id;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $grainLevelsArr = $sth->fetchAll();

    $sql = "SELECT product_id, product_name FROM prodtypes;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $prodTypesArr = $sth->fetchAll();

    $outStr="";

    $sql = "SELECT pbs.silo_id, pbs.silo_name, pbs.product_id, pbs.bs_addr, pbs.grain_level_fromTS, pbs.grain_level, p.product_name
                FROM prodtypesbysilo AS pbs INNER JOIN prodtypes AS p ON pbs.product_id = p.product_id;";

    $sth = $dbh->query($sql);

    if($sth==false){
        return false;
    }

    $rows = $sth->fetchAll();

    $outStr = "<table class=\"table table-hover text-center\" id=\"table-prodtypesbysilo\">
                    <thead>
                        <tr>
                            <th scope=\"col\">Силос</th><th scope=\"col\">БС</th><th style=\"width: 200px;\" scope=\"col\">Определение уровня</th>
                            <th scope=\"col\">Уровень</th><th scope=\"col\">Тип продукта</th>
                        </tr>
                    </thead>
                    <tbody>";

    for($i=0; $i<count($rows); $i++){

        if($rows[$i]['grain_level_fromTS']){
            $grainLevelFromTSStrV="автоматически";
            $currValue="auto";
        } else {
            $grainLevelFromTSStrV="в ручную";
            $currValue="manual";
        }

        $grainLevelFromTSStr = "<select class=\"form-control mx-auto\" name=\"\"
                                    id=\"prodtypesbysilo-grain-level-from-TS-".$rows[$i]['silo_id']."\"
                                    onchange=\"vSConf_tblProdtypesbysiloUpdate()\" $inputsDisabled>";

        if($currValue=="auto"){
            $grainLevelFromTSStr .= "<option value=\"$currValue\">$grainLevelFromTSStrV</option><option value=\"manual\">в ручную</option>";
        } else {
            $grainLevelFromTSStr .= "<option value=\"$currValue\">$grainLevelFromTSStrV</option><option value=\"auto\">автоматически</option>";
        }

        $grainLevelDisabled = $currValue=="auto" || $accessLevel<1 ? "disabled" : "";

        $grainLevelFromTSStr .= "</select>";

        $grainLevelStr = "  <select class=\"form-control mx-auto\" name=\"\"
                                id=\"prodtypesbysilo-grain-level-".$rows[$i]['silo_id']."\"
                                onchange=\"vSConf_tblProdtypesbysiloUpdate()\" $grainLevelDisabled>
                                <option value=\"".$rows[$i]['grain_level']."\">".$rows[$i]['grain_level']."</option>";
 
        for($j=0; $j<=($grainLevelsArr[$i]['max(sensor_num)']+1);$j++){
            if( $rows[$i]['grain_level'] != $j ){
                $grainLevelStr .= "<option value=\"$j\">".$j."</option>";
            }
        }
 
        $grainLevelStr .= "</select>";
 
        $productNameStr =   "<select class=\"form-control mx-auto\" name=\"\"
                                id=\"prodtypesbysilo-product-name-".$rows[$i]['silo_id']."\"
                                onchange=\"vSConf_tblProdtypesbysiloUpdate()\" $inputsDisabled>
                                <option value=\"".$rows[$i]['product_id']."\">".$rows[$i]['product_name']."</option>";

        foreach($prodTypesArr as $product){
            if($product['product_id']!=$rows[$i]['product_id']){
                $productNameStr .= "<option value=\"".$product['product_id']."\">".$product['product_name']."</option>";
            }
            
        }

        $productNameStr .= "</select>";


        $outStr .= "
        <tr>
            <td><div class=\"\">"
                .$rows[$i]['silo_name']                             //  Силос
            ."</div></td>
            <td><div class=\"\">"
                .$rows[$i]['bs_addr']                              //  БС
            ."</div></td>
            <td><div style=\"width: 150px;\">
                ".$grainLevelFromTSStr                              //  Определение уровня
            ."</div></td>
            <td><div class=\"\" style=\"margin-left: auto; margin-right: auto; width: 50px;\">
                ".$grainLevelStr                                     //  Уровень заполнения
            ."</div></td>
            <td>
                ".$productNameStr                                   //  Тип продукта
            ."</td>
        </tr>
        ";

    }

    $outStr .= "</tbody></table>";

    return $outStr;
}

if( isset($_POST['POST_vSConf_draw_Prodtypesbysilo']) ) {
    echo vSConf_draw_Prodtypesbysilo($dbh, $accessLevel);
}
//  Формирование очереди с перечнем изменений для таблицы "Типы продукта"
function vSConf_prodtypes_remove($dbh, $product_id){

    $sql = "DELETE FROM prodtypes WHERE product_id=$product_id";
    $sth = $dbh->query($sql);

    return;
}

function vSConf_prodtypes_insert($dbh, $product_id, $product_name, $t_min, $t_max, $v_min, $v_max){

    $query="INSERT INTO prodtypes (product_id, product_name, t_min, t_max, v_min, v_max) VALUES ($product_id, \"$product_name\", $t_min, $t_max, $v_min, $v_max);";
    $stmt = $dbh->prepare($query);
    $stmt->execute();

    return $query;
}

function vSConf_prodtypes_update($dbh, $product_id, $product_name, $t_min, $t_max, $v_min, $v_max){

    $sql = "UPDATE prodtypes SET product_name='$product_name', t_min='$t_min', t_max='$t_max', v_min ='$v_min', v_max='$v_max' WHERE product_id=$product_id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    return $sql;
}
//  Все необходимые изменения из страницы визуализации (удаление, добавление и изменение характеристик продукта) сохраняются в виде очереди
//  и при нажатии на кнопку "Сохранить" в порядке очереди вызываются функции vSConf_prodtypes_remove(), vSConf_prodtypes_insert(), vSConf_prodtypes_update()
if( isset($_POST['POST_vSConf_prodtypes_changes_queue']) ) {

    $prodTypesChangesQueue = $_POST['POST_vSConf_prodtypes_changes_queue'];

    foreach($prodTypesChangesQueue as $currChange){

        if (key($currChange)=="remove_row"){
            vSConf_prodtypes_remove( $dbh, $currChange[key($currChange)]['product_id'] );
        } elseif(key($currChange)=="update_row"){
            vSConf_prodtypes_update(    $dbh,
                                $currChange[key($currChange)]['product_id'],
                                $currChange[key($currChange)]['product_name'],
                                $currChange[key($currChange)]['t_min'],
                                $currChange[key($currChange)]['t_max'],
                                $currChange[key($currChange)]['v_min'],
                                $currChange[key($currChange)]['v_max']);
        } elseif(key($currChange)=="insert_row"){
            vSConf_prodtypes_insert(    $dbh,
                                $currChange[key($currChange)]['product_id'],
                                $currChange[key($currChange)]['product_name'],
                                $currChange[key($currChange)]['t_min'],
                                $currChange[key($currChange)]['t_max'],
                                $currChange[key($currChange)]['v_min'],
                                $currChange[key($currChange)]['v_max']);
        }

    }

    echo "Изменения успешно внесены в Базу Данных";
}
//  Формирование массива с перечнем изменений для таблицы "Загрузка силосов"
function vSConf_prodtypesbysilo_update($dbh, $silo_id, $grainLevelFromTS, $grain_level, $product_id){

    $sql = "UPDATE prodtypesbysilo SET grain_level_FromTS='$grainLevelFromTS', grain_level='$grain_level', product_id='$product_id' WHERE silo_id=$silo_id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    return $sql;
}
//  В отличии от таблицы "Типы продукта", изменения для таблицы "Загрузка силосов" хранятся в виде массива и при нажатии на кнопку "Сохранить"
//  за один вызов функции vSConf_prodtypesbysilo_update() все изменения сохраняются в Базе Данных
if( isset($_POST['POST_vSConf_prodtypesbysilo_update_list']) ) {

    $prodtypesBySiloUpdateList = $_POST['POST_vSConf_prodtypesbysilo_update_list'];

    foreach($prodtypesBySiloUpdateList as $currUpdate){

        vSConf_prodtypesbysilo_update(  $dbh,
                                $currUpdate['silo_id'],
                                $currUpdate['grain_level_from_TS'],
                                $currUpdate['grain_level'],
                                $currUpdate['product_id']);

    }

    echo "Изменения успешно внесены в Базу Данных";

}

//  Получение текущих настроек для отрисоки на странице
if( isset($_POST['POST_vSConf_get_ts_connection_settings']) ) {
    echo json_encode(array($IPAddr,$port));
}

//  Настройка параметров подключения к Термосервер
if( isset($_POST['POST_ts_connection_settings_ip']) && isset($_POST['POST_ts_connection_settings_port']) ) {

    //  Сохраняем новые настройки IP-адреса и порта
    vSConf_ts_connection_settings_save($dbh, $_POST['POST_ts_connection_settings_ip'], $_POST['POST_ts_connection_settings_port']);

    if(         (  file_exists($_FILES['POST_termoServerIniFile']['tmp_name']) &&  is_uploaded_file($_FILES['POST_termoServerIniFile']['tmp_name']) ) &&
                ( !file_exists($_FILES['POST_termoClientIniFile']['tmp_name']) || !is_uploaded_file($_FILES['POST_termoClientIniFile']['tmp_name']) )    ) {
            
            setcookie("popup_pjtUpdate_TermoClientIniWasNotUploaded", "OK", time()+60);         //  Файл TermoClient.ini не был загружен
            header('Location: silo_config.php');

    } else if ( (  file_exists($_FILES['POST_termoClientIniFile']['tmp_name']) &&  is_uploaded_file($_FILES['POST_termoClientIniFile']['tmp_name']) ) &&
                ( !file_exists($_FILES['POST_termoServerIniFile']['tmp_name']) || !is_uploaded_file($_FILES['POST_termoServerIniFile']['tmp_name']) ) ) {
            
            setcookie("popup_pjtUpdate_TermoServerIniWasNotUploaded", "OK", time()+60);         //  Файл TermoServer.ini не был загружен
            header('Location: silo_config.php');

    } else if ( ( file_exists($_FILES['POST_termoClientIniFile']['tmp_name']) &&  is_uploaded_file($_FILES['POST_termoClientIniFile']['tmp_name']) ) &&
                ( file_exists($_FILES['POST_termoServerIniFile']['tmp_name']) &&  is_uploaded_file($_FILES['POST_termoServerIniFile']['tmp_name']) )) {

        $uploadedTermoServerINI  =	@parse_ini_string(replaceForbiddenChars(file_get_contents($_FILES['POST_termoServerIniFile']['tmp_name'])), true);
        $uploadedTermoClientINI  =	@parse_ini_string(replaceForbiddenChars(file_get_contents($_FILES['POST_termoClientIniFile']['tmp_name'])), true);

        if( ! isIniFileTermoServerOK($uploadedTermoServerINI) ){                                //  Файл TermoServer.ini поврежден

            setcookie("popup_pjtUpdate_TermoServerIni_Damaged", "OK", time()+60);
            header('Location: silo_config.php');

        } else if( ! isIniFileTermoClientOK($uploadedTermoClientINI) ){                         //  Файл TermoClient.ini поврежден

            setcookie("popup_pjtUpdate_TermoClientIni_Damaged", "OK", time()+60);
            header('Location: silo_config.php');
            
        } else if( ! areIniFilesConsistent($uploadedTermoServerINI,$uploadedTermoClientINI)){   //  Файлы не соответствуют друг другу

            setcookie("popup_pjtUpdate_IniFilesAreNotConsistent", "OK", time()+60);
            header('Location: silo_config.php');

        } else {                                                                                //  Все ОК. Обновляем проект

            //  Перемещаем файлы в папку Settings
            move_uploaded_file($_FILES['POST_termoServerIniFile']['tmp_name'], "settings/TermoServer.ini");
            move_uploaded_file($_FILES['POST_termoClientIniFile']['tmp_name'], "settings/TermoClient.ini");

            $termoServerINI  =	@parse_ini_string(replaceForbiddenChars(file_get_contents('settings/TermoServer.ini')), true);
            $termoClientINI  =	@parse_ini_string(replaceForbiddenChars(file_get_contents('settings/TermoClient.ini')), true);

            projectUpdate($dbh, $termoClientINI, $termoServerINI);
            header('Location: index.php');
        }

    } else {    //  Были изменены только настройки IP-адреса и порта
        setcookie("popupTSConnSettingsChanged", "OK", time()+60);
        header('Location: silo_config.php');
    }

}

function vSConf_ts_connection_settings_save($dbh, $ts_ip, $ts_port){

    $query = "UPDATE zernoib.ts_conn_settings SET ts_ip='$ts_ip', ts_port='$ts_port' WHERE id=1;";
    $stmt = $dbh->prepare($query);
    $stmt->execute();

    return;
}

//  Операции с Базой Данных --------------------------------------------------------------------------------------------------------------------------------
//  Резервное копирование БД
//  Отправка AJAX запроса, который должен вернуть ссылку на файл
if( isset($_POST['POST_vSConf_db_create_backup']) ) {
    $sql_backup_file = "dbBackups/". dbname . date("_d.m.Y_H.i.s") . '.sql';
    $dumpCommand = '/wamp64/bin/mysql/mysql5.7.31/bin/mysqldump.exe --host='.servername.' --user='.username.' --password='.password.' --databases '.dbname.' > '. $sql_backup_file;
    system($dumpCommand);
    echo $sql_backup_file;
}

//  Восстановить БД из резервной копии ------------------------------------------------
//  Проверка файла резервной копии на соответствие текущей Базе Данных
function vSConf_db_checkDBFileToCurrentDB($dbh, $dbBackupFile){

    $sql = "SELECT silo_id, silo_name FROM prodtypesbysilo ORDER BY silo_id DESC LIMIT 1;";
    $sth = $dbh->query($sql);
    $prodtypesbysilo_rows = $sth->fetchAll();
    $db_prodtypesbysilo_last_silo_id = $prodtypesbysilo_rows[0]['silo_id'];
    $db_prodtypesbysilo_last_silo_name = "'".$prodtypesbysilo_rows[0]['silo_name']."'";

    $sql = "SELECT sensor_id FROM sensors ORDER BY sensor_id DESC LIMIT 1;";
    $sth = $dbh->query($sql);
    $db_sensors_rows = $sth->fetchAll();
    $db_sensors_last_sensor_id = $db_sensors_rows[0]['sensor_id'];

    //  Проверяем последний silo_id и silo_name
    if (preg_match_all('/INSERT INTO `prodtypesbysilo` VALUES .+,\((\d+),(.+),.+,.+,.+,.+,.+,.+,.+,.+,.+\);/', $dbBackupFile, $result)) {
        $dbBackupFile_last_silo_id = $result[1][0];     //only values inside quotation marks
        $dbBackupFile_last_silo_name = $result[2][0];
    }

    //  Проверяем максимальный sensor_id
    if (preg_match_all('/INSERT INTO `sensors` VALUES .+,\((\d+),\d+,\d+,\d+,\d+,\d+,\d+,.+,.+,.+,.+,.+,.+,.+,\d+,.+,\d+,.+,\d+,.+,\d+,.+,\d+,.+,.+\);/', $dbBackupFile, $result)) {
        $dbBackupFile_last_sensor_id = $result[1][0];   //only values inside quotation marks
    }

    if( $db_prodtypesbysilo_last_silo_id == $dbBackupFile_last_silo_id &&
        $db_prodtypesbysilo_last_silo_name == $dbBackupFile_last_silo_name &&
        $db_sensors_last_sensor_id == $dbBackupFile_last_sensor_id ){
        return true;
    }

    return false;
    
}

//  ! Необходимо добавить try catch, так как возможны ошибки при выполнении sql команд
function vSConf_db_restore_from_backup($dbh, $dbBackupFile){
    //  Необходимо предварительно сохранить таблицу с пользователями и таблицу с настройками подключения к Термосерверу

    //  Сохраняем текущее состояние таблицы users
    $sql = "SELECT user_id, user_name, password, access_level FROM users;";
    $sth = $dbh->query($sql);
    if($sth==false){
        return false;
    }
    $users_rows = $sth->fetchAll();

    $query_users = SQL_STATEMENT_DROP_USERS.SQL_STATEMENT_CREATE_USERS."INSERT INTO users (user_id, user_name, password, access_level) VALUES ";

    foreach($users_rows as $users_row){
        $query_users .= "(".$users_row['user_id'].", '".$users_row['user_name']."', '".$users_row['password']."', '".$users_row['access_level']."'),";
    }
    $query_users = substr( $query_users, 0, -1 ).";";

    //  Сохраняем текущее состояние таблицы ts_conn_settings
    $sql = "SELECT id, ts_ip, ts_port FROM ts_conn_settings;";
    $sth = $dbh->query($sql);
    if($sth==false){
        return false;
    }
    $ts_conn_settings_rows = $sth->fetchAll();

    $query_ts_conn_settings=SQL_STATEMENT_DROP_TS_CONN_SETTINGS.SQL_STATEMENT_CREATE_TS_CONN_SETTINGS
                            ."INSERT INTO zernoib.ts_conn_settings (id, ts_ip, ts_port) VALUES ";
    foreach($ts_conn_settings_rows as $ts_conn_settings_row){
        $query_ts_conn_settings .= "(".$ts_conn_settings_row['id'].", '".$ts_conn_settings_row['ts_ip']."', '".$ts_conn_settings_row['ts_port']."'),";
    }
    $query_ts_conn_settings = substr( $query_ts_conn_settings, 0, -1 ).";";

    $stmt = $dbh->prepare( $dbBackupFile . $query_users . $query_ts_conn_settings );
    $stmt->execute();

    return;
}

if(isset($_POST["POST_sconf_db_restore_from_backup"])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["databaseBackupFile"]["name"]);
    if(move_uploaded_file($_FILES["databaseBackupFile"]["tmp_name"], $target_file)){
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_type = finfo_file( $fileInfo, $target_file );
        finfo_close( $fileInfo );
        if($detected_type!=="text/plain"){

            setcookie("db_databaseBackupFile_unknownFormat", "OK", time()+3600);            //  Если пользователь загрузил файл не того формата
            header('Location: silo_config.php');

        } else {
            
            if (vSConf_db_checkDBFileToCurrentDB($dbh, file_get_contents($target_file)) ){  //  Если загруженный файл соответствует конфигурации БД текущего проекта
                vSConf_db_restore_from_backup($dbh, file_get_contents($target_file));   //  Выполняем восстановление БД
                // Удаляем все файлы в папке uploads
                $files = glob('uploads/*'); // get all file names
                foreach($files as $file){ // iterate files
                if(is_file($file))
                    unlink($file); // delete file
                }
                setcookie("dbRestoredSuccessfully", "OK", time()+3600);
                header('Location: silo_config.php');
            } else {
                setcookie("db_databaseBackupFile_is_Bad", "OK", time()+3600);               //  Загруженный файл не соответствует конфигурации текущей БД
                header('Location: silo_config.php');
            }

        }
    } else {

        setcookie("errorUploadingFile", "OK", time()+3600);
        header('Location: silo_config.php');

    }
}

//  Очистить БД
//  Отправка AJAX-запроса с командой на очистку БД с измерениями
if( isset($_POST['POST_vSConf_db_truncate_measurements']) ) {
    ddl_truncate_Measurements($dbh);
    echo "База данных успешно очищена";
}

//  Удаление записей, которые старше одного месяца
if( isset($_POST['POST_vSConf_db_delete_old_measurements']) ) {
    ddl_delete_old_measurements($dbh, "1 MONTH");
    echo "Записи успешно удалены";
}

//  Очистка журнала АПС ---------------------------------------------------------------------------------------------------------------------------------------------
if( isset($_POST['POST_vSConf_clear_log']) ) {
    logClear($logFile);
    echo "Журнал АПС успешно очищен";
}

?>