function authGetCurrentUser(){
    $.ajax({
        url: '/webTermometry/scripts/auth.php',
        type: 'POST',
        cache: false,
        data: { 'get_current_user': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            curr_user = fromPHP;
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
