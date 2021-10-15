<!doctype html>
<html lang="en">
  <head>
    <?php
      $webSiteTitle="DEBUG"; require_once "blocks/head.php";      
      require_once($_SERVER['DOCUMENT_ROOT'].'/webTermometry/visualisation/visu_report.php');
    ?>
  </head>

  <body>
    
    <div class="container-fluid h-100">

      <?php
        require_once "blocks/header.php";
      ?>

      <main>

        <div class="row row-cols-1 row-cols-md-3 mb-3 text-center">

          <div class="col-md-6">
            <div class="card rounded-3 shadow-sm">
              <div class="card-header py-3">
                <h4 class="my-0 fw-normal">Установка значений</h4>
              </div>
              <div class="card-body" style="height:750px; overflow: auto; text-align: left;" >
                <table>
                  
                  <!--  -->
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить температуру для всех датчиков силоса</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <form>
                    <td>
                      <input type="number" id="dbg_1_silo_num" name="dbg_1_silo_num" value="0" style="width: 50px;">
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td style="padding: 5px;">
                      Температура
                    </td>
                    <td>
                      <input type="number" id="dbg_1_temperature" name="dbg_1_temperature" value="0" style="width: 100px;">
                    </td>
                    <td>
                      <button type="button" id="dbg_1_button">Установить</button>
                    </td>
                    </form>
                  </tr>

                  <script>
                    $('#dbg_1_button').click(function(){

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_1_silo_num': $('#dbg_1_silo_num').val(), 'dbg_1_temperature': $('#dbg_1_temperature').val() },
                          dataType: 'html',
                          success: function(fromPHP) { alert(fromPHP) }
                      });

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_refresh': 1 },
                          dataType: 'html',
                          success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
                      });

                    });
                  </script>

                  <!--  -->
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить скорость для всех датчиков силоса</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <td>
                      <input type="number" id="dbg_2_silo_num" name="dbg_2_silo_num" value="0" style="width: 50px;">
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td style="padding: 5px;">
                      Скорость
                    </td>
                    <td>
                      <input type="number" id="dbg_2_t_speed" name="dbg_2_t_speed" value="0" style="width: 100px;">
                    </td>
                    <td>
                      <button type="button" id="dbg_2_button">Установить</button>
                    </td>
                  </tr>

                  <script>
                    $('#dbg_2_button').click(function(){

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_2_silo_num': $('#dbg_2_silo_num').val(), 'dbg_2_t_speed': $('#dbg_2_t_speed').val() },
                          dataType: 'html',
                          success: function(fromPHP) { alert(fromPHP) }
                      });

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_refresh': 1 },
                          dataType: 'html',
                          success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
                      });

                    });
                  </script>

                  <!--  -->
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить уровень заполнения силоса</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <td>
                      <input type="number" id="dbg_3_silo_num" name="dbg_3_silo_num" value="0" style="width: 50px;">
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td style="padding: 5px;">
                      Уровень
                    </td>
                    <td>
                      <input type="number" id="dbg_3_grain_level" name="dbg_3_grain_level" value="0" style="width: 100px;">
                    </td>
                    <td>
                      <button type="button" id="dbg_3_button">Установить</button>
                    </td>
                  </tr>

                  <script>
                    $('#dbg_3_button').click(function(){

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_3_silo_num': $('#dbg_3_silo_num').val(), 'dbg_3_grain_level': $('#dbg_3_grain_level').val() },
                          dataType: 'html',
                          success: function(fromPHP) { alert(fromPHP) }
                      });

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_refresh': 1 },
                          dataType: 'html',
                          success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
                      });

                    });
                  </script>

                  <!--  -->
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить температуру для всех датчиков подвески</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <td>
                      <input type="number" id="dbg_4_silo_num" name="dbg_4_silo_num" value="0" style="width: 50px;">
                    </td>
                    <td>
                      Подвеска
                    </td>
                    <td>
                      <input type="number" id="dbg_4_podv_num" name="dbg_4_podv_num" value="0" style="width: 50px;">
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td style="padding: 5px;">
                      Температура
                    </td>
                    <td>
                      <input type="number" id="dbg_4_temperature" name="dbg_4_temperature" value="0" style="width: 100px;">
                    </td>

                    <td>
                      <button type="button" id="dbg_4_button">Установить</button>
                    </td>
                  </tr>

                  <script>
                    $('#dbg_4_button').click(function(){

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_4_silo_num': $('#dbg_4_silo_num').val(),
                                  'dbg_4_podv_num': $('#dbg_4_podv_num').val(),
                                  'dbg_4_temperature': $('#dbg_4_temperature').val() },
                          dataType: 'html',
                          success: function(fromPHP) { alert(fromPHP) }
                      });

                    $.ajax({
                        url: 'debug/debugScript.php',
                        type: 'POST',
                        cache: false,
                        data: { 'dbg_refresh': 1 },
                        dataType: 'html',
                        success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
                    });

                    });
                  </script>

                  <!--  -->
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить скорость для всех датчиков подвески</td>
                  </tr>
                  <tr>
                  <td>
                    Силос
                  </td>
                  <td>
                    <input type="number" id="dbg_5_silo_num" name="dbg_5_silo_num" value="0" style="width: 50px;">
                  </td>
                  <td>
                    Подвеска
                  </td>
                  <td>
                    <input type="number" id="dbg_5_podv_num" name="dbg_5_podv_num" value="0" style="width: 50px;">
                  </td>
                  <td>
                  </td>
                  <td>
                  </td>
                  <td style="padding: 5px;">
                    Скорость
                  </td>
                  <td>
                    <input type="number" id="dbg_5_t_speed" name="dbg_5_t_speed" value="0" style="width: 100px;">
                  </td>
                  <td>
                    <button type="button" id="dbg_5_button">Установить</button>
                  </td>
                  </tr>

                  <script>
                    $('#dbg_5_button').click(function(){

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_5_silo_num': $('#dbg_5_silo_num').val(),
                                  'dbg_5_podv_num': $('#dbg_5_podv_num').val(),
                                  'dbg_5_t_speed': $('#dbg_5_t_speed').val() },
                          dataType: 'html',
                          success: function(fromPHP) { alert(fromPHP) }
                      });

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_refresh': 1 },
                          dataType: 'html',
                          success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
                      });

                    });
                  </script>

                  <!--  -->
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить температуру для одного датчика</td>
                  </tr>
                  <tr>
                  <td>
                    Силос
                  </td>
                  <td>
                    <input type="number" id="dbg_6_silo_num" name="dbg_6_silo_num" value="0" style="width: 50px;">
                  </td>
                  <td>
                    Подвеска
                  </td>
                  <td>
                    <input type="number" id="dbg_6_podv_num" name="dbg_6_podv_num" value="0" style="width: 50px;">
                  </td>
                  <td>
                    Датчик
                  </td>
                  <td>
                    <input type="number" id="dbg_6_sensor_num" name="dbg_6_sensor_num" value="0" style="width: 50px;">
                  </td>
                  <td style="padding: 5px;">
                    Температура
                  </td>
                  <td>
                    <input type="number" id="dbg_6_temperature" name="dbg_6_temperature" value="0" style="width: 100px;">
                  </td>
                  <td>
                    <button type="button" id="dbg_6_button">Установить</button>
                  </td>
                  </tr>

                  <script>
                    $('#dbg_6_button').click(function(){

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_6_silo_num': $('#dbg_6_silo_num').val(),
                                  'dbg_6_podv_num': $('#dbg_6_podv_num').val(),
                                  'dbg_6_sensor_num': $('#dbg_6_sensor_num').val(),
                                  'dbg_6_temperature': $('#dbg_6_temperature').val() },
                          dataType: 'html',
                          success: function(fromPHP) { alert(fromPHP) }
                      });

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_refresh': 1 },
                          dataType: 'html',
                          success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
                      });

                    });
                  </script>

                  <!--  -->
                  <tr>
                    <td colspan="5" style="padding: 10px; font-weight: bold;">Установить скорость для одного датчика</td>
                  </tr>
                  <tr>
                    <td>
                      Силос
                    </td>
                    <td>
                      <input type="number" id="dbg_7_silo_num" name="dbg_7_silo_num" value="0" style="width: 50px;">
                    </td>
                    <td>
                      Подвеска
                    </td>
                    <td>
                      <input type="number" id="dbg_7_podv_num" name="dbg_7_podv_num" value="0" style="width: 50px;">
                    </td>
                    <td>
                      Датчик
                    </td>
                    <td>
                      <input type="number" id="dbg_7_sensor_num" name="dbg_7_sensor_num" value="0" style="width: 50px;">
                    </td>
                    <td style="padding: 5px;">
                      Скорость
                    </td>
                    <td>
                      <input type="number" id="dbg_7_t_speed" name="dbg_7_t_speed" value="0" style="width: 100px;">
                    </td>
                    <td>
                      <button type="button" id="dbg_7_button">Установить</button>
                    </td>
                  </tr>

                  <script>
                    $('#dbg_7_button').click(function(){

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_7_silo_num': $('#dbg_7_silo_num').val(),
                                  'dbg_7_podv_num': $('#dbg_7_podv_num').val(),
                                  'dbg_7_sensor_num': $('#dbg_7_sensor_num').val(),
                                  'dbg_7_t_speed': $('#dbg_7_t_speed').val() },
                          dataType: 'html',
                          success: function(fromPHP) { alert(fromPHP) }
                      });

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_refresh': 1 },
                          dataType: 'html',
                          success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
                      });

                    });
                  </script>

                  <tr>
                    <td colspan="10" style="text-align: center; padding: 10px; font-weight: bold;">ВСЕ ЗНАЧЕНИЯ НАЧИНАЮТСЯ С НУЛЯ !</td>
                  </tr>

                </table>

                <button type="button" id="dbg_write_measurements_to_db" style="position: absolute; bottom: 2%;">Сохранить текущие показания в БД</button>
                
                <?php
                  require_once "php/currValsFromTS.php";

                  if($simulation_mode){
                    echo "<div style=\"position: absolute; bottom: 2%; right:2%\">ВКЛЮЧЕН РЕЖИМ ОТЛАДКИ</div>";
                  }
                ?>

              </div>
            </div>
          </div>

          <script>
            $('#dbg_write_measurements_to_db').click(function(){

              $.ajax({
                  url: 'debug/debugScript.php',
                  type: 'POST',
                  cache: false,
                  data: { 'write_measurements_to_db': 1 },
                  dataType: 'html',
                  success: function(fromPHP) { alert(fromPHP) }
              });

            });
          </script>

          <div class="col-md-6 align-bottom">
            <div class="card mb-4 rounded-3 shadow-sm border-primary">
              <div class="card-header py-3 text-black border-primary align-bottom" style="vertical-align: bottom;">
                <h4 class="my-0 fw-normal align-center" id="CurrentSiloName" style="position: absolute; top: auto; left: 40%;">Парметры</h4>

                <button type="button" id="dbg_refresh" style="position: relative; float: right;">Обновить</button>

                <script>
                    $('#dbg_refresh').click(function(){

                      $.ajax({
                          url: 'debug/debugScript.php',
                          type: 'POST',
                          cache: false,
                          data: { 'dbg_refresh': 1 },
                          dataType: 'html',
                          success: function(fromPHP) { document.getElementById("debug_parameters_table").innerHTML = fromPHP; }
                      });

                      

                    });

                    </script>

              </div>  
              <div class="card-body d-flex justify-content-center" style="height:750px; overflow: auto;" id="debug_parameters_table">

                <?php
                  require_once "debug/debugScript.php";
                  echo debug_get_debug_table();
                ?>
              
              </div>

            </div>
          </div>

        </div>

      </main>

    </div>
    
  </body>
</html>