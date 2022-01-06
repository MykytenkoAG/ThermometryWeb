<?php

mb_internal_encoding("UTF-8");

//  Выбор режима работы ----------------------------------------------------------------------------------------------------------------
$simulation_mode = true;
                   //false;
$debugPageDisableElements = $simulation_mode ? "" : "disabled";     //  Отключение элементов на странице отладки

//  Работа с Базой Данных --------------------------------------------------------------------------------------------------------------
const SERVER_NAME = "localhost";
const USERNAME = "root";
const password = "";
const DBNAME = "zernoib";

$pdo_options = [
    PDO::ATTR_TIMEOUT => 5,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"//,
    //PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION//,
    //PDO::ATTR_CASE => PDO::CASE_NATURAL//,
    //PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING
];

$logFile = substr(__DIR__,0,-11).'/logs/log.txt';

$CURRENT_WORKING_DIRECTORY = getcwd();

$POSSIBLE_ERRORS = array(
    "NoTermoServer.ini" => array(
        "message" => "Файл TermoServer.ini отсутствует в папке проекта ".$CURRENT_WORKING_DIRECTORY."\settings.",
        "image" => ""
    ),
    "NoTermoClient.ini" => array(
        "message" => "Файл TermoClient.ini отсутствует в папке проекта ".$CURRENT_WORKING_DIRECTORY."\settings.",
        "image" => ""
    ),
    "DamagedTermoServer.ini" => array(
        "message" => "Файл ".$CURRENT_WORKING_DIRECTORY."\settings\TermoServer.ini поврежден.",
        "image" => ""
    ),
    "DamagedTermoClient.ini" => array(
        "message" => "Файл ".$CURRENT_WORKING_DIRECTORY."\settings\TermoClient.ini поврежден.",
        "image" => ""
    ),
    "IniFilesInconsistent" => array(
        "message" => "Файлы ".$CURRENT_WORKING_DIRECTORY."\settings\TermoServer.ini и ".$CURRENT_WORKING_DIRECTORY."\settings\TermoClient.ini не соответствуют друг другу.<br>",
        "image" => ""
    ),
    "ProjectIsOutOfDate" => array(
        "message" => "<br><p style=\"margin-left: 30px;\">Текущая версия файлов ".$CURRENT_WORKING_DIRECTORY."\settings\TermoServer.ini и ".$CURRENT_WORKING_DIRECTORY."\settings\TermoClient.ini не соответствует настройкам ПО Термосервер.</p><br>",
        "image" => ""
    ),
    "TermoServerIsOff" => array(
        "message" => "На данный момент программа Термосервер выключена или настройки подключения к ней не корректны.<br>",
        "image" => ""
    ),
    "SolutionINIFilesAreDamaged" => array(
        "message" => "Войдите под учетной записью технолога и загрузите необходимые конфигурационные файлы на странице настроек.<br>",
        "image" => ""
    ),
    "SolutionTermoServerIsOff" => array(
        "message" => "Включите программа Термосервер или войдите под учетной записью технолога и откорректируйте параметры подключения на странице настроек.",
        "image" => ""
    )
);

const SQL_STATEMENT_DROP_ALL_TABLES = 
       "DROP TABLE IF EXISTS ".DBNAME.".measurements;
        DROP TABLE IF EXISTS ".DBNAME.".dates;
        DROP TABLE IF EXISTS ".DBNAME.".sensors;
        DROP TABLE IF EXISTS ".DBNAME.".prodtypesbysilo;
        DROP TABLE IF EXISTS ".DBNAME.".silosesgroups;
        DROP TABLE IF EXISTS ".DBNAME.".prodtypes;
        DROP TABLE IF EXISTS ".DBNAME.".errors;
        DROP TABLE IF EXISTS ".DBNAME.".users;";

const SQL_STATEMENT_DROP_USERS = "DROP TABLE IF EXISTS ".DBNAME.".users;";

const SQL_STATEMENT_CREATE_USERS =
       "CREATE TABLE IF NOT EXISTS ".DBNAME.".users
        (user_id INT NOT NULL AUTO_INCREMENT,
        user_name VARCHAR(20) NOT NULL,
        password VARCHAR(32) NOT NULL,
        access_level INT NOT NULL DEFAULT 0,
        PRIMARY KEY (user_id))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_ERRORS = "DROP TABLE IF EXISTS ".DBNAME.".errors;";

