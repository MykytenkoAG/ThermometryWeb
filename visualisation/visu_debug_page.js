function debug_page_init(){

    for(let i=1; i<=7; i++){
        let current_silo = document.getElementById("dbg_silo_"+i);
        setSelectOptions(current_silo, Object.keys(project_conf_array));
        redrawSelectsRow("dbg_silo_"+i);
    }

    redrawMainDbgTable();

    return;
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
