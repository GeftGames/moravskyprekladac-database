<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_pattern_to.php";
        tagsEditorDynamic();

        // relations list
        include "components/give_relations_pattern.php";
        $listR=give_relations_pattern($conn,"verb", true);

        // side menu
        echo FilteredList($listR, "verb_relations", [], $_SESSION['translate']);

        // from list for <select>
        $sqlFrom="SELECT `id`, `label` FROM `verb_patterns_cs`;";

        $listFrom=[];
        $resultFrom = $conn->query($sqlFrom);
        if (!$resultFrom) {
            $sqlDone=false;
            throwError("SQL error: ".$sqlFrom);
        }else{
            while ($rowFrom = $resultFrom->fetch_assoc()) {
                $listFrom[]=[$rowFrom["id"], $rowFrom["label"]];
            }
        }

        $idFrom=0;

        $GLOBALS["onload"].= /** @lang JavaScript */"
        verb_relations_changed=function() { 
            let id = flist_verb_relations.getSelectedIdInList();
        
            // no selected
            if (id==null) return;
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=verb_relation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    let d=json.data;
                    document.getElementById('verbId').value=id;
                    // set custom base
                    console.log(d);
                    document.getElementById('usecustombase').checked = (d.custombase != null);
                    document.getElementById('custombase').value = d.custombase;
                    //from
                    filteredSearchList_verb_from.selectId(d.from);                   
                    filteredSearchList_verb_from.reload();
                    //to
                    to_load(d.to);
                }else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_verb_relations.EventItemSelectedChanged(verb_relations_changed);
        ";

        $GLOBALS["script"].= /** @lang JavaScript */"
            var flist_verb_relations; 
            var currentverbRelationSave = function() {
                let froms=document.getElementById('listreturnholder_verb_from').value;              
                let id=document.getElementById('verbId').value;   
                let usecustombase = document.getElementById('usecustombase').checked;
                let custombase = document.getElementById('custombase').value;           
    
                let formData = new URLSearchParams();
                formData.append('action', 'verb_relation_update');
                formData.append('id', id);
                formData.append('from', froms);
                formData.append('custombase', usecustombase ? custombase: null);
                
                formData.append('to', to_save());
               
                fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                }).then(response => response.json())
                .then(json => {
                    if (json.status==='OK') {                          
                        flist_verb_relations.getSelectedItemsInList()[0].innerText=document.getElementById('selectedLabel_verb_from').innerText;
                    }else console.log('error currentverbRelationSave', json);
                });
            };";
        ?>
    </div>
    <div class="editorView">
        <div id="verb">
            <div class="section row">
                <input type="checkbox" id="usecustombase">
                <label for="usecustombase" style="user-select: none;inline-size: -webkit-fill-available;">Jiný základ</label>&nbsp;
                <input type="text" id="custombase" placeholder="zele">
            </div>

            <div class="section row">
                <label for="verb_from" id="name">Z</label>&nbsp;
                <div id="select_verb_from"></div>
                <?php createSelectList($listFrom, "verb_from", $idFrom);?>
            </div>

            <div class="section">
                <label for="verb_from" id="name">Na</label>
                <?php echo multiple_pattern_to([], "verb"); ?>
            </div>

            <div class="section">
                <input type="hidden" id="verbId" value="-1">
                <a onclick="currentverbRelationSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>

<?php
$arrShapeTables=[
    //[name, show, len, code, display]
        ["Infinitiv", false, 1, "infinitive"],
        ["Přítomný", true, 6, "continous"],
        ["Budoucí", true, 6, "future"],
        ["Rozkazovací", true, 3, "imperative"],
        ["Minulý činný", true, 8, "past_active"],
        ["Minulý trpný", true, 8, "past_passive"],
        ["Přechodník přítomný", true, 3, "transgressive_cont"],
        ["Přechodník minulý", true, 3, "transgressive_past"],
        ["Podmiňovací", true, 6, "auxiliary"],
];
$jsArrShapeTables=json_encode($arrShapeTables, JSON_UNESCAPED_UNICODE);

$GLOBALS["script"].= /** @lang JavaScript */"

