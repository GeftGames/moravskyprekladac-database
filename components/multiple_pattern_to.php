<?php

function multiple_pattern_to($list, $DDname) {
    $cites=[];
    // $cites=[[label, id], [label, id], [label, id], ...]
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $sql = "SELECT id, label FROM piecesofcite WHERE translate = $_SESSION[translate] ORDER BY label;";
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
    $sqlTo="SELECT `id`, `label` FROM `".$DDname."_patterns_to` WHERE `translate` = ".$_SESSION['translate'].";";
    $resultTo = $conn->query($sqlTo);
    if (!$resultTo) {
        $sqlDone=false;
        throwError("SQL error: ".$sqlTo);
    }

    // list to
    while ($rowTo = $resultTo->fetch_assoc()) {
        $listTo[]=[$rowTo["id"], $rowTo["label"]];
    }

    $listToEncoded=json_encode($listTo);

    // cites
    $GLOBALS["script"].=/** @lang JavaScript */"
    var cites = ".$citesJson.";
   
    var to_save = function() {
        let data=[]; 
        let wrap=document.getElementById('listTo').childNodes;
        let i=0;
        for (let row of wrap) {
            // get row
            let rowObj={};
            for (let e of row) {
                let typeE=e.getAttribute('seltype');
                if (typeE!=null) {
                    if (typeE==='comment'/* || typeE==='priority'*/) {
                        rowObj[typeE]=e.value;
                    } else if (typeE==='shapeto') {
                        rowObj[typeE]=wrap.querySelector('input[type=hidden]');
                    }else if (typeE==='source') {
                        let citeEl=wrap.querySelector('.checkboxCite[type=checkbox]');
                        let listCites=[];
                        for (let cite of citeEl) {
                            if (cite.checked) {
                                listCites.push(cite.value);
                                break;
                            }
                        }
                        rowObj[typeE]=listCites.join('|');
                    } else {
                        console.error('unknown typeE', typeE);
                    }
                }
            }
            
            //save row in format [id, priority, shapeto, comment, source]
            let item=list[i]; 
            item.push([i, i/*rowObj['priority']*/,  rowObj['shapeto'], rowObj['comment'], rowObj['source']]);
            data.push(item);            
            i++;
        }
        /*for (let i=0; i<list.length; i++) {
            let item=list[i];
            item.push(document.getElementById('id'+i).value);
            item.push(document.getElementById('to'+i).value);
            
            item.push(document.getElementById('comment'+i).value);
            item.push(document.getElementById('cite'+i).value);
            data.push(item);
        }*/
        return data;// send to fetch, then to server
    };

    var to_load = function(list) {
        // clear
        document.getElementById('listTo').innerHTML='';

        // sort list by priority (lovest at start),
        list.sort(function(a,b){ return a['priority']-b['priority']; });

        for (let item of list) {
            let cites=item['cite'].split(',');
            let undetected='';
            if (item['tmp_pattern_from_body']!=null || item['tmp_imp_from_pattern']!=null) undetected='body: '+item['tmp_pattern_from_body']+'; pat: '+item['tmp_imp_from_pattern']
            to_add(item['id'], item['shape'], item['comment'], cites, undetected, item['custombase']);
        }
    };

    var cite_add = function(id) {
        let holder=document.getElementById('citeHolder');
        
       // let tag=document.createElement('div');
        
    };

    let maxId=".count($list). ";
    
    var to_add = function(defId, defShapeTo, defComment, defCite, undetected, custombase) {
      //  console.log(defCite);
        let idAdd = (defId === -1 ? maxId: defId);
        let wrapItem=document.createElement('div');
        wrapItem.style='';   
        wrapItem.style='display: flex;flex-direction: row;';   
           
        {
            let dpr=document.createElement('div');  
            dpr.style='display: flex; flex-direction: column; justify-content:space-evenly;';
            wrapItem.appendChild(dpr);
               
            // up priority
            let btnPriorityUp=document.createElement('a');
            btnPriorityUp.className='button';
            btnPriorityUp.innerText='⯅';
            btnPriorityUp.addEventListener('click', ()=>{
                to_PriorityUp(wrap);
            });
            dpr.appendChild(btnPriorityUp);           
                    
              // down priority
            let btnPriorityDown=document.createElement('a');
            btnPriorityDown.className='button';
            btnPriorityDown.innerText='⯆';
            btnPriorityDown.addEventListener('click', ()=>{
                to_PriorityDown(wrap);
            });
            dpr.appendChild(btnPriorityDown);   
        }
        // wrap
        let wrap=document.createElement('table');
        wrap.className='trTo'; 
        wrapItem.appendChild(wrap);
     
        
        // id
        let id_holder=document.createElement('input');
        id_holder.type='hidden';
        id_holder.value=idAdd;      
        id_holder.setAttribute('seltype','id');
        wrap.appendChild(id_holder);
        
        // body
        let trBody=document.createElement('tr');
        wrap.appendChild(trBody);
        
        let tdBody=document.createElement('td');
        trBody.appendChild(tdBody);
        
        let usecustombody=document.createElement('input');
        usecustombody.type='checkbox';
        usecustombody.checked=custombase!=null;
        usecustombody.id = 'usecustombody_' + Date.now();
        tdBody.appendChild(usecustombody);
        
        let custombodylabel=document.createElement('label');
        custombodylabel.htmlFor=usecustombody.id;
        custombodylabel.innerText='Jiný základ';
        tdBody.appendChild(custombodylabel);
        
        let tdBodyTB=document.createElement('td');
        trBody.appendChild(tdBodyTB);
        console.log(custombase);
        let custombodyTextbox=document.createElement('input');
        custombodyTextbox.type='text';
        custombodyTextbox.value=custombase;
        usecustombody.addEventListener('click', ()=>{ // focus on checkbox click
            if (usecustombody.checked) {
                custombodyTextbox.disabled=false;
                custombodyTextbox.focus();
                custombodyTextbox.style.opacity='1';
            } else {
                custombodyTextbox.disabled=true;  
                custombodyTextbox.style.opacity='0.5';
            }              
        });
        if (!usecustombody.checked){
            custombodyTextbox.disabled=true;  
            custombodyTextbox.style.opacity='0.5';
        }
        tdBodyTB.appendChild(custombodyTextbox);
           
      
        // pattern
        let trPattern=document.createElement('tr');
        wrap.appendChild(trPattern);
        
        let tdPatternLabel=document.createElement('td');
        trPattern.appendChild(tdPatternLabel);
        
        let labelPattern=document.createElement('span');
        labelPattern.innerText='Tvar';
        tdPatternLabel.appendChild(labelPattern);
        
        let tdPattern=document.createElement('td');
        trPattern.appendChild(tdPattern);
        
        let wrapPattern=document.createElement('div');
        wrapPattern.className='row';
        wrapPattern.style='width:100%;';
        
            //pattern
            let idSelectPattern='To'+maxId;
            let text=document.createElement('div');
            text.id='select_'+idSelectPattern;
            text.setAttribute('seltype','shapeto');
            wrapPattern.appendChild(text);
            
            // btn go to pattern
            let btnGoToPattern=document.createElement('a');
            btnGoToPattern.className='button';
            btnGoToPattern.innerText='Zobrazit';
            btnGoToPattern.addEventListener('click', ()=>{
                showPatternEditor(defShapeTo);
            });            
            wrapPattern.appendChild(btnGoToPattern);
            
        tdPattern.appendChild(wrapPattern);
          
        // priority
     /*   let trPriority=document.createElement('tr');
        wrap.appendChild(trPriority);
        
        let tdPriorityLabel=document.createElement('td');
        trPriority.appendChild(tdPriorityLabel);
        
        let labelPriority=document.createElement('span');
        labelPriority.innerText='Priorita';
        tdPriorityLabel.appendChild(labelPriority);
                
        let tdPriority=document.createElement('td');
        trPriority.appendChild(tdPriority);
        
        let priority=document.createElement('select');    
        priority.style='margin-bottom: 3px;';
        priority.setAttribute('seltype','priority');
        if (defPriority!=null) priority.value=defPriority;
        tdPriority.appendChild(priority);   
        
        for (let o of [['primární', 1], ['výchozí', 0], ['vedlejší', -1]]) {
            let option=document.createElement('option');
            option.innerText=o[0];
            option.value=o[1];
            priority.appendChild(option);
        }*/
          
        // tags 
        let trTags=document.createElement('tr');
        wrap.appendChild(trTags);
        
        let tdTagsLabel=document.createElement('td');
        trTags.appendChild(tdTagsLabel);
        
        let labelTags=document.createElement('span');
        labelTags.innerText='Tagy';
        tdTagsLabel.appendChild(labelTags);
        
        let tdTags=document.createElement('td');
        trTags.appendChild(tdTags);
        
        let tags=tagManagerCreate('tagy',maxId);
        tdTags.appendChild(tags);
        
        // comment
        let trComment=document.createElement('tr');
        wrap.appendChild(trComment);
        
        let tdCommentLabel=document.createElement('td');
        trComment.appendChild(tdCommentLabel);
        
        let labelComment=document.createElement('span');
        labelComment.innerText='Komentář';
        tdCommentLabel.appendChild(labelComment);
        
        let tdComment=document.createElement('td');
        trComment.appendChild(tdComment);
        
        let comment=document.createElement('input');
        comment.type='text';
        comment.className='comment';
        comment.placeholder='komentář';
        comment.style='max-width: 9cm;';
        comment.setAttribute('seltype','comment');
        if (defComment!=null) comment.value=defComment;
        tdComment.appendChild(comment);

        // Cite
        let trCite=document.createElement('tr');
        wrap.appendChild(trCite);  
        
        let tdCiteL=document.createElement('td');
        trCite.appendChild(tdCiteL);
        
        let labelCite=document.createElement('span');
        labelCite.innerText='Zdroj';
        tdCiteL.appendChild(labelCite);
        
        let tdCite=document.createElement('td');
      //  tdCite.colSpan=2;
        trCite.appendChild(tdCite);
        
        let wrapsource=document.createElement('div');
        tdCite.appendChild(wrapsource); 
          
            let wrapmenu=document.createElement('div');
            wrapmenu.style.display='none';
            wrapmenu.className='listSearchSelect popupChoose';
        
            let source=document.createElement('span');
            source.setAttribute('seltype', 'source');
            source.className='filterSelect';            
            source.addEventListener('click', function() {
                if (wrapmenu.style.display==='block') {
                    wrapmenu.style.display='none';
                    citeBack.style.display='none';
                } else { 
                    wrapmenu.style.display='block';
                    citeBack.style.display='block';
                }
            });
            wrapsource.appendChild(source); 
            wrapsource.appendChild(wrapmenu); 
            
         
                        
            let citeLabel=document.createElement('span');
           // citeLabel.innerText='zdroj';
            source.appendChild(citeLabel);
            
            function SetLabel(){
                let labels=[];
                for (let r of wrapmenu.childNodes) {
                    let input=r.childNodes[0];
                    if (input.checked) {
                        let label=r.childNodes[1].innerText;
                        labels.push(label.substring(0,7));
                    }
                }
                
                citeLabel.innerText=/*'zdroj: '+*/labels.join(', ');
            }
            
            // arrow [v]
            let arrow=document.createElement('span');
            arrow.innerText='▼';
            arrow.className='filterbtnpop';
            source.appendChild(arrow); 
                       
            //background full screen
            let citeBack=document.createElement('div');
            citeBack.className='listSearchBack';
            source.appendChild(citeBack);
            
            for (let o of cites) {
                let citeId=o[1];
                let citeLabel=o[0];
                // row
                let row=document.createElement('li');
                row.style='list-style: none';
                wrapmenu.appendChild(row);
                
                // checkbox
                let idChecked=defId+'_'+citeId;
                let option=document.createElement('input');
                option.type='checkbox';  
                option.className='checkboxCite';
                option.checked=defCite.includes(citeId);
               // console.log(defCite, citeId, defCite.includes(citeId));
                option.value=citeId;
                option.id=idChecked
                option.addEventListener('click', ()=>{SetLabel();});
                row.appendChild(option);
                
                // label
                let label=document.createElement('label');
                label.innerText=citeLabel;
                label.htmlFor=idChecked;
                row.appendChild(label);
            }
            SetLabel();
            
            //existing cites
            let citeHolder=document.createElement('div');
            citeHolder.id='citeHolder';
            citeLabel.appendChild(citeHolder);
            
        // certainty
      /*  let certainty=document.createElement('select');    
        certainty.style='margin-bottom: 3px;';
        certainty.setAttribute('seltype','priority');
        if (defCertainty!=null) certainty.value=defCertainty;
        wrap.appendChild(certainty);   
        
        for (let o of [['<nenastaveno>', 0], ['pravděpodobné', 1], ['téměř jisté', 2], ['jisté', 3]]) {
            let option=document.createElement('option');
            option.innerText=o[0];
            option.value=o[1];
            certainty.appendChild(option);
        }*/
        // info old
        if (undetected!=null) {
            let span=document.createElement('span');
            span.innerText=undetected;
            wrap.appendChild(span);   
        }
        
        // remove button
        let btnRemove=document.createElement('a');
        btnRemove.className='button';
        btnRemove.innerText='Smazat';
        btnRemove.addEventListener('click', ()=>{
            to_remove(wrap);
        });
        wrap.appendChild(btnRemove);   
        
        let parent=document.getElementById('listTo');
        parent.appendChild(wrapItem);
                
        // create select filter element
        let classselectpattern= createSelectFilter(idSelectPattern, ".$listToEncoded.", defShapeTo);
                
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
        wrap.outerHTML='';   
    };";

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
    $html.='</div><a class="button" onclick="to_add(-1, /*null, */null, null, null, null, null)">Přidat</a>';
    return $html;
}
