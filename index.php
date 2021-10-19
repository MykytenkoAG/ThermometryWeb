<!doctype html>
<html lang="ru">
  <head>
    <?php
      $webSiteTitle="Термометрия"; require_once "blocks/head.php";
      require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/visualisation/visu_index.php');
    ?>
    <script type="text/javascript" src="visualisation/visu_index.js"></script>
  </head>
  <body>
      <?php require_once "blocks/header.php"; ?>

      <script>

      </script>

      <style>
        .silo-number:hover{
          background-color: green;
        }

        .silo:hover div {
          background-color: blue;
          color: white;
        }
        </style>

      <main>
        <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1 row-cols-lg-2 row-cols-xl-2 row-cols-xxl-3 g-0">

          <div class="col-12 col-md-5 col-lg-4 col-xl-4 col-xxl-3 g-1">
            <div class="card h-100">
              <div class="card-body ">

                <div class="d-flex justify-content-center">
                  <h5 class="card-title" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;">
                    <svg width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle pb-1" viewBox="0 0 16 16">
                      <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                    </svg>
                    АПС
                  </h5>
                </div>

                <div class="card-body" style="padding:0px;">

                  <div class="d-flex justify-content-center">

                    <div class="" style="width: 400px;">

                      <table class="" style="padding: 0px; text-align: left;">
                        <tr>
                          <th style="margin: 0px; padding: 0px; padding-left: 10px; width: 140px;">
                            Время
                          </th>
                          <th style="margin: 0px; padding: 0px; width: 60px;">
                            Силос
                          </th>
                          <th style="margin: 0px; padding: 0px; width: 30px;">
                            ТП
                          </th>
                          <th style="margin: 0px; padding: 0px; width: 40px;">
                            НД
                          </th>
                          <th style="margin: 0px; padding: 0px; width: 120px;">
                            АПС
                          </th>
                        </tr>
                      </table>

                    </div>

                  </div>

                  <div class="col p-1 mx-auto" style="max-height: 650px; width: 430px; overflow: auto; text-align: left; border-style: solid; border-color: gray;
                                                      border-width: 1px;">
                    <div class="" id="ind-table-alarms">
                      <?php
                        echo getCurrentAlarms();
                      ?>
                    </div>
                  </div>

                  <div class="col-sm p-1">
                    <button type="button" class="btn btn-light" style="width: 100%;">
                      Отключить все неисправные датчики
                    </button>
                  </div>

                  <div class="col-sm p-1">
                    <button type="button" class="btn btn-light" style="width: 100%;">
                      Включить все отключенные датчики
                    </button>
                  </div>

                  <div class="col-sm p-1">
                    <button type="button" class="btn btn-light" style="width: 100%;">
                      Отключить автоопределение уровня на всех силосах
                    </button>
                  </div>

                </div>

              </div>
              
            </div>

          </div>

          <div class="col-12 col-md-7 col-lg-8 col-xl-8 col-xxl-5 g-1">
            <div class="card h-100">
              <div class="card-body">

                <div class="d-flex justify-content-center">
                  <h5 class="card-title justify-content-center" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;">План расположения силосов</h5>
                </div>

                <div class="row row-cols-2">

                  <div class="col-1 col-sm-1 col-md-0 col-lg-0 col-xl-0 col-xxl-0" style="height: 20vh;"></div>

                  <div class="col-10 col-sm-10 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                    <?php
                      echo createSiloPlan();
                    ?>
                  </div>

                  <div class="col-1 col-sm-1 col-md-0 col-lg-0 col-xl-0 col-xxl-0" style="height: 0px;"></div>

                </div>

              </div>
            </div>
          </div>

          <div class="col-12 col-xs-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-4 g-1">
            <div class="card h-100">
              <div class="card-body">

                <div class="d-flex justify-content-center">
                  <h5 class="card-title justify-content-center" id="current-silo-name" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px;">Силос 1</h5>
                </div>

                <div class="col mt-3">
                  <div class="row">

                    <div class="col-4" style="padding-left: 5px; padding-right: 5px;">
                      <button type="button" id="btn-temperatures" onClick="onBtnClicked(event.target.id)" class="btn btn-light"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Отображение текущих температур каждого датчика" style="width: 100%;">
                        <svg width="16" height="16" fill="currentColor" class="bi bi-thermometer-half" viewBox="0 0 16 16">
                          <path d="M9.5 12.5a1.5 1.5 0 1 1-2-1.415V6.5a.5.5 0 0 1 1 0v4.585a1.5 1.5 0 0 1 1 1.415z"/>
                          <path d="M5.5 2.5a2.5 2.5 0 0 1 5 0v7.55a3.5 3.5 0 1 1-5 0V2.5zM8 1a1.5 1.5 0 0 0-1.5 1.5v7.987l-.167.15a2.5 2.5 0 1 0 3.333 0l-.166-.15V2.5A1.5 1.5 0 0 0 8 1z"/>
                        </svg>
                        Температуры, &deg;C
                      </button>
                    </div>

                    <div class="col-4" style="padding-left: 5px; padding-right: 5px;">
                      <button type="button"  id="btn-speeds" onClick="onBtnClicked(event.target.id)"  class="btn btn-light"
                        data-bs-toggle="tooltip" data-bs-placement="right" title="Отображение скоростей изменения температуры каждого датчика" style="width: 100%;">
                        <svg width="16" height="16" fill="currentColor" class="bi bi-thermometer-half" viewBox="0 0 16 16">
                          <path d="M9.5 12.5a1.5 1.5 0 1 1-2-1.415V6.5a.5.5 0 0 1 1 0v4.585a1.5 1.5 0 0 1 1 1.415z"/>
                          <path d="M5.5 2.5a2.5 2.5 0 0 1 5 0v7.55a3.5 3.5 0 1 1-5 0V2.5zM8 1a1.5 1.5 0 0 0-1.5 1.5v7.987l-.167.15a2.5 2.5 0 1 0 3.333 0l-.166-.15V2.5A1.5 1.5 0 0 0 8 1z"/>
                        </svg>
                        <svg width="16" height="16" fill="currentColor" class="bi bi-graph-up" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5z"/>
                        </svg>
                        Скорости, &deg;C/сут.
                      </button>
                    </div>

                    <div class="col-4"
                        data-bs-toggle="tooltip" data-bs-placement="right"
                        title="Отображение характеристик продукта"
                        style="padding-left: 5px; padding-right: 5px;">
                      <button type="button" id="ind-btn-parameters" class="btn btn-light" type="button"
                              data-bs-toggle="collapse"
                              data-bs-target=".product-characteristics"
                              aria-expanded="false" style="width: 100%;">
                        <svg width="16" height="16" fill="currentColor" class="bi bi-list-task" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M2 2.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-.5-.5H2zM3 3H2v1h1V3z"/>
                          <path d="M5 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM5.5 7a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9zm0 4a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9z"/>
                          <path fill-rule="evenodd" d="M1.5 7a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5V7zM2 7h1v1H2V7zm0 3.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5H2zm1 .5H2v1h1v-1z"/>
                        </svg>
                        Параметры
                      </button>
                    </div>

                  </div>
                </div>

                <div style="display: grid; height: 750px;">

                  <div class="mt-3 collapse product-characteristics">
                    <table class="table">
                      <tbody>
                        <tr>
                          <td style="padding-top: 3px; padding-bottom: 3px;">Продукт</td>
                          <td id="ind-prod-tbl-1-prodtype" class="table-active" style="padding-top: 3px; padding-bottom: 3px;">Пшеница-кл.1 вл.10% сорн.1%</td>
                          <td style="padding-top: 3px; padding-bottom: 3px;">Tмакс.</td>
                          <td id="ind-prod-tbl-1-t-max" class="table-active" style="padding-top: 3px; padding-bottom: 3px;">35&deg;C</td>
                          <td style="padding-top: 3px; padding-bottom: 3px;">Vмакс.</td>
                          <td id="ind-prod-tbl-1-v-max" class="table-active" style="padding-top: 3px; padding-bottom: 3px;">3&deg;C/сут.</td>
                        </tr>
                        <tr>
                          <td colspan="6" style="padding: 3px;">Температура</td>
                        </tr>
                        <tr>
                          <td colspan="6" style="margin: 0px; padding: 3px;">
                            <div class="row m-1" style="height: 25px; background-image: linear-gradient(to right, #00FF00, yellow, red);">
                              <div id="ind-prod-tbl-3-t-min" class="col" style="padding-bottom: 3px; text-align: left;">20</div>
                              <div id="ind-prod-tbl-3-t-avg" class="col" style="padding-bottom: 3px; text-align: center;">25</div>
                              <div id="ind-prod-tbl-3-t-max" class="col" style="padding-bottom: 3px; text-align: right;">30</div>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="6" style="padding: 3px;">Скорость изменения температуры</td>
                        </tr>
                        <tr>
                          <td colspan="6" style="padding: 3px;">
                            <div class="row m-1" style="height: 25px; background-image: linear-gradient(to right, #00FF00, yellow, red);">
                              <div id="ind-prod-tbl-5-v-min" class="col" style="padding-bottom: 3px; text-align: left;">2.0</div>
                              <div id="ind-prod-tbl-5-v-avg" class="col" style="padding-bottom: 3px; text-align: center;">2.5</div>
                              <div id="ind-prod-tbl-5-v-max" class="col" style="padding-bottom: 3px; text-align: right;">3.0</div>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="6" style="padding: 3px;">
                            <div>
                              <div style="float: left; margin-left: 5px; padding: 3px;">Диапазон температур </div>
                              <div id="ind-prod-tbl-6-rng-t-min" style="float: left; margin-left: 5px; padding: 3px;">20&deg;C</div>
                              <div style="float: left; margin-left: 0px; padding: 2px;">..</div>
                              <div id="ind-prod-tbl-6-rng-t-max" style="float: left; margin-left: 5px; padding: 3px;">30&deg;C</div>
                              <div style="float: left; margin-left: 25px; padding: 3px;">Максимальная скорость </div>
                              <div id="ind-prod-tbl-6-v-max" style="float: left; margin-left: 5px; padding: 3px;">3&deg;C/сут.</div>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>


                  <div class="align-bottom" id="silo-param-table" style="position:absolute; margin-top: auto; margin-bottom: 0px; margin-left:auto; margin-right:auto; overflow: auto;">
                      <?php
                        echo createTemperaturesTable(0);
                      ?>
                  </div>

                </div>
                
              </div>

            </div>
          </div>

        </div>

      </main>
      
  </body>
</html>