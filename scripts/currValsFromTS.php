<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/configParameters.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbDebugTables.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbCreateTables.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/checkCurrConfig.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbCurrVals.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/dbAlarms.php');

update_lvl($dbh, $arrayOfLevels);
update_t_v($dbh, $arrayOfTemperatures, $arrayOfTempSpeeds, $serverDate);

setNACK ($dbh, $serverDate);
resetACK($dbh, $serverDate);

if( isset($_POST['read_vals']) ) {
	echo "Данные успешно прочитаны" ;
}

if( isset($_POST['is_sound_on']) ) {
    echo isSoundOn($dbh);
}

if( isset($_POST['acknowledge']) ) {
	setACK($dbh,$serverDate);
    echo "Произведено подтверждение сигналов АПС" ;
}

if( isset($_POST['get_project_conf_array']) ) {
    echo json_encode( getProjectConfArr($dbh) ) ;
}

?>