let curr_instr_format="text";

function init_instruction(){

    document.getElementById("hdr-href-instruction.php").setAttribute("class", "nav-link text-primary");

    $('#instr-href-text').removeClass("text-black");
    $('#instr-href-text').addClass("text-primary");
    $('#instr-pdf-document').show();
    $('#instr-mp4-video').hide();
}

$("a").hover(
    function() {
        if(   ( $(this).attr('id').split("-").pop() === current_page) || 
              ( $(this).attr('id')  === 'instr-href-text' && curr_instr_format==="text") ||
              ( $(this).attr('id')  === 'instr-href-video' && curr_instr_format==="video") ){
            return;
        }
        $(this).removeClass("text-black");
        $(this).addClass("text-primary");
    },
    function() {
        if(  ( $(this).attr('id').split("-").pop() === current_page) ||
             ( $(this).attr('id')  === 'instr-href-text' && curr_instr_format==="text" ) ||
             ( $(this).attr('id')  === 'instr-href-video' && curr_instr_format==="video") ){
            return;
        }
        $(this).removeClass("text-primary");
        $(this).addClass("text-black");
    }
);

$('#instr-href-text').click(
    function () { 
        curr_instr_format="text";
        $('#instr-href-text').removeClass("text-black");
        $('#instr-href-text').addClass("text-primary");
        $('#instr-href-video').removeClass("text-primary");
        $('#instr-href-video').addClass("text-black");
        $('#instr-pdf-document').show();
        $('#instr-mp4-video').hide();
     }
);

$('#instr-href-video').click(
    function () {
        curr_instr_format="video";
        $('#instr-href-text').removeClass("text-primary");
        $('#instr-href-text').addClass("text-black");
        $('#instr-href-video').removeClass("text-black");
        $('#instr-href-video').addClass("text-primary");
        $('#instr-pdf-document').hide();
        $('#instr-mp4-video').show();


    } 
);