// Load pattern
var showPatternEditor = function(id) {
    // no selected
    if (id==null) return;
    
    // show popup
    let popup=document.getElementById('popup_verbPattern');
    popup.style.display='flex';    
    
    document.getElementById('popup_verbPattern_loading').style.display='none';

    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=verb_pattern_to_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            let d=json.data;
            document.getElementById('verbId').value=id;
            document.getElementById('verbLabel').value=d.label;
            document.getElementById('verbBase').value=d.base;

            if (d.category===null) d.category=0;
            document.getElementById('verbCategory').value=d.category; 

            let shapesTypes=$jsArrShapeTables;
            
            // shapes load
            let shapeTypeIndex=0;
            for (let s of shapesTypes) {
                let code='shapes_'+s[3]; // 'continous', 'future', ...              
                let exists=d[code]!==undefined && d[code]!==''; // if this type of table conjunction exists
             
                let checkbox=s[1];// if exists checkbox
                
                // checkbox
                if (checkbox) {
                    document.getElementById('verbforms'+shapeTypeIndex).checked=exists;
                }
              
                // textboxs
                if (exists) {
                    let shapes=d[code].split('|');
                    let shapesLen=s[2];
                 //   console.log(shapes);
                    for (let i=0; i<shapesLen; i++) { 
                        let shape1=shapes[i];                            
                     //   console.log(shape1,shapeTypeIndex,i);
                        let textbox=document.getElementById('verbShape'+shapeTypeIndex+''+i);
                        if (textbox==null) console.error('\"verbShape'+shapeTypeIndex+''+i+'\" does not exists');
                        if (shape1===undefined) textbox.value='';
                        else textbox.value=shape1;
                    }
                }
                shapeTypeIndex++;
            }

            changeVisibility();

            // tags
            if (d.tags!=null) {
                let arrTags=d.tags.split('|');
                tagSet(arrTags, 'verb_to');
            } else {
                tagSet([], 'verb_to');
            }
           
        } else {
            console.log('error sql', json);
            document.getElementById('popup_nounPattern_loading').innerHTML='ID: \"'+id+'\"<br>'+ JSON.stringify(json);   
        }
    });
};

// Save pattern
var currentverbTOSave = function() {
    let label=document.getElementById('verbLabel').value;
    let base=document.getElementById('verbBase').value;
    let gender=document.getElementById('verbGender').value;
    let uppercase=document.getElementById('verbUppercase').value;
    let verbId=document.getElementById('verbId').value;
    let tags=document.getElementById('verb_todatatags').value;
    let pattern=document.getElementById('verbPattern').value;
    let shapes=[];
    for (let i=0; i<14; i++) {
        let textbox=document.getElementById('verb'+i);
        shapes[i]=textbox.value
    }

    let formData = new URLSearchParams();
    formData.append('action', 'verb_pattern_to_update');
    formData.append('id', verbId);
    formData.append('label', label);
    formData.append('base', base);
    formData.append('gender', gender);
    formData.append('pattern', pattern);
    formData.append('uppercase', uppercase);
    formData.append('shapes', shapes.join('|'));
    formData.append('tags', tags);

    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            // hide popup
            let popup=document.getElementById('popup_verbPattern');
            popup.style.display='none';
        }else {
            console.log('error currentRegionSave',json);            
        }
    });
};

  var changeVisibility = function() {
            let shapesTypes=$jsArrShapeTables;
            
            for (let i=0; i<shapesTypes.length; i++) {
                // checkboxl
                if (shapesTypes[i][1]) {
                    let display=document.getElementById('verbforms'+i).checked;
                    document.getElementById('verbtable'+i).style.display=display ? 'inline' : 'none';
                }
            }
        };

";
?>

