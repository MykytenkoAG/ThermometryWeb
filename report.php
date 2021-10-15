<!doctype html>
<html lang="en">
  <head>
    <?php
      $webSiteTitle="Отчет"; require_once "blocks/head.php";      
      require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/visualisation/visu_report.php');
    ?>
  </head>
  <body>
      <?php require_once "blocks/header.php"; ?>
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
                        echo createMeasurementCheckboxes(getAllMeasurementDates());
                      ?>
                    </div>

                  </div>

                  <div class="col-8">

                    <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-1">

                      <div class="col">
                        <h5 style="font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Печатные данные</h5>
                        
                        <div class="form-check mt-2">
                          <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
                          <label class="form-check-label" for="exampleRadios1">
                            Средние температуры в слоях
                          </label>
                        </div>
                        <div class="form-check mt-1">
                          <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
                          <label class="form-check-label" for="exampleRadios2">
                            Температуры каждого датчика в слоях
                          </label>
                        </div>
                        <div class="form-check mt-1">
                          <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios3" value="option3">
                          <label class="form-check-label" for="exampleRadios3">
                            Температуры датчика в подвеске
                          </label>
                        </div>

                      </div>

                      <div class="col mt-3">
                        <h5 style="font-family: Arial, Helvetica, sans-serif; font-size: 16px;">Входные данные</h5>

                        <div class="mt-2">
                          <label for="rprt-pr-f-silo-name" class="form-label">Силос</label>
                          <select class="form-control" style="width: 70px" id="rprt-pr-f-silo-name" onchange="prFormChangedSilo()">
                            <option value="all">все</option>
                          </select>

                          <div class="row mt-2">

                            <div class="col-4 mr-1">
                              <label for="rprt-pr-f-podv-num" class="form-label">Подвеска</label>
                              <select class="form-control" style="width: 70px" id="rprt-pr-f-podv-num" onchange="prFormChangedPodv()">
                                <option value="all">все</option>
                              </select>
                            </div>

                            <div class="col-4 mr-1">
                              <label for="rprt-pr-f-layer-num" class="form-label">Слой</label>
                              <select class="form-control" style="width: 70px" id="rprt-pr-f-layer-num">
                                <option value="all">все</option>
                              </select>
                            </div>

                            <div class="col-4 mr-1">
                              <label for="rprt-pr-f-sensor-num" class="form-label">Датчик</label>
                              <select class="form-control" style="width: 70px" id="rprt-pr-f-sensor-num">
                                <option value="all">все</option>
                              </select>
                            </div>

                          </div>

                          <div class="row">

                            <div class="col p-1">
                              <button type="submit" class="btn btn-primary mt-3 w-100" onclick="">
                                <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                PDF
                              </button>
                            </div>

                            <div class="col p-1">
                              <button type="submit" class="btn btn-primary mt-3 w-100" onclick="">
                                <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                XLS
                              </button>
                            </div>

                            <div class="col p-1">
                              <button type="submit" class="btn btn-primary mt-3 w-100" onclick="">
                                <svg width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                  <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                  <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                </svg>
                                CSV
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
                <script src="visualisation/visu_report.js"></script>
                
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
                          <!--<input type="number" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">-->
                          <select class="form-control" style="width: 70px" id="rprt-chart-silo-name" onchange="chartChangedSilo()">
                            <option value="1">1</option>
                            <option value="2">2</option>
                          </select>
                        </td>
                        <td>
                          <!--<input type="number" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">-->
                          <select class="form-control" style="width: 70px" id="rprt-chart-podv-num" onchange="chartChangedPodv()">
                            <option value="1">1</option>
                          </select>
                        </td>
                        <td>
                          <!--<input type="number" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-sm">-->
                          <select class="form-control" style="width: 70px" id="rprt-chart-sensor-num">
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

                  <button type="submit" class="btn btn-primary" onclick="addNewLineOnChart()">
                    <svg width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                      <path d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z"/>
                    </svg>
                    Добавить
                  </button>

                </div>
              </div>
            </div>
          </div>

        </div>
      </main>
      
  </body>
</html>