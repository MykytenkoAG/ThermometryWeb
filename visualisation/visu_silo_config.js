//  TODO
/*
    При наличии активных АПС обе таблицы должны блокироваться
    
    Добавить обработку кнопки "Сменить пароль"

    Добавить обработку кнопки "Скачать протокол работы программы"

    Добавить обработку кнопки "Скачать протокол работы АПС"

    Добавить возможность очищать БД

    Добавить возможность сохранять БД в файл

    Добавить возможность восстанавливать БД из резервной копии

*/

let arrayOfLevels = [];
let tbl_prodtypes_changed;
let tbl_prodtypesbysilo_changed;

let POST_vSConf_prodtypes_changes_queue = [];
let POST_vSConf_prodtypesbysilo_update_list = [];

function init_silo_config() {
    document.getElementById("hdr-href-silo_config.php").setAttribute("class", "nav-link text-primary");
    getArrayOfLevels();
    tbl_prodtypes_changed = 0;
    tbl_prodtypesbysilo_changed = 0;
    POST_vSConf_prodtypes_changes_queue.length = 0;
    POST_vSConf_prodtypesbysilo_update_list.length = 0;
}
//  Получение массива с текущими уровнями для установки уровня в автоматическом режиме для таблицы "Загрузка силосов"
function getArrayOfLevels() {
    $.ajax({
        url: '/webTermometry/scripts/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_array_of_levels': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            arrayOfLevels = (JSON.parse(fromPHP));
            console.log(arrayOfLevels);
        }
    });
    return;
}
//  отключение всех входов для таблицы с заданным id
function tableInputsDisable(table_id) {
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
function buttonDisable(button_id) {
    document.getElementById(button_id).disabled = true;
    return;
}
//  Включение кнопки с заданным id
function buttonEnable(button_id) {
    document.getElementById(button_id).disabled = false;
    return;
}
//  Отрисовка таблицы "Типы продукта"
function redrawTableProdtypes() {
    $.ajax({
        url: 'visualisation/visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vSConf_draw_Prodtypes': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("table-product-types").innerHTML = fromPHP;
            POST_vSConf_prodtypes_changes_queue.length = 0;
            tbl_prodtypes_changed = 0;
            buttonDisable("table-prodtypes-btn-save-changes");
            buttonDisable("table-prodtypes-btn-discard-changes");
        }
    });
    return;
}
//  Отрисовка таблицы "Загрузка силосов"
function redrawTableProdtypesbysilo() {
    $.ajax({
        url: 'visualisation/visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vSConf_draw_Prodtypesbysilo': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("table-product-types-by-silo").innerHTML = fromPHP;
            POST_vSConf_prodtypesbysilo_update_list.length = 0;
            tbl_prodtypesbysilo_changed = 0;
            buttonDisable("table-prodtypesbysilo-btn-save-changes");
            buttonDisable("table-prodtypesbysilo-btn-discard-changes");
        }
    });
    return;
}
//  Сохранение изменений
function silo_config_save_changes() {

    if (tbl_prodtypes_changed == 1) {
        $.ajax({
            url: 'visualisation/visu_silo_config.php',
            type: 'POST',
            cache: false,
            data: { 'POST_vSConf_prodtypes_changes_queue': POST_vSConf_prodtypes_changes_queue },
            dataType: 'html',
            success: function(fromPHP) {
                redrawTableProdtypes();
                redrawTableProdtypesbysilo();
                document.getElementById("modal-info-body-message").innerText = "Изменения успешно внесены в Базу Данных";
                $("#modal-info").modal('show');
            }
        });
    }

    if (tbl_prodtypesbysilo_changed == 1) {
        $.ajax({
            url: 'visualisation/visu_silo_config.php',
            type: 'POST',
            cache: false,
            data: { 'POST_vSConf_prodtypesbysilo_update_list': POST_vSConf_prodtypesbysilo_update_list },
            dataType: 'html',
            success: function(fromPHP) {
                redrawTableProdtypes();
                redrawTableProdtypesbysilo();
                document.getElementById("modal-info-body-message").innerText = "Изменения успешно внесены в Базу Данных";
                $("#modal-info").modal('show');
            }
        });
    }

    return;
}
//  Возвращение значений к текущему состоянию
function silo_config_discard_changes() {

    redrawTableProdtypes();
    redrawTableProdtypesbysilo();
    if (curr_user == "tehn") {
        buttonEnable("table-prodtypes-btn-add");
    }

}
//  Таблица "Типы продукта". Кнопка "Добавить"
$("#table-prodtypes-btn-add").click(function() {
    tblProdtypesAddRow();
});
//  Таблица "Типы продукта". Кнопка "Сохранить изменения"
$("#table-prodtypes-btn-save-changes").click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Сохранить изменения?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "silo_config_save_changes()");
    $("#modal-are-you-sure").modal('show');
});
//  Таблица "Типы продукта". Кнопка "Отменить изменения"
$("#table-prodtypes-btn-discard-changes").click(function() {
    silo_config_discard_changes();
});
//  Таблица "Загрузка силосов". Кнопка "Сохранить изменения"
$("#table-prodtypesbysilo-btn-save-changes").click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Сохранить изменения?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "silo_config_save_changes()");
    $("#modal-are-you-sure").modal('show');
});
//  Таблица "Загрузка силосов". Кнопка "Отменить изменения"
$("#table-prodtypesbysilo-btn-discard-changes").click(function() {
    silo_config_discard_changes();
});

