<?php require_once('auth.php'); ?>
<!doctype html>
<html lang="en">
  <head>
    <?php
      $webSiteTitle="DEBUG";
      require_once "head.php";
      require_once('visu_debug_page.php');
    ?>
  </head>
  <body>
    <?php
      require_once "header.php";
      require_once "modals.php";
      require_once ('configParameters.php');
    ?>
    <div class="container-fluid h-100">
      <main>
        <div class="row row-cols-1 row-cols-md-3 mb-3 text-center">
          <div class="col-md-6 g-1">
            <div class="card rounded-3 shadow-sm">
              <div class="card-header py-3" style="height: 60px;">
                <h4 class="my-0 fw-normal">Установка значений</h4>
              </div>
              <div class="card-body" style="height:750px; overflow: auto; text-align: left;" >
                <table>
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить температуру для всех датчиков силоса</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <form>
                    <td>
                      <select class="form-control"
                        id="dbg_silo_1" onchange="redrawRowOfSelects(event.target.id)"
                        style="width: 50px; text-align: right;">
                        <option value="1">1</option>
                      </select>
                    </td>
                    <td></td><td></td><td></td><td>
                    </td>
                    <td style="padding: 5px;">
                      Температура
                    </td>
                    <td>
                      <?php
                        echo "<input type=\"number\" class=\"form-control\" id=\"dbg_t_1\" name=\"POST_vDbgPage_dbg_1_temperature\" value=\"0\" style=\"width: 100px;\" $debugPageDisableElements>";
                      ?>
                    </td>
                    <td>
                      <button type="button" class="form-control" id="dbg_1_button"
                      onclick="onClickDbgBtn_1('dbg_silo_1','dbg_t_1')">
                      Установить</button>
                    </td>
                    </form>
                  </tr>
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить скорость для всех датчиков силоса</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <td>
                      <select class="form-control"
                        id="dbg_silo_2" onchange="redrawRowOfSelects(event.target.id)"
                        style="width: 50px; text-align: right;">
                        <option value="1">1</option>
                      </select>
                    </td>
                    <td></td><td></td><td></td><td>
                    </td>
                    <td style="padding: 5px;">
                      Скорость
                    </td>
                    <td>
                      <?php
                        echo "<input type=\"number\" class=\"form-control\" id=\"dbg_v_2\" name=\"POST_vDbgPage_dbg_2_t_speed\" value=\"0\" style=\"width: 100px;\" $debugPageDisableElements>";
                      ?>
                    </td>
                    <td>
                      <button type="button" class="form-control" id="dbg_2_button"
                      onclick="onClickDbgBtn_2('dbg_silo_2','dbg_v_2')">
                      Установить</button>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить уровень заполнения силоса</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <td>
                      <select class="form-control"
                        id="dbg_silo_3" onchange="redrawRowOfSelects(event.target.id)"
                        style="width: 50px; text-align: right;">
                        <option value="1">1</option>
                      </select>
                    </td>
                    <td></td><td></td><td></td><td>
                    </td>
                    <td style="padding: 5px;">
                      Уровень
                    </td>
                    <td>
                      <?php
                        echo "<select class=\"form-control\"
                        id=\"dbg_level_3\" onchange=\"redrawRowOfSelects(event.target.id)\"
                        style=\"width: 100px;\" $debugPageDisableElements>
                        <option value=\"0\">0</option>
                      </select>";
                      ?>
                      
                    </td>
                    <td>
                      <button class="form-control" type="button" id="dbg_3_button"
                      onclick="onClickDbgBtn_3('dbg_silo_3','dbg_level_3')">
                      Установить</button>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить температуру для всех датчиков подвески</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <td>
                      <select class="form-control"
                        id="dbg_silo_4" onchange="redrawRowOfSelects(event.target.id)"
                        style="width: 50px; text-align: right;">
                        <option value="1">1</option>
                      </select>
                    </td>
                    <td>
                      Подвеска
                    </td>
                    <td>
                      <select class="form-control"
                        id="dbg_podv_4" onchange="redrawRowOfSelects(event.target.id)"
                        style="width: 50px; text-align: right;">
                        <option value="1">1</option>
                      </select>
                    </td>
                    <td></td><td>
                    </td>
                    <td style="padding: 5px;">
                      Температура
                    </td>
                    <td>
                      <?php
                        echo "<input type=\"number\" class=\"form-control\" id=\"dbg_t_4\" name=\"dbg_t_4\" value=\"0\" style=\"width: 100px;\" $debugPageDisableElements>";
                      ?>
                    </td>
                    <td>
                      <button type="button" class="form-control" id="dbg_4_button"
                      onclick="onClickDbgBtn_4('dbg_silo_4', 'dbg_podv_4', 'dbg_t_4')">
                      Установить</button>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить скорость для всех датчиков подвески</td>
                  </tr>
                  <tr>
                  <td>
                    Силос
                  </td>
                  <td>
                    <select class="form-control"
                      id="dbg_silo_5" onchange="redrawRowOfSelects(event.target.id)"
                      style="width: 50px; text-align: right;">
                      <option value="1">1</option>
                    </select>
                  </td>
                  <td>
                    Подвеска
                  </td>
                  <td>
                    <select class="form-control"
                      id="dbg_podv_5" onchange="redrawRowOfSelects(event.target.id)"
                      style="width: 50px; text-align: right;">
                      <option value="1">1</option>
                    </select>
                  </td>
                  <td></td><td>
                  </td>
                  <td style="padding: 5px;">
                    Скорость
                  </td>
                  <td>
                    <?php
                      echo "<input type=\"number\" class=\"form-control\" id=\"dbg_v_5\" name=\"POST_vDbgPage_dbg_5_t_speed\" value=\"0\" style=\"width: 100px;\" $debugPageDisableElements>";
                    ?>
                  </td>
                  <td>
                    <button type="button" class="form-control" id="dbg_5_button"
                    onclick="onClickDbgBtn_5('dbg_silo_5', 'dbg_podv_5', 'dbg_v_5')">
                    Установить</button>
                  </td>
                  </tr>
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить температуру для одного датчика</td>
                  </tr>
                  <tr>
                  <td>
                    Силос
                  </td>
                  <td>
                    <select class="form-control"
                      id="dbg_silo_6" onchange="redrawRowOfSelects(event.target.id)"
                      style="width: 50px; text-align: right;">
                      <option value="1">1</option>
                    </select>
                  </td>
                  <td>
                    Подвеска
                  </td>
                  <td>
                    <select class="form-control"
                      id="dbg_podv_6" onchange="redrawRowOfSelects(event.target.id)"
                      style="width: 50px; text-align: right;">
                      <option value="1">1</option>
                    </select>
                  </td>
                  <td>
                    Датчик
                  </td>
                  <td>
                    <select class="form-control"
                      id="dbg_sensor_6" onchange="redrawRowOfSelects(event.target.id)"
                      style="width: 50px; text-align: right;">
                      <option value="1">1</option>
                    </select>
                  </td>
                  <td style="padding: 5px;">
                    Температура
                  </td>
                  <td>
                    <?php
                      echo "<input type=\"number\" class=\"form-control\" id=\"dbg_t_6\" name=\"POST_vDbgPage_dbg_6_temperature\" value=\"0\" style=\"width: 100px;\" $debugPageDisableElements>";
                    ?>
                  </td>
                  <td>
                    <button type="button" class="form-control" id="dbg_6_button"
                    onclick="onClickDbgBtn_6('dbg_silo_6', 'dbg_podv_6', 'dbg_sensor_6', 'dbg_t_6')">
                    Установить</button>
                  </td>
                  </tr>
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить скорость для одного датчика</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <td>
                      <select class="form-control"
                        id="dbg_silo_7" onchange="redrawRowOfSelects(event.target.id)"
                        style="width: 50px; text-align: right;">
                        <option value="1">1</option>
                      </select>
                    </td>
                    <td>
                      Подвеска
                    </td>
                    <td>
                      <select class="form-control"
                        id="dbg_podv_7" onchange="redrawRowOfSelects(event.target.id)"
                        style="width: 50px; text-align: right;">
                        <option value="1">1</option>
                      </select>
                    </td>
                    <td>
                      Датчик
                    </td>
                    <td>
                      <select class="form-control"
                        id="dbg_sensor_7" onchange="redrawRowOfSelects(event.target.id)"
                        style="width: 50px; text-align: right;">
                        <option value="1">1</option>
                      </select>
                    </td>
                    <td style="padding: 5px;">
                      Скорость
                    </td>
                    <td>
                      <?php
                        echo "<input type=\"number\" class=\"form-control\" id=\"dbg_v_7\" name=\"POST_vDbgPage_dbg_7_t_speed\" value=\"0\" style=\"width: 100px;\" $debugPageDisableElements>";
                      ?>
                    </td>
                    <td>
                      <button type="button" class="form-control" id="dbg_7_button"
                      onclick="onClickDbgBtn_7('dbg_silo_7', 'dbg_podv_7', 'dbg_sensor_7', 'dbg_v_7')">Установить</button>
                    </td>
                  </tr>
                </table>

                <button type="button" class="form-control mt-5" id="dbg_8_button" style="position: absolute; width: 300px; bottom: 8%;"
                onclick="onClickDbgBtn_8()">Обнулить все показания</button>

                <button type="button" class="form-control"
                        id="dbg_write_measurements_to_db"
                        style="position: absolute; width: 300px; bottom: 2%;"
                        onclick="onClickDbgBtn_AddMeas()">
                    Сохранить текущие показания в БД
                </button>
                <?php
                  if($simulation_mode){
                    echo "<div style=\"position: absolute; bottom: 2%; right:2%\">ВКЛЮЧЕН РЕЖИМ ОТЛАДКИ</div>";
                  }
                ?>
              </div>
            </div>
          </div>
          <div class="col-md-6 g-1">
            <div class="card rounded-3 shadow-sm">
              <div class="card-header py-3" style="height: 60px;">
                <h4 class="my-0 fw-normal align-center" id="CurrentSiloName" style="position: absolute; top: auto; left: 40%;">Параметры</h4>
              </div>  
              <div class="card-body d-flex justify-content-center" style="height:750px; overflow: auto;" id="debug_parameters_table">
                <?php
                  echo vDbg_draw_debugParamsTable($dbh);
                ?>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
    <script type="text/javascript" src="visu_debug_page.js"></script>
  </body>
</html>