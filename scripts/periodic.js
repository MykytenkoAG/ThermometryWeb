//let timerId = setInterval(every10Seconds, 10000);

bgSound = new Audio("/webTermometry/sound/beep.wav");

window.onload = function() {
    //bgSound.loop = true;
    //bgSound.play();
}

function every10Seconds() {
    //  Каждые 10 секунд необходимо:

    //  Опрашивать термосервер
    //  Заносить новые показания в БД
    $.ajax({
        url: '/webTermometry/php/dbSensors.php',
        type: 'POST',
        cache: false,
        data: { 'write_current_values_to_db': 1 },
        dataType: 'html',
        success: function() {}
    });

    //  Проверять возникновение новых алармов
    $.ajax({
        url: '/webTermometry/php/alarms.php',
        type: 'POST',
        cache: false,
        data: { 'check_alarms': 1 },
        dataType: 'html',
        success: function() {}
    });
    //  При возникновении включать звук

    //  Обновлять визуальные элементы на главной странице если мы на ней находимся

    //onSiloClicked("silo-7");

    //bgSound.loop = false;
    //bgSound.stop();

}




/*
<audio id="myAudio">
<source src="/webTermometry/sound/beep.wav" type="audio/wav">
    Your browser does not support the audio element.
</audio>

<!--       <button onclick="playAudio()" type="button">Play Audio</button>
<button onclick="pauseAudio()" type="button">Pause Audio</button> -->

<script>
var x = document.getElementById("myAudio"); 

function playAudio() { 
x.loop=true;
x.play(); 
} 

function pauseAudio() { 
x.pause(); 
} 
</script>   */