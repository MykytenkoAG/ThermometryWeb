function init_report() {

    document.getElementById("hdr-href-report.php").setAttribute("class", "nav-link text-primary");
    //  Блокировка кнопок "Скачать PDF" и "Скачать XLSX"
    vRep_rprtprf_checkDatesAndBlockDownloadButtons();
    //  Инициализация элементов select печатных форм
    setSelectOptions(document.getElementById("rprtprf_silo_1"), ["all"].concat(silo_names_array));
    redrawRowOfSelects("rprtprf_silo_1");
    //  Отключены элементов select в зависимости от положения радиокнопки
    vRep_prfSelectsDisable();

    //  Инициализация элементов select графика температуры
    let selects = document.getElementById("rep-chart-time-temperature").getElementsByTagName('select');
    let chart_silo_1 = selects.item(selects.length - 4);
    setSelectOptions(chart_silo_1, silo_names_array);
    redrawRowOfSelects(chart_silo_1.id);

    //  Построение графика, в случае если мы попали на эту страницу из главной
    const chart_silo_name = getCookie("chart_silo_name");
    const chart_podv_num = getCookie("chart_podv_num");
    const chart_sensor_num = getCookie("chart_sensor_num");
    const chart_period = getCookie("chart_period");
    if (chart_silo_name != "" && chart_silo_name != null &&
        chart_podv_num != "" && chart_podv_num != null &&
        chart_sensor_num != "" && chart_sensor_num != null &&
        chart_period != "" && chart_period != null) {

        selects.item(selects.length - 4).value = chart_silo_name;
        selects.item(selects.length - 3).value = chart_podv_num;
        selects.item(selects.length - 2).value = chart_sensor_num;
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
        url: 'visu_report.php',
        type: 'POST',
        cache: false,
        data: {
            'POST_vRep_getTableForChart_silo_name': silo_name,
            'POST_vRep_getTableForChart_podv_id': podv_id - 1,
            'POST_vRep_getTableForChart_sensor_num': sensor_num - 1,
            'POST_vRep_getTableForChart_period': period
        },
        dataType: 'html',
        success: function(fromPHP) {

            let newDataset = {
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

            temperatureGraph.data.datasets.push(newDataset);
            temperatureGraph.update();

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
    setSelectOptions(input_silo_num, silo_names_array);
    redrawRowOfSelects(input_silo_num.id);

    return;
}
//  Chart JS
//  setup
let data = {
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
let temperatureGraph = new Chart(document.getElementById('temperatureGraph'), config);


//  Функции для сохранения графика в PDF
function vRep_Convert() {
    //По нажатию на кнопку получаем канвас
    var canvas = document.getElementById('temperatureGraph');

    let pdfProp = vRep_createBasicPDFPropStructure();
    //console.log(pdfProp);
    pdfProp.pageMargins = [20, 60, 20, 20];
    pdfProp.content.push({
        image: canvas.toDataURL("img/silo_round_alarm.png"),
        width: 800
    });
    createPdf(pdfProp).open();
}

//  Печатные формы ---------------------------------------------------------------------------------------------------------------------------------------
function vRep_rprtprf_checkDatesAndBlockDownloadButtons() {

    if (vRep_rprtprf_getArrayOfDates().length == 0) {
        document.getElementById("rprtprf-btn-download-PDF").disabled = true;
        document.getElementById("rprtprf-btn-download-XLS").disabled = true;
    } else {
        document.getElementById("rprtprf-btn-download-PDF").disabled = false;
        document.getElementById("rprtprf-btn-download-XLS").disabled = false;
    }

    return;
}

//  Получение массивов входных данных для создания печатных форм
//  Получение массива дат
function vRep_rprtprf_getArrayOfDates() {

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
function vRep_rprtprf_getArrayOfSilo() {

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
function vRep_rprtprf_getArrayOfPodv() {

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
function vRep_rprtprf_getArrayOfSensors() {

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
function vRep_rprtprf_getArrayOfLayers() {

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

//  Функции для сохранения печатных форм в формате PDF и XLSX
//  Функиця для получение JSON-объекта из PHP
function vRep_getJSONForPrintedForms(fileFormat) {

    const arrayOfDates = vRep_rprtprf_getArrayOfDates();

    if (document.getElementById("prfrb_avg-t-by-layer").checked) {

        const arrayOfSilo = vRep_rprtprf_getArrayOfSilo();
        const arrayOfLayers = vRep_rprtprf_getArrayOfLayers();

        $.ajax({
            url: 'visu_report.php',
            type: 'POST',
            cache: false,
            data: {
                'POST_vRep_getAvgTemperByLayer_arrayOfSilos': arrayOfSilo,
                'POST_vRep_getAvgTemperByLayer_arrayOfLayers': arrayOfLayers,
                'POST_vRep_getAvgTemperByLayer_arrayOfDates': arrayOfDates
            },
            dataType: 'html',
            success: function(fromPHP) {
                const field1 = 'date';
                const field2 = 'silo';
                const field3 = '';
                const field4 = 'layerTemperatures';
                const sheetHeader = 'Данные о средних температурах по слоям';
                if (fileFormat === "PDF") {
                    createPrintedFormPDF(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                } else if (fileFormat === "XLSX") {
                    creatPrintedFormXLSX(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                }
            }
        });

    } else if (document.getElementById("prfrb_t-by-layer").checked) {

        const arrayOfSilo = vRep_rprtprf_getArrayOfSilo();
        const arrayOfLayers = vRep_rprtprf_getArrayOfLayers();

        $.ajax({
            url: 'visu_report.php',
            type: 'POST',
            cache: false,
            data: {
                'POST_vRep_getSensorTemperByLayer_arrayOfSilos': arrayOfSilo,
                'POST_vRep_getSensorTemperByLayer_arrayOfLayers': arrayOfLayers,
                'POST_vRep_getSensorTemperByLayer_arrayOfDates': arrayOfDates
            },
            dataType: 'html',
            success: function(fromPHP) {
                const field1 = 'date';
                const field2 = 'silo';
                const field3 = 'layer';
                const field4 = 'sensorTemperatures';
                const sheetHeader = 'Данные о температуре каждого датчика в слоях';
                if (fileFormat === "PDF") {
                    createPrintedFormPDF(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                } else if (fileFormat === "XLSX") {
                    creatPrintedFormXLSX(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                }
            }
        });

    } else if (document.getElementById("prfrb_t-by-sensor").checked) {

        const arrayOfSilo = vRep_rprtprf_getArrayOfSilo();
        const arrayOfPodvs = vRep_rprtprf_getArrayOfPodv();
        const arrayOfSensors = vRep_rprtprf_getArrayOfSensors();

        $.ajax({
            url: 'visu_report.php',
            type: 'POST',
            cache: false,
            data: {
                'POST_vRep_getSensorTemperByPodv_arrayOfSilos': arrayOfSilo,
                'POST_vRep_getSensorTemperByPodv_arrayOfPodv': arrayOfPodvs,
                'POST_vRep_getSensorTemperByPodv_arrayOfSensors': arrayOfSensors,
                'POST_vRep_getSensorTemperByPodv_arrayOfDates': arrayOfDates
            },
            dataType: 'html',
            success: function(fromPHP) {
                const field1 = 'date';
                const field2 = 'silo';
                const field3 = 'podv';
                const field4 = 'sensorTemperatures';
                const sheetHeader = 'Данные о температурах каждого датчика в подвеске';
                if (fileFormat === "PDF") {
                    createPrintedFormPDF(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                } else if (fileFormat === "XLSX") {
                    creatPrintedFormXLSX(JSON.parse(fromPHP), field1, field2, field3, field4, sheetHeader);
                }
            }
        });

    }

    return;
}

//  Создание объекта с базовыми свойствами PDF-документа
function vRep_createBasicPDFPropStructure() {

    let pdfProp = {};
    pdfProp.pageSize = "a4";
    pdfProp.pageOrientation = "landscape";
    pdfProp.pageMargins = [20, 30, 20, 20];
    pdfProp.styles = {};
    pdfProp.styles.header = {};
    pdfProp.styles.header.fontSize = 18;
    pdfProp.styles.header.bold = true;
    pdfProp.styles.header.alignment = "center";
    pdfProp.styles.header.margin = [0, 0, 0, 10];
    pdfProp.content = [];

    return pdfProp;
}

//  Функция создания двухмерного массива для текущей даты, который можно будет вставить в PDF и XLSX документ
function vRep_create2dTableForCurrDate(JSONObj, field, tableHeader, col1Header, col2Header) {

    let outArr = [];
    outArr[0] = [{ text: tableHeader, style: 'tableHeader', colSpan: 2, alignment: 'center' }, {}];
    outArr.push([col1Header, col2Header]);

    for (let i = 0; i < JSONObj[field].length; i++) {
        const currKey = Object.keys(JSONObj[field][i])[0];
        outArr.push([currKey, JSONObj[field][i][currKey]]);
    }

    return outArr;
}

function createPrintedFormPDF(JSONObj, field1, field2, field3, field4, sheetHeader) {

    //  console.log(JSONObj);

    let pdfProp = vRep_createBasicPDFPropStructure();
    let field;
    let col1Header;
    let col2Header;
    let tableHeader;

    if (sheetHeader === "Данные о средних температурах по слоям") {
        field = field4;
        col1Header = "Слой №";
        col2Header = "Средняя\nтемпература";
    } else if (sheetHeader === "Данные о температуре каждого датчика в слоях") {
        field = field4;
        col1Header = "Подв. №";
        col2Header = "Температура";
    } else if (sheetHeader === "Данные о температурах каждого датчика в подвеске") {
        field = field4;
        col1Header = "Дат. №";
        col2Header = "Температура";
    }

    let j = -1;
    for (let i = 0; i < JSONObj.length; i++) {

        const currJSONObj = JSONObj[i];

        if (sheetHeader === "Данные о средних температурах по слоям") {
            tableHeader = "Силос " + currJSONObj[field2] + "\n" + "Дата: " + currJSONObj[field1].split(" ")[0] + "\n" + currJSONObj[field1].split(" ")[1];
        } else if (sheetHeader === "Данные о температуре каждого датчика в слоях") {
            tableHeader = "Силос " + currJSONObj[field2] + "\n" + "Дата: " + currJSONObj[field1].split(" ")[0] + "\n" + currJSONObj[field1].split(" ")[1] + "\n" + "Слой " + currJSONObj[field3];
        } else if (sheetHeader === "Данные о температурах каждого датчика в подвеске") {
            tableHeader = "Силос " + currJSONObj[field2] + "\n" + "Дата: " + currJSONObj[field1].split(" ")[0] + "\n" + currJSONObj[field1].split(" ")[1] + "\n" + "Подвеска " + currJSONObj[field3];
        }

        if (i == 0 || (i % 6 == 0)) {
            j++;
            pdfProp.content.push({ text: sheetHeader, style: 'header', alignment: 'center' });
            j++;
            pdfProp.content.push({ pageBreak: 'after', layout: 'noBorders', table: {} });
            pdfProp.content[j].table = {
                body: [
                    []
                ]
            };

            //pdfProp.content[j].table.body[0] = [ { table:{ body: vRep_create2dTableForCurrDate(currJSONObj, field, tableHeader, col1Header, col2Header) } } ];
            //continue;
        }
        pdfProp.content[j].table.body[0].push({ table: { body: vRep_create2dTableForCurrDate(currJSONObj, field, tableHeader, col1Header, col2Header) } });
        if (i == JSONObj.length - 1) {
            pdfProp.content[j].pageBreak = "";
        }
    }

    createPdf(pdfProp).open();

    return;
}

//  Сохранение XLSX-файла
function vRep_saveXLSX(wb, sheetHeader) {

    var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });

    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
        return buf;
    }

    saveAs(new Blob([s2ab(wbout)], { type: "application/octet-stream" }), (sheetHeader + '.xlsx'));

    return;
}

function creatPrintedFormXLSX(JSONObj, field1, field2, field3, field4, sheetHeader) {

    var wb = XLSX.utils.book_new();
    wb.Props = {
        Title: "Printed forms",
        Subject: "Printed form",
        Author: "NE",
        CreatedDate: new Date(2021, 10, 31)
    };

    //  Координаты текущей ячейки
    let currentCol = 0;
    let currentRow = 0;

    //  Заголовок таблицы
    let ws;

    let field;
    let col1Header;
    let col2Header;
    let tableHeader;
    let currentSheet;

    //  Переменные для функции vRep_create2dTableForCurrDate()
    if (sheetHeader === "Данные о средних температурах по слоям") {
        field = field4;
        col1Header = "Слой №";
        col2Header = "Средняя\nтемпература";
    } else if (sheetHeader === "Данные о температуре каждого датчика в слоях") {
        field = field4;
        col1Header = "Подв. №";
        col2Header = "Температура";
    } else if (sheetHeader === "Данные о температурах каждого датчика в подвеске") {
        field = field4;
        col1Header = "Дат. №";
        col2Header = "Температура";
    }

    for (let i = 0; i < JSONObj.length; i++) {

        const currJSONObj = JSONObj[i];

        if (currentSheet != ("Силос " + currJSONObj[field2])) {
            if (i > 0) {
                wb.Sheets[currentSheet] = ws;
            }
            currentSheet = ("Силос " + currJSONObj[field2]);
            if (wb.SheetNames.indexOf(currentSheet) == -1) {
                //  Создание нового листа
                wb.SheetNames.push(currentSheet);
                //  Инициализация нового листа
                ws = XLSX.utils.json_to_sheet([{ A: "" }], { header: ["A"], skipHeader: true });
                currentCol = 0;
                currentRow = 0;
            } else {
                ws = wb.Sheets[currentSheet];
                currentCol = getXLSXLastColNumber(wb.Sheets[currentSheet]) + 5;
                currentRow = 0;
            }

        }

        if (sheetHeader === "Данные о средних температурах по слоям") {

            XLSX.utils.sheet_add_json(ws, [
                ["Дата: " + currJSONObj[field1].split(" ")[0]]
            ], { skipHeader: true, origin: { c: currentCol, r: currentRow } });

            currentRow++;

            XLSX.utils.sheet_add_json(ws, [
                [currJSONObj[field1].split(" ")[1]]
            ], { skipHeader: true, origin: { c: currentCol, r: currentRow } });

            currentRow++;

        } else if (sheetHeader === "Данные о температуре каждого датчика в слоях") {

            XLSX.utils.sheet_add_json(ws, [
                ["Дата: " + currJSONObj[field1].split(" ")[0]]
            ], { skipHeader: true, origin: { c: currentCol, r: currentRow } });

            currentRow++;

            XLSX.utils.sheet_add_json(ws, [
                [currJSONObj[field1].split(" ")[1]]
            ], { skipHeader: true, origin: { c: currentCol, r: currentRow } });

            currentRow++;

            XLSX.utils.sheet_add_json(ws, [
                ["Слой " + currJSONObj[field3]]
            ], { skipHeader: true, origin: { c: currentCol, r: currentRow } });

            currentRow++;

        } else if (sheetHeader === "Данные о температурах каждого датчика в подвеске") {

            XLSX.utils.sheet_add_json(ws, [
                ["Дата: " + currJSONObj[field1].split(" ")[0]]
            ], { skipHeader: true, origin: { c: currentCol, r: currentRow } });

            currentRow++;

            XLSX.utils.sheet_add_json(ws, [
                [currJSONObj[field1].split(" ")[1]]
            ], { skipHeader: true, origin: { c: currentCol, r: currentRow } });

            currentRow++;

            XLSX.utils.sheet_add_json(ws, [
                ["Подвеска " + currJSONObj[field3]]
            ], { skipHeader: true, origin: { c: currentCol, r: currentRow } });

            currentRow++;

        }

        const currDateTemperatures = vRep_create2dTableForCurrDate(currJSONObj, field, "", col1Header, col2Header);

        for (let j = 1; j < currDateTemperatures.length; j++) {

            XLSX.utils.sheet_add_json(ws, [
                currDateTemperatures[j]
            ], { skipHeader: true, origin: { c: currentCol, r: currentRow } });

            currentRow++;

        }

        currentCol += 3;
        currentRow = 0;



        if (i == JSONObj.length - 1) {
            wb.Sheets[currentSheet] = ws;
        }

    }

    vRep_saveXLSX(wb, sheetHeader);

    return;
}
//  Получение номера последнего заполненного столбца в текущем листе
function getXLSXLastColNumber(ws) {

    const XLSXCells = Object.keys(ws);

    let lastColNumber = 0;

    for (let i = 0; i < XLSXCells.length; i++) {

        if (XLSXCells[i][0] === "!") {
            continue;
        }

        const currColNumber = alphaToNum(XLSXCells[i].match(/[A-Za-z]+/)[0]);
        if (currColNumber > lastColNumber) {
            lastColNumber = currColNumber;
        }

    }

    return lastColNumber;
}

function alphaToNum(alpha) {

    var i = 0,
        num = 0,
        len = alpha.length;

    for (; i < len; i++) {
        num = num * 26 + alpha.charCodeAt(i) - 0x40;
    }

    return num - 1;

}