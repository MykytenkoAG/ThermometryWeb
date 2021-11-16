<?php

mb_internal_encoding("UTF-8");

$logFile = 'logs/log.txt';

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
       "DROP TABLE IF EXISTS zernoib.measurements;
        DROP TABLE IF EXISTS zernoib.dates;
        DROP TABLE IF EXISTS zernoib.sensors;
        DROP TABLE IF EXISTS zernoib.prodtypesbysilo;
        DROP TABLE IF EXISTS zernoib.silosesgroups;
        DROP TABLE IF EXISTS zernoib.prodtypes;
        DROP TABLE IF EXISTS zernoib.errors;
        DROP TABLE IF EXISTS zernoib.users;";

const SQL_STATEMENT_DROP_USERS = "DROP TABLE IF EXISTS zernoib.users;";

const SQL_STATEMENT_CREATE_USERS =
       "CREATE TABLE IF NOT EXISTS zernoib.users
        (user_id INT NOT NULL AUTO_INCREMENT,
        user_name VARCHAR(20) NOT NULL,
        password VARCHAR(32) NOT NULL,
        access_level INT NOT NULL DEFAULT 0,
        PRIMARY KEY (user_id))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_ERRORS = "DROP TABLE IF EXISTS zernoib.errors;";

const SQL_STATEMENT_CREATE_ERRORS =
       "CREATE TABLE IF NOT EXISTS zernoib.errors
        (error_id INT NOT NULL,
        error_description VARCHAR(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        error_desc_short VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        error_desc_for_visu VARCHAR(70) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        PRIMARY KEY (error_id))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_DATES = "DROP TABLE IF EXISTS zernoib.dates;";

const SQL_STATEMENT_CREATE_DATES = 
        "CREATE TABLE IF NOT EXISTS zernoib.dates
        (date_id INT NOT NULL AUTO_INCREMENT,
        date TIMESTAMP NOT NULL,
        PRIMARY KEY (date_id))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_PRODTYPES = "DROP TABLE IF EXISTS zernoib.prodtypes;";

const SQL_STATEMENT_CREATE_PRODTYPES =
       "CREATE TABLE IF NOT EXISTS zernoib.prodtypes
        (product_id INT NOT NULL AUTO_INCREMENT,
        product_name VARCHAR(60) NOT NULL,
        t_min FLOAT NOT NULL, t_max FLOAT NOT NULL,
        v_min FLOAT NOT NULL, v_max FLOAT NOT NULL,
        PRIMARY KEY (product_id))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_SILOSESGROUPS = "DROP TABLE IF EXISTS zernoib.silosesgroups;";

const SQL_STATEMENT_CREATE_SILOSESGROUPS =
       "CREATE TABLE IF NOT EXISTS zernoib.silosesgroups
        (silo_group INT NOT NULL,
        silo_group_name VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
        silo_group_col INT NULL DEFAULT NULL,
        silo_group_row INT NULL DEFAULT NULL,
        silo_group_size FLOAT NULL DEFAULT NULL,
        PRIMARY KEY (silo_group))
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_PRODTYPESBYSILO = "DROP TABLE IF EXISTS zernoib.prodtypesbysilo;";

const SQL_STATEMENT_CREATE_PRODTYPESBYSILO =
       "CREATE TABLE IF NOT EXISTS zernoib.prodtypesbysilo
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

const SQL_STATEMENT_DROP_SENSORS = "DROP TABLE IF EXISTS zernoib.sensors;";

const SQL_STATEMENT_CREATE_SENSORS =
       "CREATE TABLE IF NOT EXISTS zernoib.sensors
        (sensor_id INT NOT NULL,
        silo_id INT NOT NULL, podv_id INT NOT NULL, sensor_num INT NOT NULL,
        is_enabled BOOLEAN NOT NULL DEFAULT TRUE,
        current_temperature FLOAT NOT NULL,
        current_speed FLOAT NOT NULL,
        curr_t_text VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0',
        curr_v_text VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.0',
        curr_t_colour VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
        curr_v_colour VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
        server_date TIMESTAMP NULL DEFAULT NULL,
        NACK_Tmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_NACK_Tmax TIMESTAMP NULL DEFAULT NULL,
        ACK_Tmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_ACK_Tmax TIMESTAMP NULL DEFAULT NULL,
        NACK_Vmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_NACK_Vmax TIMESTAMP NULL DEFAULT NULL,
        ACK_Vmax BOOLEAN NOT NULL DEFAULT FALSE,	TIME_ACK_Vmax TIMESTAMP NULL DEFAULT NULL,
        NACK_err BOOLEAN NOT NULL DEFAULT FALSE,	TIME_NACK_err TIMESTAMP NULL DEFAULT NULL,
        ACK_err BOOLEAN NOT NULL DEFAULT FALSE,	TIME_ACK_err TIMESTAMP NULL DEFAULT NULL,
        error_id INT NULL DEFAULT NULL,
        PRIMARY KEY (sensor_id),
        CONSTRAINT sensors_fk FOREIGN KEY (silo_id) REFERENCES prodtypesbysilo(silo_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
        CONSTRAINT sens_err_fk FOREIGN KEY (error_id) REFERENCES errors(error_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_MEASUREMENTS = "DROP TABLE IF EXISTS zernoib.measurements;";

const SQL_STATEMENT_TRUNCATE_MEASUREMENTS = "TRUNCATE zernoib.measurements;";

const SQL_STATEMENT_CREATE_MEASUREMENTS =
       "CREATE TABLE IF NOT EXISTS zernoib.measurements
        (date_id INT NOT NULL,
        sensor_id INT NOT NULL,
        temperature FLOAT NULL,
        INDEX (date_id),
        CONSTRAINT measurements_fk_date_id FOREIGN KEY (date_id) REFERENCES dates(date_id) ON DELETE RESTRICT ON UPDATE RESTRICT,
        CONSTRAINT measurements_fk_sensor_id FOREIGN KEY (sensor_id) REFERENCES sensors(sensor_id) ON DELETE RESTRICT ON UPDATE RESTRICT)
        ENGINE = InnoDB
        CHARSET=utf8 COLLATE utf8_general_ci;";

const SQL_STATEMENT_DROP_TS_CONN_SETTINGS = "DROP TABLE IF EXISTS zernoib.ts_conn_settings;";

const SQL_STATEMENT_CREATE_TS_CONN_SETTINGS =
       "CREATE TABLE IF NOT EXISTS zernoib.ts_conn_settings
        ( id INT NOT NULL AUTO_INCREMENT,
        ts_ip VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '127.0.0.1',
        ts_port SMALLINT NOT NULL DEFAULT '200', PRIMARY KEY (id))
        ENGINE = InnoDB;";

const SQL_STATEMENT_SELECT_TS_CONN_SETTINGS =
       "SELECT id, ts_ip, ts_port FROM zernoib.ts_conn_settings WHERE id=1;";
       
const SQL_STATEMENT_INIT_TS_CONN_SETTINGS =
       "INSERT INTO zernoib.ts_conn_settings (id, ts_ip, ts_port) VALUES (1, '127.0.0.1', '200');";


?>