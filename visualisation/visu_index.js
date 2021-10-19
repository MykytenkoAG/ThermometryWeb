function init_index(){


    console.log("index initialisation");

    redrawSiloStatus();

    lastParamSelectButtonID = "btn-temperatures";
    document.getElementById("btn-temperatures").className = "btn btn-success";
    lastSiloID = "silo-0";
    onSiloClicked(lastSiloID);

    return;
}

//  Алармы
function redrawTableCurrentAlarms(){
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'get_current_alarms': 1 },
        dataType: 'html',
        success: function(fromPHP) { document.getElementById("ind-table-alarms").innerHTML = fromPHP; }
    });
    return;
}

//  !   TODO
function disableAllDefectiveSensors(){

    return;
}

//  !   TODO
function enableAllSensors(){

    return;
}

//  Состояние силосов
const img_arr_silo_status = [
                                [
                                    ["img/silo_round_disabled.png",     "img/silo_round_disabled.png"],
                                    ["img/silo_round_alarm_CRC.png",    "img/silo_round_alarm_CRC.png"],
                                    ["img/silo_round_alarm.png",        "img/silo_round_OK.png"],
                                    ["img/silo_round_alarm.png",        "img/silo_round_alarm.png"],
                                    ["img/silo_round_OK.png",           "img/silo_round_OK.png"]
                                ],
                                [
                                    ["img/silo_square_disabled.png",     "img/silo_square_disabled.png"],
                                    ["img/silo_square_alarm_CRC.png",    "img/silo_square_alarm_CRC.png"],
                                    ["img/silo_square_alarm.png",        "img/silo_square_OK.png"],
                                    ["img/silo_square_alarm.png",        "img/silo_square_alarm.png"],
                                    ["img/silo_square_OK.png",           "img/silo_square_OK.png"]
                                ]
                            ];

let arr_silo_status=[];
let curr_NACK_state=0;

let timer_silo_blink = setInterval( ()=>{

                            for(let i=0; i<arr_silo_status.length; i++){
                                document.getElementById("silo-"+i).setAttribute("src",img_arr_silo_status[ arr_silo_status[i][0] ][ arr_silo_status[i][1] ][curr_NACK_state]);
                            }

                            curr_NACK_state = 1-curr_NACK_state;

                            }, 1000);

function redrawSiloStatus(){

    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'get_silo_current_status': 1 },
        dataType: 'html',
        success: function(fromPHP) {

            const silo_status_arr = JSON.parse(fromPHP);

            //  console.log(silo_status_arr);

            arr_silo_status.length=0;

            for(let i=0; i<silo_status_arr.length; i++){

                arr_silo_status.push(silo_status_arr[i]);

            }

        }
    });

    return;
}

//  Правый сайтбар
let lastParamSelectButtonID;                //  Кнопка для выбора отображаемых параметров (температуры, скорости)
let lastSiloID;

function onSiloClicked(silo_id) {

    if          (lastParamSelectButtonID === "btn-temperatures") {
        redrawTableTemperatures(silo_id);                           //  таблица температур
    } else if   (lastParamSelectButtonID === "btn-speeds") {
        redrawTableTemperatureSpeeds(silo_id);                      //  таблица скоростей
    }

    redrawSiloNameText(silo_id);                                    //  Название силоса
    redrawProductParametersTable(silo_id);                          //  Параметры продукта

    lastSiloID = silo_id;

}

function onBtnClicked(btn_id) {
    lastParamSelectButtonID = btn_id;
    onSiloClicked(lastSiloID);
    document.getElementById("btn-temperatures").className   = "btn btn-light";
    document.getElementById("btn-speeds").className         = "btn btn-light";
    document.getElementById(btn_id).className               = "btn btn-success";    //  Подсвечиваем выбранную кнопку
    return;
}

function redrawSiloNameText(silo_id){
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'silo_id_forText': silo_id },
        dataType: 'html',
        success: function(fromPHP) { document.getElementById("current-silo-name").innerHTML = fromPHP; }
    });
    return;
}

function redrawProductParametersTable(silo_id){
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
    return;
}

function redrawTableTemperatures(silo_id){
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'silo_id_for_temperature_table': silo_id },
        dataType: 'html',
        success: function(fromPHP) { document.getElementById("silo-param-table").innerHTML = fromPHP; }
    });
    return;
}

function redrawTableTemperatureSpeeds(silo_id){
    $.ajax({
        url: 'visualisation/visu_index.php',
        type: 'POST',
        cache: false,
        data: { 'silo_id_for_speeds_table': silo_id },
        dataType: 'html',
        success: function(fromPHP) { document.getElementById("silo-param-table").innerHTML = fromPHP; }
    });
    return;
}

//  !   TODO
function disableCurrentSensor(silo_id, podv_num, sensor_num){

    return;
}

//  !   TODO
function enableCurrentSensor(silo_id, podv_num, sensor_num){

    return;
}

//  !   TODO
function disableCurrentPodv(silo_id, podv_num, sensor_num){

    return;
}

//  !   TODO
function enableCurrentPodv(silo_id, podv_num, sensor_num){

    return;
}
