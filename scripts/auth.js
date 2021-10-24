function authGetCurrentUser(){
    $.ajax({
        url: '/webTermometry/scripts/auth.php',
        type: 'POST',
        cache: false,
        data: { 'get_current_user': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            curr_user = fromPHP;
            if((current_page==="silo_config.php") && !["oper","tehn"].includes(curr_user)){
                document.location.href = "index.php";
            }
            if((current_page==="silo_config.php") && curr_user==="tehn"){
                buttonEnable("table-prodtypes-btn-add");
            }
        }
    });
}

function modalClearInput(inputPasswordID){
    document.getElementById(inputPasswordID).value = "";
    return;
}

function authSignIn(user, inputPassID){
    password = document.getElementById(inputPassID).value;

    $.ajax({
        url: '/webTermometry/scripts/auth.php',
        type: 'POST',
        cache: false,
        data: { 'auth_user_name': user , 'auth_password': password },
        dataType: 'html',
        success: function(fromPHP) {
            if(fromPHP=="WRONG"){
                document.getElementById("modal-info-header").setAttribute("style","background-color: #520007;");
                document.getElementById("modal-info-title").innerText = "Ошибка";
                document.getElementById("modal-info-body-message").innerText = "Пароль не верный!";
                modalClearInput('modal-sign-in-password');
                modalClearInput('modal-pass-change-pwd1');
                modalClearInput('modal-pass-change-pwd2');
                $("#modal-info").modal('show');
            } else {
                document.location.href = current_page;
            }
        }
    });

    return;
}

function authSignOut(){

    $.ajax({
        url: '/webTermometry/scripts/auth.php',
        type: 'POST',
        cache: false,
        data: { 'auth_sign_out': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            if(current_page === "silo_config.php"){
                document.location.href = "index.php";
            } else {
                document.location.href = current_page;
            }
        }
    });

    return;
}

function authPasswordChange(user, inputPassID1, inputPassID2){

    pwd1 = document.getElementById(inputPassID1).value;
    pwd2 = document.getElementById(inputPassID2).value;

    if(pwd1!==pwd2){
        document.getElementById("modal-info-header").setAttribute("style","background-color: #520007;");
        document.getElementById("modal-info-title").innerText = "Ошибка";
        document.getElementById("modal-info-body-message").innerText = "Введенные пароли не совпадают!";
        modalClearInput('modal-sign-in-password');
        modalClearInput('modal-pass-change-pwd1');
        modalClearInput('modal-pass-change-pwd2');
        $("#modal-info").modal('show');
    } else {
        $.ajax({
            url: '/webTermometry/scripts/auth.php',
            type: 'POST',
            cache: false,
            data: { 'auth_pwd_change_user_name': user , 'auth_pwd_change_password': pwd1 },
            dataType: 'html',
            success: function(fromPHP) {
                document.getElementById("modal-info-header").setAttribute("style","background-color: #4046ff;");
                document.getElementById("modal-info-title").innerText = "";
                document.getElementById("modal-info-body-message").innerText = "Пароль успешно изменен!";
                $("#modal-info").modal('show');
            }
        });
    }

    return;
}
