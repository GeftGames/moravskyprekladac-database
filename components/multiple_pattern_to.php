<?php

function multiple_pattern_to($list) {
    $cites=[];
    // $cites=[[label, id], [label, id], [label, id], ...]
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $sql = "SELECT id, label FROM piecesofcite ORDER BY label";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cites[] = [$row['label'], $row['id']];
        }
    }

    // Convert the PHP array to a JavaScript array
    $citesJson = json_encode($cites);

    // cites
    $GLOBALS["script"].=
    /** @lang JavaScript */"
    var cites = $citesJson;";

    // Save
    $GLOBALS["script"].=
    /** @lang JavaScript */'        
    var to_save = function() {
        let data=[]; 
        let wrap=document.getElementById("listTo").childNodes;
        let i=0;
        for (let row of wrap) {
            // get row
            let rowObj={};
            for (let e of row) {
                let typeE=e.getAttribute("seltype");
                if (typeE!=null) {
                    if (typeE==="comment" || typeE==="source" || typeE==="priority") {
                        rowObj[typeE]=e.value;
                    } else if (typeE==="shapeto") {
                        rowObj[typeE]=wrap.querySelector("input[type=hidden]");
                    }else{
                        console.error("unknown typeE", typeE);
                    }
                }
            }
            
            //save row in format [id, priority, shapeto, comment, source]
            let item=list[i]; 
            item.push([i, rowObj["priority"],  rowObj["shapeto"], rowObj["comment"], rowObj["source"]]);
            data.push(item);            
            i++;
        }
        /*for (let i=0; i<list.length; i++) {
            let item=list[i];
            item.push(document.getElementById("id"+i).value);
            item.push(document.getElementById("to"+i).value);
            
            item.push(document.getElementById("comment"+i).value);
            item.push(document.getElementById("cite"+i).value);
            data.push(item);
        }*/
        return data;// send to fetch, then to server
    };'; 

    // Load
    $GLOBALS["script"].=
    /** @lang JavaScript */'
    var to_load = function(list) {
        // clear
        document.getElementById("listTo").innerHTML="";
        
        for (let item of list) {
            //let item=list[i];
            console.log(item);
            to_add(item["id"], item["priority"], item["shape"], item["comment"], item["cite"]);
        }
    };';

    // add
    $GLOBALS["script"].=
    /** @lang JavaScript */'
    let maxId='.count($list).';
    var to_add = function(defId, defPriority, defShapeTo, defComment, defCite) {
        let idAdd = (defId==-1 ? maxId: defId);
        let wrap=document.createElement("div");
        wrap.classList="row";   
        
        // id
        let id_holder=document.createElement("input");
        id_holder.type="hidden";
        id_holder.value=idAdd;      
        id_holder.setAttribute("seltype","id");
        wrap.appendChild(id_holder);
        
        let priority=document.createElement("select");     
        priority.setAttribute("seltype","priority");
        if (defPriority!=null) priority.value=defPriority;
        wrap.appendChild(priority);   
        
        for (let o of [["primární", 1], ["výchozí", 0], ["vedlejší", -1]]){
            let option=document.createElement("option");
            option.innerText=o[0];
            option.value=o[1];
            priority.appendChild(option);
        }
        
        let text=document.createElement("div");
        text.id="select_To"+maxId;
        text.setAttribute("seltype","shapeto");
        if (defShapeTo!=null) text.value=defShapeTo;
        wrap.appendChild(text);
        
        // comment
        let comment=document.createElement("input");
        comment.type="text";
        comment.className="comment";
        comment.placeholder="komentář";
        comment.setAttribute("seltype","comment");
        if (defComment!=null) comment.value=defComment;
        wrap.appendChild(comment);

        let source=document.createElement("select");
        source.setAttribute("seltype", "source");
        if (defCite!=null) source.value=defCite;
        wrap.appendChild(source); 
        for (let o of cites) {
            let option=document.createElement("option");
            option.innerText=o[0];
            option.value=o[1];
            source.appendChild(option);
        }

        let btnRemove=document.createElement("a");
        btnRemove.className="button";
        btnRemove.innerText="Smazat";
        btnRemove.addEventListener("click", ()=>{
            to_remove(wrap);
        });
        wrap.appendChild(btnRemove);   
        
        let parent=document.getElementById("listTo");
        parent.appendChild(wrap);
        
        createSelectFilter("To"+maxId);
        maxId++;
    };';

    // remove
    $GLOBALS["script"].=
    /** @lang JavaScript */'
    var to_remove = function(wrap) {
        wrap.outerHTML="";   
    };';

    $html='<div id="listTo">';
    $i=0;
  /*  foreach ($list as $item) {
        // text to, comment, cite
        $html.='<div class="lineFromTo">
        <input id="id'.$i.'" type="hidden" value="'.$item[0].'">
        <span>Text</span>&nbsp;<input id="shape'.$i.'" type="text" value="'.$item[0].'">
        &nbsp; &nbsp; <span>Komentář</span>&nbsp;<input type="text" id="comment'.$i.'" value="'.$item[1].'">
        &nbsp; &nbsp; <span>Zdroj</span>&nbsp;<select id="cite'.$i.'" value="'.$item[2].'"><option value="0">Bez zdroje</option></select>
        <a class="button">Smazat</a>
        </div>';
    }*/
    $html.='</div><a class="button" onclick="to_add(-1, null, null, null, null)">Přidat</a>';
    return $html;
}
