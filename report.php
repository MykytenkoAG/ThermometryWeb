<?php require_once('auth.php'); ?>
<!doctype html>
<html lang="en">
  <head>
    <?php
      $webSiteTitle="Отчет";
      require_once "head.php";
      require_once('visu_report.php');
    ?>

    <script src="libs/pdfmake.min.js"></script>
    <script src="libs/vfs_fonts.js"></script>
    <script src="libs/xlsx.full.min.js"></script>

  </head>
  <body>
      <?php
        require_once "header.php";
        require_once "modals.php";
      ?>
      <main>
        <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1 row-cols-lg-1 row-cols-xl-2 row-cols-xxl-3 m-0 g-0">

          <div class="col-12 col-lg-12 col-xl-5 col-xxl-3 g-1">

            <div class="card border-light h-100">
              <div class="card-body">
                
                <div class="d-flex mb-2 justify-content-center">
                  <h5 class="card-title justify-content-center" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;">Печатные формы</h5>
                </div>

                <div class="row">

                  <div class="col-4 text-center">
                    <h5 style="font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Дата и время</h4>

                    <div class="mt-3">
                      <?php
                        echo vRep_drawMeasCheckboxes(vRep_getAllMeasDates($dbh));
                      ?>
                    </div>

                  </div>

                  <div class="col-8">

                    <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-1">

                      <div class="col">
                        <h5 style="font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Печатные данные</h5>
                        
                        <div class="form-check mt-2">
                          <input class="form-check-input" type="radio" name="exampleRadios"
                                  id="prfrb_avg-t-by-layer" onchange="vRep_prfSelectsDisable()" value="avg-t-by-layer" checked>
                          <label class="form-check-label" for="prfrb_avg-t-by-layer">
                            Средние температуры в слоях
                          </label>
                        </div>
                        <div class="form-check mt-1">
                          <input class="form-check-input" type="radio" name="exampleRadios"
                                  id="prfrb_t-by-layer" onchange="vRep_prfSelectsDisable()" value="t-by-layer">
                          <label class="form-check-label" for="prfrb_t-by-layer">
                            Температуры каждого датчика в слоях
                          </label>
                        </div>
                        <div class="form-check mt-1">
                          <input class="form-check-input" type="radio" name="exampleRadios"
                                  id="prfrb_t-by-sensor" onchange="vRep_prfSelectsDisable()" value="t-by-sensor">
                          <label class="form-check-label" for="prfrb_t-by-sensor">
                            Температуры датчика в подвеске
                          </label>
                        </div>

                      </div>

                      <div class="col mt-3">
                        <h5 style="font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Входные данные</h5>

                        <div class="mt-2">
                          <label for="rprtprf_silo_1" class="form-label">Силос</label>
                          <select class="form-control"
                            id="rprtprf_silo_1"     onchange="redrawRowOfSelects(event.target.id)"
                            style="width: 70px">
                            <option value="all">все</option>
                          </select>

                          <div class="row mt-2">

                            <div class="col-4 mr-1">
                              <label for="rprtprf_podv_1" class="form-label" disabled>Подвеска</label>
                              <select class="form-control"
                                id="rprtprf_podv_1" onchange="redrawRowOfSelects(event.target.id)"
                                style="width: 70px">
                                <option value="all">все</option>
                              </select>
                            </div>

                            <div class="col-4 mr-1">
                              <label for="rprtprf_layer_1" class="form-label">Слой</label>
                              <select class="form-control"
                                id="rprtprf_layer_1"
                                style="width: 70px">
                                <option value="all">все</option>
                              </select>
                            </div>

                            <div class="col-4 mr-1">
                              <label for="rprtprf_sensor_1" class="form-label" disabled>Датчик</label>
                              <select class="form-control"
                                id="rprtprf_sensor_1"
                                style="width: 70px">
                                <option value="all">все</option>
                              </select>
                            </div>

                          </div>

                          <div class="row">

                            <div class="col p-1">
                              <button type="submit" class="btn btn-primary mt-3 w-100" id="rprtprf-btn-download-PDF" onclick="vRep_getJSONForPrintedForms('PDF');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-pdf" viewBox="0 0 16 16">
                                  <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                  <path d="M4.603 12.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.701 19.701 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.187-.012.395-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.065.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.716 5.716 0 0 1-.911-.95 11.642 11.642 0 0 0-1.997.406 11.311 11.311 0 0 1-1.021 1.51c-.29.35-.608.655-.926.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.27.27 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.647 12.647 0 0 1 1.01-.193 11.666 11.666 0 0 1-.51-.858 20.741 20.741 0 0 1-.5 1.05zm2.446.45c.15.162.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.881 3.881 0 0 0-.612-.053zM8.078 5.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                </svg>
                                PDF
                              </button>
                            </div>

                            <div class="col p-1">
                              <button type="submit" class="btn btn-primary mt-3 w-100" id="rprtprf-btn-download-XLS" onclick="vRep_getJSONForPrintedForms('XLSX');">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel" viewBox="0 0 16 16">
                                  <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>
                                  <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                </svg>
                                XLS
                              </button>
                            </div>

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-12 col-xl-7 col-xxl-6 g-1">
            <div class="card border-light h-100">
              <div class="card-body">
                <div class="d-flex mb-2 justify-content-center">
                  <h5 class="card-title justify-content-center" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;">График температуры</h5>
                </div>
                <canvas id="temperatureGraph"></canvas>

                <script type="text/javascript" src="libs/chart.js"></script>
                <script src="libs/chartjs-adapter-date-fns.bundle.js"></script>

                <script type="text/javascript" src="visu_report.js"></script>               

              </div>
            </div>
          </div>

          <div class="col-12 col-lg-12 col-xl-12 col-xxl-3 g-1">
            <div class="card border-light h-100">
              <div class="card-body">

                <div class="d-flex justify-content-center">
                  <h5 class="card-title" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;">Выбор датчика</h5>
                </div>

                <div class="card-body">

                  <table class="table table-hover text-center" id="rep-chart-time-temperature">
                    <thead>
                      <tr>
                        <th scope="col">Силос</th>
                        <th scope="col">НП</th>
                        <th scope="col">НД</th>
                        <th scope="col">Цвет</th>
                        <th scope="col">Период</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>
                          <select class="form-control"
                            id="rprtchart_silo_1" onchange="redrawRowOfSelects(event.target.id)"
                            style="width: 70px">
                            <option value="1">1</option>
                          </select>
                        </td>
                        <td>
                          <select class="form-control"
                            id="rprtchart_podv_1" onchange="redrawRowOfSelects(event.target.id)"
                            style="width: 70px">
                            <option value="1">1</option>
                          </select>
                        </td>
                        <td>
                          <select class="form-control"
                            id="rprtchart_sensor_1"
                            style="width: 70px">
                            <option value="1">1</option>
                          </select>
                        </td>
                        <td>
                          <input type="color" class="form-control form-control-color" id="exampleColorInput" value="#52A300" title="Choose your color">
                        </td>
                        <td>
                          <select class="form-control" style="width: 80px" id="inlineFormCustomSelect">
                            <option value="month">месяц</option>
                            <option value="day">сутки</option>
                          </select>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  <button type="submit" class="btn btn-primary" onclick="vRep_addNewLineOnChart()">
                    <svg width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                      <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/>
                    </svg>
                    Добавить
                  </button>

                  <button type="submit" class="btn btn-primary" onclick="vRep_Convert();" style="width: 120px; height: 38px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-pdf" viewBox="0 0 16 16">
                      <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                      <path d="M4.603 12.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.701 19.701 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.187-.012.395-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.065.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.716 5.716 0 0 1-.911-.95 11.642 11.642 0 0 0-1.997.406 11.311 11.311 0 0 1-1.021 1.51c-.29.35-.608.655-.926.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.27.27 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.647 12.647 0 0 1 1.01-.193 11.666 11.666 0 0 1-.51-.858 20.741 20.741 0 0 1-.5 1.05zm2.446.45c.15.162.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.881 3.881 0 0 0-.612-.053zM8.078 5.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                    </svg>
                    PDF
                  </button>

                </div>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-12 col-xl-12 col-xxl-12 g-1">
            <div id="content">
                
            </div>
          </div>
          
        </div>
      </main>
      
  </body>

</html>