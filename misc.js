const current_page = window.location.pathname.split("/").pop() === "" ? "index.php" : window.location.pathname.split("/").pop();
const mainTimerPeriod = 10000;
const mainTimer = setInterval(() => { getNewAlarmsNumber(); }, mainTimerPeriod); //  Действия, которые выполняются каждые десять секунд
let curr_user;
let serverDateTime;
let alarmsNACKNumber = 0;
let project_conf_array = [];
let silo_names_array = [];
let silo_name_with_max_podv_number;

//  Работа с cookie -----------------------------------------------------------------------------------------------------------------------------------------
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

function modalWindows() {
    //  Главная страница. Произошло обновление проекта
    const project_was_updated = getCookie("popupProjectWasUpdated");
    if (project_was_updated === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Проект успешно обновлен. Приятного пользования!";
        $("#modal-info").modal('show');
        document.cookie = 'popupProjectWasUpdated=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Страница настроек. Файл TermoClient.ini не был загружен
    const popup_pjtUpdate_TermoClientIniWasNotUploaded = getCookie("popup_pjtUpdate_TermoClientIniWasNotUploaded");
    if (popup_pjtUpdate_TermoClientIniWasNotUploaded === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Файл TermoClient.ini не был загружен.";
        $("#modal-info").modal('show');
        document.cookie = 'popup_pjtUpdate_TermoClientIniWasNotUploaded=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Страница настроек. Файл TermoServer.ini не был загружен
    const popup_pjtUpdate_TermoServerIniWasNotUploaded = getCookie("popup_pjtUpdate_TermoServerIniWasNotUploaded");
    if (popup_pjtUpdate_TermoServerIniWasNotUploaded === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Файл TermoServer.ini не был загружен.";
        $("#modal-info").modal('show');
        document.cookie = 'popup_pjtUpdate_TermoServerIniWasNotUploaded=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Файл TermoServer.ini поврежден
    const popup_pjtUpdate_TermoServerIni_Damaged = getCookie("popup_pjtUpdate_TermoServerIni_Damaged");
    if (popup_pjtUpdate_TermoServerIni_Damaged === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Файл TermoServer.ini поврежден. Загрузите корректный файл или обратитесь к поставщику.";
        $("#modal-info").modal('show');
        document.cookie = 'popup_pjtUpdate_TermoServerIni_Damaged=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Файл TermoClient.ini поврежден
    const popup_pjtUpdate_TermoClientIni_Damaged = getCookie("popup_pjtUpdate_TermoClientIni_Damaged");
    if (popup_pjtUpdate_TermoClientIni_Damaged === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Файл TermoClient.ini поврежден. Загрузите корректный файл или обратитесь к поставщику.";
        $("#modal-info").modal('show');
        document.cookie = 'popup_pjtUpdate_TermoClientIni_Damaged=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Страница настроек. Файлы TermoClient.ini и TermoServer.ini не совместимы друг с другом
    const popup_pjtUpdate_IniFilesAreNotConsistent = getCookie("popup_pjtUpdate_IniFilesAreNotConsistent");
    if (popup_pjtUpdate_IniFilesAreNotConsistent === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Файлы TermoServer.ini и TermoClient.ini не совместимы друг с другом.";
        $("#modal-info").modal('show');
        document.cookie = 'popup_pjtUpdate_IniFilesAreNotConsistent=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Страница настроек. Настройки подключения к ПО Термосервер успшено применены
    const popupTSConnSettingsChanged = getCookie("popupTSConnSettingsChanged");
    if (popupTSConnSettingsChanged === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Настройки подключения к ПО Термосервер успешно применены.";
        $("#modal-info").modal('show');
        document.cookie = 'popupTSConnSettingsChanged=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Страница настроек. Вы выбрали файл неподдерживаемого формата
    const db_databaseBackupFile_unknownFormat = getCookie("db_databaseBackupFile_unknownFormat");
    if (db_databaseBackupFile_unknownFormat === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Вы выбрали файл неподдерживаемого формата";
        $("#modal-info").modal('show');
        document.cookie = 'db_databaseBackupFile_unknownFormat=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Страница настроек. Выбранный Вами файл не соответствует конфигурации базы данных текущего проекта
    const db_databaseBackupFile_is_Bad = getCookie("db_databaseBackupFile_is_Bad");
    if (db_databaseBackupFile_is_Bad === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Выбранный Вами файл не соответствует конфигурации базы данных текущего проекта";
        $("#modal-info").modal('show');
        document.cookie = 'db_databaseBackupFile_is_Bad=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Страница настроек. База данных успешно восстановлена
    const db_successfully_restored = getCookie("dbRestoredSuccessfully");
    if (db_successfully_restored === "OK") {
        document.getElementById("modal-info-body-message").innerText = "База данных успешно восстановлена";
        $("#modal-info").modal('show');
        document.cookie = 'dbRestoredSuccessfully=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    //  Страница настроек. Ошибка при загрузке файла на сервер
    const errorUploadingFile = getCookie("errorUploadingFile");
    if (errorUploadingFile === "OK") {
        document.getElementById("modal-info-body-message").innerText = "Ошибка при загрузке файла на сервер";
        $("#modal-info").modal('show');
        document.cookie = 'errorUploadingFile=;expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }
    return;
}

//  Действия при загрузке каждой страницы -------------------------------------------------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    modalWindows();
    authGetCurrentUser(); //  Запрашиваем текущего пользователя из сессии
    getNewAlarmsNumber(); //  Проверяем наличие новых алармов, чтобы в случае необходимости включить звук
    getConf_ProjectConfArr(); //  Последовательно запрашиваем конфигурационные массивы из PHP
    
    //console.log(current_page);
    //getConf_ArrayOfSiloNames();
});

//  Получение массивов с конфигурациями для повышения интерактивности ---------------------------------------------------------------------------------------
//  Получение главного конфигурационного массива [[массив с именами (при этом индекс элемента - это id силоса)],[массив с подвесками],[массив с датчиками]]
function getConf_ProjectConfArr() {
    $.ajax({
        url: '/Thermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_project_conf_array': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            //console.log(fromPHP);
            project_conf_array = (JSON.parse(fromPHP));
            //console.log("project conf arr");
            //console.log(project_conf_array);
            //console.log("\n");
            //console.log("keys"+Object.keys(project_conf_array));
            getConf_ArrayOfSiloNames();
        }
    });
}

//  Получение массива с именами силосов для быстрого отображения названия силоса на главной странице
function getConf_ArrayOfSiloNames() {
    $.ajax({
        url: 'currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_silo_names_array': 1 },
        dataType: 'html',
        success: function(fromPHP) {

            //console.log("Silo Names Array: "+fromPHP);
            //  массив с названиями силосов, в котором индекс - это silo_id, а значение - название силоса
            silo_names_array = JSON.parse(fromPHP);

            getConf_SiloNameWithMaxPodvNumber();

        }
    });
    return;
}

//  Получение массива с максимальным количеством подвесок. Необходимо для страницы "Отчет" в сайтбаре с печатными формами
function getConf_SiloNameWithMaxPodvNumber() {
    $.ajax({
        url: '/Thermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_silo_number_with_max_podv_number': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            silo_name_with_max_podv_number = fromPHP;

            //  После того, как все необходимые данные получены, переходим к секции инициализации в зависимости от того,
            //  на какой странице находимся
            if (current_page === "index.php") {
                init_index();
            } else if (current_page === "report.php") {
                init_report();
            } else if (current_page === "debug_page.php") {
                init_debug_page();
            } else if (current_page === "silo_config.php") {
                init_silo_config();
            } else if (current_page === "instruction.php") {
                init_instruction();
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
    const opt_0 = current_silo.options[0].value === "all" ? ["all"] : []; /*глобальная переменная*/
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
function controlAudio(OnOff) {
    if (OnOff === 1) {
        document.getElementById("alarm-sound").loop = true; //  Включаем звук
        document.getElementById("alarm-sound").play();
        $('#hdr-ack').removeClass("text-black"); //  Подсвечиваем кнопку квитирования
        $('#hdr-ack').addClass("text-primart");
    } else {
        document.getElementById("alarm-sound").loop = false;
        document.getElementById("alarm-sound").pause();
        $('#hdr-ack').removeClass("text-primary");
        $('#hdr-ack').addClass("text-black");
    }
    return;
}

function alarmsAck() {

    $.ajax({
        url: '/Thermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_acknowledge_alarms': 1 },
        dataType: 'html',
        success: function(fromPHP) {
            //console.log(fromPHP);
            controlAudio(0); //  Выключаем звук
            getNewAlarmsNumber(); //  Проверяем появление новых алармов
        }
    });
    return;
}

function getNewAlarmsNumber() {

    $.ajax({
        url: '/Thermometry/currValsFromTS.php',
        type: 'POST',
        cache: false,
        data: { 'POST_currValsFromTS_get_number_of_new_alarms': 1 },
        dataType: 'html',
        success: function(fromPHP) {

            if (!isJson(fromPHP)) {

                if (fromPHP > alarmsNACKNumber) { //  Если появились неквитированные алармы
                    controlAudio(1); //  Включаем звук
                } else if (fromPHP==0){
                    controlAudio(0);
                }
                alarmsNACKNumber = fromPHP;

                if (current_page === "index.php") {
                    vIndOnClickOnSilo(lastSiloID); //  Перерисовываем таблицу с текущими показаниями
                }

            } else if (current_page !== "error_page.php" && current_page !== "silo_config.php") {
                document.location.href = "error_page.php";
            }

        }
    });
    return;
}

function isJson(item) {
    item = typeof item !== "string" ?
        JSON.stringify(item) :
        item;

    try {
        item = JSON.parse(item);
    } catch (e) {
        return false;
    }

    if (typeof item === "object" && item !== null) {
        return true;
    }

    return false;
}