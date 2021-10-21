//  Изменение цвета ссылки при наведении курсора
$("a").hover(
    function() {
        $(this).removeClass("text-black");
        $(this).addClass("text-primary");
    },
    function() {
        $(this).removeClass("text-primary");
        $(this).addClass("text-black");
    }
);

const project_urls = ["debug_page.php", "index.php", "report.php", "silo_config.php"];

let curr_url_ind;

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