
window.onload = function(){
    getProjectConfArr();
    redrawMainDbgTable();
}

function getProjectConfArr(){

    $.ajax({
        url: '/webTermometry/php/configFromINI.php',
        type: 'POST',
        cache: false,
        data: { 'get_project_conf_array': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            
            project_conf_array = (JSON.parse(fromPHP));

            for(let i=1; i<=7; i++){
                let current_silo = document.getElementById("dbg_silo_"+i);
                setSelectOptions(current_silo, Object.keys(project_conf_array));
                redrawSelectsRow("dbg_silo_"+i);
            }
            
        }
    });

}

function redrawMainDbgTable(){

    $.ajax({
        url: 'php/debugScript.php',
        type: 'POST',
        cache: false,
        data: { 'dbg_refresh': 1 },
        dataType: 'html',
        success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
    });

    return;
}

function redrawSelectsRow(select_element_id){

    const page = select_element_id.split("_")[0];
    const element_name = select_element_id.split("_")[1];
    const row_number = select_element_id.split("_")[2];
    const current_silo = document.getElementById(page + "_silo_" + row_number);
    const current_silo_selected = current_silo.options[current_silo.selectedIndex].value;

    if(element_name==="silo"){
        element_podv = document.getElementById(page+"_podv_"+row_number);
        element_sensor = document.getElementById(page+"_sensor_"+row_number);
        element_level = document.getElementById(page+"_l_"+row_number);
        if(element_podv){
            setSelectOptions( element_podv,  Object.keys(project_conf_array[current_silo_selected]) );
        }
        if(element_sensor){            
            setSelectOptions( element_sensor,  Object.keys(project_conf_array[current_silo_selected][1]) );
        }        
        if(element_level){            
            setSelectOptions( element_level,  Object.keys(project_conf_array[current_silo_selected][1]) );
        }
    }

    if(element_name==="podv"){
        const current_podv = document.getElementById("dbg_podv_"+row_number);
        const current_podv_selected = current_podv.options[current_podv.selectedIndex].value;
        element_sensor = document.getElementById(page+"_sensor_"+row_number);
        if(element_sensor){
            setSelectOptions( element_sensor,  Object.keys(project_conf_array[current_silo_selected][current_podv_selected]) );
        }
    }

    return;
}
