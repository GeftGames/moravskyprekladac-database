<?php
// priority is position
function multiple_to($list, $DDname) {
    $cites=[];
    // $cites=[[label, id], [label, id], [label, id], ...]
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $sql = "SELECT id, label FROM piecesofcite WHERE translate = $_SESSION[translate];";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cites[] = [$row['label'], $row['id']];
        }
    }

    // Convert the PHP array to a JavaScript array
    $citesJson = json_encode($cites);

    $listTo=[];

    // to
    $sqlTo="SELECT `id`, `shape` FROM `".$DDname."s_to` WHERE `relation` = 0;";
    $resultTo = $conn->query($sqlTo);
    if (!$resultTo) {
        $sqlDone=false;
        throwError("SQL error: ".$sqlTo);
    }

    // list to
    while ($rowTo = $resultTo->fetch_assoc()) {
        $listTo[]=[$rowTo["id"], $rowTo["shape"]];
    }

    $listToEncoded=json_encode($listTo);

    // cites
    $GLOBALS["script"].=
    /** @lang JavaScript */'
    var cites = '.$citesJson.';
          
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
                    if (typeE==="comment") {
                        rowObj[typeE]=e.value;
                    } else if (typeE==="shapeto") {
                        rowObj[typeE]=wrap.querySelector("input[type=hidden]");
                    }else if (typeE==="source") {
                        let citeEl=wrap.querySelector(".checkboxCite[type=checkbox]");
                        let listCites=[];
                        for (let cite of citeEl) {
                            if (cite.checked) {
                                listCites.push(cite.value);
                                break;
                            }
                        }
                        rowObj[typeE]=listCites.join(",");
                    } else {
                        console.error("unknown typeE", typeE);
                    }
                }
            }
            
            //save row in format [id, priority, shapeto, comment, source]
            let item=list[i]; 
            item.push([i, i/*position is priority*/,  rowObj["shapeto"], rowObj["comment"], rowObj["source"]]);
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
    };

    var to_load = function(list) {
        // clear prev
        document.getElementById("listTo").innerHTML="";
        
        // sort list by priority (lovest at start), 
        list.sort(function(a,b){ return a["priority"]-b["priority"]; });
        
        // display
        for (let item of list) {
            let itemcites=item["cite"].split(","); // it is not nessesay to convert into numbers            
            to_add(item["id"], item["shape"], item["comment"], itemcites, item["undetected"]);
        }
    };

    var cite_add = function(id) {
        let holder=document.getElementById("citeHolder");
        
       // let tag=document.createElement("div");
        
    };

    let maxId='.count($list). ';
    var to_add = function(defId, defShapeTo, defComment, defCite, undetected/*, defCertainty*/) {
        let idAdd = (defId === -1 ? maxId: defId);
        let wrapItem=document.createElement("div");
        wrapItem.style="display: flex;flex-direction: row;";   
        
        {
            let dpr=document.createElement("div");  
            dpr.style="display: flex; flex-direction: column; justify-content:space-evenly;";
            wrapItem.appendChild(dpr);
               
            // up priority
            let btnPriorityUp=document.createElement("a");
            btnPriorityUp.className="button";
            btnPriorityUp.innerText="⯅";
            btnPriorityUp.addEventListener("click", ()=>{
                to_PriorityUp(wrap);
            });
            dpr.appendChild(btnPriorityUp);           
                    
              // down priority
            let btnPriorityDown=document.createElement("a");
            btnPriorityDown.className="button";
            btnPriorityDown.innerText="⯆";
            btnPriorityDown.addEventListener("click", ()=>{
                to_PriorityDown(wrap);
            });
            dpr.appendChild(btnPriorityDown);   
        }
        
        // wrap
        let wrap=document.createElement("table");
        wrap.className="trTo"; 
        wrapItem.appendChild(wrap);
        
        // id
        let id_holder=document.createElement("input");
        id_holder.type="hidden";
        id_holder.value=idAdd;      
        id_holder.setAttribute("seltype","id");
        wrap.appendChild(id_holder);
        
        let trShape=document.createElement("tr");
        wrap.appendChild(trShape);
        
        let tdShapeLabel=document.createElement("td");
        trShape.appendChild(tdShapeLabel);
        
        // shape label
        let labelShape=document.createElement("span");
        labelShape.innerText="Tvar";
        tdShapeLabel.appendChild(labelShape);
        
        let tdShape=document.createElement("td");
        trShape.appendChild(tdShape);
        
        // shape
        let idSelectPattern="To"+maxId;
        let text=document.createElement("input");
        text.id="text_"+idSelectPattern;
        text.type="text";
        text.value=defShapeTo;
        tdShape.appendChild(text);
        
   /*       // priority        
        let trPriority=document.createElement("tr");
        wrap.appendChild(trPriority);
                       
        // label
        let tdPriority=document.createElement("td");
        trPriority.appendChild(tdPriority);
        
        let labelPriority=document.createElement("span");
        labelPriority.innerText="Priorita";
        tdPriority.appendChild(labelPriority);
        
        // priority select
      let tdPrioritySelect=document.createElement("td");
        trPriority.appendChild(tdPrioritySelect);
        
        let priority=document.createElement("select");    
        priority.style="margin-bottom: 3px;";
        priority.setAttribute("seltype","priority");
        if (defPriority!=null) priority.value=defPriority;
        tdPrioritySelect.appendChild(priority);   
        
        for (let o of [["primární", 1], ["výchozí", 0], ["vedlejší", -1]]) {
            let option=document.createElement("option");
            option.innerText=o[0];
            option.value=o[1];
            priority.appendChild(option);
        }   */
        
       /* // certainty
        let certainty=document.createElement("select");    
        certainty.style="margin-bottom: 3px;";
        certainty.setAttribute("seltype","certainty");
        if (defCertainty!=null) certainty.value=defCertainty;
        wrap.appendChild(certainty);   
        
        for (let o of [["<nenastaveno>", 0], ["pravděpodobné", 1], ["téměř jisté", 2], ["jisté", 3]]) {
            let option=document.createElement("option");
            option.innerText=o[0];
            option.value=o[1];
            certainty.appendChild(option);
        }*/
        
                  
        let trTags=document.createElement("tr");
        wrap.appendChild(trTags);
        
        // tag label
        let tdTagsLabel=document.createElement("td");
        trTags.appendChild(tdTagsLabel);
        
        let taglabel=document.createElement("span");
        taglabel.innerText="Tagy";
        tdTagsLabel.appendChild(taglabel);
        
        //tags
        let tdTags=document.createElement("td");
        trTags.appendChild(tdTags);
        
        let tags=tagManagerCreate("tagy", maxId);
        tdTags.appendChild(tags);
        
        // comment        
        let trComment=document.createElement("tr");
        wrap.appendChild(trComment);
        
        let tdCommentLabel=document.createElement("td");
        trComment.appendChild(tdCommentLabel);
        
        let labelComment=document.createElement("span");
        labelComment.innerText="Komentář";
        tdCommentLabel.appendChild(labelComment);
        
        let tdComment=document.createElement("td");
        trComment.appendChild(tdComment);
        
        let comment=document.createElement("input");
        comment.type="text";
        comment.className="comment";
        comment.placeholder="komentář";
        comment.style="max-width: 9cm;";
        comment.setAttribute("seltype","comment");
        if (defComment!=null) comment.value=defComment;
        tdComment.appendChild(comment);

        // Cite
        let trCite=document.createElement("tr");
        wrap.appendChild(trCite);
        
        let tdCite=document.createElement("td");
        tdCite.colSpan=2;
        trCite.appendChild(tdCite);
        
        let wrapsource=document.createElement("div");
        wrapsource.style.display="flex";
        tdCite.appendChild(wrapsource); 
          
            let wrapmenu=document.createElement("div");
            wrapmenu.style.display="none";
            wrapmenu.className="listSearchSelect popupChoose";
        
            let source=document.createElement("span");
            source.setAttribute("seltype", "source");
            //source.innerText="zdroj";//todo: set label 
            source.className="filterSelect";
            source.addEventListener("click", function() {
                if (wrapmenu.style.display==="block") {
                    wrapmenu.style.display="none";
                    citeBack.style.display="none";
                } else { 
                    wrapmenu.style.display="block";
                    citeBack.style.display="block";
                }
            });
            wrapsource.appendChild(source); 
            wrapsource.appendChild(wrapmenu);  
            
            let citeLabel=document.createElement("span");
            citeLabel.innerText="zdroj";
            source.appendChild(citeLabel);
            
            //background full screen
            let citeBack=document.createElement("div");
            citeBack.className="listSearchBack";
            source.appendChild(citeBack);
            
            function SetLabel(){
                let labels=[];
                for (let r of wrapmenu.childNodes) {
                    let input=r.childNodes[0];
                    if (input.checked) {
                        let label=r.childNodes[1].innerText;
                        let maxLabelOptionlen=13;
                        labels.push(label.substring(0,maxLabelOptionlen));
                    }
                }
                
                citeLabel.innerText="zdroj: "+labels.join(", ");
            }
           
            
            // arrow [v]
            let arrow=document.createElement("span");
            arrow.innerText="▼";
            arrow.className="filterbtnpop";
            source.appendChild(arrow); 
           
            for (let o of cites) {
                let citeId=o[1];
                let citeLabel=o[0];
                // row
                let row=document.createElement("li");
                row.style="list-style: none; display: flex; margin-bottom: 5px;";
                row.id="row"+citeId
                wrapmenu.appendChild(row);
                
                // checkbox
                let idChecked=defId+"_"+citeId;
                let option=document.createElement("input");
                option.type="checkbox";  
                option.className="checkboxCite";
                option.checked=false;
                for (let c of defCite) {
                    let num=parseInt(c);
                    console.log(citeId===c);
                    if (citeId===c) {
                        option.checked=true;
                        break;
                    }
                }
              //  console.log(cites.forEach(l=>l[0]==), citeId);
                //option.checked=cites.includes(citeId);
               // console.log(defCite, citeId, defCite.includes(citeId));
                option.value=citeId;
                option.id=idChecked
                option.addEventListener("click", function() {
                    SetLabel();
                });
                row.appendChild(option);
                
                // label
                let label=document.createElement("label");
                label.innerText=citeLabel;
                label.htmlFor=idChecked;
                label.className="labelCite";
              //  label.style="display: flex; margin-left: 5px;"
                row.appendChild(label);
        }
            
        SetLabel();
            
        //existing cites
        let citeHolder=document.createElement("div");
        citeHolder.id="citeHolder";
        source.appendChild(citeHolder);
                      
        // info old
        if (undetected!=null) {
            let span=document.createElement("span");
            span.innerText=undetected;
            wrap.appendChild(span);   
        }
            
        // remove button
        let trRemove=document.createElement("tr");
        wrap.appendChild(trRemove);
        
        let btnRemove=document.createElement("a");
        btnRemove.className="button";
        btnRemove.innerText="Smazat";
        btnRemove.addEventListener("click", ()=>{
            to_remove(wrap);
        });
        trRemove.appendChild(btnRemove);   
        
        {
            let trPriority=document.createElement("tr");
            wrap.appendChild(trPriority);
            
    
        }
        
        let parent=document.getElementById("listTo");          
        parent.appendChild(wrapItem);
       
        maxId++;
    };
    
    var to_PriorityUp=function(e) {
        let prevElement=e.previousElementSibling;
        
        if (prevElement==undefined) return; //no prev
        
        e.parentNode.insertBefore(e, prevElement);
    };
    var to_PriorityDown=function (e) {
        let nextElement=e.nextElementSibling;
        
        if (nextElement==undefined) return; //no prev
        
        e.parentNode.insertBefore(e, nextElement);
    };
    var to_remove = function(wrap) {
        if (confirm("Smazat? (nezapomeňte uložit)")){
            wrap.outerHTML="";               
        }
    };
    ';

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
    $html.='</div><a class="button" onclick="to_add(-1, /*null,*/ null, null, null, null,null)">Přidat</a>';
    return $html;
}
