<?php
function FilteredList($list, $id) : string {
    $type="";
    if (str_contains($id,"relation")) $type="_relation";

    $listContaineId = "list_".$id; // Unique ID for multiple lists
    $jsonList = json_encode($list);

    $html="<div tabindex='0' id='container_$id' class='filterList'>";
    // filter
    $html.='<input type="text" id="filter_'.$id.'" onkeyup=\'flist_'.$id.'.generateList('.$jsonList.', "'.$listContaineId.'", this.value)\'>';
    
    // list 
    $html.="<div class='listFilteredContainer' id='".$listContaineId."'>";
    $GLOBALS["onload"].="flist_$id=new filteredList('$id', '$type');\nflist_$id.generateList(".json_encode($list).");\n";
    $html.="</div>";
    // Mouse context menu
    $html.="<div id='contextmenu_$id' class='mouseContexMenu'><a onclick=\"getFilteredListById('$id').list_duplicate()\">Duplikovat</a> <a onclick=\"getFilteredListById('$id').list_remove()\">Smazat</a></div>";
    
    // Buttons down
    $html.="<select class='button' onclick=\"\"><option>Neřadit</option><option>ABC</option></select>";
    $html.="<a class='button' onclick=\"getFilteredListById('$id').list_add()\">Přidat</a>";

    $html.="</div>";

    return $html;
}