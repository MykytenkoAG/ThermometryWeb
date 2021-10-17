//  Сохраняем последние значения кнопок выбора параметров
let lastButtonID = "btn-temperatures";
let lastSiloID = "silo-0";

//  При загрузке страницы
window.onload = function() {
    document.getElementById("btn-temperatures").className = "btn btn-success";
    onSiloClicked("silo-0");
}

function onBtnClicked(btn_id) {
    lastButtonID = btn_id;
    onSiloClicked(lastSiloID);
    //  можно оптимизировать при помощи массива
    document.getElementById("btn-temperatures").className   = "btn btn-light";
    document.getElementById("btn-speeds").className         = "btn btn-light";
    document.getElementById(btn_id).className               = "btn btn-success";
}

//  Произведено нажатие на один из силосов
function onSiloClicked(silo_id) {

    if (lastButtonID === "btn-temperatures") {
        //  Отрисовка таблиц температур
        $.ajax({
            url: 'visualisation/visu_index.php',
            type: 'POST',
            cache: false,
            data: { 'silo_id_for_temperature_table': silo_id },
            dataType: 'html',
            success: function(fromPHP) { document.getElementById("silo-param-table").innerHTML = fromPHP; }
        });
    } else if (lastButtonID === "btn-speeds") {
        //  Отрисовка таблиц скоростей
        $.ajax({
            url: 'visualisation/visu_index.php',
            type: 'POST',
            cache: false,
            data: { 'silo_id_for_speeds_table': silo_id },
            dataType: 'html',
            success: function(fromPHP) { document.getElementById("silo-param-table").innerHTML = fromPHP; }
        });
    }

    //  Изменение текста номера силоса
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'silo_id_forText': silo_id },
        dataType: 'html',
        success: function(fromPHP) { document.getElementById("current-silo-name").innerHTML = fromPHP; }
    });

    //  Получение параметров силоса
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'silo_id_for_silo_parameters': silo_id },
        dataType: 'html',
        success: function(fromPHP) {

            document.getElementById("ind-prod-tbl-1-prodtype").innerHTML    = JSON.parse(fromPHP)[0];
            document.getElementById("ind-prod-tbl-1-t-max").innerHTML       = JSON.parse(fromPHP)[1];
            document.getElementById("ind-prod-tbl-1-v-max").innerHTML       = JSON.parse(fromPHP)[2];
            document.getElementById("ind-prod-tbl-3-t-min").innerHTML       = JSON.parse(fromPHP)[3];
            document.getElementById("ind-prod-tbl-3-t-avg").innerHTML       = JSON.parse(fromPHP)[4];
            document.getElementById("ind-prod-tbl-3-t-max").innerHTML       = JSON.parse(fromPHP)[5];
            document.getElementById("ind-prod-tbl-5-v-min").innerHTML       = JSON.parse(fromPHP)[6];
            document.getElementById("ind-prod-tbl-5-v-avg").innerHTML       = JSON.parse(fromPHP)[7];
            document.getElementById("ind-prod-tbl-5-v-max").innerHTML       = JSON.parse(fromPHP)[8];
            document.getElementById("ind-prod-tbl-6-rng-t-min").innerHTML   = JSON.parse(fromPHP)[9];
            document.getElementById("ind-prod-tbl-6-rng-t-max").innerHTML   = JSON.parse(fromPHP)[10];
            document.getElementById("ind-prod-tbl-6-v-max").innerHTML       = JSON.parse(fromPHP)[11];

        }
    });

    lastSiloID = silo_id;

}
