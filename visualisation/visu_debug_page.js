function init_debug_page(){

    //  Инициализация элементов select
    for(let i=1; i<=7; i++){
        let current_silo = document.getElementById("dbg_silo_"+i);
        setSelectOptions(current_silo, Object.keys(project_conf_array));
        redrawSelectsRow("dbg_silo_"+i);
    }

    //  Отрисовка главной таблицы с параметрами
    redrawMainDbgTable();

    return;
}

function redrawMainDbgTable(){

    $.ajax({
        url: 'visualisation/visu_debug_page.php',
        type: 'POST',
        cache: false,
        data: { 'dbg_refresh': 1 },
        dataType: 'html',
        success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
    });

    return;
}

function onClick_dbg_button_1(silo_name_id, temperature_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const temperature = document.getElementById(temperature_id).value;

    $.ajax({
        url:    'visualisation/visu_debug_page.php',
        type:   'POST',
        cache: false,
        data: { 'dbg_1_silo_name':   silo_name,
                'dbg_1_temperature': temperature },
        dataType: 'html',
        success: function(fromPHP) {
            alert(fromPHP);
            redrawMainDbgTable();
        }
    });

    return;
}

function onClick_dbg_button_2(silo_name_id, temperature_speed_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const temperature_speed = document.getElementById(temperature_speed_id).value;

    $.ajax({
        url:    'visualisation/visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'dbg_2_silo_name': silo_name,
                'dbg_2_t_speed':   temperature_speed },
        dataType: 'html',
        success: function(fromPHP) {
            alert(fromPHP);
            redrawMainDbgTable();
        }
    });

    return;
}

function onClick_dbg_button_3(silo_name_id, level_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const level = document.getElementById(level_id).value;

    $.ajax({
        url:    'visualisation/visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'dbg_3_silo_name':      silo_name,
                'dbg_3_grain_level':    level },
        dataType: 'html',
        success: function(fromPHP) {
            alert(fromPHP);
            redrawMainDbgTable();
        }
    });

    return;
}

function onClick_dbg_button_4(silo_name_id, podv_num_id, temperature_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const podv_num = document.getElementById(podv_num_id).value;
    const temperature = document.getElementById(temperature_id).value;

    $.ajax({
        url:    'visualisation/visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'dbg_4_silo_name':      silo_name,
                'dbg_4_podv_num':       podv_num,
                'dbg_4_temperature':    temperature },
        dataType: 'html',
        success: function(fromPHP) {
            alert(fromPHP);
            redrawMainDbgTable();
        }
    });

    return;
}

function onClick_dbg_button_5(silo_name_id, podv_num_id, temperature_speed_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const podv_num = document.getElementById(podv_num_id).value;
    const temperature_speed = document.getElementById(temperature_speed_id).value;

    $.ajax({
        url:    'visualisation/visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'dbg_5_silo_name':  silo_name,
                'dbg_5_podv_num':   podv_num,
                'dbg_5_t_speed':    temperature_speed },
        dataType: 'html',
        success: function(fromPHP) {
          alert(fromPHP);
          redrawMainDbgTable();
        }
    });

    return;
}

function onClick_dbg_button_6(silo_name_id, podv_num_id, sensor_num_id, temperature_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const podv_num = document.getElementById(podv_num_id).value;
    const sensor_num = document.getElementById(sensor_num_id).value;
    const temperature = document.getElementById(temperature_id).value;

    $.ajax({
        url:    'visualisation/visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'dbg_6_silo_name':      silo_name ,
                'dbg_6_podv_num':       podv_num,
                'dbg_6_sensor_num':     sensor_num,
                'dbg_6_temperature':    temperature },
        dataType: 'html',
        success: function(fromPHP) {
          alert(fromPHP);
          redrawMainDbgTable();
        }
    });

    return;
}

function onClick_dbg_button_7(silo_name_id, podv_num_id, sensor_num_id, temperature_speed_id){
    const silo_name = document.getElementById(silo_name_id).value;
    const podv_num = document.getElementById(podv_num_id).value;
    const sensor_num = document.getElementById(sensor_num_id).value;
    const temperature_speed = document.getElementById(temperature_speed_id).value;

    $.ajax({
        url:    'visualisation/visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'dbg_7_silo_name':  silo_name,
                'dbg_7_podv_num':   podv_num,
                'dbg_7_sensor_num': sensor_num,
                'dbg_7_t_speed':    temperature_speed },
        dataType: 'html',
        success: function(fromPHP) {
          alert(fromPHP);
          redrawMainDbgTable();
        }
    });

    return;
}

function onClick_dbg_button_add_measurements(){

    $.ajax({
        url:    'visualisation/visu_debug_page.php',
        type:   'POST',
        cache:  false,
        data: { 'write_measurements_to_db': 1 },
        dataType: 'html',
        success: function(fromPHP) {
                alert(fromPHP)
        }
    });

    return;
}


