<?php require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/auth.php'); ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php
      $webSiteTitle="Инструкция";
      require_once "blocks/head.php";
      require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/visualisation/visu_index.php');
    ?>
  </head>
  <body>
    <?php
      require_once "blocks/header.php";
      require_once "blocks/modals.php";
    ?>

    <script>
      document.getElementById("hdr-href-instruction.php").setAttribute("class", "nav-link text-primary");
    </script>

    <main>
      <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1 row-cols-lg-2 row-cols-xl-2 row-cols-xxl-3 g-0">
          <div class="col-12 col-md-5 col-lg-4 col-xl-4 col-xxl-3 g-1">
              <iframe src="docs/ТСС-02 - инструкция по эксплуатации ПО.pdf" style="margin: 10px; height: calc(100vh - 110px); width: calc(100vw - 25px);">
          </div>
      </div>
    </main>
          
  </body>
  <script type="text/javascript" src="visualisation/visu_index.js"></script>
</html>