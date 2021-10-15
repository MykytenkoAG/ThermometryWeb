let pr_f_silo_name;
let pr_f_podv_num;
let pr_f_layer_num;
let pr_f_sensor_num;

let chart_silo_name;
let chart_podv_num;
let chart_sensor_num;

window.onload = function(){
    pr_f_silo_name  = document.getElementById("rprt-pr-f-silo-name");
    pr_f_podv_num   = document.getElementById("rprt-pr-f-podv-num");
    pr_f_layer_num  = document.getElementById("rprt-pr-f-layer-num");
    pr_f_sensor_num = document.getElementById("rprt-pr-f-sensor-num");
    pr_f_podv_num.disabled = true;
    pr_f_layer_num.disabled = true;
    pr_f_sensor_num.disabled = true;

    const selects = document.getElementById("sensor-temperatures-table").getElementsByTagName('select');
    chart_silo_name  = selects.item(selects.length - 4);
    chart_podv_num   = selects.item(selects.length - 3);
    chart_sensor_num = selects.item(selects.length - 2);
    
    getProjectConfArr();
}

//  Получение главного конфигурационного ассоциативного массива
function getProjectConfArr(){

    $.ajax({
        url: '/webTermometry/php/configFromINI.php',
        type: 'POST',
        cache: false,
        data: { 'get_project_conf_array': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            
            project_conf_array = (JSON.parse(fromPHP));
            setSelectOptions( pr_f_silo_name, ["all"].concat( Object.keys(project_conf_array) ) );

            setSelectOptions( chart_silo_name,  Object.keys(project_conf_array) );
            setSelectOptions( chart_podv_num,   Object.keys(project_conf_array[1]) );
            setSelectOptions( chart_sensor_num, Object.keys(project_conf_array[1][1]) );
            
        }
    });

}

//  Функции управления чекбоксами
function prfChbAllDates(){
    const value = document.getElementById("prfchballdates").checked;
    let checkboxes = document.getElementsByTagName("input");

    for(let i=0; i<checkboxes.length; i++){
            if(checkboxes[i].id.split("_")[0]==="prfchball" || checkboxes[i].id.split("_")[0]==="prfchb"){
                checkboxes[i].checked = value;
            }
    }

    return;
}

function prfChbCurrDate(element_id){
    
    const date = element_id.split("_")[1];
    const prfChbDate = document.getElementById(element_id);
    const value = prfChbDate.checked;

    let checkboxes = document.getElementsByTagName("input");

    for(let i=0; i<checkboxes.length; i++){
            if(checkboxes[i].id.split("_")[0]==="prfchb" && checkboxes[i].id.split("_")[1]===date){
                checkboxes[i].checked = value;
            }
    }

    return;
}

//  Установка значений для элементов выбора подвески и датчика (Печатные формы)
function prFormChangedSilo(){

    const selectedSiloName = pr_f_silo_name.options[pr_f_silo_name.selectedIndex].value;

    if(selectedSiloName==="all"){
        setSelectOptions(pr_f_podv_num,     ["all"]);
        setSelectOptions(pr_f_layer_num,    ["all"]);
        setSelectOptions(pr_f_sensor_num,   ["all"]);
        pr_f_podv_num.disabled = true;
        pr_f_layer_num.disabled = true;
        pr_f_sensor_num.disabled = true;
    } else {
        setSelectOptions(pr_f_podv_num,     ["all"].concat( Object.keys(project_conf_array[selectedSiloName]) ));
        setSelectOptions(pr_f_layer_num,    ["all"]);
        setSelectOptions(pr_f_sensor_num,   ["all"]);
        pr_f_podv_num.disabled = false;
        pr_f_layer_num.disabled = true;
        pr_f_sensor_num.disabled = true;
    }

    return;
}
//  Установка значений для элемента выбора датчика (Печатные формы)
function prFormChangedPodv(){
    
    const selectedSiloName = pr_f_silo_name.options[pr_f_silo_name.selectedIndex].value;
    const selectedPodvNum = pr_f_podv_num.options[pr_f_podv_num.selectedIndex].value;

    if(selectedPodvNum==="all"){
        setSelectOptions(pr_f_layer_num,    ["all"]);
        setSelectOptions(pr_f_sensor_num,   ["all"]);
        pr_f_layer_num.disabled = true;
        pr_f_sensor_num.disabled = true;
    } else {
        setSelectOptions(pr_f_layer_num,     ["all"].concat( Object.keys(project_conf_array[selectedSiloName][selectedPodvNum]) ));
        setSelectOptions(pr_f_sensor_num,     ["all"].concat( Object.keys(project_conf_array[selectedSiloName][selectedPodvNum]) ));
        pr_f_layer_num.disabled = false;
        pr_f_sensor_num.disabled = false;
    }

    return;
}

//  Установка значений для элементов выбора подвески и датчика (Печатные формы)
function chartChangedSilo(){

    const selects = document.getElementById("sensor-temperatures-table").getElementsByTagName('select');

    const selected_silo_id = selects.item(selects.length - 4).value;

    let chart_podv_num = selects.item(selects.length - 3);
    let chart_sensor_num = selects.item(selects.length - 2);
    
    setSelectOptions( chart_podv_num,   Object.keys(project_conf_array[selected_silo_id]) );
    setSelectOptions( chart_sensor_num, Object.keys(project_conf_array[selected_silo_id][1]) );

    return;
}
//  Установка значений для элемента выбора датчика (График температуры)
function chartChangedPodv(){

    const selects = document.getElementById("sensor-temperatures-table").getElementsByTagName('select');

    const selected_silo_id = selects.item(selects.length - 4).value;
    const selected_podv_id = selects.item(selects.length - 3).value;

    let chart_sensor_num = selects.item(selects.length - 2);
    
    setSelectOptions( chart_sensor_num, Object.keys(project_conf_array[selected_silo_id][selected_podv_id]) );

    return;
}

