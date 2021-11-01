let curr_user;
const current_page = window.location.pathname.split("/").pop() === "" ? "index.php" : window.location.pathname.split("/").pop();
const mainTimerPeriod = 10000;
const mainTimer = setInterval(periodicActions, mainTimerPeriod);
let alarmsNACKNumber = 0;
let serverDateTime;
let silo_names_array = [];
let project_conf_array = [];
let silo_name_with_id_0;
let silo_name_with_max_podv_number;

//  Действия при загрузке каждой страницы -------------------------------------------------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    authGetCurrentUser();                               //  Запрашиваем текущего пользователя из сессии
    getNewAlarmsNumber();                               //  Проверяем наличие новых алармов, чтобы в случае необходимости включить звук
    if (current_page === "index.php") {
        init_index();
    } else if (current_page === "report.php"){
        getConf_ProjectConfArr();                       //  Если мы на странице с множеством выпадающих элементов, сначала получаем конфигурационные массивы
    } else if (current_page === "debug_page.php") {
        getConf_ProjectConfArr();
    } else if (current_page === "silo_config.php") {
        init_silo_config();
    } else if (current_page === "instruction.php") {
        init_instruction();
    }

});

//  Работа с cookie
function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function deleteCookie(name) {
    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
};

//  Получение массивов с конфигурациями для повышения интерактивности ---------------------------------------------------------------------------------------
//  Получение массива с именами силосов для быстрого отображения названия силоса на главной странице
function getConf_ArrayOfSiloNames() {
    $.ajax({
        url: '/webTermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_silo_names_array': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            //  массив с названиями силосов, в котором индекс - это silo_id, а значение - название силоса
            silo_names_array = (JSON.parse(fromPHP));
            //  делаем активным силос с silo_id==0
            document.getElementById("current-silo-name").innerHTML = "Силос " + silo_names_array[0];
        }
    });
    return;
}
//  Получение главного конфигурационного массива [[массив с именами (при этом индекс элемента - это id силоса)],[массив с подвесками],[массив с датчиками]]
function getConf_ProjectConfArr() {
    $.ajax({
        url: '/webTermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_project_conf_array': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            project_conf_array = (JSON.parse(fromPHP));
            console.log(project_conf_array);
            getConf_SiloNameWithID0();
        }
    });
}
//  Получение названия силоса с id==0. Необходимо для страницы "Отчет" в сайтбаре для построения графиков
function getConf_SiloNameWithID0() {
    $.ajax({
        url: '/webTermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_silo_name_with_id_0': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            silo_name_with_id_0 = (JSON.parse(fromPHP));
            getConf_SiloNameWithMaxPodvNumber();
        }
    });
    return;
}
//  Получение массива с максимальным количеством подвесок. Необходимо для страницы "Отчет" в сайтбаре с печатными формами
function getConf_SiloNameWithMaxPodvNumber() {
    $.ajax({
        url: '/webTermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_silo_number_with_max_podv_number': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            silo_name_with_max_podv_number = (JSON.parse(fromPHP));
            if (current_page === "report.php") {                        //  после получения всех необходимых массивов можно переходить к инициализации
                init_report();
            } else if (current_page === "debug_page.php") {
                init_debug_page();
            }
        }
    });
    return;
}