<div id="popup_verbPattern" class="popupBackground" style="display: none; top: 0;">
    <div class="popup">
        <div class="popupHeader"><span class="pupupTitle">Úprava skloňování</span><span onclick="popupClose('verbPattern')" class="popupClose">×</span></div>
        <div id="popup_verbPattern_loading" class="loading">Načítání</div>
        <div class="popupBody" id="popupBody" style="display: block; max-height: calc(100vh - 2cm); overflow-y: scroll;">
            <div class="row section">
                <label id="name" for="verbLabel" >Popis</label><br>
                <input type="text" id="verbLabel" value="" placeholder="dělAT" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base" for="verbBase">Základ</label><br>
                <input type="text" id="verbBase" value="" placeholder="děl" style="max-width: 9cm;">
            </div>

            <div class="section">
                <label id="name">Časování</label>
                <style>#verbTables > div{ min-width:14cm; }</style>
                <div style="" id="verbTables">
                    <?php
                    $arrGenders=["Mužský životný", "Mužský neživotný", "Ženský", "Střední"];
                    for ($g=0; $g<count($arrShapeTables); $g++) {
                        $html='<div><p>';
                        if ($arrShapeTables[$g][1])$html.='<input type="checkbox" id="verbforms'.$g.'" onclick="changeVisibility()">';
                        $html.='<label for="verbforms'.$g.'" style="user-select:none">'.$arrShapeTables[$g][0].'</label></p>';
                        $html.='<table id="verbtable'.$g.'">';
                        $typeTable=$arrShapeTables[$g][3];

                        // Infinitive
                        if ($typeTable=="infinitive") {
                            $html.= '<input id="verbShape'.$g.'0" type="text">';

                            // Continous, Future, Podmiňovací
                        } else if ($typeTable=="continous" || $typeTable=="future" || $typeTable=="auxiliary") {
                            // table header
                            $html.='<tr>
                                    <td class="tableHeader">Osoba</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                                </tr>';

                            // table body 3×2
                            for ($i=0; $i<3; $i++) {
                                $html.="<tr><td>".($i+1).".</td>"; // label person
                                for ($j=0; $j<2; $j++) $html.="<td><input id='verbShape".$g.($j==0 ? $i : 3+$i)."' type='text'></td>";
                                $html.="</tr>";
                            }

                            // Imperative
                        } else if ($typeTable=="imperative") {
                            // table header
                            $html.='<tr>
                                    <td class="tableHeader">Rod</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                                </tr>';

                            // table body 2×2 (-1)
                            $html.="<tr><td>1.</td>";
                            $html.="<td><input disabled style='opacity: .5;' type='text'></td>";
                            $html.="<td><input id='verbShape".$g."1' type='text'></td>";
                            $html.="</tr>";
                            $html.="<tr><td>2.</td>";
                            $html.="<td><input id='verbShape".$g."0' type='text'></td>";
                            $html.="<td><input id='verbShape".$g."2' type='text'></td>";
                            $html.="</tr>";

                            // Past Passive and active
                        } else if ($typeTable=="past_active" || $typeTable=="past_passive") {
                            // table header
                            $html.='<tr>
                                    <td class="tableHeader">Rod</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                                </tr>';

                            // table body 4×2
                            for ($i=0; $i<4; $i++) {
                                $html.="<tr><td>".($i==0 ? "m. ž" : ($i==1 ? "m. n" : ($i==2 ? "žen" : "stř"))).".</td>"; // gender label
                                for ($j=0; $j<2; $j++) $html.="<td><input id='verbShape".$g.($j==0 ? $i : 4+$i)."' type='text'></td>";
                                $html.="</tr>";
                            }

                            //transgressive
                        }else if ($typeTable=="transgressive_cont" || $typeTable=="transgressive_past") {
                            // table header
                            $html.='<tr>
                                    <td class="tableHeader">Rod</td>
                                    <td class="tableHeader">Jednotné</td>
                                </tr>';

                            // table body 3×1
                            $html.="<tr><td>j. m</td><td><input id='verbShape".$g."0' type='text'></td></tr>";
                            $html.="<tr><td>j. f+n</td><td><input id='verbShape".$g."1' type='text'></td></tr>";
                            $html.="<tr><td>Množný</td><td><input id='verbShape".$g."2' type='text'></td></tr>";
                        }else echo "<p>ERROR: Table does not exists: $typeTable</p>";
                        echo $html.'</table></div>';
                    }
                    ?>
                </div>
                <input type="hidden" id="verbShapes" value="-1">
            </div>

            <div class="row section">
                <label for="verbCategory">Zvratné</label>
                <select id="verbCategory" name="type">
                    <option value="0">Neznámý</option>
                    <option value="1">BEZ</option>
                    <option value="2">SI</option>
                    <option value="3">SE</option>
                </select>
                <br>
            </div>

            <div class="row section">
                <label for="verbFallReaction">Reakce pádů</label>
                <input id="verbFallReaction" type="text" placeholder="4,6">
                <br>
            </div>

            <?php echo tagsEditor("verb_to", [], "Tagy")?>

            <hr>
            <div style="float: right;">
                <input type="hidden" id="verbId" value="-1">
                <a onclick="currentverbTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>