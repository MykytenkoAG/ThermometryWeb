<?php require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/scripts/auth.php'); ?>
<!doctype html>

<html lang="en">
  <head>
    
    <?php
      $webSiteTitle="Отчет";
      require_once "blocks/head.php";
      require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/visualisation/visu_report.php');
    ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.2/vfs_fonts.min.js"></script>
    <script src="node_modules/xlsx/dist/xlsx.full.min.js"></script>

  </head>
  <body>
      <?php
        require_once "blocks/header.php";
        require_once "blocks/modals.php";
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
                                <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                PDF
                              </button>
                            </div>

                            <div class="col p-1">
                              <button type="submit" class="btn btn-primary mt-3 w-100" id="rprtprf-btn-download-XLS" onclick="vRep_getJSONForPrintedForms('XLSX');">
                                <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
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
                <canvas id="myChart"></canvas>

                <script type="text/javascript" src="node_modules/chart.js/dist/chart.js"></script>
                <script src="node_modules/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.js"></script>
                <script type="text/javascript" src="visualisation/visu_report.js"></script>
                <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
                <script type="text/javascript" src="node_modules/jspdf/dist/jspdf.umd.min.js"></script>-->


                <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js"></script>

                

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

                  <table class="table table-hover text-center" id="sensor-temperatures-table">
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
                      <table>
                        <tr>
                          <td>
                            <svg width="16" height="16" fill="currentColor" class="bi d-block mx-auto mt-1" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5z"/>
                            </svg>
                          </td>
                          <td>
                            <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                              <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                              <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                            </svg>
                          </td>
                          <td>
                            PDF
                          </td>
                        </tr>
                      </table>
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
      
      <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.8.0/html2pdf.bundle.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.debug.js"></script>


      
      
      <script src="node_modules/html2pdf.js/dist/html2pdf.js"></script>
      <script src="node_modules/html2canvas/dist/html2canvas.min.js"></script>
  </body>

</html>