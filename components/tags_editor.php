<?php

// add tag
$GLOBALS["script"].= /** @lang JavaScript */"
var tagAdd=function(text, id){
    let tagsdata = document.getElementById(id);
    tagsdata.value+='|'+text;
    
    let area=document.getElementById('tagsArea');

    let newTag=document.createElement('div');
    newTag.classList.add('tag');
    area.appendChild(newTag);

    let tagText=document.createElement('span');  
    tagText.innerText=text;
    newTag.appendChild(tagText);

    let tagRemoveE=document.createElement('a');
    tagRemoveE.classList.add('smallRemoveBtn');
    tagRemoveE.innerText='×';
    tagRemoveE.addEventListener('click', () => { tagRemove(tagRemoveE, id) });
    newTag.appendChild(tagRemoveE);
};    

var tagAddFromTextBox=function(id) {console.log(id);
    let tagAddNew = document.getElementById('tagAddNew');
    let tagToAdd=tagAddNew.value;
    if (tagToAdd==='') return;
    tagAddNew.value='';
    tagAdd(tagToAdd, id);
};

var tagRemove=function(e, id) {
    let parent=e.parentNode;
    let text=parent.childNodes[0].innerText;

    // Set new data in hidden input
    let tagsdata = document.getElementById(id);
    let tags=tagsdata.value.split('|');

    const index = tags.indexOf(text);
    tags.splice(index, 1);
    tagsdata.value=tags.join('|');

    // remove element
    parent.outerHTML='';
};
";

function tagsEditorDynamic() {
    // onload
    $GLOBALS["script"].= /** @lang JavaScript */"   
var tagManagerCreate=function(label, id) {
let idDataTags=id+'datatags';
    const section = document.createElement('div');
    section.className = 'section';

    /*const labelEl = document.createElement('label');
    labelEl.textContent = label;
    section.appendChild(labelEl);*/

    const row = document.createElement('div');
    row.className = 'row';
    
    const input = document.createElement('input');
    input.type = 'text';
    input.id = 'tagAddNew';
    input.placeholder = 'tag';
    input.style.margin = '1mm';
    
    const button = document.createElement('a');
    button.textContent = 'Přidat';
    button.className = 'button';
    button.addEventListener('click', ()=>{tagAddFromTextBox(idDataTags)});
    
    row.appendChild(input);
    row.appendChild(button);
    section.appendChild(row);

    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.id = idDataTags;
   // hiddenInput.value = tags.join('|');
    section.appendChild(hiddenInput);

    const tagsArea = document.createElement('div');
    tagsArea.id = 'tagsArea';
  //  tagsArea.innerHTML = generateTagsHTML(tags); 
    section.appendChild(tagsArea);

    return section;
};
";
}


function tagsEditor($id, $tags, $label) {
    // onload
    $GLOBALS["script"].= /** @lang JavaScript */"

var tagSet=function(list){      
    // crear tags hidden
    let tags=document.getElementById('".$id. "datatags');
    tags.value='';

    // Clear add
    tagAddNew.valu='';

    // crear tags hidden
    let area=document.getElementById('tagsArea');
    area.innerHTML='';

    for (let i of list){  
        if (i!=='') tagAdd(i, '$id'+'datatags');
    }
};
    ";

    $tagsHTML="";
    foreach ($tags as $tag) {
        $tagsHTML.='<div class="tag"><span>'.$tag.'</span><a class="smallRemoveBtn" onclick="tagRemove(this, \''.$id.'datatags\')">×</a></div>';
    }
    $iddata=$id.'datatags';
    //html
    $html = '<div class="section">
        <label>'.(isset($label) ? $label : 'Tagy').'</label> 
        <div class="row">
            <input type="text" id="tagAddNew" placeholder="expr." style="margin: 1mm;">
            <a onclick="tagAddFromTextBox(\'$iddata\')" class="button">Přidat</a>
        </div>  
        <input type="hidden" id="'.$iddata.'" value="'.join('|',$tags).'">
        <div id="tagsArea">';
    $html.=$tagsHTML;
    $html.='</div></div>';
    return $html;
}