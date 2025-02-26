<?php

function editorSourceSample() {
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
    foreach ($allparams as $param) {
        $paramName=$param[0];
        $paramCode=$param[1];
        $html.='<span><span class="add"></span><span onclick="Add('.$paramCode.')">'.$paramName.'</span></span>';
    }
    $html .= "</div><div>";

    foreach ($params as $param) {
        $paramName=$param[0];
        $paramCode=$param[1];
        $paramValue=$param[2];
        $html.='<div class="row"><label>'.$paramName.'</label><input value='.$paramValue.'>'.$paramName.'></div>';        
    }
    $html .= "</div>";
    return $html;
}
?>