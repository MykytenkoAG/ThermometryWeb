//  TODO
/*
    При наличии активных АПС обе таблицы должны блокироваться

    Добавить возможность загружать файлы TermoClient.ini и TermoServer.ini

*/

let arrayOfLevels = [];
let tbl_prodtypes_changed;
let tbl_prodtypesbysilo_changed;

let SConf_prodtypes_changes_queue = [];
let SConf_prodtypesbysilo_update_list = [];

function init_silo_config() {
    document.getElementById("hdr-href-silo_config.php").setAttribute("class", "nav-link text-primary");
    vSConf_getArrayOfLevels();
    tbl_prodtypes_changed = 0;
    tbl_prodtypesbysilo_changed = 0;
    SConf_prodtypes_changes_queue.length = 0;
    SConf_prodtypesbysilo_update_list.length = 0;

    const db_successfully_restored = getCookie("dbRestoredSuccessfully");
    if (db_successfully_restored === "OK") {
        document.getElementById("modal-info-body-message").innerText = "База данных успешно восстановлена";
        $("#modal-info").modal('show');
        document.cookie = 'dbRestoredSuccessfully=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }

}

//  Получение массива с текущими уровнями для установки уровня в автоматическом режиме для таблицы "Загрузка силосов"
function vSConf_getArrayOfLevels() {
    $.ajax({
        url: '/webTermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_array_of_levels': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            arrayOfLevels = (JSON.parse(fromPHP));
        }
    });
    return;
}
//  отключение всех входов для таблицы с заданным id
function vSConf_tableInputsDisable(table_id) {
    let inputs = document.getElementById(table_id).getElementsByTagName('input');
    let buttons = document.getElementById(table_id).getElementsByTagName('button');
    let selects = document.getElementById(table_id).getElementsByTagName('select');
    for (let i = 0; i < inputs.length; i++) {
        inputs.item(i).disabled = true;
    }
    for (let i = 0; i < buttons.length; i++) {
        buttons.item(i).disabled = true;
    }
    for (let i = 0; i < selects.length; i++) {
        selects.item(i).disabled = true;
    }

    return;
}
//  Отключение кнопки с заданным id
function vSConf_buttonDisable(button_id) {
    document.getElementById(button_id).disabled = true;
    return;
}
//  Включение кнопки с заданным id
function vSConf_buttonEnable(button_id) {
    document.getElementById(button_id).disabled = false;
    return;
}
//  Отрисовка таблицы "Типы продукта"
function vSConf_redrawTableProdtypes() {
    $.ajax({
        url: 'visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vSConf_draw_Prodtypes': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("table-product-types").innerHTML = fromPHP;
            SConf_prodtypes_changes_queue.length = 0;
            tbl_prodtypes_changed = 0;
            vSConf_buttonDisable("sconf-table-prodtypes-btn-save-changes");
            vSConf_buttonDisable("sconf-table-prodtypes-btn-discard-changes");
        }
    });
    return;
}
//  Отрисовка таблицы "Загрузка силосов"
function vSConf_redrawTableProdtypesbysilo() {
    $.ajax({
        url: 'visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vSConf_draw_Prodtypesbysilo': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("table-product-types-by-silo").innerHTML = fromPHP;
            SConf_prodtypesbysilo_update_list.length = 0;
            tbl_prodtypesbysilo_changed = 0;
            vSConf_buttonDisable("sconf-table-prodtypesbysilo-btn-save-changes");
            vSConf_buttonDisable("sconf-table-prodtypesbysilo-btn-discard-changes");
        }
    });
    return;
}
//  Сохранение изменений
function vSConf_siloConfigSaveChanges() {

    if (tbl_prodtypes_changed == 1) {
        $.ajax({
            url: 'visu_silo_config.php',
            type: 'POST',
            cache: false,
            data: { 'POST_vSConf_prodtypes_changes_queue': SConf_prodtypes_changes_queue },
            dataType: 'html',
            success: function(fromPHP) {
                vSConf_redrawTableProdtypes();
                vSConf_redrawTableProdtypesbysilo();
                document.getElementById("modal-info-body-message").innerText = "Изменения успешно внесены в Базу Данных";
                $("#modal-info").modal('show');
            }
        });
    }

    if (tbl_prodtypesbysilo_changed == 1) {
        $.ajax({
            url: 'visu_silo_config.php',
            type: 'POST',
            cache: false,
            data: { 'POST_vSConf_prodtypesbysilo_update_list': SConf_prodtypesbysilo_update_list },
            dataType: 'html',
            success: function(fromPHP) {
                vSConf_redrawTableProdtypes();
                vSConf_redrawTableProdtypesbysilo();
                document.getElementById("modal-info-body-message").innerText = "Изменения успешно внесены в Базу Данных";
                $("#modal-info").modal('show');
            }
        });
    }

    return;
}
//  Возвращение значений к текущему состоянию
function vSConf_siloConfigDiscardChanges() {

    vSConf_redrawTableProdtypes();
    vSConf_redrawTableProdtypesbysilo();
    if (curr_user == "tehn") {
        vSConf_buttonEnable("sconf-table-prodtypes-btn-add");
    }

}
//  Таблица "Типы продукта". Кнопка "Добавить"
$("#sconf-table-prodtypes-btn-add").click(function() {
    vSConf_tblProdtypesAddRow();
});
//  Таблица "Типы продукта". Кнопка "Сохранить изменения"
$("#sconf-table-prodtypes-btn-save-changes").click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Сохранить изменения?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "vSConf_siloConfigSaveChanges()");
    $("#modal-are-you-sure").modal('show');
});
//  Таблица "Типы продукта". Кнопка "Отменить изменения"
$("#sconf-table-prodtypes-btn-discard-changes").click(function() {
    vSConf_siloConfigDiscardChanges();
});
//  Таблица "Загрузка силосов". Кнопка "Сохранить изменения"
$("#sconf-table-prodtypesbysilo-btn-save-changes").click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Сохранить изменения?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "vSConf_siloConfigSaveChanges()");
    $("#modal-are-you-sure").modal('show');
});
//  Таблица "Загрузка силосов". Кнопка "Отменить изменения"
$("#sconf-table-prodtypesbysilo-btn-discard-changes").click(function() {
    vSConf_siloConfigDiscardChanges();
});

