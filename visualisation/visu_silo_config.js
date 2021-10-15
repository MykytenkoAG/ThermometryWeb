//  TODO
/*
    При наличии активных АПС обе таблицы должны блокироваться

    Добавить функции для отключения кнопок сохранения и отмены изменений

    Добавить контроль доступа
        Входить на страницу должен либо оператор, либо технолог
        Оператор имеет право только на изменение таблицы "Загрузка силосов"
        Технолог может вносить изменения в обе таблицы
    
    Добавить обработку кнопки "Сменить пароль"

    Добавить обработку кнопки "Скачать протокол работы программы"

    Добавить обработку кнопки "Скачать протокол работы АПС"

    Добавить возможность очищать БД

    Добавить возможность сохранять БД в файл

    Добавить возможность восстанавливать БД из резервной копии

*/

window.onload = function(){
    buttonDisable("table-prodtypes-btn-save-changes");
    buttonDisable("table-prodtypes-btn-discard-changes");
    buttonDisable("table-prodtypesbysilo-btn-save-changes");
    buttonDisable("table-prodtypesbysilo-btn-discard-changes");
}

function tableInputsDisable(table_id){
    let inputs = document.getElementById(table_id).getElementsByTagName('input');
    let buttons = document.getElementById(table_id).getElementsByTagName('button');
    let selects = document.getElementById(table_id).getElementsByTagName('select');
    for(let i=0; i<inputs.length; i++){
        inputs.item(i).disabled = true;
    }
    for(let i=0; i<buttons.length; i++){
        buttons.item(i).disabled = true;
    }
    for(let i=0; i<selects.length; i++){
        selects.item(i).disabled = true;
    }
    return;
}

function buttonDisable(button_id){
    document.getElementById(button_id).disabled = true;
    return;
}

function buttonEnable(button_id){
    document.getElementById(button_id).disabled = false;
    return;
}

function redrawTableProdtypes(){
    $.ajax({
        url: 'visualisation/visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'draw_table_prodtypes': 1 },
        dataType: 'html',
        success: function(fromPHP) { document.getElementById("table-product-types").innerHTML = fromPHP; }
    });
    return;
}

function redrawTableProdtypesbysilo(){
    $.ajax({
        url: 'visualisation/visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'draw_table_prodtypes_by_silo': 1 },
        dataType: 'html',
        success: function(fromPHP) { document.getElementById("table-product-types-by-silo").innerHTML = fromPHP; }
    });
    return;
}

//  Таблица "Типы продукта"
let tbl_prodtypes_changes_queue=[];

function onClickTblProdtypesRemoveRow(product_id){

    //  Отключаем другую таблицу
    tableInputsDisable("table-prodtypesbysilo");
    //  Включаем кнопки "сохранить" и "отменить изменения"
    buttonEnable("table-prodtypes-btn-save-changes");
    buttonEnable("table-prodtypes-btn-discard-changes");
    //  Удаляаем выбранную строку
    document.getElementById( "prodtypes-remove-btn-" + product_id ).parentElement.parentElement.remove();
    //  Заносим изменения в стек
    tbl_prodtypes_changes_queue.push(
        {remove_row:
            {product_id: product_id}
        }
    );
    
    return;
}

function onClickTblProdtypesUpdateRow(tbl_prodtypes_row_id){

    //  Отключаем другую таблицу
    tableInputsDisable("table-prodtypesbysilo");
    //  Включаем кнопки "сохранить" и "отменить изменения"
    buttonEnable("table-prodtypes-btn-save-changes");
    buttonEnable("table-prodtypes-btn-discard-changes");
    //  Заносим изменения в очередь
    tbl_prodtypes_changes_queue.push(
        {update_row:
            {
                product_id:     tbl_prodtypes_row_id,
                product_name:   document.getElementById("prodtypes-product-name-" + tbl_prodtypes_row_id).value,
                t_min:          document.getElementById("prodtypes-t-min-"        + tbl_prodtypes_row_id).value,
                t_max:          document.getElementById("prodtypes-t-max-"        + tbl_prodtypes_row_id).value,
                v_min:          document.getElementById("prodtypes-v-min-"        + tbl_prodtypes_row_id).value,
                v_max:          document.getElementById("prodtypes-v-max-"        + tbl_prodtypes_row_id).value
            }
        }
    );

    return;
}

