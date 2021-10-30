<?php require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/auth.php'); ?>
<!doctype html>
<html lang="en">
  <head>
   <?php
      $webSiteTitle="Ошибка";
      require_once "blocks/head.php";
   ?>
    
   </head>

   <body>
    
      <div class="container-fluid" style="height: 850px;">

         <?php
            require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/blocks/header.php');
            require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/currValsFromTS.php');
         ?>

         <div class="d-flex mt-3 mb-3 justify-content-center">
               <?php
                  echo $error;
               ?>
         </div>

         <div class="d-flex justify-content-center">
            
         </div>

      </div>
    
   </body>
</html>
