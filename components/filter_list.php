<?php
function FilteredList($list, $id, $btns) : string {
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
    $html.="<div id='contextmenu_$id' class='mouseContexMenu'>
        <a onclick=\"getFilteredListById('$id').list_duplicate()\">Duplikovat</a> 
        <a onclick=\"getFilteredListById('$id').list_remove()\">Smazat</a>";

    // other buttons
    foreach ($btns as $key => $value) {
        $name = $key;
        $link = $value;

        $html.="<a onclick=\"$link\">$name</a>";
    }

    $html.="</div>";
    
    // Buttons down
    $html.="<select id='sortTypeFilterList' class='button'>
        <option value='none'>Neřadit</option>
        <option value='abc'>A 🡒 Z</option>
        <option value='desc'>Z 🡒 A</option>
        <option value='id'>ID</option>
    </select>";

    // build in buttons
    $html.="<a class='button' onclick=\"getFilteredListById('$id').list_add()\">Přidat</a>";

    $html.="</div>";

    return $html;
}