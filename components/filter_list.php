<?php
function FilteredList($list, $id) {
    $listContaineId = "list_".$id; // Unique ID for multiple lists
    $jsonList = json_encode($list);

    $html="<div tabindex='0' id='container_$id' class='filterList'>";
    // filter
    $html.='<input type="text" id="filter_'.$id.'" onkeyup=\'flist_'.$id.'.generateList('.$jsonList.', "'.$listContaineId.'", this.value)\'>';
    
    // list 
    $html.="<div class='listFilteredContainer' id='".$listContaineId."'>";
    //  $html.=ListContent($list);
    $GLOBALS["onload"].="flist_$id=new filteredList('$id');\nflist_$id.generateList(".json_encode($list).");\n";
    $html.="</div>";
    // Mouse context menu
    $html.="<div id='contextmenu_$id' class='mouseContexMenu'><a onclick=\"getFilteredListById('$id').list_duplicate()\">Duplikovat</a> <a onclick=\"getFilteredListById('$id').list_remove()\">Smazat</a></div>";
    
    // Buttons down
    $html.="<a class='button' onclick=\"getFilteredListById('$id').list_add()\">Přidat</a>";

    $html.="</div>";

    return $html;
}
/*
function ListContent($list) : string {
    // Empty list
    if (count($list)==0) return"<span>Žádné položky</span>";
    $GLOBALS["onload"].="generateList(".json_encode($list).");";
    // list
    $html="";
    foreach ($list as $item) {
        $id=$item[0];
        $name=$item[1];// onclick='filteredListSelect(this)'
        $html.="<div class='sideItem' data-id='$id'>$name</div>";        
    }
    return $html;
}*/
?>