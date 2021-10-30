<?php

require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/configParameters.php');

//  Данный файл включается во все страницы проекта
session_start();    //  сессия необходима для сохранения текущего пользователя

$accessLevel=0;

//  Аутентификация
function auth_signIn($dbh, $userName, $password){

    $hash = "jasdghlkjsdh";
    $password = md5($hash.$password);

    $query = "SELECT SESSION_curr_access_level FROM users WHERE user_name = '$userName' AND password = '$password';" ;
	$sth = $dbh->query($query);

    $user = $sth->fetchAll();

	if(count($user)==0){
		return "WRONG";
	}

    return $user[0]['SESSION_curr_access_level'];
}

if( isset($_POST['POST_auth_signIn_user_name']) && isset($_POST['POST_auth_signIn_password']) ) {
    if( auth_signIn($dbh, $_POST['POST_auth_signIn_user_name'], $_POST['POST_auth_signIn_password']) != "WRONG"){
        $_SESSION["SESSION_curr_access_level"] = auth_signIn($dbh, $_POST['POST_auth_signIn_user_name'], $_POST['POST_auth_signIn_password']);
        echo "OK";
    } else {
        echo "WRONG";
    }
}

//  Выход из текущей учетной записи
if( isset($_POST['POST_auth_signOut']) ) {
    $_SESSION["SESSION_curr_access_level"] = 0;
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

if( isset($_POST['POST_auth_changePassword_userName']) && isset($_POST['POST_auth_changePassword_password']) ) {
    echo auth_changePassword($dbh, $_POST['POST_auth_changePassword_userName'], $_POST['POST_auth_changePassword_password']);
}

//  Проверка уровня доступа для текущего пользователя
if (isset($_SESSION["SESSION_curr_access_level"])){
    $accessLevel = $_SESSION["SESSION_curr_access_level"];
}

//  Получение текущего пользователя из сессии
function auth_getCurrentUser(){
        $current_user = "anonymous";

    if ( isset($_SESSION["SESSION_curr_access_level"]) ){

        switch($_SESSION["SESSION_curr_access_level"]){
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

if( isset($_POST['POST_auth_getCurrentUser']) ) {
    echo auth_getCurrentUser();
}

?>