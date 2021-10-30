function init_index() {
    document.getElementById("hdr-href-index.php").setAttribute("class", "nav-link text-primary");
    getConf_ArrayOfSiloNames();
    vIndRedrawSiloStatus();

    lastSiloID = "silo-0";
    lastParamSelectButtonID = "btn-temperatures";
    vIndOnClickOnValsSelectBtn(lastParamSelectButtonID);

    return;
}

//  Левый сайтбар ------------------------------------------------------------------------------------------------------------------------------
function vIndRedrawTableCurrentAlarms() {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_get_current_alarms': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("ind-table-alarms").innerHTML = fromPHP;
        }
    });
    return;
}
//  Кнопки под таблицей с текущими сигналами АПС. При нажатии вызывают появление модального окна. При этом кнопке "ОК" присваиваются функции ниже
$('#ind-btn-disable-all-def-sensors').click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Отключить все неисправные датчики?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "vIndDisAllDefectiveSensors()");
    $("#modal-are-you-sure").modal('show');
});

$('#ind-btn-enable-all-sensors').click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Включить все отключенные датчики?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "vIndEnAllSensors()");
    $("#modal-are-you-sure").modal('show');
});

$('#btn-enable-all-auto-lvl-mode').click(function() {
    document.getElementById("modal-are-you-sure-text").innerText = "Включить автоопределение уровня на всех силосах?";
    document.getElementById("modal-are-you-sure-btn-ok").innerText = "Да";
    document.getElementById("modal-are-you-sure-btn-cancel").innerText = "Отмена";
    document.getElementById("modal-are-you-sure-btn-ok").setAttribute("onclick", "vIndEnAutoLvlOnAllSilo()");
    $("#modal-are-you-sure").modal('show');
});

function vIndDisAllDefectiveSensors() {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_dis_all_defective_sensors': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            vIndOnClickOnSilo(lastSiloID);                  //  Перерисовываем таблицу с измеренными значениями для того, чтобы сразу увидеть изменения
        }
    });
    return;
}

function vIndEnAllSensors() {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_enable_all_sensors': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            vIndOnClickOnSilo(lastSiloID);                  //  Перерисовываем таблицу с измеренными значениями для того, чтобы сразу увидеть изменения
        }
    });
    return;
}

function vIndEnAutoLvlOnAllSilo() {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_enable_auto_lvl_mode_on_all_silo': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            vIndOnClickOnSilo(lastSiloID);                  //  Перерисовываем таблицу с измеренными значениями для того, чтобы сразу увидеть изменения
        }
    });
    return;
}

//  Основная часть ---------------------------------------------------------------------------------------------------------------------------
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
    curr_NACK_state = 1 - curr_NACK_state;      //  мерцание в случае активных АПС

}, silo_blink_period);

//  Функция отображения текущего состояния силосов
//  Использует массив с картинками и таймер, определенные выше
function vIndRedrawSiloStatus() {

    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_get_curr_silo_status': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            const silo_status_arr = JSON.parse(fromPHP);        //  Получаем массив, в котором индекс - это id силоса, а значение - код состояния
                                                                //  каждому коду состояния соответствует своя картинка в массиве картинок
            arr_silo_status.length = 0;
            for (let i = 0; i < silo_status_arr.length; i++) {
                arr_silo_status.push(silo_status_arr[i]);
            }
        }
    });

    return;
}

//  Правый сайтбар  -------------------------------------------------------------------------------------------------------------------------
let lastParamSelectButtonID; //  Кнопка для выбора отображаемых параметров (температуры, скорости)
let lastSiloID;

