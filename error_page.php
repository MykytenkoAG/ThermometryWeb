<?php require_once('auth.php'); ?>
<!doctype html>
<html lang="en">
  <head>
   <?php
      $webSiteTitle="Ошибка";
      require_once "head.php";
   ?>
   </head>
   <body>
    
      <div class="container-fluid">

         <?php
            require_once ('header.php');
            require_once ('modals.php');
            require_once ('currValsFromTS.php');
         ?>

         <div class="d-flex mt-3 mb-3 justify-content-center">

            <?php

                  foreach($errors as $error){

                     if($error === "NoTermoServer.ini"){
                        echo "Файл TermoServer.ini отсутствует в папке проекта ".getcwd()."\settings. Войдите под учетной записью технолога и загрузите требуемый файл на странице настроек.<br>";

                        echo "<br>";
                     }

                     if($error === "NoTermoClient.ini"){
                        echo "Файл TermoClient.ini отсутствует в папке проекта ".getcwd()."\settings. Войдите под учетной записью технолога и загрузите требуемый файл на странице настроек.<br>";

                        echo "<br>";
                     }

                     if($error === "DamagedTermoServer.ini"){
                        echo "Файл ".getcwd()."\settings\TermoServer.ini поврежден. Войдите под учетной записью технолога и загрузите требуемый файл на странице настроек.<br>";

                        echo "<br>";
                     }

                     if($error === "DamagedTermoClient.ini"){
                        echo "Файл ".getcwd()."\settings\TermoClient.ini поврежден. Войдите под учетной записью технолога и загрузите требуемый файл на странице настроек.<br>";

                        echo "<br>";
                     }

                     if($error === "IniFilesInconsistent"){
                        echo "Файлы ".getcwd()."\settings\TermoServer.ini и ".getcwd()."\settings\TermoClient.ini не соответствуют друг другу. Войдите под учетной записью технолога и загрузите актуальные версии на странице настроек.<br>";

                        echo "<br>";
                     }

                     if($error === "ProjectIsOutOfDate"){                 	
                        echo "Текущая версия файлов ".getcwd()."\settings\TermoServer.ini и ".getcwd()."\settings\TermoClient.ini не соответствует настройкам ПО Термосервер. Войдите под учетной записью технолога и загрузите актуальные версии файлов на странице настроек.<br>";

                        echo "<br>";
                     }

                     if($error === "TermoServerIsOff"){ 
                        echo "На данный момент программа Термосервер выключена или настройки соединения с ней не правильные. Включите программа Термосервер или войдите под учетной записью технолога и откорректируйте параметры подключения на странице настроек.<br>";      	

                        echo "<br>";
                     }
                  }

            ?>

         </div>

         <div class="d-flex justify-content-center">
            
         </div>

      </div>
    
   </body>
</html>
