function init_index() {
    document.getElementById("hdr-href-index.php").setAttribute("class", "nav-link text-primary");

    vIndRedrawSiloStatus();

    lastSiloID = "silo-0";
    lastParamSelectButtonID = "btn-temperatures";
    vIndOnClickOnValsSelectBtn(lastParamSelectButtonID);

    return;
}

//  Левый сайтбар ------------------------------------------------------------------------------------------------------------------------------
function vIndRedrawTableCurrentAlarms() {
    $.ajax({
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
        ["assets/img/silo_round_disabled.png", "assets/img/silo_round_disabled.png"],
        ["assets/img/silo_round_alarm_CRC.png", "assets/img/silo_round_alarm_CRC.png"],
        ["assets/img/silo_round_alarm.png", "assets/img/silo_round_OK.png"],
        ["assets/img/silo_round_alarm.png", "assets/img/silo_round_alarm.png"],
        ["assets/img/silo_round_OK.png", "assets/img/silo_round_OK.png"]
    ],
    [
        ["assets/img/silo_square_disabled.png", "assets/img/silo_square_disabled.png"],
        ["assets/img/silo_square_alarm_CRC.png", "assets/img/silo_square_alarm_CRC.png"],
        ["assets/img/silo_square_alarm.png", "assets/img/silo_square_OK.png"],
        ["assets/img/silo_square_alarm.png", "assets/img/silo_square_alarm.png"],
        ["assets/img/silo_square_OK.png", "assets/img/silo_square_OK.png"]
    ]
];
let arr_silo_status_new = [];                                               //  массив новых состояний силосов, который ми принимаем от PHP
let arr_silo_status_curr = [];                                              //  массив состояний силосов, полученный на предыдущем шаге
let curr_NACK_state = 0;                                                    //  переменная для создания мерация. Изменяет свое значение от 0 до 1 на каждом цикле

const silo_blink_period = 1000;                                             //  период мерцания

const timer_silo_blink = setInterval(() => {
    for (let i = 0; i < arr_silo_status_new.length; i++) {
        //  Перерисовывать картинки следует только при необходимости
        if( JSON.stringify(arr_silo_status_new[i]) !== JSON.stringify(arr_silo_status_curr[i]) || arr_silo_status_new[i][0]===2 || arr_silo_status_new[i][1]===2 ){

            document.getElementById("silo-" + i).setAttribute("src", img_arr_silo_status [arr_silo_status_new[i][0]] [arr_silo_status_new[i][1]] [curr_NACK_state]);

        }
        arr_silo_status_curr[i]=arr_silo_status_new[i];
    }
    curr_NACK_state = 1 - curr_NACK_state;
}, silo_blink_period);

//  Функция отображения текущего состояния силосов
//  Использует массив с картинками и таймер, определенные выше
function vIndRedrawSiloStatus() {

    $.ajax({
        url: '/Thermometry/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_get_curr_silo_status': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            arr_silo_status_new.length = 0;
            arr_silo_status_new = JSON.parse(fromPHP);
        }
    });

    return;
}

//  Правый сайтбар  -------------------------------------------------------------------------------------------------------------------------
let lastParamSelectButtonID; //  Кнопка для выбора отображаемых параметров (температуры, скорости)
let lastSiloID;

function vIndOnClickOnSilo(silo_id) {

    //alert(silo_id);

    //  Перерисовка таблиц с измеренными значениями
    if (lastParamSelectButtonID === "btn-temperatures") {
        vIndredrawTblTemperatures(silo_id);
    } else if (lastParamSelectButtonID === "btn-speeds") {
        vIndredrawTblTemperatureSpeeds(silo_id);
    }
    //  Перерисовка таблицы с параметрами продукта для текущего силоса
    if(silo_id!==lastSiloID){
        vIndredrawTblProdParameters(silo_id);
    }
    
    vIndRedrawTableCurrentAlarms();         //  Перерисовываем таблицу с текущими алармами
    vIndRedrawSiloStatus();                 //  Показываем текущий статус каждого силоса

    if (typeof silo_id !== 'undefined') {
        document.getElementById("current-silo-name").innerHTML = "Силос " + silo_names_array[silo_id.split("-").pop()];
    }

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
        url: '/Thermometry/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vInd_silo_id_for_product_parameters': silo_id },
        dataType: 'html',
        success: function(fromPHP) {

            try {
                
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
                
            } catch (e) {
                return;
            }

        }
    });
    return;
}

function vIndredrawTblTemperatures(silo_id) {
    $.ajax({
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
        url: '/Thermometry/visu_index.php',
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
    document.cookie = "chart_silo_name=" + silo_names_array[silo_id] + ";";
    document.cookie = "chart_podv_num=" + (+podv_num+1) + ";";
    document.cookie = "chart_sensor_num=" + (+sensor_num+1) + ";";
    document.cookie = "chart_period=" + period + ";";
    document.location.href = "report.php";
    return;
}
