<?php require_once('auth.php'); ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php
      $webSiteTitle="Инструкция";
      require_once "head.php";
      require_once('visu_index.php');
    ?>
  </head>
  <body>
    <?php
      require_once "header.php";
      require_once "modals.php";
    ?>

    <script>
      document.getElementById("hdr-href-instruction.php").setAttribute("class", "nav-link text-primary");
    </script>

    <main>
      <div class="row row-cols-1 row-cols-sm-12 row-cols-md-12 row-cols-lg-12 row-cols-xl-12 row-cols-xxl-12 g-0">
          <div class="g-1" style="width:3%">
            <div class="col">

              <ul class="nav me-auto mt-2" style="margin-left: 5px;">
                <li>
                    <a href="#" id="instr-href-text" class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right" title="Инструкция по эксплуатации">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-journal" viewBox="0 0 16 16">
                        <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
                        <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
                      </svg>
                    </a>
                </li>
                <li>
                    <a href="#" id="instr-href-video" class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right" title="Видео">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-play-btn" viewBox="0 0 16 16">
                        <path d="M6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814l-3.5-2.5z"/>
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm15 0a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                      </svg>
                    </a>
                </li>
                <li>
                    <a href="https://marine-electric.com/" target=”_blank” id="instr-href-company-site" class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right" title="Сайт компании">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-window-sidebar" viewBox="0 0 16 16">
                        <path d="M2.5 4a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1zm2-.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm1 .5a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                        <path d="M2 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2H2zm12 1a1 1 0 0 1 1 1v2H1V3a1 1 0 0 1 1-1h12zM1 13V6h4v8H2a1 1 0 0 1-1-1zm5 1V6h9v7a1 1 0 0 1-1 1H6z"/>
                      </svg>
                    </a>
                </li>
                <li>
                    <a href="https://www.linkedin.com/company/neptunelectro/mycompany/" target=”_blank” id="instr-href-linkedin" class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right" title="LinkedIn">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-linkedin" viewBox="0 0 16 16">
                        <path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.016zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/>
                      </svg>
                    </a>
                </li>
            </ul>


            </div>
          </div>
    <script type="text/javascript" src="visu_instruction.js"></script>

          <div class="g-1" style="width:97%">
              <video id="instr-mp4-video" style="margin: 10px; height: calc(100vh - 110px); width: calc(100vw - 85px);" controls>
                <source src="video/instruction.mp4" type="video/mp4">
                Your browser does not support HTML video.
              </video>
              <iframe id="instr-pdf-document" src="docs/ТСС-02 - инструкция по эксплуатации ПО.pdf" style="margin: 10px; height: calc(100vh - 110px); width: calc(100vw - 85px);">
          </div>

      </div>
    </main>
          
  </body>
  
</html>