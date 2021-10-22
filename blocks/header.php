<header>
    
    <nav class="py-1 bg-light border-bottom">
        <div class="container-fluid d-flex flex-wrap">
            <ul class="nav me-auto">

                <li>
                    <a href="#" id="hdr-href-debug_page" class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right" onclick="onPageChange(0)"
title="Режим симуляции данных">
                        <svg width="24" height="24" fill="currentColor" class="bi d-block mx-auto mt-1" viewBox="0 0 16 16">
                            <path d="M4.978.855a.5.5 0 1 0-.956.29l.41 1.352A4.985 4.985 0 0 0 3 6h10a4.985 4.985 0 0 0-1.432-3.503l.41-1.352a.5.5 0 1 0-.956-.29l-.291.956A4.978 4.978 0 0 0 8 1a4.979 4.979 0 0 0-2.731.811l-.29-.956z"/>
                            <path d="M13 6v1H8.5v8.975A5 5 0 0 0 13 11h.5a.5.5 0 0 1 .5.5v.5a.5.5 0 1 0 1 0v-.5a1.5 1.5 0 0 0-1.5-1.5H13V9h1.5a.5.5 0 0 0 0-1H13V7h.5A1.5 1.5 0 0 0 15 5.5V5a.5.5 0 0 0-1 0v.5a.5.5 0 0 1-.5.5H13zm-5.5 9.975V7H3V6h-.5a.5.5 0 0 1-.5-.5V5a.5.5 0 0 0-1 0v.5A1.5 1.5 0 0 0 2.5 7H3v1H1.5a.5.5 0 0 0 0 1H3v1h-.5A1.5 1.5 0 0 0 1 11.5v.5a.5.5 0 1 0 1 0v-.5a.5.5 0 0 1 .5-.5H3a5 5 0 0 0 4.5 4.975z"/>
                        </svg>
                        Отладка
                    </a>
                </li>
            
                <li>
                    <a href="#" id="hdr-href-index" class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right" onclick="onPageChange(1)"
title="Просмотр активных сигналов АПС
Отображение температур и скоростей их изменения">
                        <svg width="24" height="24" fill="currentColor" class="bi d-block mx-auto mt-1" viewBox="0 0 16 16">
                            <path d="M9.5 12.5a1.5 1.5 0 1 1-2-1.415V6.5a.5.5 0 0 1 1 0v4.585a1.5 1.5 0 0 1 1 1.415z"/>
                            <path d="M5.5 2.5a2.5 2.5 0 0 1 5 0v7.55a3.5 3.5 0 1 1-5 0V2.5zM8 1a1.5 1.5 0 0 0-1.5 1.5v7.987l-.167.15a2.5 2.5 0 1 0 3.333 0l-.166-.15V2.5A1.5 1.5 0 0 0 8 1z"/>
                        </svg>
                        Главная
                    </a>
                </li>

                <li>
                    <a href="#" id="hdr-href-report" class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right"  onclick="onPageChange(2)"
title="Построение графиков температур
Формирование отчетов">
                        <svg width="24" height="24" fill="currentColor" class="bi d-block mx-auto mt-1" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5z"/>
                        </svg>
                        Отчет
                    </a>
                </li>

                <li>
                    <a href="#" id="hdr-href-silo_config" class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right"  onclick="onPageChange(3)"
title="Настройки параметров продукта
Распределение продуктов по силосам">
                        <svg width="24" height="24" fill="currentColor" class="bi d-block mx-auto mt-1" viewBox="0 0 16 16">
                            <path d="M.102 2.223A3.004 3.004 0 0 0 3.78 5.897l6.341 6.252A3.003 3.003 0 0 0 13 16a3 3 0 1 0-.851-5.878L5.897 3.781A3.004 3.004 0 0 0 2.223.1l2.141 2.142L4 4l-1.757.364L.102 2.223zm13.37 9.019.528.026.287.445.445.287.026.529L15 13l-.242.471-.026.529-.445.287-.287.445-.529.026L13 15l-.471-.242-.529-.026-.287-.445-.445-.287-.026-.529L11 13l.242-.471.026-.529.445-.287.287-.445.529-.026L13 11l.471.242z"/>
                        </svg>
                        Настройки
                    </a>
                </li>

                <li>
                    <a href="#" id="hdr-href-instruction" class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right"
