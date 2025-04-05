<?php
function paramEditor($params, $allparams, $name, $label) {
    $GLOBALS["script"].= /** @lang JavaScript */"
    var AddParam = function(labelText, code, val, typeInput, placeholder, spanTag) {
        let parent=document.getElementById('paramEditor$name');
        let wrap=document.createElement('tr');
        wrap.setAttribute('data-code',code);
        parent.appendChild(wrap);

        // create label
        let label=document.createElement('label');
        label.innerText=labelText;
        label.style.display='table-cell';
        wrap.appendChild(label);

        // create input
        if (Array.isArray(typeInput)){
            let textbox=document.createElement('select');
            if (val!==undefined) textbox.value=val;
            textbox.style.display='table-cell';
            textbox.style.margin='1px';
            wrap.appendChild(textbox); 
            textbox.id='pe'+code;

            for (let optionValue of typeInput){
                let option=document.createElement('option');
                option.innerText=optionValue;
                textbox.appendChild(option); 
            }
            textbox.value=val;
        } else {
            let textbox=document.createElement('input');

            if (typeInput===undefined) textbox.setAttribute('type', 'text');
            else textbox.setAttribute('type', typeInput);

            if (typeInput==='checkbox') textbox.checked=val;
            else if (val!==undefined) textbox.value=val;
            textbox.id='pe'+code;
            textbox.style.display='table-cell';
            textbox.style.margin='1px';
            if (placeholder!==undefined) textbox.placeholder=placeholder;
            wrap.appendChild(textbox); 
            
            if (typeInput==='url'){
                let btnOpen=document.createElement('a');
                btnOpen.className='button';
                btnOpen.addEventListener('click', ()=>{ window.open(textbox.value, '_blank').focus(); });
                btnOpen.innerText='Otevřít';
                wrap.appendChild(btnOpen); 
            }
        }

        // create remove button
        let rmv=document.createElement('a');
        rmv.className='button';
        rmv.innerText='Smazat';
        rmv.addEventListener('click', ()=>{
            wrap.outerHTML=''; 

            // unhide existing tag
            let span = document.querySelector('span[data-code=\"'+code+'\"]');
            span.style.display='inline-block';
        });
        rmv.style.display='table-cell';
        wrap.appendChild(rmv); 

        // hide existing tag
        let span = document.querySelector('span[data-code=\"'+code+'\"]');
        span.style.display='none';
    };

    var PEGetJSON = function() {
        let json={};

        let editor=document.getElementById('paramEditor$name');
        for (let row of editor.childNodes) {
            let code=row.getAttribute('data-code');

            // get value
            let inputE=document.getElementById('pe'+code);
            let val;
            if (inputE.tagName==='INPUT') {
                let typeE=inputE.getAttribute('type');
                if (typeE==='checkbox') val=inputE.checked;
                else val=inputE.value;
            } else if (inputE.tagName==='SELECT') {
                val=inputE.value;
            } else {
                console.error('get tagname of element '+inputE);
            }

            // set value
            json[code]=val
        }
        
        return JSON.stringify(json);
    };
 
    var PELoadJSON = function(json) {
        // unhide all tags
        for (let p of ".json_encode($allparams).") {
            let paramCode=p[0];
            let span = document.querySelector('span[data-code=\"'+paramCode+'\"]');
            span.style.display='display: inline-block';
        }

        document.getElementById('paramEditor$name').innerHTML='';
        
        if (json!=null && JSON.stringify(json) !== '{}') {    
            let listToAdd=[];
            let allParams=".json_encode($allparams).";
            
            // existing
            for (let param in json) {       
                let paramCode=param;
                let paramValue=json[param];

                let found=false;
             
                // suggested
                for (let paramA of allParams) {
                    if (paramA[0]===paramCode) {
                     //   paramCode   = paramA[0];
                        paramName   = paramA[1];
                        //paramValue  = paramA[2];
                        paramType   = paramA[3];
                        placeholder = paramA[4];  
                        listToAdd.push([paramName, paramCode, paramValue, paramType, placeholder]);
                        found=true;
                        break;
                    }
                }
                
                // not suggested 
                if (!found) listToAdd.push([paramCode+'!', paramCode, paramValue, 'text', '']);
              //  AddParam(paramCode+'!', paramCode, paramValue, 'text', '');    
            }  
            
            // table for quick searching
            let positionMap = new Map();
            for (let i = 0; i < allParams.length; i++) {
                positionMap.set(allParams[i][0], i);
            }    
          
            // sort based on order in allParams
            listToAdd = listToAdd.sort((a, b) => {
                let posA = positionMap.get(a[1]) ?? -1; // Default to -1 if not found
                let posB = positionMap.get(b[1]) ?? -1; // Default to -1 if not found
                return posA - posB;
            });
            
            console.log(listToAdd);
            
            // display params rows
            for (let itemToAdd of listToAdd) {
                AddParam(itemToAdd[0], itemToAdd[1], itemToAdd[2], itemToAdd[3], itemToAdd[4]); 
            }
        }
    };";
    
 
    $htmlpe = "<div class='section'><label id=\"$label\">$label</label><br>";
   // $htmlpe .= '<input id="paramEditorJson" type="hidden">';

    // přidat
    foreach ($allparams as $param) {
        $paramCode=$param[0];
        $paramName=$param[1];
        $paramDef=$param[2];
        $paramType=$param[3];

        if (is_array($paramType)) $paramType=str_replace( '"', "'",json_encode($paramType));
        else $paramType="'$paramType'";

        $placeholder=$param[4];
        $htmlpe.='<span data-code="'.$paramCode.'" class="button" onclick="AddParam'."('$paramName', '$paramCode', '$paramDef', $paramType, '$placeholder')\">$paramName</span>";
    } 
  /*  if (count($allparamsmultiple)>0)  $htmlpe .="<br>Víceřádkové: ";
    foreach ($allparamsmultiple as $param) {
        $paramCode=$param[0];
        $paramName=$param[1];
        $htmlpe.='<span class="button"onclick="AddParam(\''.$paramName.'\', \''.$paramCode.'\')">'.$paramName.'</span>';
    }*/
    $htmlpe .= '<table style="margin-top: 5px;" id="paramEditor'.$name.'">';
    $htmlpe .= "</table></div>";

    // existing
    foreach ($params as $param) {
        $paramCode=$param[0];
        $paramValue=$param[1];
        $found=false;
        foreach ($allparams as $paramA) {
            if ($paramA[0]==$paramCode) {
                $found=true;
                $paramName=$paramA[1];
                $paramType=$paramA[3];
        
                if (is_array($paramType)) $paramType=str_replace( '"', "'",json_encode($paramType));
                else $paramType="'$paramType'";

                $placeholder=$paramA[4];
                $GLOBALS["onload"].="AddParam('$paramName', '$paramCode', '$paramValue', $paramType, '$placeholder');\n";
                break;
            }
        }
        if (!$found) $GLOBALS["onload"].="AddParam('$paramCode!', '$paramCode', '$paramValue', 'text', '');\n";
      //  $htmlpe.='<div class="row" data-code="'.$paramCode.'"><label>'.$paramName.'</label><input value='.$paramValue.'></div>';        
    }
    return $htmlpe;
}
