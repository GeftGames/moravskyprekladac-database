<?php

function multiple_simple_to($list) {
    $GLOBALS["script"].= /** @lang JavaScript */'
        var to_save = function() {
        let data=[]; 
        for (let i=0; i<list.length; i++) {
            let item=list[i];
            item.push(document.getElementById("id"+i).value);
            item.push(document.getElementById("to"+i).value);
            item.push(document.getElementById("comment"+i).value);
            item.push(document.getElementById("cite"+i).value);
            data.push(item);
        }
        return data;// send to server
    };'; 
    
    $GLOBALS["script"].= /** @lang JavaScript */'
        var to_load = function(list) {
        console.log(list);
        for (let i=0; i<list.length; i++) {
            let item=list[i];
            document.getElementById("id"+i).value=item.id;
            //document.getElementById("tags"+i).value=item.tags;
            document.getElementById("shape"+i).value=item.shape;
            document.getElementById("comment"+i).value=item.comment;
            document.getElementById("cite"+i).value=item.source;
        }
    };
    let maxId='.count($list).';
    var to_add = function() {
        let wrap=document.createElement("div");

        let text=document.createElement("input");
        wrap.appendChild(text);

        let comment=document.createElement("input");
        wrap.appendChild(comment);

        let source=document.createElement("select");
        wrap.appendChild(source); 

        let btnRemove=document.createElement("a");
        a.className="button";
        a.innerText="Smazat";
        wrap.appendChild(btnRemove);       
    };';

    $html='<div class="section">';
    $html.='<label for="phraseto" id="name">Na</label><br>';
    $i=0;
    foreach ($list as $item) {
        // text to, comment, cite
        $html.='<div class="lineFromTo">
        <input id="id'.$i.'" type="hidden" value="'.$item[0].'">
        <span>Text</span>&nbsp;<input id="shape'.$i.'" type="text" value="'.$item[0].'">
        &nbsp; &nbsp; <span>Komentář</span>&nbsp;<input type="text" id="comment'.$i.'" value="'.$item[1].'">
        &nbsp; &nbsp; <span>Zdroj</span>&nbsp;<select id="cite'.$i.'" value="'.$item[2].'"><option value="0">Bez zdroje</option></select>
        <a class="button">Smazat</a>
        </div>';
    }
    $html.='</div><a class="button">Přidat</a>';
    return $html;
}
