function init_report() {

    document.getElementById("hdr-href-report.php").setAttribute("class", "nav-link text-primary");
    //  Блокировка кнопок "Скачать PDF" и "Скачать XLSX"
    vRep_rprtprf_checkDatesAndBlockDownloadButtons();
    //  Инициализация элементов select печатных форм
    setSelectOptions(document.getElementById("rprtprf_silo_1"), ["all"].concat(Object.keys(project_conf_array)));
    setSelectOptions(document.getElementById("rprtprf_podv_1"), ["all"].concat(Object.keys(project_conf_array[silo_name_with_max_podv_number])));
    setSelectOptions(document.getElementById("rprtprf_layer_1"), ["all"].concat(Object.keys(project_conf_array[silo_name_with_max_podv_number][1])));
    setSelectOptions(document.getElementById("rprtprf_sensor_1"), ["all"].concat(Object.keys(project_conf_array[silo_name_with_max_podv_number][1])));

    vRep_prfSelectsDisable();

    //  Инициализация элементов select графика температуры
    let selects = document.getElementById("rep-chart-time-temperature").getElementsByTagName('select');

    let chart_silo_1 = selects.item(selects.length - 4);
    let chart_podv_1 = selects.item(selects.length - 3);
    let chart_sensor_1 = selects.item(selects.length - 2);

    setSelectOptions(chart_silo_1, Object.keys(project_conf_array));
    setSelectOptions(chart_podv_1, Object.keys(project_conf_array[silo_name_with_id_0]));
    setSelectOptions(chart_sensor_1, Object.keys(project_conf_array[silo_name_with_id_0][1]));

    //  Построение графика, в случае если мы попали на эту страницу из главной
    const chart_silo_name = getCookie("chart_silo_name");
    const chart_podv_num = getCookie("chart_podv_num");
    const chart_sensor_num = getCookie("chart_sensor_num");
    const chart_period = getCookie("chart_period");
    if (chart_silo_name != "" && chart_silo_name != null &&
        chart_podv_num != "" && chart_podv_num != null &&
        chart_sensor_num != "" && chart_sensor_num != null &&
        chart_period != "" && chart_period != null) {

        const chart_silo_name = Object.keys(project_conf_array)[chart_silo_name];
        selects.item(selects.length - 4).value = chart_silo_name;
        selects.item(selects.length - 3).value = Object.keys(project_conf_array[chart_silo_name])[chart_podv_num];
        selects.item(selects.length - 2).value = project_conf_array[chart_silo_name][+chart_podv_num + 1][+chart_sensor_num + 1];
        selects.item(selects.length - 1).value = chart_period;

        vRep_addNewLineOnChart();

    }
}

//  Функции управления чекбоксами
//  Вкл/Откл все чекбоксы
function vRep_prfChbAllDates() {
    const value = document.getElementById("prfchballdates").checked;
    let checkboxes = document.getElementsByTagName("input");

    for (let i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].id.split("_")[0] === "prfchball" || checkboxes[i].id.split("_")[0] === "prfchb") {
            checkboxes[i].checked = value;
        }
    }

    return;
}
//  Вкл/Откл все чекбоксы для определенного дня
function vRep_prfChbCurrDate(element_id) {

    const prfChbDate = document.getElementById(element_id);
    const date = element_id.split("_")[1];
    const value = prfChbDate.checked;

    let checkboxes = document.getElementsByTagName("input");

    for (let i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].id.split("_")[0] === "prfchb" && checkboxes[i].id.split("_")[1] === date) {
            checkboxes[i].checked = value;
        }
    }

    return;
}

