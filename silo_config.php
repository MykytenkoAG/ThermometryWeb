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
      <main>

        <div class="modal fade" id="silo-config-page-change-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header" style="background-color: #520007;">
                  <h5 class="modal-title" id="" style="color: white;">Внимание</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body"><h5 id="silo-config-page-change-modal-message"></h5></div>
              <div class="modal-footer">
                  <div style="margin: auto;">
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal"
                              onclick="tbl_prodtypes_changed=0;tbl_prodtypesbysilo_changed=0;onPageChange(curr_url_ind);">Все равно перейти</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                  </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="silo-config-successfull-changes-in-db-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header" style="background-color: #4046ff;">
                  <h5 class="modal-title" id=""></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body"><h5 id="">Изменения успешно внесены в Базу данных</h5></div>
              <div class="modal-footer">
                  <div style="margin: auto;">
                      <button type="button" class="btn btn-primary" data-bs-dismiss="modal">ОК</button>
                  </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="silo-config-save-changes-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header" style="background-color: #4046ff;">
                  <h5 class="modal-title" id=""></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body"><h5 id="">Вы уверены?</h5></div>
              <div class="modal-footer">
                  <div style="margin: auto;">
                      <button type="button" id="silo-config-save-changes-modal-ok-button" class="btn btn-primary" data-bs-dismiss="modal">Да</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Нет</button>
                  </div>
              </div>
            </div>
          </div>
        </div>

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
                      echo drawTableProdtypes($dbh);
                    ?>
                  </div>
                  <button type="submit" class="btn btn-primary" id="table-prodtypes-btn-add" onclick="onClickTblProdtypesAddRow()">
                    <svg width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                      <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/>
                    </svg>
                    Добавить
                  </button>
                  <button type="submit" class="btn btn-success" id="table-prodtypes-btn-save-changes">
                    <img  src="img/button-save-changes.png" width="20" height="20"/>
                    <span>Сохранить изменения</span>
                  </button>
                  <button type="submit" class="btn btn-danger"  id="table-prodtypes-btn-discard-changes"
                          onclick="onClickTblProdtypesDiscardChanges()" >
                          <img  src="img/button-discard-changes.png" width="20" height="20"/>
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
                        echo drawTableProdtypesbysilo($dbh);
                    ?>
                  </div>
                  <button type="submit" class="btn btn-success"
                          id="table-prodtypesbysilo-btn-save-changes">
                          <img  src="img/button-save-changes.png" width="20" height="20"/>
                          <span>Сохранить изменения</span></button>
                  <button type="submit" class="btn btn-danger" id="table-prodtypesbysilo-btn-discard-changes"
                          onclick="onClickTblProdtypesbysiloDiscardChanges()">
                    <img  src="img/button-discard-changes.png" width="20" height="20"/>
                          <span>Отменить изменения</span></button>
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