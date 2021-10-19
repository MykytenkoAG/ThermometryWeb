let current_page;
let mainTimer;
let serverDateTime;
let project_conf_array=[];
let silo_name_with_id_0;
let silo_name_with_max_podv_number;

document.addEventListener("DOMContentLoaded", () => {

    current_page = window.location.pathname.split("/").pop();
    mainTimer = setInterval( periodicActions, 10000);

    if              (current_page === "index.php" || current_page === ""){
        init_index();
    } else if       (current_page === "report.php" || current_page === "debug_page.php"){
        getProjectConfArr();
    } else if       (current_page === "silo_config.php"){
        init_silo_config();
    }

    isSoundOn();
    
});

/*  Получение главного конфигурационного ассоциативного массива
*/
function getProjectConfArr(){
    $.ajax({
        url: '/webTermometry/scripts/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'get_project_conf_array': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            project_conf_array = (JSON.parse(fromPHP));
            getSiloNameWithID0();
        }
    });
}

function getSiloNameWithID0(){
    $.ajax({
        url: '/webTermometry/visualisation/visu_report.php',
        type: 'POST',
        cache: false,
        data: { 'get_silo_name_with_id_0': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            silo_name_with_id_0 = (JSON.parse(fromPHP));
            getSiloNameWithMaxPodvNumber();
        }
    });
    return;
}

function getSiloNameWithMaxPodvNumber(){
    $.ajax({
        url: '/webTermometry/visualisation/visu_report.php',
        type: 'POST',
        cache: false,
        data: { 'get_silo_number_with_max_podv_number': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            silo_name_with_max_podv_number = (JSON.parse(fromPHP));
            if(current_page === "report.php"){
                init_report();
            } else if   (current_page === "debug_page.php"){
                init_debug_page();
            }
        }
    });
    return;
}

/*  Функция для установки аттрибутов option элемента select
*/
function setSelectOptions(dom_element, options_arr){
    while (dom_element.options.length) {
        dom_element.remove(0);
    }
    options_arr.forEach(curr_option => {
        if(curr_option==="all"){
            dom_element.add(new Option("все","all"));
        } else{
            dom_element.add(new Option(curr_option,curr_option));
        }   
    });
    return;
}
/*  Функция для установки атрибутов option для строки из элементов select
    Использует:
        главный конфигурационный ассоциативный массив project_conf_array
        функцию setSelectOptions(dom_element, options_arr)
    Используется страницами report и debug_page
    Вызывается при изменении номера выбранного силоса и подвески
*/  
function redrawSelectsRow(select_element_id){

    const page          = select_element_id.split("_")[0];
    const element_name  = select_element_id.split("_")[1];
    const row_number    = select_element_id.split("_")[2];

    const current_silo  = document.getElementById(page + "_silo_" + row_number);

    const opt_0 = current_silo.options[0].value==="all" ? ["all"] : [];
    const current_silo_selected_ind = current_silo.options[current_silo.selectedIndex].value==="all" ? silo_name_with_max_podv_number : current_silo.options[current_silo.selectedIndex].value;

    let element_podv    = document.getElementById(page + "_podv_"  + row_number);
    let element_sensor  = document.getElementById(page + "_sensor_"+ row_number);
    let element_level   = document.getElementById(page + "_level_" + row_number);
    let element_layer   = document.getElementById(page + "_layer_" + row_number);

    if(element_name==="silo"){
        if(element_podv){
            setSelectOptions( element_podv,   opt_0.concat( Object.keys(project_conf_array[current_silo_selected_ind]) ) );
        }
        if(element_sensor){
            setSelectOptions( element_sensor, opt_0.concat( Object.keys(project_conf_array[current_silo_selected_ind][1]) ) );
        }
        if(element_layer){
            setSelectOptions( element_layer,  opt_0.concat( Object.keys(project_conf_array[current_silo_selected_ind][1]) ) );
        }
        if(element_level){  //  только для страницы отладки
            setSelectOptions( element_level,  [0].concat( Object.keys( project_conf_array[current_silo_selected_ind][1]) ) );
        }
    }

    if(element_name==="podv"){
        const current_podv = document.getElementById(page + "_podv_" + row_number);
        const current_podv_selected_ind = current_podv.options[current_podv.selectedIndex].value==="all" ? 1 : current_podv.options[current_podv.selectedIndex].value;

        if(element_sensor){
            setSelectOptions( element_sensor, opt_0.concat( Object.keys(project_conf_array[current_silo_selected_ind][current_podv_selected_ind]) ) );
        }
        if(element_layer){
            setSelectOptions( element_layer,  opt_0.concat( Object.keys(project_conf_array[current_silo_selected_ind][current_podv_selected_ind]) ) );
        }
    }

    return;
}

function isSoundOn(){

    $.ajax({
        url: '/webTermometry/scripts/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'is_sound_on': 1 },
        dataType: 'html',
        success: function(fromPHP) {

            console.log(fromPHP);

            if(fromPHP=="YES"){
                document.getElementById("alarm-sound").loop = true;
                document.getElementById("alarm-sound").play();
            }else {
                
                document.getElementById("alarm-sound").pause();
            }

        }
    });
    return;
}

function acknowledgeAlarms(){
    document.getElementById("alarm-sound").pause();

    $.ajax({
        url: '/webTermometry/scripts/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'acknowledge': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            console.log(fromPHP);
            if(current_page === "index.php"){
                redrawTableCurrentAlarms();

                redrawSiloStatus();

                onSiloClicked(lastSiloID);
            }
        }
    });
    return;
}

function periodicActions() {
    
    $.ajax({
        url: '/webTermometry/scripts/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'read_vals': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            console.log(fromPHP);
            isSoundOn();

            if(current_page === "index.php" || current_page === ""){
                redrawTableCurrentAlarms();

                redrawSiloStatus();

                onSiloClicked(lastSiloID);
            }
        }
    });
}
