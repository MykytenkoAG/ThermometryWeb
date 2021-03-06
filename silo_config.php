<?php require_once __DIR__.'/php/auth/auth.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php
      $webSiteTitle=TEXTS["TITLE_SETTINGS"][LANG];
      require_once "head.php";
      require_once __DIR__.'/php/visu/visu_silo_config.php';
    ?>
  </head>
  <body>
      <?php
        require_once "header.php";
        require_once "modals.php";
      ?>
      <main>
        <?php
          foreach($errors as $error){
            echo $POSSIBLE_ERRORS[$error]['message'];
          }
        ?>
        <div class="row row-cols-1 row-cols-sm-1 row-cols-xxl-3 g-0">
          <div class="col-12  col-xl-6 col-xxl-5 g-1">
            <div class="card border-light h-100">
              <div class="card-body">
                <div class="d-flex justify-content-center">
                  <h5 class="card-title" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;"><?php echo TEXTS["SETTINGS_PRODTYPES_TITLE"][LANG]; ?></h5>
                </div>
                <div class="card-body mb-0">
                  <div id="table-product-types">
                    <?php
                      echo vSConf_draw_Prodtypes($dbh, $accessLevel);
                    ?>
                  </div>

                    <button type="submit" class="btn btn-primary" id="sconf-table-prodtypes-btn-add" disabled>
                      <svg width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                        <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/>
                      </svg>
                      <?php echo TEXTS["SETTINGS_PRODTYPES_BTN_1"][LANG]; ?>
                    </button>
                    <button type="submit" class="btn btn-success" id="sconf-table-prodtypes-btn-save-changes" disabled>
                      <img  src="assets/img/button-save-changes.png" width="16" height="16"/>
                      <span><?php echo TEXTS["SETTINGS_PRODTYPES_BTN_2"][LANG]; ?></span>
                    </button>
                    <button type="submit" class="btn btn-danger"  id="sconf-table-prodtypes-btn-discard-changes" disabled>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                      </svg>
                      <span><?php echo TEXTS["SETTINGS_PRODTYPES_BTN_3"][LANG]; ?></span>
                    </button>

                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-12 col-md-12 col-xl-6 col-xxl-5 g-1">
            <div class="card border-light h-100">
              <div class="card-body">
                <div class="d-flex justify-content-center">
                  <h5 class="card-title" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;"><?php echo TEXTS["SETTINGS_SILOLOADS_TITLE"][LANG]; ?></h5>
                </div>
                <div class="card-body mb-0">
                  <div id="table-product-types-by-silo" class="">
                    <?php
                        echo vSConf_draw_Prodtypesbysilo($dbh, $accessLevel);
                    ?>
                  </div>

                    <button type="submit" class="btn btn-success"
                      id="sconf-table-prodtypesbysilo-btn-save-changes" disabled>
                      <img  src="assets/img/button-save-changes.png" width="16" height="16"/>
                      <span><?php echo TEXTS["SETTINGS_SILOLOADS_BTN_1"][LANG]; ?></span></button>
                    <button type="submit" class="btn btn-danger" id="sconf-table-prodtypesbysilo-btn-discard-changes" disabled>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                      </svg>
                      <span><?php echo TEXTS["SETTINGS_SILOLOADS_BTN_2"][LANG]; ?></span>
                    </button>

                </div>
              </div>
            </div>
          </div>

          <div class="col-sm-12 col-xxl-2 g-1">
            <div class="card border-light h-100">
              <div class="card-body">

                <div class="d-flex justify-content-center">
                  <h5 class="card-title" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;"><?php echo TEXTS["SETTINGS_RIGHT_TITLE"][LANG]; ?></h5>
                </div>

                <div class="card-body mb-0">

                  <div class="row row-cols-3 row-cols-sm-1 row-cols-xxl-1 mt-2">

                    <div class="col-4 p-1">
                      <?php echo TEXTS["SETTINGS_RIGHT_ROW_1"][LANG]; ?>
                    </div>
                    
                    <button type="submit" id="sconf-ts-connection-settings" class="btn btn-light mb-3" <?php $ts_setting_btn_dis = $accessLevel>1 ? "": "disabled"; echo $ts_setting_btn_dis; ?>>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3h9.05zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8h2.05zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1h9.05z"/>
                      </svg>
                      <?php echo TEXTS["SETTINGS_RIGHT_BTN_1"][LANG]; ?>
                    </button>

                    <div class="col-4 p-1">
                        <?php echo TEXTS["SETTINGS_RIGHT_ROW_2"][LANG]; ?>
                    </div>

                    <button type="submit" id="sconf-db-operations" class="btn btn-light mb-3" <?php $db_operations_btn_dis = $accessLevel>1 ? "": "disabled"; echo $db_operations_btn_dis; ?>>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-server" viewBox="0 0 16 16">
                        <path d="M1.333 2.667C1.333 1.194 4.318 0 8 0s6.667 1.194 6.667 2.667V4c0 1.473-2.985 2.667-6.667 2.667S1.333 5.473 1.333 4V2.667z"/>
                        <path d="M1.333 6.334v3C1.333 10.805 4.318 12 8 12s6.667-1.194 6.667-2.667V6.334a6.51 6.51 0 0 1-1.458.79C11.81 7.684 9.967 8 8 8c-1.966 0-3.809-.317-5.208-.876a6.508 6.508 0 0 1-1.458-.79z"/>
                        <path d="M14.667 11.668a6.51 6.51 0 0 1-1.458.789c-1.4.56-3.242.876-5.21.876-1.966 0-3.809-.316-5.208-.876a6.51 6.51 0 0 1-1.458-.79v1.666C1.333 14.806 4.318 16 8 16s6.667-1.194 6.667-2.667v-1.665z"/>
                      </svg>
                      <?php echo TEXTS["SETTINGS_RIGHT_BTN_2"][LANG]; ?>
                    </button>

                    <div class="col-4 p-1">
                      <?php echo TEXTS["SETTINGS_RIGHT_ROW_3"][LANG]; ?>
                    </div>
                    <div class="col-12 mb-3">
                      <div class="row">
                          <div class="col-6 g-1">
                          <a href="logs/log.txt" type="submit" id="silo-config-btn-alarms-log-download" class="btn btn-light w-100" download>
                            <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                              <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                              <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                            </svg>
                            <?php echo TEXTS["SETTINGS_RIGHT_BTN_3"][LANG]; ?>
                          </a>
                        </div>
                        <div class="col-6 g-1">
                          <button type="submit" id="sconf-silo-config-btn-clear-log" class="btn btn-light w-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive-fill" viewBox="0 0 16 16">
                              <path d="M12.643 15C13.979 15 15 13.845 15 12.5V5H1v7.5C1 13.845 2.021 15 3.357 15h9.286zM5.5 7h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1zM.8 1a.8.8 0 0 0-.8.8V3a.8.8 0 0 0 .8.8h14.4A.8.8 0 0 0 16 3V1.8a.8.8 0 0 0-.8-.8H.8z"/>
                            </svg>
                            <?php echo TEXTS["SETTINGS_RIGHT_BTN_4"][LANG]; ?>
                          </button>
                        </div>
                      </div>
                    </div>


                    <div class="col-4 p-1">
                      ????????????????????????: <?php $currentUser= $accessLevel==1 ? "????????????????" : "????????????????"; echo $currentUser; ?>
                    </div>
                    <button type="submit" id="sconf-silo-config-btn-change-password" class="btn btn-light w-100">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
                        <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8zm4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5z"/>
                        <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                      </svg>
                      <?php echo TEXTS["SETTINGS_RIGHT_BTN_5"][LANG]; ?>
                    </button>

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
     <script src="js/visu_silo_config.js"></script>
    </body>
</html>