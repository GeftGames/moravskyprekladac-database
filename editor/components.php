<?php
function FilteredList($list, $id) {
    $listContaineId = "list".$id; // Unique ID for multiple lists
    $jsonList = json_encode($list);

    $html="<div class='filterList'>";
    // filter
    $html.='<input type="text" onkeyup=\'generateList('.$jsonList.', "'.$listContaineId.'", this.value)\'>';
    
    // list
    $html.="<div class='listFilteredContainer' id='".$listContaineId."'>";
    foreach ($list as $item) {
        $html.="<div class='selectItem' onclick='filteredListSelect(this)'>$item</div>";        
    }
    $html.="</div>";

    $html.="</div>";

    return $html;
}
function ListWithShapes($list) {
    $html="<div>";    
    // list
    $html.="<div>";
    foreach ($list as $item) {
        $html.="<div>$item</div>";        
    }
    $html.="</div>";
    $html.="</div>";

    return $html;
}
function ListNoShapes($list) {
    $html="<div>";    
    // list
    $html.="<div>";
    foreach ($list as $item) {
        $html.="<div>$item</div>";        
    }
    $html.="</div>";
    $html.="</div>";

    return $html;
}
?>