<?php require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/auth.php'); ?>
<!doctype html>
<html lang="en">
  <head>
    <?php
      $webSiteTitle="Настройки"; require_once "blocks/head.php";
      require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/visualisation/visu_silo_config.php');
    ?>
  </head>
  <body>
      <?php require_once "blocks/header.php"; ?>
      <?php require_once "blocks/modals.php"; ?>
      <main>
        <div class="row row-cols-1 row-cols-sm-1 row-cols-xxl-3 g-0">
          <div class="col-12 col-xxl-5 g-1">
            <div class="card border-light h-100">
              <div class="card-body">
                <div class="d-flex justify-content-center">
                  <h5 class="card-title" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;">Типы продукта</h5>
                </div>
                <div class="card-body mb-0">
                  <div id="table-product-types">
                    <?php
                      echo drawTableProdtypes($dbh, $accessLevel);
                    ?>
                  </div>
                  <?php
                    $tableProdtypesBtnDisabled = $accessLevel==2 ? "" : "disabled";

                    echo "
                      <button type=\"submit\" class=\"btn btn-primary\" id=\"table-prodtypes-btn-add\" onclick=\"onClickTblProdtypesAddRow()\" $tableProdtypesBtnDisabled>
                        <svg width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-plus-lg\" viewBox=\"0 0 16 16\">
                          <path d=\"M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z\"/>
                        </svg>
                        Добавить
                      </button>
                      <button type=\"submit\" class=\"btn btn-success\" id=\"table-prodtypes-btn-save-changes\" $tableProdtypesBtnDisabled>
                        <img  src=\"img/button-save-changes.png\" width=\"20\" height=\"20\"/>
                        <span>Сохранить изменения</span>
                      </button>
                      <button type=\"submit\" class=\"btn btn-danger\"  id=\"table-prodtypes-btn-discard-changes\"
                              onclick=\"onClickTblProdtypesDiscardChanges()\" $tableProdtypesBtnDisabled>
                              <img  src=\"img/button-discard-changes.png\" width=\"20\" height=\"20\"/>
                              <span>Отменить изменения</span>
                      </button>
                    ";

                  ?>

                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-12 col-md-12 col-xxl-5 g-1">
            <div class="card border-light h-100">
              <div class="card-body">
                <div class="d-flex justify-content-center">
                  <h5 class="card-title" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;">Загрузка силосов</h5>
                </div>
                <div class="card-body mb-0">
                  <div id="table-product-types-by-silo" class="">
                    <?php
                        echo drawTableProdtypesbysilo($dbh, $accessLevel);
                    ?>
                  </div>

                  <?php
                    $tableProdtypesbysiloBtnDisabled = $accessLevel==2 ? "" : "disabled";
                    echo "
                      <button type=\"submit\" class=\"btn btn-success\"
                              id=\"table-prodtypesbysilo-btn-save-changes\" $tableProdtypesbysiloBtnDisabled>
                              <img  src=\"img/button-save-changes.png\" width=\"20\" height=\"20\"/>
                              <span>Сохранить изменения</span></button>
                      <button type=\"submit\" class=\"btn btn-danger\" id=\"table-prodtypesbysilo-btn-discard-changes\"
                              onclick=\"onClickTblProdtypesbysiloDiscardChanges()\" $tableProdtypesbysiloBtnDisabled>
                        <img  src=\"img/button-discard-changes.png\" width=\"20\" height=\"20\"/>
                              <span>Отменить изменения</span>
                      </button>
                    ";
                  ?>

                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-12 col-xxl-2 g-1">
            <div class="card border-light h-100">
              <div class="card-body">

                <div class="d-flex justify-content-center">
                  <h5 class="card-title" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;">Опции</h5>
                </div>

                <div class="card-body mb-0">

                  <div class="row row-cols-3 row-cols-sm-1 row-cols-xxl-1 mt-2">

                    <div class="col-4 mb-2">
                      <div class="row">
                        Протокол работы программы
                      </div>
                      <div class="row p-1">
                        <button type="submit" class="btn btn-light" onclick="add_row()">
                          <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                          </svg>
                          Скачать
                        </button>
                      </div>
                    </div>

                    <div class="col-4 mb-2">
                      <div class="row">
                        Протокол работы АПС
                      </div>
                      <div class="row p-1">
                        <button type="submit" class="btn btn-light" onclick="add_row()">
                          <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                          </svg>
                          Скачать
                        </button>
                      </div>
                    </div>

                    <div class="col-4 mb-2">
                      <div class="row">
                        Пользователь
                      </div>
                      <div class="row p-1">
                        <button type="submit" class="btn btn-light" onclick="add_row()">
                          Сменить пароль
                        </button>
                      </div>                   
                    </div>

                  </div>
                  
                </div>
              </div>
            </div>
          </div>

        </div>
      </main>
      
      <script src="visualisation/visu_silo_config.js"></script>
    </body>
</html>