function onClickTblProdtypesAddRow(){
    //  Отключаем другую таблицу
    tableInputsDisable("table-prodtypesbysilo");
    //  Включаем кнопки "сохранить" и "отменить изменения"
    buttonEnable("table-prodtypes-btn-save-changes");
    buttonEnable("table-prodtypes-btn-discard-changes");

    //  Создаем новую строку, отображаем ее на странице
    //  Находим новый id
    const new_id = +document.getElementById("table-prodtypes")
                    .getElementsByTagName("button")[document.getElementById("table-prodtypes").getElementsByTagName("button").length-1].id.split("-").pop() + 1;

    var tbody = document.getElementById("table-prodtypes").getElementsByTagName("tbody")[0];

    //  создаем новую строку
    var table_row = document.createElement("tr");

    //  создаем столбцы
    //  product_name
    var td1 = document.createElement("td");
    var input_product_name = document.createElement("input");

    input_product_name.setAttribute("type", "text");
    input_product_name.setAttribute("id", "prodtypes-product-name-" + new_id);
    input_product_name.setAttribute("onchange", "onClickTblProdtypesUpdateRow(" + new_id + ")");
    input_product_name.setAttribute("class","form-control mx-auto");
    input_product_name.setAttribute("style","width: 300px;");
    input_product_name.value = "Новый продукт";

    //  t_min
    var td2 = document.createElement("td");
    var input_t_min = document.createElement("input");

    input_t_min.setAttribute("type", "number");
    input_t_min.setAttribute("id", "prodtypes-t-min-" + new_id);
    input_t_min.setAttribute("onchange", "onClickTblProdtypesUpdateRow(" + new_id + ")");
    input_t_min.setAttribute("class","form-control mx-auto");
    input_t_min.setAttribute("style","width: 80px;");
    input_t_min.value = 20.0;

    //  t_max
    var td3 = document.createElement("td");
    var input_t_max = document.createElement("input");

    input_t_max.setAttribute("type", "number");
    input_t_max.setAttribute("id", "prodtypes-t-max-" + new_id);
    input_t_max.setAttribute("onchange", "onClickTblProdtypesUpdateRow(" + new_id + ")");
    input_t_max.setAttribute("class","form-control mx-auto");
    input_t_max.setAttribute("style","width: 80px;");
    input_t_max.value = 30.0;

    //  v_min
    var td4 = document.createElement("td");
    var input_v_min = document.createElement("input");

    input_v_min.setAttribute("type", "number");
    input_v_min.setAttribute("id", "prodtypes-v-min-" + new_id);
    input_v_min.setAttribute("onchange", "onClickTblProdtypesUpdateRow(" + new_id + ")");
    input_v_min.setAttribute("class","form-control mx-auto");
    input_v_min.setAttribute("style","width: 60px;");
    input_v_min.value = 0.0;

    //  v_max
    var td5 = document.createElement("td");
    var input_v_max = document.createElement("input");

    input_v_max.setAttribute("type", "number");
    input_v_max.setAttribute("id", "prodtypes-v-max-" + new_id);
    input_v_max.setAttribute("onchange", "onClickTblProdtypesUpdateRow(" + new_id + ")");
    input_v_max.setAttribute("class","form-control mx-auto");
    input_v_max.setAttribute("style","width: 60px;");
    input_v_max.value = 3.0;

    //  remove button
    var td6 = document.createElement("td");
    var button_remove = document.createElement("button");

    button_remove.setAttribute("type","submit");
    button_remove.setAttribute("class","btn btn-danger mx-auto");
    button_remove.setAttribute("id","prodtypes-remove-btn-" + new_id);
    button_remove.setAttribute("onclick","onClickTblProdtypesRemoveRow(" + new_id + ")");

    var button_remove_svg = document.querySelector("svg");
    button_remove_svg.setAttribute("xmlns","http://www.w3.org/2000/svg");
    button_remove_svg.setAttribute("width","16");
    button_remove_svg.setAttribute("height","16");
    button_remove_svg.setAttribute("fill","currentColor");
    button_remove_svg.setAttribute("class","bi bi-x-lg mx-auto");
    button_remove_svg.setAttribute("viewBox","0 0 16 16");

    var button_remove_svg_path = document.createElementNS("http://www.w3.org/2000/svg","path");
    button_remove_svg_path.setAttribute("d","M1.293 1.293a1 1 0 0 1 1.414 0L8 6.586l5.293-5.293a1 1 0 1 1 1.414 1.414L9.414 8l5.293 5.293a1 1 0 0 1-1.414 1.414L8 9.414l-5.293 5.293a1 1 0 0 1-1.414-1.414L6.586 8 1.293 2.707a1 1 0 0 1 0-1.414z");

    button_remove_svg.appendChild(button_remove_svg_path);
    button_remove.appendChild(button_remove_svg);

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
    tbl_prodtypes_changes_queue.push(
        {insert_row:
            {
                product_id:     new_id,
                product_name:   document.getElementById("prodtypes-product-name-" + new_id).value,
                t_min:          document.getElementById("prodtypes-t-min-"        + new_id).value,
                t_max:          document.getElementById("prodtypes-t-max-"        + new_id).value,
                v_min:          document.getElementById("prodtypes-v-min-"        + new_id).value,
                v_max:          document.getElementById("prodtypes-v-max-"        + new_id).value
            }
        }
    );

    return;
}