const SQL_STATEMENT_CREATE_ERRORS =
       "CREATE TABLE IF NOT EXISTS ".DBNAME.".errors
        (error_id INT NOT NULL,
        error_description VARCHAR(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        error_desc_short VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        error_desc_for_visu VARCHAR(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        PRIMARY KEY (error_id))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_DATES = "DROP TABLE IF EXISTS ".DBNAME.".dates;";

const SQL_STATEMENT_CREATE_DATES = 
        "CREATE TABLE IF NOT EXISTS ".DBNAME.".dates
        (date_id INT NOT NULL AUTO_INCREMENT,
        date TIMESTAMP NOT NULL,
        PRIMARY KEY (date_id))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_PRODTYPES = "DROP TABLE IF EXISTS ".DBNAME.".prodtypes;";

const SQL_STATEMENT_CREATE_PRODTYPES =
       "CREATE TABLE IF NOT EXISTS ".DBNAME.".prodtypes
        (product_id INT NOT NULL AUTO_INCREMENT,
        product_name VARCHAR(60) NOT NULL,
        t_min FLOAT NOT NULL, t_max FLOAT NOT NULL,
        v_min FLOAT NOT NULL, v_max FLOAT NOT NULL,
        PRIMARY KEY (product_id))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_SILOSESGROUPS = "DROP TABLE IF EXISTS ".DBNAME.".silosesgroups;";

const SQL_STATEMENT_CREATE_SILOSESGROUPS =
       "CREATE TABLE IF NOT EXISTS ".DBNAME.".silosesgroups
        (silo_group INT NOT NULL,
        silo_group_name VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        silo_group_col INT NULL DEFAULT NULL,
        silo_group_row INT NULL DEFAULT NULL,
        silo_group_size FLOAT NULL DEFAULT NULL,
        PRIMARY KEY (silo_group))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_PRODTYPESBYSILO = "DROP TABLE IF EXISTS ".DBNAME.".prodtypesbysilo;";

const SQL_STATEMENT_CREATE_PRODTYPESBYSILO =
       "CREATE TABLE IF NOT EXISTS ".DBNAME.".prodtypesbysilo
        (silo_id INT NOT NULL,
        silo_name VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        bs_addr INT NOT NULL DEFAULT 1,
        product_id INT NOT NULL,
        grain_level_fromTS BOOLEAN NOT NULL DEFAULT TRUE,
        grain_level INT NOT NULL,
        is_square BOOLEAN NOT NULL DEFAULT TRUE,
        size FLOAT NOT NULL DEFAULT 1,
        position_col INT NOT NULL DEFAULT 0,
        position_row INT NOT NULL DEFAULT 0,
        silo_group INT NOT NULL DEFAULT 0,
        PRIMARY KEY (silo_id),
        CONSTRAINT prodtypesbysilo_fk FOREIGN KEY (product_id) REFERENCES prodtypes(product_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
        CONSTRAINT prodtypesbysilo_fk_sg FOREIGN KEY (silo_group) REFERENCES silosesgroups(silo_group) ON DELETE RESTRICT ON UPDATE RESTRICT)
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_SENSORS = "DROP TABLE IF EXISTS ".DBNAME.".sensors;";

const SQL_STATEMENT_CREATE_SENSORS =
       "CREATE TABLE IF NOT EXISTS ".DBNAME.".sensors
        (sensor_id INT NOT NULL,
        silo_id INT NOT NULL, podv_id INT NOT NULL, sensor_num INT NOT NULL,
        is_enabled BOOLEAN NOT NULL DEFAULT TRUE,
        current_temperature FLOAT NULL,
        current_speed FLOAT NULL,
        curr_t_text VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0',
        curr_v_text VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0',
        curr_t_colour VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
        curr_v_colour VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
        server_date TIMESTAMP NULL DEFAULT NULL,
        NACK_Tmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_NACK_Tmax TIMESTAMP NULL DEFAULT NULL,
        ACK_Tmax  BOOLEAN NOT NULL DEFAULT FALSE,	TIME_ACK_Tmax  TIMESTAMP NULL DEFAULT NULL,
        RST_Tmax  BOOLEAN NOT NULL DEFAULT FALSE,	TIME_RST_Tmax  TIMESTAMP NULL DEFAULT NULL,
        NACK_Vmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_NACK_Vmax TIMESTAMP NULL DEFAULT NULL,
        ACK_Vmax  BOOLEAN NOT NULL DEFAULT FALSE,	TIME_ACK_Vmax  TIMESTAMP NULL DEFAULT NULL,
        RST_Vmax  BOOLEAN NOT NULL DEFAULT FALSE,	TIME_RST_Vmax  TIMESTAMP NULL DEFAULT NULL,
        NACK_err  BOOLEAN NOT NULL DEFAULT FALSE,	TIME_NACK_err  TIMESTAMP NULL DEFAULT NULL,
        ACK_err   BOOLEAN NOT NULL DEFAULT FALSE,	TIME_ACK_err   TIMESTAMP NULL DEFAULT NULL,
        RST_err   BOOLEAN NOT NULL DEFAULT FALSE,	TIME_RST_err   TIMESTAMP NULL DEFAULT NULL,
        error_id INT NULL DEFAULT NULL,
        PRIMARY KEY (sensor_id),
        CONSTRAINT sensors_fk FOREIGN KEY (silo_id) REFERENCES prodtypesbysilo(silo_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
        CONSTRAINT sens_err_fk FOREIGN KEY (error_id) REFERENCES errors(error_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_MEASUREMENTS = "DROP TABLE IF EXISTS ".DBNAME.".measurements;";

const SQL_STATEMENT_TRUNCATE_MEASUREMENTS = "TRUNCATE ".DBNAME.".measurements;";

const SQL_STATEMENT_CREATE_MEASUREMENTS =
       "CREATE TABLE IF NOT EXISTS ".DBNAME.".measurements
        (date_id INT NOT NULL,
        sensor_id INT NOT NULL,
        temperature FLOAT NULL,
        INDEX (date_id),
        CONSTRAINT measurements_fk_date_id FOREIGN KEY (date_id) REFERENCES dates(date_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
        CONSTRAINT measurements_fk_sensor_id FOREIGN KEY (sensor_id) REFERENCES sensors(sensor_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_TS_CONN_SETTINGS = "DROP TABLE IF EXISTS ".DBNAME.".ts_conn_settings;";

const SQL_STATEMENT_CREATE_TS_CONN_SETTINGS =
       "CREATE TABLE IF NOT EXISTS ".DBNAME.".ts_conn_settings
        ( id INT NOT NULL AUTO_INCREMENT,
        ts_ip VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '127.0.0.1',
        ts_port SMALLINT NOT NULL DEFAULT '200', PRIMARY KEY (id))
        ENGINE = InnoDB;";

const SQL_STATEMENT_SELECT_TS_CONN_SETTINGS =
       "SELECT id, ts_ip, ts_port FROM ".DBNAME.".ts_conn_settings WHERE id=1;";
       
const SQL_STATEMENT_INIT_TS_CONN_SETTINGS =
       "INSERT INTO ".DBNAME.".ts_conn_settings (id, ts_ip, ts_port) VALUES (1, '127.0.0.1', '200');";

const SQL_STATEMENT_CREATE_TELEGRAM_USERS =
       "CREATE TABLE ".DBNAME.".`telegram_users`
       ( `user_id` INT(64) NOT NULL , `notifications_on` TINYINT(1) NOT NULL , PRIMARY KEY (`user_id`))
       ENGINE = InnoDB;";

//  Выбор языка приложения
if(!isset($_COOKIE["application_language"])){
    require_once("lang_ru.php");
} else if ($_COOKIE["application_language"]==="RU"){
    require_once("lang_ru.php");
} else if ($_COOKIE["application_language"]==="EN"){
    require_once("lang_en.php");
} else if ($_COOKIE["application_language"]==="UA"){
    require_once("lang_ua.php");
}
//  Массив с переводами
const TEXTS = array(

    //  titles
    "TITLE_DEBUG"                   => array("RU"=>"Отладка",           "UA"=>"Відлагодження",  "EN"=>"Debug"),
    "TITLE_INDEX"                   => array("RU"=>"Термометрия",       "UA"=>"Термометрія",    "EN"=>"Thermometry"),
    "TITLE_REPORT"                  => array("RU"=>"Отчет",             "UA"=>"Звіт",           "EN"=>"Report"),
    "TITLE_SETTINGS"                => array("RU"=>"Настройки",         "UA"=>"Налаштування",   "EN"=>"Settings"),
    "TITLE_INSTRUCTION"             => array("RU"=>"Инструкция",        "UA"=>"Інструкція",     "EN"=>"Instruction"),
    "TITLE_SIGN_IN"                 => array("RU"=>"Вход",              "UA"=>"Вхід",           "EN"=>"Sign in"),
    "TITLE_SIGN_UP"                 => array("RU"=>"Регистрация",       "UA"=>"Реєстрація",     "EN"=>"Sign up"),

    //  sign in and sign up pages
    "SIGN_IN_USER"                  => array("RU"=>"Пользователь",      "UA"=>"Користувач",      "EN"=>"User"),
    "SIGN_IN_PASSWORD"              => array("RU"=>"Пароль",            "UA"=>"Пароль",          "EN"=>"Password"),
    "SIGN_IN_REMEMBER_ME"           => array("RU"=>"Запомнить меня",    "UA"=>"Запам'ятати мене","EN"=>"Remember me"),
    "SIGN_IN_SIGN_IN"               => array("RU"=>"Войти",             "UA"=>"Увійти",          "EN"=>"Sign in"),
    "SIGN_IN_SIGN_UP"               => array("RU"=>"Зарегистрироваться","UA"=>"Зареєструватися", "EN"=>"Sign up"),
    "SIGN_IN_INDEX"                 => array("RU"=>"На главную",        "UA"=>"На головну",      "EN"=>"Main page"),
    "SIGN_UP_CONFIRM_PASSWORD"      => array("RU"=>"Повторите пароль",  "UA"=>"Повторіть пароль","EN"=>"Confirm password"),
    "SIGN_UP_PHOTO"                 => array("RU"=>"Фото",              "UA"=>"Фото",            "EN"=>"Photo"),

    //  header
    "HDR_PAGE_NAME_DEBUG"           => array("RU"=>"Отладка",           "UA"=>"Відлагодження",  "EN"=>"Debug"),
    "HDR_PAGE_NAME_MAIN"            => array("RU"=>"Главная",           "UA"=>"Головна",        "EN"=>"Main page"),
    "HDR_PAGE_NAME_REPORT"          => array("RU"=>"Отчет",             "UA"=>"Звіт",           "EN"=>"Report"),
    "HDR_PAGE_NAME_SETTINGS"        => array("RU"=>"Настройки",         "UA"=>"Налаштування",   "EN"=>"Settings"),
    "HDR_PAGE_NAME_INSTRUCTION"     => array("RU"=>"Инструкция",        "UA"=>"Інструкція",     "EN"=>"Instruction"),
    "HDR_ACK"                       => array("RU"=>"Квитировать АПС",   "UA"=>"Квітувати АПС",  "EN"=>"Acknowledge"),
    "HDR_OPER"                      => array("RU"=>"Оператор",          "UA"=>"Оператор",       "EN"=>"Operator"),
    "HDR_TEHN"                      => array("RU"=>"Технолог",          "UA"=>"Технолог",       "EN"=>"Technologist"),
    "HDR_SIGN_OUT"                  => array("RU"=>"Выйти",             "UA"=>"Вийти",          "EN"=>"Sign out"),

    //  Главная страница
    "INDEX_LEFT_TABLE_TITLE"        => array("RU"=>"АПС",               "UA"=>"АПС",    "EN"=>"Warnings"),
    "INDEX_LEFT_TABLE_COL_1"        => array("RU"=>"Время",             "UA"=>"Час",    "EN"=>"Time"),
    "INDEX_LEFT_TABLE_COL_2"        => array("RU"=>"Силос",             "UA"=>"Силос",  "EN"=>"Silo"),
    "INDEX_LEFT_TABLE_COL_3"        => array("RU"=>"ТП",                "UA"=>"ТП",     "EN"=>"T#"),
    "INDEX_LEFT_TABLE_COL_4"        => array("RU"=>"НД",                "UA"=>"НД",     "EN"=>"S#"),
    "INDEX_LEFT_TABLE_COL_5"        => array("RU"=>"АПС",               "UA"=>"АПС",    "EN"=>"Warn"),
    "INDEX_LEFT_BTN_1"              => array("RU"=>"Отключить все неисправные датчики",
                                             "UA"=>"Відключити усі несправні датчики",
                                             "EN"=>"Disable all faulty sensors"),
    "INDEX_LEFT_BTN_2"              => array("RU"=>"Включить все отключенные датчики",
                                             "UA"=>"Включити всі відключені датчики",
                                             "EN"=>"Enable all disabled sensors"),
    "INDEX_LEFT_BTN_3"              => array("RU"=>"Включить автоопределение уровня на всех силосах",
                                             "UA"=>"Ввімкнути автовизначення рівня на усіх силосах",
                                             "EN"=>"Enable automatic level detection on all silos"),

    "INDEX_TITLE_SILO_PLAN"         => array("RU"=>"План расположения силосов","UA"=>"План розташування силосів","EN"=>"Silo plan"),

    "INDEX_RIGHT_SILO"              => array("RU"=>"Силос",                 "UA"=>"Силос",                      "EN"=>"Silo"),
    "INDEX_RIGHT_TEMPERATURES"      => array("RU"=>"Температуры, &deg;C",   "UA"=>"Температури, &deg;C",        "EN"=>"Temperatures, &deg;C"),
    "INDEX_RIGHT_T_SPEEDS"          => array("RU"=>"Скорости, &deg;C/сут.", "UA"=>"Швидкості, &deg;C/сут.",     "EN"=>"T. speeds, &deg;C/сут."),
    "INDEX_RIGHT_PARAMETERS"        => array("RU"=>"Параметры",             "UA"=>"Параметри",                  "EN"=>"Parameters"),

    //  Отчет
    "REPORT_LEFT_TITLE"             => array("RU"=>"Печатные формы","UA"=>"Друковані форми","EN"=>"Printed forms"),
    "REPORT_LEFT_DATE"              => array("RU"=>"Дата и время","UA"=>"Дата та час","EN"=>"Date and time"),
    "REPORT_LEFT_PR_DATA_TITLE"     => array("RU"=>"Печатные данные","UA"=>"Друковані дані","EN"=>"Print data"),
    "REPORT_LEFT_PR_DATA_1"         => array("RU"=>"Средние температуры в слоях",
                                             "UA"=>"Середні температури у шарах",
                                             "EN"=>"Average temperatures in layers"),
    "REPORT_LEFT_PR_DATA_2"         => array("RU"=>"Температуры каждого датчика в слоях",
                                             "UA"=>"Температура кожного датчика у шарах",
                                             "EN"=>"Temperatures of each sensor in layers"),
    "REPORT_LEFT_PR_DATA_3"         => array("RU"=>"Температуры датчика в подвеске",
                                             "UA"=>"Температури датчика у підвісці",
                                             "EN"=>"Sensor temperatures in cables"),
    "REPORT_LEFT_IN_DATA_TITLE"     => array("RU"=>"Входные данные","UA"=>"Вхідні дані","EN"=>"Input data"),
    "REPORT_LEFT_IN_DATA_SILO"      => array("RU"=>"Силос","UA"=>"Силос","EN"=>"Silo"),
    "REPORT_LEFT_IN_DATA_PODV"      => array("RU"=>"Подвеска","UA"=>"Підвіска","EN"=>"Temperature cable"),
    "REPORT_LEFT_IN_DATA_LAYER"     => array("RU"=>"Слой","UA"=>"Шар","EN"=>"Layer"),
    "REPORT_LEFT_IN_DATA_SENSOR"    => array("RU"=>"Датчик","UA"=>"Датчик","EN"=>"Sensor"),

    "REPORT_CHART_TITLE"            => array("RU"=>"График температуры","UA"=>"Графік температури","EN"=>"Temperature chart"),
    
    "REPORT_RIGHT_TITLE"            => array("RU"=>"Выбор датчика","UA"=>"Вибір датчика","EN"=>"Sensor selection"),
    "REPORT_RIGHT_SILO"             => array("RU"=>"Силос","UA"=>"Силос","EN"=>"Silo"),
    "REPORT_RIGHT_PODV"             => array("RU"=>"НП","UA"=>"НП","EN"=>"T#"),
    "REPORT_RIGHT_SENSOR"           => array("RU"=>"НД","UA"=>"НД","EN"=>"S#"),
    "REPORT_RIGHT_COLOUR"           => array("RU"=>"Цвет","UA"=>"Колір","EN"=>"Colour"),
    "REPORT_RIGHT_PERIOD"           => array("RU"=>"Период","UA"=>"Період","EN"=>"Period"),
    "REPORT_RIGHT_BTN_ADD"          => array("RU"=>"Добавить","UA"=>"Додати","EN"=>"Add"),

    //  Настройки
    "SETTINGS_PRODTYPES_TITLE"      => array("RU"=>"Типы продукта","UA"=>"Типи продукта","EN"=>"Product types"),
    "SETTINGS_PRODTYPES_COL_1"      => array("RU"=>"Тип продукта","UA"=>"Тип продукта","EN"=>"Product type"),
    "SETTINGS_PRODTYPES_COL_2"      => array("RU"=>"Т мин.","UA"=>"T мин.","EN"=>"T min."),
    "SETTINGS_PRODTYPES_COL_3"      => array("RU"=>"Т кр.","UA"=>"Т кр.","EN"=>"T max."),
    "SETTINGS_PRODTYPES_COL_4"      => array("RU"=>"V мин.","UA"=>"V мин.","EN"=>"V min."),
    "SETTINGS_PRODTYPES_COL_5"      => array("RU"=>"V кр.","UA"=>"V кр.","EN"=>"V max."),

    "SETTINGS_PRODTYPES_BTN_1"      => array("RU"=>"Добавить","UA"=>"Додати","EN"=>"Add"),
    "SETTINGS_PRODTYPES_BTN_2"      => array("RU"=>"Сохранить изменения","UA"=>"Зберегти","EN"=>"Save changes"),
    "SETTINGS_PRODTYPES_BTN_3"      => array("RU"=>"Отменить изменения","UA"=>"Відмінити","EN"=>"Discard changes"),

    "SETTINGS_SILOLOADS_TITLE"      => array("RU"=>"Загрузка силосов","UA"=>"Завантаження силосів","EN"=>"Silo loads"),
    "SETTINGS_SILOLOADS_COL_1"      => array("RU"=>"Силос","UA"=>"Силос","EN"=>"Silo"),
    "SETTINGS_SILOLOADS_COL_2"      => array("RU"=>"БС","UA"=>"БС","EN"=>"BS"),
    "SETTINGS_SILOLOADS_COL_3"      => array("RU"=>"Определение уровня","UA"=>"Визначення рівня","EN"=>"Level measuring"),
    "SETTINGS_SILOLOADS_COL_4"      => array("RU"=>"Уровень","UA"=>"Рівень","EN"=>"Level"),
    "SETTINGS_SILOLOADS_COL_5"      => array("RU"=>"Тип продукта","UA"=>"Тип продукта","EN"=>"Product type"),

    "SETTINGS_SILOLOADS_BTN_1"      => array("RU"=>"Сохранить изменения","UA"=>"Зберегти","EN"=>"Save changes"),
    "SETTINGS_SILOLOADS_BTN_2"      => array("RU"=>"Отменить изменения","UA"=>"Відмінити","EN"=>"Discard changes"),

    "SETTINGS_RIGHT_TITLE"          => array("RU"=>"Опции","UA"=>"Опції","EN"=>"Options"),
    "SETTINGS_RIGHT_ROW_1"          => array("RU"=>"Работа с ПО Термосервер","UA"=>"Робота з ПЗ Термосервер","EN"=>"Working with TermoServer software"),
    "SETTINGS_RIGHT_ROW_2"          => array("RU"=>"Работа с БД","UA"=>"Робота з БД","EN"=>"Working with DB"),
    "SETTINGS_RIGHT_ROW_3"          => array("RU"=>"Протокол работы АПС","UA"=>"Протокол роботи АПС","EN"=>"Log"),
    "SETTINGS_RIGHT_ROW_4"          => array("RU"=>"Пользователь: ","UA"=>"Користувач: ","EN"=>"User"),

    "SETTINGS_RIGHT_BTN_1"          => array("RU"=>"Настроить","UA"=>"Налаштувати","EN"=>"Configure"),
    "SETTINGS_RIGHT_BTN_2"          => array("RU"=>"Операции","UA"=>"Операції","EN"=>"Operations"),
    "SETTINGS_RIGHT_BTN_3"          => array("RU"=>"Скачать","UA"=>"Завантажити","EN"=>"Download"),
    "SETTINGS_RIGHT_BTN_4"          => array("RU"=>"Очистить","UA"=>"Очистити","EN"=>"Clear"),
    "SETTINGS_RIGHT_BTN_5"          => array("RU"=>"Изменить пароль","UA"=>"Змінити пароль","EN"=>"Change password"),

);

?>