//  Включение/Отключение элементов выбора при нажатых радиокнопках
function vRep_prfSelectsDisable() {

    if (document.getElementById("prfrb_avg-t-by-layer").checked || document.getElementById("prfrb_t-by-layer").checked) {
        document.getElementById("rprtprf_podv_1").disabled = true;
        document.getElementById("rprtprf_layer_1").disabled = false;
        document.getElementById("rprtprf_sensor_1").disabled = true;

    }

    if (document.getElementById("prfrb_t-by-sensor").checked) {
        document.getElementById("rprtprf_podv_1").disabled = false;
        document.getElementById("rprtprf_layer_1").disabled = true;
        document.getElementById("rprtprf_sensor_1").disabled = false;
    }

    return;
}

//  Добавление кривой на график
function vRep_addNewLineOnChart() {

    //  Получаем доступ ко всем полям
    const inputs = document.getElementById("rep-chart-time-temperature").getElementsByTagName('input');
    const selects = document.getElementById("rep-chart-time-temperature").getElementsByTagName('select');

    const silo_name = selects.item(selects.length - 4).value;
    const podv_id = +selects.item(selects.length - 3).value;
    const sensor_num = +selects.item(selects.length - 2).value;
    const line_colour = inputs.item(inputs.length - 1).value;
    const period = selects.item(selects.length - 1).value;

    $.ajax({
        url: 'visualisation/visu_report.php',
        type: 'POST',
        cache: false,
        data: { 'POST_vRep_getTableForChart_silo_name': silo_name,
                'POST_vRep_getTableForChart_podv_id': podv_id - 1,
                'POST_vRep_getTableForChart_sensor_num': sensor_num - 1,
                'POST_vRep_getTableForChart_period': period },
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
                newDataset.data.push({ x: element["date"], y: element["temperature"] });
            });
            newDataset.label = silo_name + '.' + podv_id + '.' + sensor_num;
            newDataset.backgroundColor[0] = 'rgba(' + parseInt(line_colour.slice(1, 3), 16) + "," +
                parseInt(line_colour.slice(3, 5), 16) + "," +
                parseInt(line_colour.slice(5, 7), 16) + ",1)";
            newDataset.borderColor[0] = 'rgba(' + parseInt(line_colour.slice(1, 3), 16) + "," +
                parseInt(line_colour.slice(3, 5), 16) + "," +
                parseInt(line_colour.slice(5, 7), 16) + ",1)";

            myChart.data.datasets.push(newDataset);
            myChart.update();

            deleteCookie("chart_silo_name");
            deleteCookie("chart_podv_num");
            deleteCookie("chart_sensor_num");
            deleteCookie("chart_period");

            vRep_addNewTableRow();

        }
    });
}
//  Добавление строки в таблицу
function vRep_addNewTableRow() {

    //  Отключаем элементы на последней строке
    let inputs = document.getElementById("rep-chart-time-temperature").getElementsByTagName('input');
    let selects = document.getElementById("rep-chart-time-temperature").getElementsByTagName('select');

    selects.item(selects.length - 4).disabled = true;
    selects.item(selects.length - 3).disabled = true;
    selects.item(selects.length - 2).disabled = true;
    inputs.item(inputs.length - 1).disabled = true;
    selects.item(selects.length - 1).disabled = true;

    row_num = +selects.item(selects.length - 4).id.split("_")[2] + 1; //  Номер строки. Вычисляем для присваивания нового id элементам

    //  получаем доступ к tbody
    let tbody = document.getElementById("rep-chart-time-temperature").getElementsByTagName("tbody")[0];
    //  создаем новую строку
    let row = document.createElement("tr");
    //  создаем столбцы
    let td1 = document.createElement("td");
    let input_silo_num = document.createElement("select");
    input_silo_num.setAttribute("id", "rprtchart_silo_" + row_num);
    input_silo_num.setAttribute("onchange", "redrawRowOfSelects(event.target.id)");
    input_silo_num.className = "form-control";
    td1.appendChild(input_silo_num);

    let td2 = document.createElement("TD");
    let input_podv_num = document.createElement("select");
    input_podv_num.setAttribute("id", "rprtchart_podv_" + row_num);
    input_podv_num.setAttribute("onchange", "redrawRowOfSelects(event.target.id)");
    input_podv_num.className = "form-control";
    td2.appendChild(input_podv_num);

    let td3 = document.createElement("TD");
    var input_sensor_num = document.createElement("select");
    input_sensor_num.setAttribute("id", "rprtchart_sensor_" + row_num);
    input_sensor_num.className = "form-control";
    td3.appendChild(input_sensor_num);
    //  Новый цвет выбирается случайным образом
    let td4 = document.createElement("TD");
    let input_color = document.createElement("input");
    input_color.type = "color";
    let colour_value = "";
    for (let i = 0; i < 3; i++) {
        if (i == 0) {
            colour_value += "#";
        }
        const current_colour = Math.floor(Math.random() * 256).toString(16);
        if (current_colour.length < 2) {
            colour_value += "0" + current_colour;
        } else {
            colour_value += current_colour;
        }
        if (i == 2) {
            input_color.setAttribute("value", colour_value);
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

    //  Производим инициализацию элементов select
    setSelectOptions(input_silo_num, Object.keys(project_conf_array));
    setSelectOptions(input_podv_num, Object.keys(project_conf_array[silo_name_with_id_0]));
    setSelectOptions(input_sensor_num, Object.keys(project_conf_array[silo_name_with_id_0][1]));

    return;
}
//  Chart JS
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


//  !!!!!
//  !   Следует заменить на библиотеку PDF Make
//  Функции для сохранения графика в PDF
function vRep_Convert() {
    //По нажатию на кнопку получаем канвас
    var canvas = document.getElementById('myChart');
    // И создаем из него картиику в base64
    var quality = 1; // качество от 0 до 1, заодно и сжать можно
    var myImage = {
        data: canvas.toDataURL('image/png', quality),
        height: canvas.height,
        width: canvas.width
    };
    // теперь из картинки делаем PDF
    vRep_createPDF(myImage);
}
//image - должен иметь свойста height,width и data - хранит картинку в base64
function vRep_createPDF(image) {
    let w = vRep_ConvertPxToMM(image.width);
    let h = vRep_ConvertPxToMM(image.height);
    var orientation = w > h ? 'l' : 'p';

    //Создаем документ PDF размером с нашу картинку
    var docPDF = new jsPDF(orientation, 'mm', [w, h]);
    //рисуем картинку на всю страницу

    docPDF.addImage(image.data, 'PNG', 0, 0);


    //Сохраням полученный файл
    //Возможные значения : dataurl, datauristring, bloburl, blob, arraybuffer, ('save', filename)
    docPDF.output('save', 'График температуры.pdf');
}
function vRep_ConvertPxToMM(pixels) {
    return Math.floor(pixels * 0.264583);
}

//  Печатные формы ---------------------------------------------------------------------------------------------------------------------------------------
function vRep_rprtprf_checkDatesAndBlockDownloadButtons(){

    if(vRep_rprtprf_getArrayOfDates().length==0){
        document.getElementById("rprtprf-btn-download-PDF").disabled = true;
        document.getElementById("rprtprf-btn-download-XLS").disabled = true;
    } else {
        document.getElementById("rprtprf-btn-download-PDF").disabled = false;
        document.getElementById("rprtprf-btn-download-XLS").disabled = false;
    }

    return;
}

//  Получение массивов входных данных для создания печатных форм ---------------------------
//  Получение массива дат
function vRep_rprtprf_getArrayOfDates(){

    let arrayOfDates = [];

    let dateCheckboxes = document.getElementsByTagName("input");
    const re = /\w+_(\d{4}-\d{2}-\d{2}_\d{2}:\d{2}:\d{2})/;

    for (let i = 0; i < dateCheckboxes.length; i++) {
        if (dateCheckboxes[i].id.match(re)) {
            if (dateCheckboxes[i].checked) {
                arrayOfDates.push(dateCheckboxes[i].id.match(re)[1].replace('_', ' '));
            }
        }
    }

    return arrayOfDates;
}
//  Получение массива силосов
function vRep_rprtprf_getArrayOfSilo(){

    let arrayOfSilo = [];
    let currSilo = document.getElementById("rprtprf_silo_1");

    if (currSilo.value === "all") {
        for (let i = 0; i < currSilo.options.length; i++) {
            if (currSilo.options[i].value === "all") {
                continue;
            }
            arrayOfSilo.push(currSilo.options[i].value);
        }
    } else {
        arrayOfSilo.push(currSilo.value);
    }

    return arrayOfSilo;
}
//  Получение массива подвесок
function vRep_rprtprf_getArrayOfPodv(){

    let arrayOfPodvs = [];
    let currPodv = document.getElementById("rprtprf_podv_1");

    if (currPodv.value === "all") {
        for (let i = 0; i < currPodv.options.length; i++) {
            if (currPodv.options[i].value === "all") {
                continue;
            }
            arrayOfPodvs.push(currPodv.options[i].value);
        }
    } else {
        arrayOfPodvs.push(currPodv.value);
    }

    return arrayOfPodvs;
}
//  Получение массива датчиков
function vRep_rprtprf_getArrayOfSensors(){

    let arrayOfSensors = [];
    let currSensor = document.getElementById("rprtprf_sensor_1");

    if (currSensor.value === "all") {
        for (let i = 0; i < currSensor.options.length; i++) {
            if (currSensor.options[i].value === "all") {
                continue;
            }
            arrayOfSensors.push(currSensor.options[i].value);
        }
    } else {
        arrayOfSensors.push(currSensor.value);
    }

    return arrayOfSensors;
}
//  Получение массива слоев
function vRep_rprtprf_getArrayOfLayers(){
    
    let arrayOfLayers = [];
    let currLayer = document.getElementById("rprtprf_layer_1");

    if (currLayer.value === "all") {
        for (let i = 0; i < currLayer.options.length; i++) {
            if (currLayer.options[i].value === "all") {
                continue;
            }
            arrayOfLayers.push(currLayer.options[i].value);
        }
    } else {
        arrayOfLayers.push(currLayer.value);
    }

    return arrayOfLayers;
}

//  Функции для сохранения печатных форм в формате PDF и XLSX ---------------------------------
//  Функиця для получение JSON-объекта из PHP
function vRep_getJSONForPrintedForms(fileFormat){

    const arrayOfDates = vRep_rprtprf_getArrayOfDates();

    if (document.getElementById("prfrb_avg-t-by-layer").checked) {

        const arrayOfSilo = vRep_rprtprf_getArrayOfSilo();
        const arrayOfLayers = vRep_rprtprf_getArrayOfLayers();

        $.ajax({
            url: 'visualisation/visu_report.php',
            type: 'POST',
            cache: false,
            data: { 'POST_vRep_getAvgTemperByLayer_arrayOfSilos': arrayOfSilo,
                    'POST_vRep_getAvgTemperByLayer_arrayOfLayers': arrayOfLayers,
                    'POST_vRep_getAvgTemperByLayer_arrayOfDates': arrayOfDates },
            dataType: 'html',
            success: function(fromPHP) {
                const field1 = 'date';
                const field2 = 'silo';
                const field3 = '';
                const field4 = 'layerTemperatures';
                const sheetHeader = 'Данные о средних температурах по слоям';
                if(fileFormat==="PDF"){
                    createPrintedFormPDF(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                } else if (fileFormat==="XLSX"){
                    creatPrintedFormXLSX(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                }
            }
        });

    } else if (document.getElementById("prfrb_t-by-layer").checked) {

        const arrayOfSilo = vRep_rprtprf_getArrayOfSilo();
        const arrayOfLayers = vRep_rprtprf_getArrayOfLayers();

        $.ajax({
            url: 'visualisation/visu_report.php',
            type: 'POST',
            cache: false,
            data: { 'POST_vRep_getSensorTemperByLayer_arrayOfSilos': arrayOfSilo,
                    'POST_vRep_getSensorTemperByLayer_arrayOfLayers': arrayOfLayers,
                    'POST_vRep_getSensorTemperByLayer_arrayOfDates': arrayOfDates },
            dataType: 'html',
            success: function(fromPHP) {
                const field1 = 'date';
                const field2 = 'silo';
                const field3 = 'layer';
                const field4 = 'sensorTemperatures';
                const sheetHeader = 'Данные о температуре каждого датчика в слоях';
                if(fileFormat==="PDF"){
                    createPrintedFormPDF(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                } else if (fileFormat==="XLSX"){
                    creatPrintedFormXLSX(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                }
            }
        });

    } else if (document.getElementById("prfrb_t-by-sensor").checked) {

        const arrayOfSilo = vRep_rprtprf_getArrayOfSilo();
        const arrayOfPodvs = vRep_rprtprf_getArrayOfPodv();
        const arrayOfSensors = vRep_rprtprf_getArrayOfSensors();

        $.ajax({
            url: 'visualisation/visu_report.php',
            type: 'POST',
            cache: false,
            data: { 'POST_vRep_getSensorTemperByPodv_arrayOfSilos': arrayOfSilo,
                    'POST_vRep_getSensorTemperByPodv_arrayOfPodv': arrayOfPodvs,
                    'POST_vRep_getSensorTemperByPodv_arrayOfSensors': arrayOfSensors,
                    'POST_vRep_getSensorTemperByPodv_arrayOfDates': arrayOfDates },
            dataType: 'html',
            success: function(fromPHP) {
                const field1 = 'date';
                const field2 = 'silo';
                const field3 = 'podv';
                const field4 = 'sensorTemperatures';
                const sheetHeader = 'Данные о температурах каждого датчика в подвеске';
                if(fileFormat==="PDF"){
                    createPrintedFormPDF(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                } else if (fileFormat==="XLSX"){
                    creatPrintedFormXLSX(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                }
            }
        });

    }

    return;
}

//  Создание объекта с базовыми свойствами PDF-документа
function vRep_createBasicPDFPropStructure(){

    let pdfProp = {};
    pdfProp.pageSize = "a4";
    pdfProp.pageOrientation = "landscape";
    pdfProp.pageMargins = [20,30,20,20];
    pdfProp.styles = {};
    pdfProp.styles.header = {};
    pdfProp.styles.header.fontSize = 18;
    pdfProp.styles.header.bold = true;
    pdfProp.styles.header.alignment = "center";
    pdfProp.styles.header.margin = [0,0,0,10];
    pdfProp.content = [];

    return pdfProp;
}

function vRep_create2dTableForCurrDate(JSONObj, field, tableHeader, col1Header, col2Header){

    let outArr=[];
    outArr[0]=[{text:  tableHeader, style: 'tableHeader', colSpan: 2, alignment: 'center'},{}];
    outArr.push( [col1Header, col2Header] );

    for(let i=0; i<JSONObj[field].length; i++){
        outArr.push( [i+1, JSONObj[field][i][i+1]] );
    }

    return outArr;
}

function createPrintedFormPDF(JSONObj, field1, field2, field3, field4, sheetHeader){

    //  console.log(JSONObj);

    let pdfProp = vRep_createBasicPDFPropStructure();
    let field; let col1Header; let col2Header; let tableHeader;

    if (sheetHeader==="Данные о средних температурах по слоям") {
        field = field4;
        col1Header = "Слой №";
        col2Header = "Средняя\nтемпература";
    } else if (sheetHeader==="Данные о температуре каждого датчика в слоях"){
        field = field4;
        col1Header = "Подв. №";
        col2Header = "Температура";
    } else if (sheetHeader==="Данные о температурах каждого датчика в подвеске"){
        field = field4;
        col1Header = "Дат. №";
        col2Header = "Температура";
    }

    let j=-1;
    for(let i=0; i<JSONObj.length; i++){

        const currJSONObj = JSONObj[i];

        if (sheetHeader==="Данные о средних температурах по слоям") {
            tableHeader = "Силос " + currJSONObj[field2]+"\n"+ "Дата: " + currJSONObj[field1].split(" ")[0] + "\n" + currJSONObj[field1].split(" ")[1];
        } else if (sheetHeader==="Данные о температуре каждого датчика в слоях"){
            tableHeader = "Силос " + currJSONObj[field2]+"\n"+ "Дата: " + currJSONObj[field1].split(" ")[0] + "\n" + currJSONObj[field1].split(" ")[1] + "\n" + "Слой " + currJSONObj[field3];
        } else if (sheetHeader==="Данные о температурах каждого датчика в подвеске"){
            tableHeader = "Силос " + currJSONObj[field2]+"\n"+ "Дата: " + currJSONObj[field1].split(" ")[0] + "\n" + currJSONObj[field1].split(" ")[1] + "\n" + "Подвеска " + currJSONObj[field3];
        }
        
        if(i==0 || (i%6==0)){
            j++;
            pdfProp.content.push( {text: sheetHeader, style: 'header', alignment: 'center'} );
            j++;
            pdfProp.content.push( {pageBreak: 'after', layout: 'noBorders', table: {} } );
            pdfProp.content[j].table = {body:[[  ]]};

            pdfProp.content[j].table.body[0] = [ { table:{ body: vRep_create2dTableForCurrDate(currJSONObj, field, tableHeader, col1Header, col2Header) } } ];

            continue;
        }
        pdfProp.content[j].table.body[0].push(   { table:{ body: vRep_create2dTableForCurrDate(currJSONObj, field, tableHeader, col1Header, col2Header) } } );
        if(i==JSONObj.length-1){
            pdfProp.content[j].pageBreak = "";
        }
    }

    createPdf( pdfProp ).open();

    return;
}

function creatPrintedFormXLSX(JSONObj, field1, field2, field3, field4, sheetHeader){


    //vRep_createXLSX(JSONObj);

    return;
}

//  Создание структуры XLSX-документа
function vRep_createXLSX(pdfProbObj){
    var wb = XLSX.utils.book_new();
    wb.Props = {
            Title: "SheetJS Tutorial",
            Subject: "Test",
            Author: "NE",
            CreatedDate: new Date(2017,12,19)
    };

    wb.SheetNames.push("New");


    let currentCol = 0; let currentRow = 1;
    /* Initial row */
    var ws = XLSX.utils.json_to_sheet([ { A: pdfProbObj.content[0].text } ], {header: ["A"], skipHeader: true});

    for(let i=0; i<pdfProbObj.content.length; i++ ){
        
        if(pdfProbObj.content[i].table){
            for(let j=0; j<pdfProbObj.content[i].table.body[0].length; j++){

                for(let k=0; k<pdfProbObj.content[i].table.body[0][j].table.body.length; k++){

                    let cellContent;
                    if(k==0){
                        cellContent = pdfProbObj.content[i].table.body[0][j].table.body[k][0]['text'].split("\n");
                        console.log(cellContent)
                    } else {
                        cellContent = pdfProbObj.content[i].table.body[0][j].table.body[k];
                    }

                    XLSX.utils.sheet_add_json(ws, [
                        cellContent
                    ], {skipHeader: true, origin: {c:currentCol, r:currentRow} });

                    currentRow++;

                }

                currentCol +=3; currentRow = 1;

            }
        }

    }

    wb.Sheets["New"] = ws;

    vRep_saveXLSX(wb);
    return;
}
//  Сохранение документа
function vRep_saveXLSX(wb){

    var wbout = XLSX.write(wb, {bookType:'xlsx',  type: 'binary'});
    function s2ab(s) {
            var buf = new ArrayBuffer(s.length);
            var view = new Uint8Array(buf);
            for (var i=0; i<s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
            return buf;
    }

    saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}), 'test.xlsx');

    return;
}