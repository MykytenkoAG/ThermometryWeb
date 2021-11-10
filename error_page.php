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
                  echo $possibleErrors[$error]['message'];
               }
            ?>
         </div>
         <div class="d-flex justify-content-center">
         </div>
      </div>
   </body>
</html>
