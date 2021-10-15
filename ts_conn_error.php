<!doctype html>
<html lang="en">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="">
      <script src="js/jquery.js"></script>
		<script src="js/myscripts.js"></script>
      <title>Connection Error</title>
      <link href="css/bootstrap.min.css" rel="stylesheet">
    
   </head>


   <body>
    
      <div class="container-fluid" style="height: 850px;">

         <?php
            require_once ($_SERVER['DOCUMENT_ROOT'].'/webTermometry/blocks/header.php');
         ?>

         <div class="d-flex mt-3 mb-3 justify-content-center">
               <h1>Проверьте, запущен ли TermoServer.</h1>
         </div>

         <div class="d-flex justify-content-center">
            <img src="/webTermometry/img/connError.gif" width="500px" alt="termoServer">
         </div>

      </div>
    
   </body>
</html>
