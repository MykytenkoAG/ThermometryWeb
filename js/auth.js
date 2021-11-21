//  Функция определения текущего пользователя. Вызывается каждый раз при заходе на новую страницу
function authGetCurrentUser() {
    $.ajax({
        url: '/Thermometry/php/auth/auth.php',
        type: 'POST',
        cache: false,
        data: { 'POST_auth_getCurrentUser': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            curr_user = fromPHP;
            if ((current_page === "silo_config.php") && !["oper", "tehn"].includes(curr_user)) { //  Выход на главную в случае, если мы на странице настроек
                document.location.href = "index.php";
            }
            if ((current_page === "silo_config.php") && curr_user === "tehn") { //  Включение кнопки "Добавить", в случае, если пользователь - технолог
                vSConf_buttonEnable("sconf-table-prodtypes-btn-add");
            }
        }
    });
}
//  Функция для очистки неудачно введенного пароля
function modalPasswordInputClear(inputPasswordID) {
    document.getElementById(inputPasswordID).value = "";
    return;
}
//  Аутентификация. В случае правильно введенного пароля, текущий пользователь сохраняется в сессии, которая автоматически очищается при выходе из браузера
function authSignIn(user, inputPassID) {
    password = document.getElementById(inputPassID).value;

    $.ajax({
        url: '/Thermometry/php/auth/auth.php',
        type: 'POST',
        cache: false,
        data: { 'POST_auth_signIn_user_name': user, 'POST_auth_signIn_password': password },
        dataType: 'html',
        success: function(fromPHP) {
            if (fromPHP == "WRONG") {
                document.getElementById("modal-info-header").setAttribute("style", "background-color: #520007;");
                document.getElementById("modal-info-title").innerText = "Ошибка";
                document.getElementById("modal-info-body-message").innerText = "Пароль не верный!";
                modalPasswordInputClear('modal-sign-in-password');
                modalPasswordInputClear('modal-pass-change-pwd1');
                modalPasswordInputClear('modal-pass-change-pwd2');
                $("#modal-info").modal('show');
            } else {
                document.location.href = current_page; //   В случае правильно введенного пароля просто перезагружаем страницу, все остальное сделано в PHP
            }
        }
    });

    return;
}
//  Функция выхода из текущей учетной записи
function authSignOut() {
    $.ajax({
        url: '/Thermometry/php/auth/auth.php',
        type: 'POST',
        cache: false,
        data: { 'POST_auth_signOut': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            if (current_page === "silo_config.php") {
                document.location.href = "index.php";
            } else {
                document.location.href = current_page;
            }
        }
    });
    return;
}
//  Функция для смены пароля
function authPasswordChange(user, inputPassID1, inputPassID2) {

    pwd1 = document.getElementById(inputPassID1).value;
    pwd2 = document.getElementById(inputPassID2).value;

    if (pwd1 !== pwd2) {
        document.getElementById("modal-info-header").setAttribute("style", "background-color: #520007;");
        document.getElementById("modal-info-title").innerText = "Ошибка";
        document.getElementById("modal-info-body-message").innerText = "Введенные пароли не совпадают!";
        modalPasswordInputClear('modal-sign-in-password');
        modalPasswordInputClear('modal-pass-change-pwd1');
        modalPasswordInputClear('modal-pass-change-pwd2');
        $("#modal-info").modal('show');
    } else {
        $.ajax({
            url: '/Thermometry/php/auth/auth.php',
            type: 'POST',
            cache: false,
            data: { 'POST_auth_changePassword_userName': user, 'POST_auth_changePassword_password': pwd1 },
            dataType: 'html',
            success: function(fromPHP) {
                document.getElementById("modal-info-header").setAttribute("style", "background-color: #4046ff;");
                document.getElementById("modal-info-title").innerText = "";
                document.getElementById("modal-info-body-message").innerText = "Пароль успешно изменен!";
                $("#modal-info").modal('show');
            }
        });
    }

    return;
}