


<script type="text/javascript" src="../node_modules/jquery/dist/jquery.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>


<script type="text/javascript" src="../bootstrap-multiselect/dist/js/bootstrap-multiselect.js"></script>
<link href="../bootstrap-multiselect/dist/css/bootstrap-multiselect.css" rel="stylesheet"/>




<select id="example-getting-started" multiple="multiple">
    <option value="cheese">Cheese</option>
    <option value="tomatoes">Tomatoes</option>
    <option value="mozarella">Mozzarella</option>
    <option value="mushrooms">Mushrooms</option>
    <option value="pepperoni">Pepperoni</option>
    <option value="onions">Onions</option>
</select>


<script type="text/javascript">
    $(document).ready(function() {
        $('#example-getting-started').multiselect();
    });
</script>

<?php

//require_once ('../php/configFromINI.php');
$dbh = new PDO("mysql:host=127.0.0.1;dbname=zernoib", 'root', '');

//echo drawTableProdtypes();
$a=1;

function drawTableProdtypes(){

    global $dbh;
    $outStr="";

    $sql = "SELECT p.product_id, p.product_name, p.t_min, p.t_max, p.v_min, p.v_max, pbs.silo_id 
            FROM prodtypes AS p LEFT JOIN prodtypesbysilo AS pbs ON p.product_id = pbs.product_id
            GROUP BY p.product_id";

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
                    value=\"".$row['product_name']."\">
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-t-min-".$row['product_id']."\"
                    onchange=\"onClickTblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 80px;\"
                    value=\"".$row['t_min']."\">
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-t-max-".$row['product_id']."\"
                    onchange=\"onClickTblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 80px;\"
                    value=\"".$row['t_max']."\">
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-v-min-".$row['product_id']."\"
                    onchange=\"onClickTblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 60px;\"
                    value=\"".$row['v_min']."\">
            </td>
            <td>
                <input type=\"number\"

                    id=\"prodtypes-v-max-".$row['product_id']."\"
                    onchange=\"onClickTblProdtypesUpdateRow(".$row['product_id'].")\"

                    class=\"form-control mx-auto\" aria-label=\"Sizing example input\" aria-describedby=\"inputGroup-sizing-sm\" style=\"width: 60px;\"
                    value=\"".$row['v_max']."\">
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

function getProjectConfArr(){

    global $dbh;
    $projectConfArr = array();

    $sql = "SELECT s.sensor_id, s.silo_id, pbs.silo_name, s.podv_id, s.sensor_num
            FROM zernoib.sensors AS s INNER JOIN zernoib.prodtypesbysilo AS pbs
            ON s.silo_id = pbs.silo_id;";
    $sth = $dbh->query($sql);
    
    if($sth==false){
        return false;
    }
    $rows = $sth->fetchAll();

    $currSiloName="";
    $currPodvNum="";

    for ($i=0; $i<count($rows); $i++){

        if($currSiloName != $rows[$i]['silo_name']){
            $currSiloName = $rows[$i]['silo_name'];
            $currPodvNum="";
            $projectConfArr[$currSiloName]=array();
        }

        if($currPodvNum != ($rows[$i]['podv_id'] + 1) ){
            $currPodvNum = $rows[$i]['podv_id'] + 1;
            $projectConfArr[$currSiloName][$currPodvNum]=array();
        }

        $projectConfArr[$currSiloName][$currPodvNum][$rows[$i]['sensor_num'] + 1] = $rows[$i]['sensor_num'] + 1;

    }

    return $projectConfArr;
}

getAllMeasurementDates();

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
        <div class=\"form-check mt-2 mb-2 collapse\">
            <input class=\"form-check-input\" type=\"checkbox\" value=\"\" id=\""."__\">
            <label class=\"form-check-label\">
                Все
            </label>
        </div>";
    }

    foreach($measurementArray as $date => $time){

        $outStr.= " <p>
                        <button class=\"btn btn-secondary\" type=\"button\" data-bs-toggle=\"collapse\"
                                data-bs-target=\".d$date\" aria-expanded=\"false\">
                            $date
                        </button>
                    </p>";

        foreach($time as $measTime){
            $outStr.= "
                    <div class=\"form-check mt-2 mb-2 collapse multi-collapse d$date\">
                        <input class=\"form-check-input\" type=\"checkbox\" value=\"\" id=\"$date"."_$measTime\">
                        <label class=\"form-check-label\">
                            $measTime
                        </label>
                    </div>";
        }

    }

    return $outStr;
}

?>