function onClickTblProdtypesDiscardChanges(){
    //  Очищаем массивы и восстанавливаем таблицу
    tbl_prodtypes_changes_queue.length=0;
    redrawTableProdtypes();
    //  Блокируем кнопки "сохранить" и "отменить изменения"
    buttonDisable("table-prodtypes-btn-save-changes");
    buttonDisable("table-prodtypes-btn-discard-changes");
    //  Включаем вторую таблицу
    redrawTableProdtypesbysilo();

    return;
}

function onClickTblProdtypesSaveChanges(){

    $.ajax({
        url: 'visualisation/visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'tbl_prodtypes_changes_queue': tbl_prodtypes_changes_queue },
        dataType: 'html',
        success: function(fromPHP) {
            alert("Изменения успешно внесены в Базу Данных");
        }
    });

    //  Очщаем очередь
    tbl_prodtypes_changes_queue.length=0;
    //  Перерисовываем таблицу
    redrawTableProdtypes();
    //  Блокируем кнопки "сохранить" и "отменить изменения"
    buttonDisable("table-prodtypes-btn-save-changes");
    buttonDisable("table-prodtypes-btn-discard-changes");
    //  Включаем вторую таблицу
    redrawTableProdtypesbysilo();

    return;
}

//  Таблица "Загрузка силосов"
let tbl_prodtypesbysilo_update_list=[];  //  массив объектов

function onChangeTblProdtypesbysilo(){
    //  Отключаем другую таблицу
    tableInputsDisable("table-prodtypes");
    buttonDisable("table-prodtypes-btn-add");
    buttonDisable("table-prodtypes-btn-save-changes");
    buttonDisable("table-prodtypes-btn-discard-changes");

    //  Сохраняем строку в tbl_prodtypesbysilo_update_list
    const selects = document.getElementById("table-prodtypesbysilo").getElementsByTagName("select");

    tbl_prodtypesbysilo_update_list.length = 0;

    let i=0;
    while(i<selects.length){

        const silo_id = selects[i].id.split('-').pop();
        
        const grain_level_from_TS =  selects[i].value=="auto" ? 1 : 0;
        i++;
        const grain_level = selects[i].value;
        i++;
        const product_id = selects[i].value;
        i++;
        tbl_prodtypesbysilo_update_list.push({
            silo_id : silo_id,
            grain_level_from_TS: grain_level_from_TS,
            grain_level: grain_level,
            product_id: product_id
        });
    }

    //  Включаем кноки "сохранить" и "отменить изменения"
    buttonEnable("table-prodtypesbysilo-btn-save-changes");
    buttonEnable("table-prodtypesbysilo-btn-discard-changes");

    return;
}

function onClickTblProdtypesbysiloDiscardChanges(){
    //  Очищаем массивы
    tbl_prodtypesbysilo_update_list.length = 0;
    //  Блокируем кнопки "сохранить" и "отменить изменения"
    buttonDisable("table-prodtypesbysilo-btn-save-changes");
    buttonDisable("table-prodtypesbysilo-btn-discard-changes");
    //  Включаем обе таблицы
    redrawTableProdtypes();
    redrawTableProdtypesbysilo();

    return;
}

function onClickTblProdtypesbysiloSaveChanges(){
    
    $.ajax({
        url: 'visualisation/visu_silo_config.php',
        type: 'POST',
        cache: false,
        data: { 'tbl_prodtypesbysilo_update_list': tbl_prodtypesbysilo_update_list },
        dataType: 'html',
        success: function(fromPHP) {
            alert("Изменения успешно внесены в Базу Данных");
        }
    });

    tbl_prodtypesbysilo_update_list.length = 0;
    //  Блокируем кнопки "сохранить" и "отменить изменения"
    buttonDisable("table-prodtypesbysilo-btn-save-changes");
    buttonDisable("table-prodtypesbysilo-btn-discard-changes");
    //  Включаем обе таблицы
    redrawTableProdtypes();
    redrawTableProdtypesbysilo();    

    return;
}
