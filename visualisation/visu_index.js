function init_index() {
    document.getElementById("hdr-href-index.php").setAttribute("class", "nav-link text-primary");
    getConf_ArrayOfSiloNames();
    redrawSiloStatus();

    lastParamSelectButtonID = "btn-temperatures";
    document.getElementById("btn-temperatures").className = "btn btn-success";
    lastSiloID = "silo-0";
    onSiloClicked(lastSiloID);

    return;
}

//  Левый сайтбар
function redrawTableCurrentAlarms() {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'get_current_alarms': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("ind-table-alarms").innerHTML = fromPHP;
        }
    });
    return;
}

$('#ind-btn-disable-all-def-sensors').click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Отключить все неисправные датчики?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "disable_all_defective_sensors()");
    $("#modal-are-you-sure").modal('show');
});

$('#ind-btn-enable-all-sensors').click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Включить все отключенные датчики?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "enable_all_sensors()");
    $("#modal-are-you-sure").modal('show');
});

$('#btn-enable-all-auto-lvl-mode').click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Включить автоопределение уровня на всех силосах?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "enable_all_auto_lvl_mode()");
    $("#modal-are-you-sure").modal('show');
});

function disable_all_defective_sensors() {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'disable_all_defective_sensors': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            console.log(fromPHP);
            onSiloClicked(lastSiloID);
        }
    });
    return;
}

function enable_all_sensors() {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'enable_all_sensors': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            console.log(fromPHP);
            onSiloClicked(lastSiloID);
        }
    });
    return;
}

function enable_all_auto_lvl_mode() {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'enable_auto_lvl_mode': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            onSiloClicked(lastSiloID);
        }
    });
    return;
}

//  Основная часть
const img_arr_silo_status = [
    [
        ["img/silo_round_disabled.png", "img/silo_round_disabled.png"],
        ["img/silo_round_alarm_CRC.png", "img/silo_round_alarm_CRC.png"],
        ["img/silo_round_alarm.png", "img/silo_round_OK.png"],
        ["img/silo_round_alarm.png", "img/silo_round_alarm.png"],
        ["img/silo_round_OK.png", "img/silo_round_OK.png"]
    ],
    [
        ["img/silo_square_disabled.png", "img/silo_square_disabled.png"],
        ["img/silo_square_alarm_CRC.png", "img/silo_square_alarm_CRC.png"],
        ["img/silo_square_alarm.png", "img/silo_square_OK.png"],
        ["img/silo_square_alarm.png", "img/silo_square_alarm.png"],
        ["img/silo_square_OK.png", "img/silo_square_OK.png"]
    ]
];
let arr_silo_status = [];
let curr_NACK_state = 0;

const silo_blink_period = 1000;
const timer_silo_blink = setInterval(() => {

    for (let i = 0; i < arr_silo_status.length; i++) {
        document.getElementById("silo-" + i).setAttribute("src", img_arr_silo_status[arr_silo_status[i][0]][arr_silo_status[i][1]][curr_NACK_state]);
    }

    curr_NACK_state = 1 - curr_NACK_state;

}, silo_blink_period);

function redrawSiloStatus() {

    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'get_silo_current_status': 1 },
        dataType: 'html',
        success: function(fromPHP) {

            const silo_status_arr = JSON.parse(fromPHP);

            //  console.log(silo_status_arr);

            arr_silo_status.length = 0;

            for (let i = 0; i < silo_status_arr.length; i++) {

                arr_silo_status.push(silo_status_arr[i]);

            }

        }
    });

    return;
}

//  Правый сайтбар
let lastParamSelectButtonID; //  Кнопка для выбора отображаемых параметров (температуры, скорости)
let lastSiloID;

function onSiloClicked(silo_id) {

    if (lastParamSelectButtonID === "btn-temperatures") {
        redrawTableTemperatures(silo_id); //  таблица температур
    } else if (lastParamSelectButtonID === "btn-speeds") {
        redrawTableTemperatureSpeeds(silo_id); //  таблица скоростей
    }

    redrawProductParametersTable(silo_id); //  Параметры продукта

    lastSiloID = silo_id;

}

function onBtnClicked(btn_id) {
    lastParamSelectButtonID = btn_id;
    onSiloClicked(lastSiloID);
    document.getElementById("btn-temperatures").className = "btn btn-light";
    document.getElementById("btn-speeds").className = "btn btn-light";
    document.getElementById(btn_id).className = "btn btn-success"; //  Подсвечиваем выбранную кнопку
    return;
}

