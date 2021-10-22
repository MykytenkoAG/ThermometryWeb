let current_page;
const project_urls = ["debug_page.php", "index.php", "report.php", "silo_config.php"];
let curr_url_ind=1;

//  Изменение цвета ссылки при наведении курсора
$("a").hover(
    function() {
        if( current_page == ($(this).attr('id').split("-").pop()+".php")){
            return;
        }
        $(this).removeClass("text-black");
        $(this).addClass("text-primary");
    },
    function() {
        if( current_page == ($(this).attr('id').split("-").pop()+".php")){
            return;
        }
        $(this).removeClass("text-primary");
        $(this).addClass("text-black");
    }
);

$('#hdr-auth-oper').click(
    function(){
        $("#modal-auth-oper").modal('show');
    }
)

$('#hdr-auth-tehn').click(
    function(){
        $("#modal-auth-tehn").modal('show');
    }
)

function onPageChange(url_ind) {
    curr_url_ind = url_ind;
    if (current_page == "silo_config.php") {
        if (tbl_prodtypes_changed || tbl_prodtypesbysilo_changed) {
            const table_changed_name = tbl_prodtypes_changed == 1 ? "\"Типы продукта\"" : "\"Загрузка силосов\"";
            const alert_message = "У Вас есть несохраненные изменения в таблице " + table_changed_name + ". Вы уверены, что хотите покинуть данную страницу?";

            document.getElementById("silo-config-page-change-modal-message").innerText = alert_message;
            $("#silo-config-page-change-modal").modal('show');

        } else {
            document.location.href = project_urls[url_ind];
        }
    } else {
        document.location.href = project_urls[url_ind];
    }
}

function auth_oper(){

    const auth_user_name = "oper";
    const auth_password = document.getElementById("modal-pass-oper").value;

    $.ajax({
        url: '/webTermometry/scripts/auth.php',
        type: 'POST',
        cache: false,
        data: { 'auth_user_name': auth_user_name , 'auth_password': auth_password },
        dataType: 'html',
        success: function(fromPHP) {
            //alert(fromPHP);          
            if(fromPHP=="WRONG"){
                $("#modal-wrong-password").modal('show');
                
            } else {
                document.location.href = project_urls[curr_url_ind];
            }
            
        }
    });

    return;
}

function auth_tehn(){

    const auth_user_name = "tehn";
    const auth_password = document.getElementById("modal-pass-tehn").value;

    $.ajax({
        url: '/webTermometry/scripts/auth.php',
        type: 'POST',
        cache: false,
        data: { 'auth_user_name': auth_user_name , 'auth_password': auth_password },
        dataType: 'html',
        success: function(fromPHP) {  
            //alert(fromPHP);          
            if(fromPHP=="WRONG"){
                $("#modal-wrong-password").modal('show');
                
            } else {
                document.location.href = project_urls[curr_url_ind];
            }
            

        }
    });

    return;
}

function auth_sign_out(){

    $.ajax({
        url: '/webTermometry/scripts/auth.php',
        type: 'POST',
        cache: false,
        data: { 'auth_sign_out': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.location.href = project_urls[curr_url_ind];
        }
    });

    return;
}