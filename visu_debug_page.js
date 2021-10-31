function init_debug_page(){

    document.getElementById("hdr-href-debug_page.php").setAttribute("class", "nav-link text-primary");

    //  Инициализация элементов select
    for(let i=1; i<=7; i++){    //  7 - потому, что 7 строк кнопок
        let current_silo = document.getElementById("dbg_silo_"+i);
        setSelectOptions(current_silo, Object.keys(project_conf_array));
        redrawRowOfSelects("dbg_silo_"+i);
    }

    //  Отрисовка главной таблицы с параметрами
    redrawMainDbgTable();

    return;
}
//  Отрисовка таблицы со всеми параметрами
function redrawMainDbgTable(){
    $.ajax({
        url: 'visu_debug_page.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vDbgPage_dbg_refresh': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("debug_parameters_table").innerHTML = fromPHP;
        }
    });
    return;
}
//  Установить температуру для всех датчиков силоса
function onClickDbgBtn_1(silo_name_id, temperature_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const temperature = document.getElementById(temperature_id).value;
    $.ajax({
        url:    'visu_debug_page.php',
        type:   'POST',
        cache: false,
        data: { 'POST_vDbgPage_dbg_1_silo_name':   silo_name,
                'POST_vDbgPage_dbg_1_temperature': temperature },
        dataType: 'html',
        success: function(fromPHP) {
            redrawMainDbgTable();
        }
    });
    return;
}
//  Установить скорость изменения температуры для всех датчиков силоса
function onClickDbgBtn_2(silo_name_id, temperature_speed_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const temperature_speed = document.getElementById(temperature_speed_id).value;
    $.ajax({
        url:    'visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'POST_vDbgPage_dbg_2_silo_name': silo_name,
                'POST_vDbgPage_dbg_2_t_speed':   temperature_speed },
        dataType: 'html',
        success: function(fromPHP) {
            redrawMainDbgTable();
        }
    });
    return;
}
//  Установить уровень заполнения силоса
function onClickDbgBtn_3(silo_name_id, level_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const level = document.getElementById(level_id).value;
    $.ajax({
        url:    'visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'POST_vDbgPage_dbg_3_silo_name':      silo_name,
                'POST_vDbgPage_dbg_3_grain_level':    level },
        dataType: 'html',
        success: function(fromPHP) {
            redrawMainDbgTable();
        }
    });
    return;
}
//  Установить температуру для всех датчиков подвески
function onClickDbgBtn_4(silo_name_id, podv_num_id, temperature_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const podv_num = document.getElementById(podv_num_id).value;
    const temperature = document.getElementById(temperature_id).value;
    $.ajax({
        url:    'visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'POST_vDbgPage_dbg_4_silo_name':      silo_name,
                'POST_vDbgPage_dbg_4_podv_num':       podv_num,
                'POST_vDbgPage_dbg_4_temperature':    temperature },
        dataType: 'html',
        success: function(fromPHP) {
            redrawMainDbgTable();
        }
    });
    return;
}
//  Установить скорость изменения температуры для всех датчиков подвески
function onClickDbgBtn_5(silo_name_id, podv_num_id, temperature_speed_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const podv_num = document.getElementById(podv_num_id).value;
    const temperature_speed = document.getElementById(temperature_speed_id).value;
    $.ajax({
        url:    'visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'POST_vDbgPage_dbg_5_silo_name':  silo_name,
                'POST_vDbgPage_dbg_5_podv_num':   podv_num,
                'POST_vDbgPage_dbg_5_t_speed':    temperature_speed },
        dataType: 'html',
        success: function(fromPHP) {
            redrawMainDbgTable();
        }
    });
    return;
}
//  Установить температуру для одного датчика
function onClickDbgBtn_6(silo_name_id, podv_num_id, sensor_num_id, temperature_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const podv_num = document.getElementById(podv_num_id).value;
    const sensor_num = document.getElementById(sensor_num_id).value;
    const temperature = document.getElementById(temperature_id).value;
    $.ajax({
        url:    'visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'POST_vDbgPage_dbg_6_silo_name':      silo_name ,
                'POST_vDbgPage_dbg_6_podv_num':       podv_num,
                'POST_vDbgPage_dbg_6_sensor_num':     sensor_num,
                'POST_vDbgPage_dbg_6_temperature':    temperature },
        dataType: 'html',
        success: function(fromPHP) {
            redrawMainDbgTable();
        }
    });
    return;
}
//  Установить скорость изменения температуры для одного датчика
function onClickDbgBtn_7(silo_name_id, podv_num_id, sensor_num_id, temperature_speed_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const podv_num = document.getElementById(podv_num_id).value;
    const sensor_num = document.getElementById(sensor_num_id).value;
    const temperature_speed = document.getElementById(temperature_speed_id).value;
    $.ajax({
        url:    'visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'POST_vDbgPage_dbg_7_silo_name':  silo_name,
                'POST_vDbgPage_dbg_7_podv_num':   podv_num,
                'POST_vDbgPage_dbg_7_sensor_num': sensor_num,
                'POST_vDbgPage_dbg_7_t_speed':    temperature_speed },
        dataType: 'html',
        success: function(fromPHP) {
            redrawMainDbgTable();
        }
    });
    return;
}
//  Обнулить все показания
function onClickDbgBtn_8(){
    $.ajax({
        url:    'visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'POST_vDbgPage_dbg_8_set_all_params_to_0':  1 },
        dataType: 'html',
        success: function(fromPHP) {
            redrawMainDbgTable();
        }
    });
    return;
}
//  Добавить текущие показания в Базу Данных
function onClickDbgBtn_AddMeas(){
    $.ajax({
        url:    'visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'POST_vDbgPage_write_measurements_to_db': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            document.getElementById("modal-info-body-message").innerText = fromPHP;
            $("#modal-info").modal('show');
        }
    });
    return;
}