function vIndOnClickOnSilo(silo_id) {

    //  Перерисовка таблиц с измеренными значениями
    if (lastParamSelectButtonID === "btn-temperatures") {
        vIndredrawTblTemperatures(silo_id);
    } else if (lastParamSelectButtonID === "btn-speeds") {
        vIndredrawTblTemperatureSpeeds(silo_id);
    }
    //  Перерисовка таблицы с параметрами продукта для текущего силоса
    vIndredrawTblProdParameters(silo_id);

    lastSiloID = silo_id;

}
//  Кнопка выбора отображаемых параметров для силоса (температуры или скорости изменения температур)
function vIndOnClickOnValsSelectBtn(btn_id) {
    lastParamSelectButtonID = btn_id;
    vIndOnClickOnSilo(lastSiloID);
    document.getElementById("btn-temperatures").className = "btn btn-light";
    document.getElementById("btn-speeds").className = "btn btn-light";
    document.getElementById(btn_id).className = "btn btn-success"; //  Подсвечиваем выбранную кнопку
    return;
}

function vIndredrawTblProdParameters(silo_id) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_silo_id_for_product_parameters': silo_id },
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

function vIndredrawTblTemperatures(silo_id) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_temperature_table_silo_id': silo_id },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("silo-param-table").innerHTML = fromPHP;
        }
    });
    return;
}

function vIndredrawTblTemperatureSpeeds(silo_id) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_speeds_table_silo_id': silo_id },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("silo-param-table").innerHTML = fromPHP;
        }
    });
    return;
}

//  Включение/Отключение конкретного датчика/подвески путем нажатия на ячейку с измеренным значением температуры или скорости ее изменения
function vIndSelectedSensorDisable(silo_id, podv_num, sensor_num) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_sensorDisable_silo_id': silo_id, 'POST_vInd_sensorDisable_podv_id': podv_num, 'POST_vInd_sensorDisable_sensor_num': sensor_num },
        dataType: 'html',
        success: function(fromPHP) {
            vIndOnClickOnSilo(lastSiloID);
        }
    });
    return;
}

function vIndSelectedSensorEnable(silo_id, podv_num, sensor_num) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_sensorEnable_silo_id': silo_id, 'POST_vInd_sensorEnable_podv_id': podv_num, 'POST_vInd_sensorEnable_sensor_num': sensor_num },
        dataType: 'html',
        success: function(fromPHP) {
            vIndOnClickOnSilo(lastSiloID);
        }
    });
    return;
}

function vIndSelectedPodvDisable(silo_id, podv_num) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_podvDisable_silo_id': silo_id, 'POST_vInd_podvDisable_podv_id': podv_num },
        dataType: 'html',
        success: function(fromPHP) {
            vIndOnClickOnSilo(lastSiloID);
        }
    });
    return;
}

function vIndSelectedPodvEnable(silo_id, podv_num) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_podvEnable_silo_id': silo_id, 'POST_vInd_podvEnable_podv_id': podv_num },
        dataType: 'html',
        success: function(fromPHP) {
            vIndOnClickOnSilo(lastSiloID);
        }
    });
    return;
}

//  Изменение уровня при помощи слайдера
function vIndChangeSourceOfLvl(silo_id, lvl_mode) {
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_change_source_of_grain_level_silo_id': silo_id, 'POST_vInd_change_source_of_grain_level_source': lvl_mode },
        dataType: 'html',
        success: function(fromPHP) {
            vIndOnClickOnSilo(lastSiloID);
        }
    });
    return;
}

function vIndWriteGrainLvlFromSlider(silo_id) {

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
        data: { 'POST_vInd_writeLevelFromSliderForCurrSilo_silo_id': silo_id, 'POST_vInd_writeLevelFromSliderForCurrSilo_grainLevel': lvl_slider.value },
        dataType: 'html',
        success: function(fromPHP) {
            vIndOnClickOnSilo(lastSiloID);
        }
    });

    return;
}

//  Построение графика температуры для выбранного силоса
//  В функции происходит запоминание номера датчика в cookie и переход на страницу "Отчет"
function vIndDrawChartForSelectedSensor(silo_id, podv_num, sensor_num, period) {
    document.cookie = "chart_silo_name=" + silo_id + ";";
    document.cookie = "chart_podv_num=" + podv_num + ";";
    document.cookie = "chart_sensor_num=" + sensor_num + ";";
    document.cookie = "chart_period=" + period + ";";
    document.location.href = "report.php";
    return;
}
