let intent_page; //  вспомогательная переменная на тот случай, если пользователь хочет перейти на какую-либо
//  страницу, не сохранив изменения на странице настроек
$("a").hover(
    function() {
        if (($(this).attr('id').split("-").pop() === current_page) ||                       //  изменять подсветку текущей страницы нельзя
            ($(this).attr('id').split("-").pop() === curr_user) ||                          //  текущего пользователя тоже
            (($(this).attr('id').split("-").pop() === "ack") && alarmsNACKNumber > 0)) {    //  и кнопку квитирования при наличии неквитированных сигналов АПС
            return;
        }
        $(this).removeClass("text-black");
        $(this).addClass("text-primary");
    },
    function() {
        if (($(this).attr('id').split("-").pop() === current_page) ||
            ($(this).attr('id').split("-").pop() === curr_user) ||
            (($(this).attr('id').split("-").pop() === "ack") && alarmsNACKNumber > 0)) {
            return;
        }
        $(this).removeClass("text-primary");
        $(this).addClass("text-black");
    }
);

$("a").click(
    function() {
        const lastIDSymbols = $(this).attr('id').split("-").pop();
        if (lastIDSymbols.substr(-3) === "php") {
            intent_page = lastIDSymbols;
            if (intent_page === "silo_config.php") {
                if (!["oper", "tehn"].includes(curr_user)) {
                    document.getElementById("modal-info-header").setAttribute("style", "background-color: #520007;");
                    document.getElementById("modal-info-body-message").innerText = "У Вас нет доступа к данной странице. Авторизуйтесь как оператор или технолог.";
                    $("#modal-info").modal('show');
                } else {
                    document.location.href = intent_page;
                }
            } else if (current_page == "silo_config.php") {
                if (tbl_prodtypes_changed || tbl_prodtypesbysilo_changed) {
                    const table_changed_name = tbl_prodtypes_changed == 1 ? "\"Типы продукта\"" : "\"Загрузка силосов\"";
                    const alert_message = "У Вас есть несохраненные изменения в таблице " + table_changed_name + ". Вы уверены, что хотите покинуть данную страницу?";
                    document.getElementById("modal-silo-config-page-change-message").innerText = alert_message;
                    document.getElementById("modal-silo-config-page-change-btn-ok").setAttribute("onclick", "tbl_prodtypes_changed=0;tbl_prodtypesbysilo_changed=0;document.location.href=intent_page;");
                    $("#modal-silo-config-page-change").modal('show');
                } else {
                    document.location.href = lastIDSymbols;
                }
            } else {
                document.location.href = lastIDSymbols;
            }
        } else if (lastIDSymbols.substr(-4) === "oper") {
            if (curr_user !== "oper") {
                document.getElementById("modal-sign-in-btn-close").setAttribute("onclick", "modalPasswordInputClear('modal-sign-in-password')");
                document.getElementById("modal-sign-in-btn-cancel").setAttribute("onclick", "modalPasswordInputClear('modal-sign-in-password')");
                document.getElementById("modal-sign-in-user-name").innerText = "Оператор";
                document.getElementById("modal-sign-in-btn-ok").setAttribute("onclick", "authSignIn('oper', 'modal-sign-in-password')");
                $("#modal-sign-in").modal('show');
            }
        } else if (lastIDSymbols.substr(-4) === "tehn") {
            if (curr_user !== "tehn") {
                document.getElementById("modal-sign-in-btn-close").setAttribute("onclick", "modalPasswordInputClear('modal-sign-in-password')");
                document.getElementById("modal-sign-in-btn-cancel").setAttribute("onclick", "modalPasswordInputClear('modal-sign-in-password')");
                document.getElementById("modal-sign-in-user-name").innerText = "Технолог";
                document.getElementById("modal-sign-in-btn-ok").setAttribute("onclick", "authSignIn('tehn', 'modal-sign-in-password')");
                $("#modal-sign-in").modal('show');
            }
        } else if (lastIDSymbols === "ack") {
            alarmsAck();
        } else if (lastIDSymbols === "out") {
            authSignOut();
        }

    }
)

//  Выбор языка приложения
$('#hdr-lng-select-ru').click(
    function() {
        document.cookie = "application_language=RU;";
        document.location.href = current_page;
    }
)
$('#hdr-lng-select-en').click(
    function() {
        document.cookie = "application_language=EN;";
        document.location.href = current_page;
    }
)
$('#hdr-lng-select-ua').click(
    function() {
        document.cookie = "application_language=UA;";
        document.location.href = current_page;
    }
)
