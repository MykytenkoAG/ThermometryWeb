<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/configParameters.php');

//  Данный файл включается во все страницы проекта
session_start();    //  сессия необходима для сохранения текущего пользователя

$accessLevel=0;

//  Аутентификация
function auth_signIn($dbh, $userName, $password){

    $hash = "jasdghlkjsdh";
    $password = md5($hash.$password);

    $query = "SELECT access_level FROM users WHERE user_name = '$userName' AND password = '$password';" ;
	$sth = $dbh->query($query);

    $user = $sth->fetchAll();

	if(count($user)==0){
		return "WRONG";
	}

    return $user[0]['access_level'];
}

if( isset($_POST['auth_user_name']) && isset($_POST['auth_password']) ) {
    if( auth_signIn($dbh, $_POST['auth_user_name'], $_POST['auth_password']) != "WRONG"){
        $_SESSION["access_level"] = auth_signIn($dbh, $_POST['auth_user_name'], $_POST['auth_password']);
        echo "OK";
    } else {
        echo "WRONG";
    }
}

//  Выход из текущей учетной записи
if( isset($_POST['auth_sign_out']) ) {
    $_SESSION["access_level"] = 0;
    echo "OK";
}

//  Смена пароля
function auth_changePassword($dbh, $userName, $password){

    $hash = "jasdghlkjsdh";
    $password = md5($hash.$password);

    $query = "UPDATE users SET password='$password' WHERE user_name = '$userName';" ;
    $stmt = $dbh->prepare($query);
    $stmt->execute();

    return "OK";
}

if( isset($_POST['auth_pwd_change_user_name']) && isset($_POST['auth_pwd_change_password']) ) {
    echo auth_changePassword($dbh, $_POST['auth_pwd_change_user_name'], $_POST['auth_pwd_change_password']);
}

//  Проверка уровня доступа для текущего пользователя
if (isset($_SESSION["access_level"])){
    $accessLevel = $_SESSION["access_level"];
}

//  Получение текущего пользователя из сессии
function auth_getCurrentUser(){
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

    return $current_user;
}

if( isset($_POST['get_current_user']) ) {
    echo auth_getCurrentUser();
}

?>