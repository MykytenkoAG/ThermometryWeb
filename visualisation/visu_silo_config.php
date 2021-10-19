<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/currValsFromTS.php');

function drawTableProdtypes(){

    global $dbh;
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
                    onchange=\"onClickTblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 300px;\"
                    value=\"".$row['product_name']."\"></input>
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-t-min-".$row['product_id']."\"
                    onchange=\"onClickTblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 80px;\"
                    value=\"".$row['t_min']."\"></input>
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-t-max-".$row['product_id']."\"
                    onchange=\"onClickTblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 80px;\"
                    value=\"".$row['t_max']."\"></input>
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-v-min-".$row['product_id']."\"
                    onchange=\"onClickTblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 60px;\"
                    value=\"".$row['v_min']."\"></input>
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-v-max-".$row['product_id']."\"
                    onchange=\"onClickTblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 60px;\"
                    value=\"".$row['v_max']."\"></input>
            </td>
            <td>
                <button type=\"submit\" class=\"btn btn-danger mx-auto\"
                    id=\"prodtypes-remove-btn-".$row['product_id']."\" onclick=\"onClickTblProdtypesRemoveRow(".$row['product_id'].")\" ";
                    
            if( ! is_null($row['silo_id']) || count($rows)==1 ){
                $outStr .= "disabled";
            }

            $outStr .= ">
                   <svg width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-x-lg mx-auto\" viewBox=\"0 0 16 16\">
                        <path d=\"M1.293 1.293a1 1 0 0 1 1.414 0L8 6.586l5.293-5.293a1 1 0 1 1 1.414 1.414L9.414 8l5.293 5.293a1 1 0 0 1-1.414 1.414L8 9.414l-5.293 5.293a1 1 0 0 1-1.414-1.414L6.586 8 1.293 2.707a1 1 0 0 1 0-1.414z\"/>
                    </svg>
                </button>
            </td>
        </tr>";

    }

    $outStr .= "</tbody></table>";

    return $outStr;
}

function drawTableProdtypesbysilo(){

    global $dbh;

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

        $grainLevelFromTSStr="  <select class=\"form-control mx-auto\" name=\"\"
                                    id=\"prodtypesbysilo-grain-level-from-TS-".$rows[$i]['silo_id']."\"
                                    onchange=\"onChangeTblProdtypesbysilo()\"
                                    >
                                    <option value=\"$currValue\">$grainLevelFromTSStrV</option>
                                    <option value=\"auto\">автоматически</option>
                                    <option value=\"manual\">в ручную</option>
                                </select>";

        $grainLevelStr = "  <select class=\"form-control mx-auto\" name=\"\"
                                id=\"prodtypesbysilo-grain-level-".$rows[$i]['silo_id']."\"
                                onchange=\"onChangeTblProdtypesbysilo()\"
                                >
                                <option value=\"".$rows[$i]['grain_level']."\">".$rows[$i]['grain_level']."</option>";
 
        for($j=0; $j<=($grainLevelsArr[$i]['max(sensor_num)']);$j++){
            $grainLevelStr .= "<option value=\"$j\">".$j."</option>";
        }
 
        $grainLevelStr .= "</select>";
 
        $productNameStr =   "<select class=\"form-control mx-auto\" name=\"\"
                                id=\"prodtypesbysilo-product-name-".$rows[$i]['silo_id']."\"
                                onchange=\"onChangeTblProdtypesbysilo()\"
                                >
                                <option value=\"".$rows[$i]['product_id']."\">".$rows[$i]['product_name']."</option>";

        foreach($prodTypesArr as $product){
            $productNameStr .= "<option value=\"".$product['product_id']."\">".$product['product_name']."</option>";
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

/*	Можно вызывать только при отсутствии активных АПС
    Должен оставаться хотя бы один продукт*/
function prodtypesRemove($product_id){
    global $dbh;

    $sql = "DELETE FROM prodtypes WHERE product_id=$product_id";
    $sth = $dbh->query($sql);

    return;
}

/*	Функция добавления нового продукта*/
function prodtypesInsert($product_id, $product_name, $t_min, $t_max, $v_min, $v_max){

    global $dbh;

    $query="INSERT INTO prodtypes (product_id, product_name, t_min, t_max, v_min, v_max) VALUES ($product_id, \"$product_name\", $t_min, $t_max, $v_min, $v_max);";
    $stmt = $dbh->prepare($query);
    $stmt->execute();

    return $query;
}

/*  Можно вызывать только при отсутствии активных АПС */
function prodtypesUpdate($product_id, $product_name, $t_min, $t_max, $v_min, $v_max){
    global $dbh;

    $sql = "UPDATE prodtypes SET product_name='$product_name', t_min='$t_min', t_max='$t_max', v_min ='$v_min', v_max='$v_max' WHERE product_id=$product_id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    return $sql;
}

/*  Функция изменения загрузки силоса*/
function prodtypesbysiloUpdate($silo_id, $grainLevelFromTS, $grain_level, $product_id){
    global $dbh;

    $sql = "UPDATE prodtypesbysilo SET grain_level_FromTS='$grainLevelFromTS', grain_level='$grain_level', product_id='$product_id' WHERE silo_id=$silo_id";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();

    return $sql;
}

if( isset($_POST['draw_table_prodtypes']) ) {
    echo drawTableProdtypes();
}

if( isset($_POST['draw_table_prodtypes_by_silo']) ) {
    echo drawTableProdtypesbysilo();
}

if( isset($_POST['tbl_prodtypes_changes_queue']) ) {

    $prodTypesChangesQueue = $_POST['tbl_prodtypes_changes_queue'];

    foreach($prodTypesChangesQueue as $currChange){

        if (key($currChange)=="remove_row"){
            prodtypesRemove( $currChange[key($currChange)]['product_id'] );
        } elseif(key($currChange)=="update_row"){
            prodtypesUpdate(    $currChange[key($currChange)]['product_id'],
                                $currChange[key($currChange)]['product_name'],
                                $currChange[key($currChange)]['t_min'],
                                $currChange[key($currChange)]['t_max'],
                                $currChange[key($currChange)]['v_min'],
                                $currChange[key($currChange)]['v_max']);
        } elseif(key($currChange)=="insert_row"){
            prodtypesInsert(    $currChange[key($currChange)]['product_id'],
                                $currChange[key($currChange)]['product_name'],
                                $currChange[key($currChange)]['t_min'],
                                $currChange[key($currChange)]['t_max'],
                                $currChange[key($currChange)]['v_min'],
                                $currChange[key($currChange)]['v_max']);
        }

    }

    echo "Изменения успешно внесены в Базу Данных";
}

if( isset($_POST['tbl_prodtypesbysilo_update_list']) ) {

    $prodtypesBySiloUpdateList = $_POST['tbl_prodtypesbysilo_update_list'];

    foreach($prodtypesBySiloUpdateList as $currUpdate){

        prodtypesbysiloUpdate(  $currUpdate['silo_id'],
                                $currUpdate['grain_level_from_TS'],
                                $currUpdate['grain_level'],
                                $currUpdate['product_id']);

    }

    echo "Изменения успешно внесены в Базу Данных";

}

?>