//  Таблица "Типы продукта"
//  Удалить строку
function tblProdtypesRemoveRow(product_id) {

    tbl_prodtypes_changed = 1;

    tableInputsDisable("table-prodtypesbysilo");

    buttonEnable("table-prodtypes-btn-save-changes");
    buttonEnable("table-prodtypes-btn-discard-changes");

    document.getElementById("prodtypes-remove-btn-" + product_id).parentElement.parentElement.remove();

    POST_vSConf_prodtypes_changes_queue.push({
        remove_row: { product_id: product_id }
    });

    return;
}
//  Обновить показания
function tblProdtypesUpdateRow(tbl_prodtypes_row_id) {

    tbl_prodtypes_changed = 1;

    tableInputsDisable("table-prodtypesbysilo");

    if (checkProductNames()) {
        buttonEnable("table-prodtypes-btn-save-changes");
    }
    buttonEnable("table-prodtypes-btn-discard-changes");

    POST_vSConf_prodtypes_changes_queue.push({
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
function tblProdtypesAddRow() {

    tbl_prodtypes_changed = 1;

    //  Отключаем другую таблицу
    tableInputsDisable("table-prodtypesbysilo");
    //  Включаем кнопки "сохранить" и "отменить изменения"
    if (checkProductNames()) {
        buttonEnable("table-prodtypes-btn-save-changes");
    }
    buttonEnable("table-prodtypes-btn-discard-changes");

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
    input_product_name.setAttribute("onchange", "tblProdtypesUpdateRow(" + new_id + ")");
    input_product_name.setAttribute("oninput", "checkProductNames()");
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
    input_t_min.setAttribute("onchange", "tblProdtypesUpdateRow(" + new_id + ")");
    input_t_min.setAttribute("class", "form-control mx-auto");
    input_t_min.setAttribute("style", "width: 80px;");
    input_t_min.value = 20.0;

    //  t_max
    var td3 = document.createElement("td");
    var input_t_max = document.createElement("input");

    input_t_max.setAttribute("type", "number");
    input_t_max.setAttribute("id", "prodtypes-t-max-" + new_id);
    input_t_max.setAttribute("onchange", "tblProdtypesUpdateRow(" + new_id + ")");
    input_t_max.setAttribute("class", "form-control mx-auto");
    input_t_max.setAttribute("style", "width: 80px;");
    input_t_max.value = 30.0;

    //  v_min
    var td4 = document.createElement("td");
    var input_v_min = document.createElement("input");

    input_v_min.setAttribute("type", "number");
    input_v_min.setAttribute("id", "prodtypes-v-min-" + new_id);
    input_v_min.setAttribute("onchange", "tblProdtypesUpdateRow(" + new_id + ")");
    input_v_min.setAttribute("class", "form-control mx-auto");
    input_v_min.setAttribute("style", "width: 60px;");
    input_v_min.value = 0.0;

    //  v_max
    var td5 = document.createElement("td");
    var input_v_max = document.createElement("input");

    input_v_max.setAttribute("type", "number");
    input_v_max.setAttribute("id", "prodtypes-v-max-" + new_id);
    input_v_max.setAttribute("onchange", "tblProdtypesUpdateRow(" + new_id + ")");
    input_v_max.setAttribute("class", "form-control mx-auto");
    input_v_max.setAttribute("style", "width: 60px;");
    input_v_max.value = 3.0;

    //  remove button
    var td6 = document.createElement("td");
    var button_remove = document.createElement("button");

    button_remove.setAttribute("type", "submit");
    button_remove.setAttribute("class", "btn btn-danger mx-auto");
    button_remove.setAttribute("id", "prodtypes-remove-btn-" + new_id);
    button_remove.setAttribute("onclick", "tblProdtypesRemoveRow(" + new_id + ")");

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
    POST_vSConf_prodtypes_changes_queue.push({
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
function checkProductNames() {

    tbl_prodtypes_changed = 1;
    tableInputsDisable("table-prodtypesbysilo");

    let inputs = document.getElementById("table-prodtypes").getElementsByClassName("productname");
    let checkOK = true;

    for (let i = 0; i < inputs.length; i++) {
        inputs[i].setAttribute("style", "width: 300px;");
    }
    buttonEnable("table-prodtypes-btn-save-changes");
    buttonEnable("table-prodtypes-btn-discard-changes");
    for (let i = 0; i < inputs.length; i++) {
        if (!isProductNameValid(inputs[i].value) || inputs[i].value==="") {
            checkOK = false;
            inputs[i].setAttribute("style", "width: 300px; color:red");
            buttonDisable("table-prodtypes-btn-save-changes");
        }
        for (let j = i + 1; j < inputs.length; j++) {
            if (inputs[i].value == inputs[j].value) {
                checkOK = false;
                inputs[i].setAttribute("style", "width: 300px; color:red");
                inputs[j].setAttribute("style", "width: 300px; color:red");
                buttonDisable("table-prodtypes-btn-save-changes");
            }
        }
    }

    return checkOK;
}
//  Проверка имени на запрещенные символы
function isProductNameValid(value) {
    var pattern = new RegExp(/[~`!#$\^&*+=\\[\]\\';/{}|\\":<>\?]/); //unacceptable chars
    if (pattern.test(value)) {
        return false;
    }
    return true; //good input
}

//  Таблица "Загрузка силосов"
function tblProdtypesbysiloUpdate() {

    tbl_prodtypesbysilo_changed = 1;

    tableInputsDisable("table-prodtypes");
    buttonDisable("table-prodtypes-btn-add");
    buttonDisable("table-prodtypes-btn-save-changes");
    buttonDisable("table-prodtypes-btn-discard-changes");

    let selects = document.getElementById("table-prodtypesbysilo").getElementsByTagName("select");

    POST_vSConf_prodtypesbysilo_update_list.length = 0;

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
        POST_vSConf_prodtypesbysilo_update_list.push({
            silo_id: silo_id,
            grain_level_from_TS: grain_level_from_TS,
            grain_level: grain_level,
            product_id: product_id
        });
    }

    buttonEnable("table-prodtypesbysilo-btn-save-changes");
    buttonEnable("table-prodtypesbysilo-btn-discard-changes");

    return;
}

//  Опции
//  Изменить пароль
$("#silo-config-btn-change-password").click(function() {
    document.getElementById("modal-sign-in-btn-close").setAttribute("onclick", "modalClearInput('modal-pass-change-pwd1');modalClearInput('modal-pass-change-pwd2')");
    document.getElementById("modal-sign-in-btn-cancel").setAttribute("onclick", "modalClearInput('modal-pass-change-pwd1');modalClearInput('modal-pass-change-pwd2')");
    document.getElementById("modal-pass-change-btn-ok").setAttribute("onclick", "authPasswordChange('" + curr_user + "', 'modal-pass-change-pwd1', 'modal-pass-change-pwd2')");
    $("#modal-pass-change").modal('show');
});