<?php require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/auth.php'); ?>
<!doctype html>
<html lang="en">
  <head>
    <?php
      $webSiteTitle="Настройки";
      require_once "blocks/head.php";
      require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/visualisation/visu_silo_config.php');
    ?>
  </head>
  <body>
      <?php
        require_once "blocks/header.php";
        require_once "blocks/modals.php";
      ?>
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

                    <button type="submit" class="btn btn-primary" id="table-prodtypes-btn-add" disabled>
                      <svg width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                        <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/>
                      </svg>
                      Добавить
                    </button>
                    <button type="submit" class="btn btn-success" id="table-prodtypes-btn-save-changes" disabled>
                      <img  src="img/button-save-changes.png" width="16" height="16"/>
                      <span>Сохранить изменения</span>
                    </button>
                    <button type="submit" class="btn btn-danger"  id="table-prodtypes-btn-discard-changes" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                              <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                            </svg>
                            <span>Отменить изменения</span>
                    </button>

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

                    <button type="submit" class="btn btn-success"
                            id="table-prodtypesbysilo-btn-save-changes" disabled>
                            <img  src="img/button-save-changes.png" width="16" height="16"/>
                            <span>Сохранить изменения</span></button>
                    <button type="submit" class="btn btn-danger" id="table-prodtypesbysilo-btn-discard-changes" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                              <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                            </svg>
                            <span>Отменить изменения</span>
                    </button>

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

                    <!--<div class="col-4 mb-2">
                      <div class="row">
                        Протокол работы программы
                      </div>
                      <div class="row p-1">
                        <button type="submit" id="silo-config-btn-web-server-log-download" class="btn btn-light">
                          <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                          </svg>
                          Скачать
                        </button>
                      </div>
                    </div>-->

                    <div class="col-4 mb-2">
                      <div class="row">
                        Протокол работы АПС
                      </div>
                      <div class="row p-1">
                        <a href="logs/log.txt" type="submit" id="silo-config-btn-alarms-log-download" class="btn btn-light" download>
                          <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                          </svg>
                          Скачать
                          </a>
                      </div>
                    </div>

                    <div class="col-4 mb-2">
                      <div class="row">
                        Пользователь: <?php $currentUser= $accessLevel==1 ? "Оператор" : "Технолог"; echo $currentUser; ?>
                      </div>
                      <div class="row p-1">
                        <button type="submit" id="silo-config-btn-change-password" class="btn btn-light">
                          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                            <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                            <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                          </svg>
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