title="Инструкция по применению системы">
                        <svg width="24" height="24" fill="currentColor" class="bi d-block mx-auto mt-1" viewBox="0 0 16 16">
                            <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
                        </svg>
                        Инструкция
                    </a>
                </li>

            </ul>
            <ul class="nav">
                <li>
                    <a href="#" id="hdr-ack"  class="nav-link text-black" data-bs-toggle="tooltip" data-bs-placement="right" onclick="acknowledgeAlarms()"
title="Квитировать сигналы АПС">
                    <svg width="24" height="24" fill="currentColor" class="bi d-block mx-auto mt-1" viewBox="0 0 16 16">
                        <path d="M5.164 14H15c-1.5-1-2-5.902-2-7 0-.264-.02-.523-.06-.776L5.164 14zm6.288-10.617A4.988 4.988 0 0 0 8.995 2.1a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 7c0 .898-.335 4.342-1.278 6.113l9.73-9.73zM10 15a2 2 0 1 1-4 0h4zm-9.375.625a.53.53 0 0 0 .75.75l14.75-14.75a.53.53 0 0 0-.75-.75L.625 15.625z"/>
                    </svg>
                        Квитировать АПС
                    </a>
                </li>
<?php
    if( in_array( $accessLevel, array(0,1) ) ){
        echo "
                <li>
                    <a href=\"#\" id=\"hdr-auth-oper\" class=\"nav-link text-black\" data-bs-toggle=\"tooltip\" data-bs-placement=\"right\"
title=\"Авторизоваться как Оператор\">
                        <svg width=\"24\" height=\"24\" fill=\"currentColor\" class=\"bi d-block mx-auto mt-1\" viewBox=\"0 0 16 16\">
                            <path d=\"M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z\"/>                
                        </svg>
                        Оператор
                    </a>
                </li>
        ";
    }

    if( in_array( $accessLevel, array(0,2) ) ){

        echo "
                <li>
                    <a href=\"#\" id=\"hdr-auth-tehn\" class=\"nav-link text-black\" data-bs-toggle=\"tooltip\" data-bs-placement=\"right\"
title=\"Авторизоваться как технолог\">
                        <svg width=\"24\" height=\"24\" fill=\"currentColor\" class=\"bi d-block mx-auto mt-1\" viewBox=\"0 0 16 16\">
                            <path d=\"M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z\"/>                
                        </svg>
                        Технолог
                    </a>
                </li>
        ";

    }

    if($accessLevel>0){
        echo "
                <li>
                    <a href=\"#\" id=\"hdr-sign-out\" class=\"nav-link text-black\" onclick=\"auth_sign_out()\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" width=\"24\" height=\"24\" fill=\"currentColor\" class=\"bi d-block mx-auto mt-1\" viewBox=\"0 0 16 16\">
                            <path d=\"M14 0a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h12zM5.904 10.803 10 6.707v2.768a.5.5 0 0 0 1 0V5.5a.5.5 0 0 0-.5-.5H6.525a.5.5 0 1 0 0 1h2.768l-4.096 4.096a.5.5 0 0 0 .707.707z\"/>
                        </svg>
                        Выйти
                    </a>
                </li>
        ";
    }

?>
                
            </ul>
        </div>
    </nav>

    <script type="text/javascript" src="blocks/header.js"></script>
    
    <audio id="alarm-sound" src="sound/ES_Submarine Alarm 1 - SFX Producer.mp3"></audio>

</header>