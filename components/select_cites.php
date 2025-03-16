<?php
function selectCites($list) {
    $html='
<div class="section">
    <label id="to">Zdroje</label><br>
    <select>
        <option></option>
    </select>  
    <a class="button">Přidat</a>';
    foreach ($list as $tag) {
        $html.='<div class="tag"><span>'.$tag.'</span><a class="smallRemoveBtn" onclick="tagRemove(this)">×</a></div>';
    }
    $html='<input type="hidden">
</div>';

    return $html;
}