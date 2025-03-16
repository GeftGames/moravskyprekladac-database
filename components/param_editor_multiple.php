<?php
function paramMultipleEditor($params, $allparams, $name, $label) {
    $GLOBALS["script"].="var AddParamMultiple = function(parent, divAllTags, labelText, code, val, typeInput, placeholder, spanTag) {
        //let parent=document.getElementById('paramEditorMultiple$name');
        let wrap=document.createElement('tr');
        wrap.setAttribute('data-code', code);
        parent.appendChild(wrap);

        // create label
        let label=document.createElement('label');
        label.innerText=labelText;
        label.style.display='table-cell';
        wrap.appendChild(label);

        // create input
        if (Array.isArray(typeInput)) {
            let textbox=document.createElement('select');
            if (val!=undefined) textbox.value=val;
            textbox.style.display='table-cell';
            textbox.style.margin='1px';
            wrap.appendChild(textbox); 
            textbox.setAttribute('code', code);
            textbox.id='pe'+code;

            for (let optionValue of typeInput){
                let option=document.createElement('option');
                option.innerText=optionValue;
                textbox.appendChild(option); 
            }
            textbox.value=val;
        } else {
            let textbox=document.createElement('input');

            if (typeInput==undefined) textbox.setAttribute('type', 'text');
            else textbox.setAttribute('type', typeInput);

            if (typeInput=='checkbox') textbox.checked=val;
            else if (val!=undefined) textbox.value=val;
            textbox.setAttribute('code', code);
            textbox.style.display='table-cell';
            textbox.style.margin='1px';
            if (placeholder!=undefined) textbox.placeholder=placeholder;
            wrap.appendChild(textbox); 
        }

        // create remove button
        let rmv=document.createElement('a');
        rmv.className='button';
        rmv.innerText='Smazat';
        rmv.addEventListener('click', ()=>{
            wrap.outerHTML=''; 

            // unhide existing tag

            let span = divAllTags.querySelector('span[data-code=\"'+code+'\"]');
            span.style.display='inline-block';
        });
        rmv.style.display='table-cell';
        wrap.appendChild(rmv); 

        // hide existing tag
        let span = divAllTags.querySelector('span[data-code=\"'+code+'\"]');
        span.style.display='none';
    };

    var AddPart = function() {
        let editor=document.getElementById('editor$name');

        let divAllTags=document.createElement('div');  
        divAllTags.id='tagseditor$name';

        let table=document.createElement('table');
        table.style='margin-top: 5px; border-left: solid;';
        table.id='paramEditorMultiple$name'+maxId;
        
        for (let tag of ".json_encode($allparams).") {
            let code=tag[0], label=tag[1], val=tag[2], type=tag[3], placeholder=tag[4];
            let stag=document.createElement('span');
            stag.setAttribute('data-code', code);
            stag.className='button';
            stag.innerText=label;
            stag.addEventListener('click', () => {
                AddParamMultiple(table, divAllTags, label, code, val, type, placeholder);
            });
            divAllTags.appendChild(stag);
        }
        editor.appendChild(divAllTags);
        
        editor.appendChild(table);
        maxId++;
        return table;
    };

    var PEMGetJSON = function() {
        let rows=[];

        let editor=document.getElementById('editor$name');

        let parts=editor.querySelectorAll('table');
        if (parts==null) return '[]';
      
        for (let part of parts) {
            let json={};
            for (let row of part.childNodes) {
                let code=row.getAttribute('data-code');
       
                // get value
                let inputE=row.querySelector('input', 'select');
                let val;         
                if (inputE.tagName=='INPUT') {
                    let typeE=inputE.getAttribute('type');
                    if (typeE=='checkbox') val=inputE.checked;
                    else val=inputE.value;
                } else if (inputE.tagName=='SELECT') {
                    val=inputE.value;
                } else {
                    console.error('get tagname of element '+inputE);
                }

                // set value
                json[code]=val;
            }
            rows.push(json);
        }
        
        return JSON.stringify(rows);
    };
 
    var PEMLoadJSON = function(arr) {
        // remove all parts
        let editor=document.getElementById('editorsample');
        editor.innerHTML='';
        
        if (arr!=null && JSON.stringify(arr) != '{}') {
            if (Array.isArray(arr)) {
                maxId=0
                for (let json of arr) {
                    let part = AddPart(); 

                    for (let param in json) {       
                        let paramCode=param;
                        let paramValue=json[param];

                        let found=false;
                        for (let paramA of ".json_encode($allparams).") {
                            if (paramA[0]==paramCode) {
                                found=true;
                                let paramName=paramA[1];
                                let paramType=paramA[3];
                                let placeholder=paramA[4];
                                AddParamMultiple(part, paramName, paramCode, paramValue, paramType, placeholder);
                                break;
                            }
                        }
                        if (!found) AddParamMultiple(part, paramCode+'!', paramCode, paramValue, 'text', '');    
                    }
                }
            }
        }
    };";
 
    $htmlpe = "<div class='section'><label id=\"$label\">$label</label><br><div id='editor$name'>";
       
    $rowId=0;
    foreach ($params as $paramRow) {
        $htmlpe .= "<span>Atributy</span><div>";
        $table='paramEditorMultiple'.$name.$rowId;

        // přidat
        foreach ($allparams as $param) {
            $paramCode=$param[0];
            $paramName=$param[1];
            $paramDef =$param[2];
            $paramType=$param[3];

            if (is_array($paramType)) $paramType=str_replace('"', "'", json_encode($paramType));
            else $paramType="'$paramType'";

            $placeholder=$param[4];
            $htmlpe.='<span data-code="'.$paramCode.'" class="button" onclick="AddParam'."('$table', '$paramName', '$paramCode', '$paramDef', $paramType, '$placeholder')\">$paramName</span>";
        }
        $htmlpe .= '</div><table style="margin-top: 5px;" id="'.$table.'">';
        $htmlpe .= "</table>";

        foreach ($paramRow as $param){
            $paramCode=$param[0];
            $paramValue=$param[1];
            $found=false;
            foreach ($allparams as $paramA) {
                if ($paramA[0]==$paramCode) {
                    $found=true;
                    $paramName=$paramA[1];
                    $paramType=$paramA[3];
        
                    if (is_array($paramType)) $paramType=str_replace('"', "'",json_encode($paramType));
                    else $paramType="'$paramType'";

                    $placeholder=$paramA[4];
                    $GLOBALS["onload"].="AddParamMultiple($table, $rowId, '$paramName', '$paramCode', '$paramValue', $paramType, '$placeholder');\n";
                    break;
                }
            }
        }
       
        if (!$found) $GLOBALS["onload"].="AddParamMultiple($table, $rowId, '$paramCode!', '$paramCode', '$paramValue', 'text', '');\n";
        $rowId++;
    }
    $htmlpe.='</div><a class="button" onclick="AddPart()">Přidat</a>';        
   
    $GLOBALS['script'].="var maxId=$rowId;";
    return $htmlpe."</div>";
}
?>