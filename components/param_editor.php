<?php
/*
function editorSourceSample() {
    return paramEditor(
        [], 
        [
            ["born_place",      "datum pořízení",       "",         "text",     ""], 
            ["writer_name",     "jméno zapisovatele",   "",         "text",     ""],
            ["strany",          "strany publikace",     "",         "text",     ""],
            ["kapitola",        "kapitoly publikace",   "",         "text",     ""],
            ["odkaz",           "url odkaz",            "",         "text",     ""],
            ["cislo_periodika", "číslo periodika",      "",         "text",     ""],
            ["ročník_periodika", "ročník periodika",      "",         "text",     ""],
        ],
        "sourcesample"
    );
}*/
/*
function editorSource() {
    return paramEditor(
        [
           // ["nazev", "Nážečí na Břeclavsku a v dolním pomoraví"], 
           // ["jmeno", "František"], 
           // ["prijmeni", "Svěrák"], 
        ],
        [ //[save code,     label,         default,     type,       example]
            ["typ",         "typ",     "kniha",         ['kniha', 'periodikum', 'web', 'sncj'],   "kniha"],

            ["nazev",       "název",        "",         "text",     "Nářečí na Břeclavsku a v dolním..."], 
            ["podnazev",    "podnázev",     "",         "text",     ""],
            ["periodikum",  "periodikum",   "",         "text",     ""],

            ["titul_pred",  "titul před jménem",        "",         "text",     "Ph. Dr."], 
            ["jmeno",       "jméno",        "",         "text",     "František"], 
            ["prijmeni",    "příjmení",     "",         "text",     "Svěrák"], 
            ["titul_za",    "titul za jménem","",       "text",     "Ph. Dr."], 
            ["spoluautori", "spoluautoři",  "",         "text",     "PRIJMENI, Jmeno; PRIJMENI, Jmeno"], 
            ["spolecnost",  "společnost",   "",         "text",     "Masarykova universita"],
            
            ["vydani",      "číslo vydání", "",         "number",   "1"], 
            ["rok_vydani",  "rok pořízení", "",         "number",   "1900"], 
            ["vydavatel",   "vydavatel",    "",         "text",     "Nakladatelství XX"], 
            ["misto",       "místo vydání", "",         "text",     ""],
            ["i",           "sncj misto",   "",         "text",     ""],
            
            ["odkaz",       "url odkaz",    "",         "text",      "https://..."], 
            ["format",      "formát",       "online",   "text",     "online"], 
            
            ["legalnost",   "legálnost",    true,       "checkbox",  true        ],
            ["licence",     "licence",      "",         "text",     "Volné dílo"],
            
            ["kapitola",    "kapitola",     "",         "text",     ""],
            ["strany",      "strany",       "",         "text",     ""],
            
            ["issn",        "issn",         "",         "text",     ""],
            ["ibsn",        "ibsn",         "",         "text",     ""],

            ["cislo",       "číslo",        "",         "text",     ""],
            ["rocnik",      "ročník",       "",         "text",     ""],

            ["poznámka",    "poznámka",     "",        "text",     ""],

            ["rok_pristupu","rok přístupu", "",         "number",   ""],
            ["mesic_pristupu","měsíc přístupu", "",     "number",   ""],
            ["den_pristupu","den přístupu", "",         "number",   ""],
        ]
    );
}*/

function paramEditor($params, $allparams, $name, $label) {
    $GLOBALS["script"].="var AddParam = function(labelText, code, val, typeInput, placeholder, spanTag) {
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
            if (val!=undefined) textbox.value=val;
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

            if (typeInput==undefined) textbox.setAttribute('type', 'text');
            else textbox.setAttribute('type', typeInput);

            if (typeInput=='checkbox') textbox.checked=val;
            else if (val!=undefined) textbox.value=val;
            textbox.id='pe'+code;
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
        
        if (json!=null && JSON.stringify(json) != '{}') {
            // existing
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
                        AddParam(paramName, paramCode, paramValue, paramType, placeholder);
                        break;
                    }
                }
                if (!found) AddParam(paramCode+'!', paramCode, paramValue, 'text', '');    
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
?>