//  Добавление кривой на график
function addNewLineOnChart(){

    //  Получаем доступ ко всем полям
    let inputs = document.getElementById("sensor-temperatures-table").getElementsByTagName('input');
    let selects = document.getElementById("sensor-temperatures-table").getElementsByTagName('select');


    let silo_id = selects.item(selects.length - 4).value;
    let podv_id = selects.item(selects.length - 3).value;
    let sensor_num = selects.item(selects.length - 2).value;
    let line_colour = inputs.item(inputs.length - 1).value;
    let period = selects.item(selects.length - 1).value;

    //  !       Передаем параметры в PHP
    $.ajax({
        url: 'visualisation/visu_report.php',
        type: 'POST',
        cache: false,
        data: { 'silo_id': silo_id, 'podv_id': podv_id, 'sensor_num': sensor_num, 'period': period },
        dataType: 'html',
        success: function(fromPHP) {

            const newDataset = {
                label: '',
                data: [],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.2)',
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                ],
                borderWidth: 1
            };

            JSON.parse(fromPHP).forEach(element => {
                newDataset.data.push( {x: element["date"], y: element["temperature"]} );
            });
            newDataset.label = silo_id + '.' + podv_id + '.' + sensor_num;
            newDataset.backgroundColor[0] = 'rgba(' + parseInt(line_colour.slice(1,3),16) + ","
                                                    + parseInt(line_colour.slice(3,5),16) + ","
                                                    + parseInt(line_colour.slice(5,7), 16) + ",1)";
            newDataset.borderColor[0] = 'rgba('     + parseInt(line_colour.slice(1,3),16)+ ","
                                                    + parseInt(line_colour.slice(3,5),16) + ","
                                                    + parseInt(line_colour.slice(5,7),16) + ",1)";

            myChart.data.datasets.push(newDataset);
            myChart.update();    

        }
    });
    
    addNewTableRow();
}
//  Добавление строки в таблицу
function addNewTableRow(){

    //  Отключаем элементы на последней строке
    let inputs = document.getElementById("sensor-temperatures-table").getElementsByTagName('input');
    let selects = document.getElementById("sensor-temperatures-table").getElementsByTagName('select');

    selects.item(selects.length - 4).disabled = true;
    selects.item(selects.length - 3).disabled = true;
    selects.item(selects.length - 2).disabled = true;
    inputs.item(inputs.length - 1).disabled = true;
    selects.item(selects.length - 1).disabled = true;

    //  получаем доступ к tbody
    let tbody = document.getElementById("sensor-temperatures-table").getElementsByTagName("tbody")[0];
    //  создаем новую строку
    let row = document.createElement("tr");
    //  создаем столбцы
    let td1 = document.createElement("td");
    let input_silo_num = document.createElement("select");
    input_silo_num.setAttribute("onchange","chartChangedSilo()");
    input_silo_num.className = "form-control";
    td1.appendChild(input_silo_num);

    let td2 = document.createElement("TD");
    let input_podv_num = document.createElement("select");
    input_podv_num.setAttribute("onchange","chartChangedPodv()");
    input_podv_num.className = "form-control";
    td2.appendChild(input_podv_num);

    let td3 = document.createElement("TD");
    var input_sensor_num = document.createElement("select");
    input_sensor_num.className = "form-control";
    td3.appendChild(input_sensor_num);

    let td4 = document.createElement("TD");
    let input_color = document.createElement("input");
    input_color.type = "color";
    
    let colour_value="";
    for(let i=0; i<3; i++){
        if(i==0){
            colour_value += "#";
        }
        const current_colour = Math.floor(Math.random() * 256).toString(16);
        if(current_colour.length<2){
            colour_value += "0" + current_colour;
        } else{
            colour_value += current_colour;
        }
        if(i==2){
            input_color.setAttribute("value",colour_value);
        }
    }
    
    input_color.className = "form-control form-control-color";
    td4.appendChild(input_color);

    let td5 = document.createElement("td");
    let select_period = document.createElement("select");
    select_period.options.add(new Option("месяц", "month"));
    select_period.options.add(new Option("сутки", "day"));
    select_period.className = "form-control";
    td5.appendChild(select_period);

    row.appendChild(td1);
    row.appendChild(td2);
    row.appendChild(td3);
    row.appendChild(td4);
    row.appendChild(td5);
    tbody.appendChild(row);

    setSelectOptions( input_silo_num, Object.keys(project_conf_array) );
    setSelectOptions( input_podv_num, Object.keys(project_conf_array[1]) );
    setSelectOptions( input_sensor_num, Object.keys(project_conf_array[1][1]) );

    return;
}

//  setup
const data = {
    datasets: []
};
//  config
const config = {
    type: 'line',
    data: data,
    options: {
        scales: {
            x: {
            type: 'time',
            time: {
                unit: 'day'
            }
            },
            y: {
                beginAtZero: true
            }
        }
    }
};    
//  render / init block
let myChart = new Chart(document.getElementById('myChart'), config);
