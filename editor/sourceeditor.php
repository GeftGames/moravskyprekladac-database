<?php

function editorSourceSample($id) {
    return paramEditor(
        [
            ["datum narození", "born_date",""], 
            ["místo narození", "born_place",""], 
            ["jméno zapisovatele", "writer_name", ""]
        ],
        [
            ["datum narození", "born_date"], 
            ["místo narození", "born_place"], 
            ["jméno zapisovatele", "writer_name"],
            ["rok zápisu", "writed_year"],            
            ["místa bydlení", "live_places"],            
        ]
    );
}

function paramEditor($params, $allparams) {
    $html = "<span>Základní atributy</span><div>";
    foreach ($param as $allparams) {
        $paramName=$param[0];
        $paramCode=$param[1];
        $html.='<span><span class="add"></span><span onclick="Add('+$paramCode+')">'+$paramName+'</span></span>';
    }
    $html .= "</div><div>";

    foreach ($param as $params) {
        $paramName=$param[0];
        $paramCode=$param[1];
        $paramValue=$param[2];
        $html.='<div class="row"><label>'+$paramName+'</label><input value='+$paramCode+'>'+$paramName+'></div>';        
    }
    $html .= "</div>";
    return $html;
}
?>