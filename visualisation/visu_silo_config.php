<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/currValsFromTS.php');

//  Отрисовка таблицы "Типы продукта"
function vSConf_draw_Prodtypes($dbh, $accessLevel){

    $inputsDisabled = $accessLevel<2 ? "disabled" : "";

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
                    <img  src=\"img/icon-remove.png\" width=\"20\" height=\"20\"/>
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

    $inputsDisabled = $accessLevel<1 ? "disabled" : "";

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

?>