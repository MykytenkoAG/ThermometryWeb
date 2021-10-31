<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Page</title>
</head>
<body>

    <iframe src="docs/ТСС-02 - инструкция по эксплуатации ПО.pdf" width="1800" height="500">

    <?php
        //require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/currValsFromTS.php');
        /*
        echo "<br>";
        print_r($arrayOfTemperatures);
        echo "<br>";
        echo "<br>";
        print_r($arrayOfTempSpeeds);
        echo "<br>";
        echo "<br>";
        print_r($arrayOfLevels);
        echo "<br>";
        echo "<br>";
        print_r($serverDate);
        echo "<br>";
        */
        //	Необходимые параметры для подключения к БД
        $servername = '127.0.0.1'; $username = 'root'; $password = ''; $dbname = 'zernoib';
        //	Создание объекта PDO для работы с Базой Данных
        $dbh = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);	//[PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        $serverDate="19.10.2021 14:05:00";

    ?>

</body>
</html>