//  Таблица "Типы продукта"
//  Удалить строку
function vSConf_tblProdtypesRemoveRow(product_id) {

    tbl_prodtypes_changed = 1;

    vSConf_tableInputsDisable("table-prodtypesbysilo");

    vSConf_buttonEnable("sconf-table-prodtypes-btn-save-changes");
    vSConf_buttonEnable("sconf-table-prodtypes-btn-discard-changes");

    document.getElementById("prodtypes-remove-btn-" + product_id).parentElement.parentElement.remove();

    SConf_prodtypes_changes_queue.push({
        remove_row: { product_id: product_id }
    });

    return;
}
//  Обновить показания
function vSConf_tblProdtypesUpdateRow(tbl_prodtypes_row_id) {

    tbl_prodtypes_changed = 1;

    vSConf_tableInputsDisable("table-prodtypesbysilo");

    if (vSConf_checkProductNames()) {
        vSConf_buttonEnable("sconf-table-prodtypes-btn-save-changes");
    }
    vSConf_buttonEnable("sconf-table-prodtypes-btn-discard-changes");

    SConf_prodtypes_changes_queue.push({
        update_row: {
            product_id: tbl_prodtypes_row_id,
            product_name: document.getElementById("prodtypes-product-name-" + tbl_prodtypes_row_id).value,
            t_min: document.getElementById("prodtypes-t-min-" + tbl_prodtypes_row_id).value,
            t_max: document.getElementById("prodtypes-t-max-" + tbl_prodtypes_row_id).value,
            v_min: document.getElementById("prodtypes-v-min-" + tbl_prodtypes_row_id).value,
            v_max: document.getElementById("prodtypes-v-max-" + tbl_prodtypes_row_id).value
        }
    });

    return;
}
//  Добавить строку
function vSConf_tblProdtypesAddRow() {

    tbl_prodtypes_changed = 1;

    //  Отключаем другую таблицу
    vSConf_tableInputsDisable("table-prodtypesbysilo");
    //  Включаем кнопки "сохранить" и "отменить изменения"
    if (vSConf_checkProductNames()) {
        vSConf_buttonEnable("sconf-table-prodtypes-btn-save-changes");
    }
    vSConf_buttonEnable("sconf-table-prodtypes-btn-discard-changes");

    //  Создаем новую строку, отображаем ее на странице
    //  Находим новый id
    const new_id = +document.getElementById("table-prodtypes")
        .getElementsByTagName("button")[document.getElementById("table-prodtypes").getElementsByTagName("button").length - 1].id.split("-").pop() + 1;

    var tbody = document.getElementById("table-prodtypes").getElementsByTagName("tbody")[0];

    //  создаем новую строку
    var table_row = document.createElement("tr");

    //  создаем столбцы
    //  product_name
    var td1 = document.createElement("td");
    var input_product_name = document.createElement("input");

    input_product_name.setAttribute("type", "text");
    input_product_name.setAttribute("id", "prodtypes-product-name-" + new_id);
    input_product_name.setAttribute("onchange", "vSConf_tblProdtypesUpdateRow(" + new_id + ")");
    input_product_name.setAttribute("oninput", "vSConf_checkProductNames()");
    input_product_name.setAttribute("class", "form-control mx-auto productname");
    input_product_name.setAttribute("style", "width: 300px;");

    let k = 1;
    let new_name = "Новый продукт " + k;
    let currProductNames = document.getElementById("table-prodtypes").getElementsByClassName("productname");
    let i = 0;
    while (i < currProductNames.length) {
        if (new_name == currProductNames[i].value) {
            k++;
            new_name = "Новый продукт " + k;
            i = 0;
            continue;
        }
        i++;
    }
    input_product_name.value = new_name;

    //  t_min
    var td2 = document.createElement("td");
    var input_t_min = document.createElement("input");

    input_t_min.setAttribute("type", "number");
    input_t_min.setAttribute("id", "prodtypes-t-min-" + new_id);
    input_t_min.setAttribute("onchange", "vSConf_tblProdtypesUpdateRow(" + new_id + ")");
    input_t_min.setAttribute("class", "form-control mx-auto");
    input_t_min.setAttribute("style", "width: 80px;");
    input_t_min.value = 20.0;

    //  t_max
    var td3 = document.createElement("td");
    var input_t_max = document.createElement("input");

    input_t_max.setAttribute("type", "number");
    input_t_max.setAttribute("id", "prodtypes-t-max-" + new_id);
    input_t_max.setAttribute("onchange", "vSConf_tblProdtypesUpdateRow(" + new_id + ")");
    input_t_max.setAttribute("class", "form-control mx-auto");
    input_t_max.setAttribute("style", "width: 80px;");
    input_t_max.value = 30.0;

    //  v_min
    var td4 = document.createElement("td");
    var input_v_min = document.createElement("input");

    input_v_min.setAttribute("type", "number");
    input_v_min.setAttribute("id", "prodtypes-v-min-" + new_id);
    input_v_min.setAttribute("onchange", "vSConf_tblProdtypesUpdateRow(" + new_id + ")");
    input_v_min.setAttribute("class", "form-control mx-auto");
    input_v_min.setAttribute("style", "width: 60px;");
    input_v_min.value = 0.0;

    //  v_max
    var td5 = document.createElement("td");
    var input_v_max = document.createElement("input");

    input_v_max.setAttribute("type", "number");
    input_v_max.setAttribute("id", "prodtypes-v-max-" + new_id);
    input_v_max.setAttribute("onchange", "vSConf_tblProdtypesUpdateRow(" + new_id + ")");
    input_v_max.setAttribute("class", "form-control mx-auto");
    input_v_max.setAttribute("style", "width: 60px;");
    input_v_max.value = 3.0;

    //  remove button
    var td6 = document.createElement("td");
    var button_remove = document.createElement("button");

    button_remove.setAttribute("type", "submit");
    button_remove.setAttribute("class", "btn btn-danger mx-auto");
    button_remove.setAttribute("id", "prodtypes-remove-btn-" + new_id);
    button_remove.setAttribute("onclick", "vSConf_tblProdtypesRemoveRow(" + new_id + ")");

    var button_remove_img = document.createElement("img");

    button_remove_img.setAttribute("src", "img/icon-remove.png");
    button_remove_img.setAttribute("width", "20");
    button_remove_img.setAttribute("height", "20");

    button_remove.appendChild(button_remove_img);

    td1.appendChild(input_product_name);
    td2.appendChild(input_t_min);
    td3.appendChild(input_t_max);
    td4.appendChild(input_v_min);
    td5.appendChild(input_v_max);
    td6.appendChild(button_remove);

    table_row.appendChild(td1);
    table_row.appendChild(td2);
    table_row.appendChild(td3);
    table_row.appendChild(td4);
    table_row.appendChild(td5);
    table_row.appendChild(td6);

    tbody.appendChild(table_row);

    //  Добавляем действие по добавлению в очередь
    SConf_prodtypes_changes_queue.push({
        insert_row: {
            product_id: new_id,
            product_name: document.getElementById("prodtypes-product-name-" + new_id).value,
            t_min: document.getElementById("prodtypes-t-min-" + new_id).value,
            t_max: document.getElementById("prodtypes-t-max-" + new_id).value,
            v_min: document.getElementById("prodtypes-v-min-" + new_id).value,
            v_max: document.getElementById("prodtypes-v-max-" + new_id).value
        }
    });

    return;
}
//  Валидация форм --------------------------------------------------------------------------------------------------------------------------------------------
//  Проверка двух и более строк с одинаковыми названиями продукта
function vSConf_checkProductNames() {

    tbl_prodtypes_changed = 1;
    vSConf_tableInputsDisable("table-prodtypesbysilo");

    let inputs = document.getElementById("table-prodtypes").getElementsByClassName("productname");
    let checkOK = true;

    for (let i = 0; i < inputs.length; i++) {
        inputs[i].setAttribute("style", "width: 300px;");
    }
    vSConf_buttonEnable("sconf-table-prodtypes-btn-save-changes");
    vSConf_buttonEnable("sconf-table-prodtypes-btn-discard-changes");
    for (let i = 0; i < inputs.length; i++) {
        if (!vSConf_isProductNameValid(inputs[i].value) || inputs[i].value==="") {
            checkOK = false;
            inputs[i].setAttribute("style", "width: 300px; color:red");
            vSConf_buttonDisable("sconf-table-prodtypes-btn-save-changes");
        }
        for (let j = i + 1; j < inputs.length; j++) {
            if (inputs[i].value == inputs[j].value) {
                checkOK = false;
                inputs[i].setAttribute("style", "width: 300px; color:red");
                inputs[j].setAttribute("style", "width: 300px; color:red");
                vSConf_buttonDisable("sconf-table-prodtypes-btn-save-changes");
            }
        }
    }

    return checkOK;
}
//  Проверка имени на запрещенные символы
function vSConf_isProductNameValid(value) {
    var pattern = new RegExp(/[~`!#$\^&*+=\\[\]\\';/{}|\\":<>\?]/); //unacceptable chars
    if (pattern.test(value)) {
        return false;
    }
    return true; //good input
}

//  Таблица "Загрузка силосов"
function vSConf_tblProdtypesbysiloUpdate() {

    tbl_prodtypesbysilo_changed = 1;

    vSConf_tableInputsDisable("table-prodtypes");
    vSConf_buttonDisable("sconf-table-prodtypes-btn-add");
    vSConf_buttonDisable("sconf-table-prodtypes-btn-save-changes");
    vSConf_buttonDisable("sconf-table-prodtypes-btn-discard-changes");

    let selects = document.getElementById("table-prodtypesbysilo").getElementsByTagName("select");

    SConf_prodtypesbysilo_update_list.length = 0;

    let i = 0;
    while (i < selects.length) {
        const silo_id = selects[i].id.split('-').pop();
        const grain_level_from_TS = selects[i].value == "auto" ? 1 : 0;
        i++;
        if (grain_level_from_TS == 1) {
            selects[i].value = arrayOfLevels[Math.floor(i / 3)];
        }
        selects[i].disabled = grain_level_from_TS;
        const grain_level = selects[i].value;
        i++;
        const product_id = selects[i].value;
        i++;
        SConf_prodtypesbysilo_update_list.push({
            silo_id: silo_id,
            grain_level_from_TS: grain_level_from_TS,
            grain_level: grain_level,
            product_id: product_id
        });
    }

    vSConf_buttonEnable("sconf-table-prodtypesbysilo-btn-save-changes");
    vSConf_buttonEnable("sconf-table-prodtypesbysilo-btn-discard-changes");

    return;
}

//  Опции ---------------------------------------------------------------------------------------------------------------
//  Настройка параметров подключения к Термосервер
//  Создать модальное окно с двумя полями ввода: IP и port
$("#sconf-ts-connection-settings").click(function() {
    $.ajax({
        url: 'visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vSConf_get_ts_connection_settings': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("modal-ts-connection-settings-ip").value = JSON.parse(fromPHP)[0];
            document.getElementById("modal-ts-connection-settings-port").value = JSON.parse(fromPHP)[1];
            vSConf_checkIP();
            $("#modal-ts-connection-settings").modal('show');
        }
    });
});

//  Валидация ip-адреса
function vSConf_checkIP() {

    const value = document.getElementById("modal-ts-connection-settings-ip").value;

    var pattern = new RegExp(/((^\s*((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))\s*$)|(^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$))/);
    if (pattern.test(value)) {
        document.getElementById("modal-ts-connection-settings-ip").setAttribute("style", "color:black");
        document.getElementById("modal-ts-connection-settings-btn-ok").disabled = false;
        return;
    }
    document.getElementById("modal-ts-connection-settings-ip").setAttribute("style", "color:red");
    document.getElementById("modal-ts-connection-settings-btn-ok").disabled = true;
    return;

}

//  Сохранение настроек подключения к ПО Термосервер
function vSConf_ts_connection_settings_Save(){

    const ts_ip = document.getElementById("modal-ts-connection-settings-ip").value;
    const ts_port = document.getElementById("modal-ts-connection-settings-port").value;
    
    $.ajax({
        url: 'visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vSConf_ts_connection_settings_save_ip': ts_ip,
                'POST_vSConf_ts_connection_settings_save_port': ts_port},
        dataType: 'html',
        success: function(fromPHP) {

            document.getElementById("modal-info-body-message").innerText = fromPHP;
            $("#modal-info").modal('show');

        }
    });

    return;
}

//  Операции с БД -------------------------------------------------------------------------------------------------------
$("#sconf-db-operations").click(function() {
    $("#modal-db-operations").modal('show');
});

//  Резервное копирование БД
//  Отправка AJAX запроса, который должен вернуть ссылку на файл
$("#sconf-db-create-backup").click(function() {
    $.ajax({
        url: 'visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vSConf_db_create_backup': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            window.location.href = fromPHP;
        }
    });
});

//  Очистить БД
//  Отправка AJAX-запроса с командой на очистку БД с измерениями
//  После успешного выполнения команды необходимо вызвать модальное окно с уведомлением об успешной очистке БД
$("#sconf-db-truncate-measurements").click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Очистить базу данных?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "vSConf_db_truncate_measurements()");
    $("#modal-are-you-sure").modal('show');
});

function vSConf_db_truncate_measurements(){
    $.ajax({
        url: 'visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vSConf_db_truncate_measurements': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("modal-info-body-message").innerText = fromPHP;
            $("#modal-info").modal('show');
        }
    });
}

//  Изменить пароль
$("#sconf-silo-config-btn-change-password").click(function() {
    document.getElementById("modal-sign-in-btn-close").setAttribute("onclick", "modalPasswordInputClear('modal-pass-change-pwd1');modalPasswordInputClear('modal-pass-change-pwd2')");
    document.getElementById("modal-sign-in-btn-cancel").setAttribute("onclick", "modalPasswordInputClear('modal-pass-change-pwd1');modalPasswordInputClear('modal-pass-change-pwd2')");
    document.getElementById("modal-pass-change-btn-ok").setAttribute("onclick", "authPasswordChange('" + curr_user + "', 'modal-pass-change-pwd1', 'modal-pass-change-pwd2')");
    $("#modal-pass-change").modal('show');
});