//  Работа с элементами select -------------------------------------------------------------------------------------------------------------------------------
//  Функция для установки аттрибутов option элемента select
function setSelectOptions(dom_element, options_arr) {
    while (dom_element.options.length) {
        dom_element.remove(0);
    }
    options_arr.forEach(curr_option => {
        if (curr_option === "all") {
            dom_element.add(new Option("все", "all"));
        } else {
            dom_element.add(new Option(curr_option, curr_option));
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
function redrawRowOfSelects(select_element_id) {

    //  Парсим id элемента
    const page = select_element_id.split("_")[0];
    const element_name = select_element_id.split("_")[1];
    const row_number = select_element_id.split("_")[2];

    const current_silo = document.getElementById(page + "_silo_" + row_number);

    //  В зависимости от того, пренадлежит ли элемент к печатным формам или нет, в нем будет присутствовать или отсутствовать пункт "Все"
    const opt_0 = current_silo.options[0].value === "all" ? ["all"] : [];                               /*глобальная переменная*/
    const current_silo_selected_ind = current_silo.options[current_silo.selectedIndex].value === "all" ? silo_name_with_max_podv_number : current_silo.options[current_silo.selectedIndex].value;

    let element_podv = document.getElementById(page + "_podv_" + row_number);
    let element_sensor = document.getElementById(page + "_sensor_" + row_number);
    let element_level = document.getElementById(page + "_level_" + row_number);
    let element_layer = document.getElementById(page + "_layer_" + row_number);

    if (element_name === "silo") {
        if (element_podv) {
            setSelectOptions(element_podv, opt_0.concat(Object.keys(project_conf_array[current_silo_selected_ind])));
        }
        if (element_sensor) {
            setSelectOptions(element_sensor, opt_0.concat(Object.keys(project_conf_array[current_silo_selected_ind][1])));
        }
        if (element_layer) {
            setSelectOptions(element_layer, opt_0.concat(Object.keys(project_conf_array[current_silo_selected_ind][1])));
        }
        if (element_level) { //  только для страницы отладки
            setSelectOptions(element_level, [0].concat(Object.keys(project_conf_array[current_silo_selected_ind][1])));
        }
    }

    if (element_name === "podv") {
        const current_podv = document.getElementById(page + "_podv_" + row_number);
        const current_podv_selected_ind = current_podv.options[current_podv.selectedIndex].value === "all" ? 1 : current_podv.options[current_podv.selectedIndex].value;

        if (element_sensor) {
            setSelectOptions(element_sensor, opt_0.concat(Object.keys(project_conf_array[current_silo_selected_ind][current_podv_selected_ind])));
        }
        if (element_layer) {
            setSelectOptions(element_layer, opt_0.concat(Object.keys(project_conf_array[current_silo_selected_ind][current_podv_selected_ind])));
        }
    }

    return;
}

//  Работа с АПС ---------------------------------------------------------------------------------------------------------------------------------------------
function controlAudio(OnOff){
    if(OnOff===1){
        document.getElementById("alarm-sound").loop = true;     //  Включаем звук
        document.getElementById("alarm-sound").play();
        $('#hdr-ack').removeClass("text-black");                //  Подсвечиваем кнопку квитирования
        $('#hdr-ack').addClass("text-primart");
    } else {
        document.getElementById("alarm-sound").loop = false;
        document.getElementById("alarm-sound").pause();
        $('#hdr-ack').removeClass("text-primary");
        $('#hdr-ack').addClass("text-black");
    }
    return;
}

function getNewAlarmsNumber() {

    $.ajax({
        url: '/webTermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_number_of_new_alarms': 1 },
        dataType: 'html',
        success: function(fromPHP) {

            if (fromPHP > alarmsNACKNumber) {           //  Если появились неквитированные алармы
                controlAudio(1);                        //  Включаем звук
            }

            alarmsNACKNumber = fromPHP;

            if (current_page === "index.php") {
                vIndRedrawTableCurrentAlarms();         //  Перерисовываем таблицу с текущими алармами
                vIndRedrawSiloStatus();                 //  Показываем текущий статус каждого силоса
                vIndOnClickOnSilo(lastSiloID);          //  Перерисовываем таблицу с текущими показаниями
            }
            
        }
    });
    return;
}

function alarmsAck() {

    $.ajax({
        url: '/webTermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_acknowledge_alarms': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            controlAudio(0);            //  Выключаем звук
            getNewAlarmsNumber();       //  Проверяем появление новых алармов
        }
    });
    return;
}

function periodicActions() {
    getNewAlarmsNumber();           //  Проверяем появление новых алармов, а заодно выполняем считывание текущих показаний и занесение их в БД
}