function redrawProductParametersTable(silo_id) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'silo_id_for_silo_parameters': silo_id },
        dataType: 'html',
        success: function(fromPHP) {

            document.getElementById("ind-prod-tbl-1-prodtype").innerHTML = JSON.parse(fromPHP)[0];
            document.getElementById("ind-prod-tbl-1-t-max").innerHTML = JSON.parse(fromPHP)[1];
            document.getElementById("ind-prod-tbl-1-v-max").innerHTML = JSON.parse(fromPHP)[2];
            document.getElementById("ind-prod-tbl-3-t-min").innerHTML = JSON.parse(fromPHP)[3];
            document.getElementById("ind-prod-tbl-3-t-avg").innerHTML = JSON.parse(fromPHP)[4];
            document.getElementById("ind-prod-tbl-3-t-max").innerHTML = JSON.parse(fromPHP)[5];
            document.getElementById("ind-prod-tbl-5-v-min").innerHTML = JSON.parse(fromPHP)[6];
            document.getElementById("ind-prod-tbl-5-v-avg").innerHTML = JSON.parse(fromPHP)[7];
            document.getElementById("ind-prod-tbl-5-v-max").innerHTML = JSON.parse(fromPHP)[8];
            document.getElementById("ind-prod-tbl-6-rng-t-min").innerHTML = JSON.parse(fromPHP)[9];
            document.getElementById("ind-prod-tbl-6-rng-t-max").innerHTML = JSON.parse(fromPHP)[10];
            document.getElementById("ind-prod-tbl-6-v-max").innerHTML = JSON.parse(fromPHP)[11];

            document.getElementById("current-silo-name").innerHTML = "Силос " + silo_names_array[silo_id.split("-").pop()];
        }
    });
    return;
}

function redrawTableTemperatures(silo_id) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'silo_id_for_temperature_table': silo_id },
        dataType: 'html',
        success: function(fromPHP) {
            //console.log(fromPHP);
            document.getElementById("silo-param-table").innerHTML = fromPHP;
        }
    });
    return;
}

function redrawTableTemperatureSpeeds(silo_id) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'silo_id_for_speeds_table': silo_id },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("silo-param-table").innerHTML = fromPHP;
        }
    });
    return;
}

function selectedSensorDisable(silo_id, podv_num, sensor_num) {
    //    console.log("Disable: ", silo_id, podv_num, sensor_num);
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'sensor_disable_silo_id': silo_id, 'sensor_disable_podv_num': podv_num, 'sensor_disable_sensor_num': sensor_num },
        dataType: 'html',
        success: function(fromPHP) {
            console.log(fromPHP);
            onSiloClicked(lastSiloID);
        }
    });
    return;
}

function selectedSensorEnable(silo_id, podv_num, sensor_num) {
    //    console.log("Enable: ", silo_id, podv_num, sensor_num);
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'sensor_enable_silo_id': silo_id, 'sensor_enable_podv_num': podv_num, 'sensor_enable_sensor_num': sensor_num },
        dataType: 'html',
        success: function(fromPHP) {
            console.log(fromPHP);
            onSiloClicked(lastSiloID);
        }
    });
    return;
}

function selectedPodvDisable(silo_id, podv_num) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'podv_disable_silo_id': silo_id, 'podv_disable_podv_num': podv_num },
        dataType: 'html',
        success: function(fromPHP) {
            onSiloClicked(lastSiloID);
        }
    });
    return;
}

function selectedPodvEnable(silo_id, podv_num) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'podv_enable_silo_id': silo_id, 'podv_enable_podv_num': podv_num },
        dataType: 'html',
        success: function(fromPHP) {
            onSiloClicked(lastSiloID);
        }
    });
    return;
}

function change_grain_level_mode(silo_id, lvl_mode) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'change_level_mode_silo_id': silo_id, 'change_level_mode_level_mode': lvl_mode },
        dataType: 'html',
        success: function(fromPHP) {
            onSiloClicked(lastSiloID);
        }
    });
    return;
}

function change_grain_level_from_slider(silo_id) {

    let lvl_slider;

    if (document.getElementById("lvl-slider-t-" + silo_id)) {
        lvl_slider = document.getElementById("lvl-slider-t-" + silo_id);
    } else {
        lvl_slider = document.getElementById("lvl-slider-v-" + silo_id);
    }

    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'change_level_from_slider_silo_id': silo_id, 'change_level_from_slider_grain_level': lvl_slider.value },
        dataType: 'html',
        success: function(fromPHP) {
            console.log(fromPHP);
            onSiloClicked(lastSiloID);
        }
    });

    return;
}

function selectedSensorDrawChart(silo_id, podv_num, sensor_num, period) {
    document.cookie = "chart_silo_id=" + silo_id + ";";
    document.cookie = "chart_podv_num=" + podv_num + ";";
    document.cookie = "chart_sensor_num=" + sensor_num + ";";
    document.cookie = "chart_period=" + period + ";";
    document.location.href = "report.php";
    return;
}