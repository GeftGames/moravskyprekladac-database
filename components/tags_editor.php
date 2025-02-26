<?php

function tagsEditor($id, $tags) {
    // onload
    $GLOBALS["script"].="
var tagRemove=function(e) {
    let parent=e.parentNode;
    let text=parent.childNodes[0].innerText;

    // Set new data in hidden input
    let tagsdata = document.getElementById('".$id."datatags');
    let tags=tagsdata.value.split('|');

    const index = tags.indexOf(text);
    tags.splice(index, 1);
    tagsdata.value=tags.join('|');

    // remove element
    parent.outerHTML='';
}
var tagAdd=function(){ 
    let tagAddNew = document.getElementById('tagAddNew');
    let tagToAdd=tagAddNew.value;
    if (tagToAdd=='') return;

    let tagsdata = document.getElementById('".$id."datatags');
    tagsdata.value+='|'+tagToAdd;
    tagAddNew.value='';

    let area=document.getElementById('tagsArea');

    let newTag=document.createElement('div');
    newTag.classList.add('tag');
    area.appendChild(newTag);

    let tagText=document.createElement('span');  
    tagText.innerText=tagToAdd;
    newTag.appendChild(tagText);

    let tagRemoveE=document.createElement('a');
    tagRemoveE.classList.add('smallRemoveBtn');
    tagRemoveE.innerText='×';
    tagRemoveE.addEventListener('click', () => {tagRemove(tagRemoveE)});
    newTag.appendChild(tagRemoveE);
}
var tagSet=function(list){
    // crear tags hidden
    let tags=document.getElementById('".$id."datatags');
    tags.value='';

    // Clear add
    tagAddNew.valu='';

    // crear tags hidden
    let area=document.getElementById('tagsArea');
    area.innerHTML='';

    for (let i of list){
        tagAdd(i);
    }
}
";
    $tagsHTML="";
    foreach ($tags as $tag) {
        $tagsHTML.='<div class="tag"><span>'.$tag.'</span><a class="smallRemoveBtn" onclick="tagRemove(this)">×</a></div>';
    }
    //html
    $html = '<div class="section">
        <label>Tagy</label> 
        <div class="row">
            <input type="text" id="tagAddNew" placeholder="expr." style="margin: 1mm;">
            <a onclick="tagAdd()" class="button">Přidat</a>
        </div>  
        <input type="hidden" id="'.$id.'datatags" value="'.join('|',$tags).'">
        <div id="tagsArea">';
    $html.=$tagsHTML;
    $html.='</div></div>';
    return $html;
}