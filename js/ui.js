let project_conf_array = [];

//  Функция для установки аттрибутов option элемента select
function setSelectOptions(dom_element, options_arr){
    while (dom_element.options.length) {
        dom_element.remove(0);
    }
    options_arr.forEach(element => {
        if(element==="all"){
            dom_element.add(new Option("все","all"));
        } else{
            dom_element.add(new Option(element,element));
        }   
    });
    return;
}

