<?php

session_start();    //  сессия необходима для сохранения текущего пользователя

$accessLevel=0;
/*  Авторизация
*/
if( isset($_POST['auth_user_name']) && isset($_POST['auth_password']) ) {

    require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/configParameters.php');

    $hash = "jasdghlkjsdh";
    $userName = $_POST['auth_user_name'];
    $password = md5($hash.$_POST['auth_password']);

    $query = "SELECT access_level FROM users WHERE user_name = '$userName' AND password = '$password';" ;
	$sth = $dbh->query($query);

    $user = $sth->fetchAll();

	if(count($user)==0){
        echo "WRONG";
		return;
	}

    $_SESSION["access_level"] = $user[0]['access_level'];

    echo "OK";

}
/*  Выход из текущей учетной записи
*/
if( isset($_POST['auth_sign_out']) ) {

    $_SESSION["access_level"] = 0;
    echo "OK";

}
/*  Смена пароля
*/
if( isset($_POST['auth_pwd_change_user_name']) && isset($_POST['auth_pwd_change_password']) ) {

    require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/configParameters.php');

    $hash = "jasdghlkjsdh";
    $userName = $_POST['auth_pwd_change_user_name'];
    $password = md5($hash.$_POST['auth_pwd_change_password']);

    $query = "UPDATE users SET password='$password' WHERE user_name = '$userName';" ;
    $stmt = $dbh->prepare($query);
    $stmt->execute();

    echo "OK";
}
/*  Проверка уровня доступа для текущего пользователя
*/
if (isset($_SESSION["access_level"])){
    $accessLevel = $_SESSION["access_level"];
}
/*  Получение текущего пользователя из сессии
*/
if( isset($_POST['get_current_user']) ) {

    $current_user = "anonymous";

    if ( isset($_SESSION["access_level"]) ){

        switch($_SESSION["access_level"]){
        case 1:
            $current_user =  "oper";
            break;
        case 2:
            $current_user =  "tehn";
            break;
        }
    }

    echo $current_user;
}

?>