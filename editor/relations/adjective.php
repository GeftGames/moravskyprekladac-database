<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_pattern_to.php";
        tagsEditorDynamic();

        // relations list
        include "components/give_relations_pattern.php";
        $listR=give_relations_pattern($conn,"adjective");

        // side menu
        echo FilteredList($listR, "adjective_relations", [], $_SESSION['translate']);

        // from list for <select>
        $sqlFrom="SELECT `id`, `label` FROM `adjective_patterns_cs`;";

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
        adjective_relations_changed=function() { 
            let id = flist_adjective_relations.getSelectedIdInList();
        
            // no selected
            if (id==null) return;
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=adjective_relation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('adjectiveId').value=id;
                    // set custom base
                    document.getElementById('usecustombase').checked = (json.custombase != null);
                    document.getElementById('custombase').value = json.custombase;
                    //from
                    filteredSearchList_adjective_from.selectId(json.from);                   
                    filteredSearchList_adjective_from.reload();
                    //to
                   // console.log(json.to);
                    to_load(json.to);
                }else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_adjective_relations.EventItemSelectedChanged(adjective_relations_changed);
        ";

        $GLOBALS["script"].= /** @lang JavaScript */"
            var flist_adjective_relations; 
            var currentAdjectiveRelationSave = function() {
                let froms=document.getElementById('listreturnholder_adjective_from').value;              
                let id=document.getElementById('adjectiveId').value;              
                let usecustombase = document.getElementById('usecustombase').checked;
                let custombase = document.getElementById('custombase').value;

                let formData = new URLSearchParams();
                formData.append('action', 'adjective_relation_update');
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
                        flist_adjective_relations.getSelectedItemsInList()[0].innerText=document.getElementById('selectedLabel_adjective_from').innerText;
                    }else console.log('error currentAdjectiveRelationSave', json);
                });
            };";
        ?>
    </div>
    <div class="editorView">
        <div id="adjective">
            <div class="section row">
                <input type="checkbox" id="usecustombase">
                <label for="usecustombase" style="user-select: none;inline-size: -webkit-fill-available;">Jiný základ</label>&nbsp;
                <input type="text" id="custombase" placeholder="zele">
            </div>

            <div class="section row">
                <label for="adjective_from" id="name">Z</label>&nbsp;
                <div id="select_adjective_from"></div>
                <?php createSelectList($listFrom, "adjective_from", $idFrom);?>
            </div>

            <div class="section">
                <label for="adjective_from" id="name">Na</label>
                <?php echo multiple_pattern_to([], "adjective"); ?>
            </div>

            <div class="section">
                <input type="hidden" id="adjectiveId" value="-1">
                <a onclick="currentAdjectiveRelationSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>

<?php
$GLOBALS["script"].= /** @lang JavaScript */"

// Load pattern
var showPatternEditor = function(id) { 
    // show popup
    let popup=document.getElementById('popup_adjectivePattern');
    popup.style.display='flex';
    
    // show loading message
    document.getElementById('popup_adjectivePattern_loading').style.display='flex';
    document.getElementById('popup_adjectivePattern_loading').innerText='Načítám...';
    document.getElementById('popupBody').style.display='none';
    
    fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=adjective_pattern_to_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            // hide loading message
            document.getElementById('popup_adjectivePattern_loading').style.display='none';
            document.getElementById('popupBody').style.display='block';
            
            // set values
            document.getElementById('adjectiveId').value=id;
            document.getElementById('adjectiveLabel').value=json.label;
            document.getElementById('adjectiveBase').value=json.base;
            
            if (json.gender==null) json.gender=0;
            document.getElementById('adjectiveGender').value=json.gender; 
            
            let rawShapes=json.shapes;
            let shapes;
            if (rawShapes!=null) {
                shapes=json.shapes.split('|'); 
            } else shapes=[];
            for (let i=0; i<14; i++) {
                let shape=shapes[i];                            
                let textbox=document.getElementById('adjective'+i);

                if (shape===undefined) textbox.value='';
                else textbox.value=shape;
            }                   

            if (json.tags!=null) {
                let arrTags=json.tags.split('|');
                tagSet(arrTags);
            }else{
                tagSet([]);
            }
               
        }else {
            // error
            document.getElementById('popup_adjectivePattern_loading').innerHTML='ID: \"'+id+'\"<br>'+ JSON.stringify(json);     
            console.log('error sql', json);
        }
    });
};

// Save pattern
var currentAdjectiveTOSave = function() {
    let label=document.getElementById('adjectiveLabel').value;
    let base=document.getElementById('adjectiveBase').value;
    let gender=document.getElementById('adjectiveGender').value;
    let uppercase=document.getElementById('adjectiveUppercase').value;
    let adjectiveId=document.getElementById('adjectiveId').value;
    let tags=document.getElementById('adjective_todatatags').value;
    let pattern=document.getElementById('adjectivePattern').value;
    let shapes=[];
    for (let i=0; i<14; i++) {
        let textbox=document.getElementById('adjective'+i);
        shapes[i]=textbox.value
    }

    let formData = new URLSearchParams();
    formData.append('action', 'adjective_pattern_to_update');
    formData.append('id', adjectiveId);
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
            let popup=document.getElementById('popup_adjectivePattern');
            popup.style.display='none';
        }else {
            console.log('error currentRegionSave',json);            
        }
    });
};";
?>

<div id="popup_adjectivePattern" class="popupBackground" style="display: none; top: 0;">
    <div class="popup">
        <div class="popupHeader"><span class="pupupTitle">Úprava skloňování</span><span onclick="popupClose('adjectivePattern')" class="popupClose">×</span></div>
        <div id="popup_adjectivePattern_loading" class="loading">Načítání</div>
        <div class="popupBody" id="popupBody">

            <div class="row section">
                <label id="name" for="adjectiveLabel">Popis</label><br>
                <input type="text" id="adjectiveLabel" value="" placeholder="pohádKA">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base" for="adjectiveBase">Základ</label><br>
                <input type="text" id="adjectiveBase" value="" placeholder="pohád">
            </div>

            <div class="section">
                <label id="name" for="adjectiveShapes">Pád</label>
                <table>
                    <tr>
                        <td class="tableHeader">Pád</td>
                        <td class="tableHeader">Jednotné</td>
                        <td class="tableHeader">Množné</td>
                    </tr>
                    <?php
                    $html="";
                    for ($i=0; $i<7; $i++) {
                        $html.="<tr><td>".($i+1).".</td>";
                        for ($j=0; $j<2; $j++) $html.="<td><input id='adjective".($j==0 ? $i : 7+$i)."' type='text'></td>";
                        $html.="</tr>";
                    }
                    echo $html;
                    ?>
                </table>
                <input type="hidden" id="adjectiveShapes" value="-1">
            </div>

            <table class="formTable">
                <tr class="row section">
                    <td><label for="adjectiveGender">Rod</label></td>
                    <td>
                        <select id="adjectiveGender" name="type">
                            <option value="0">Neznámý</option>
                            <option value="4">Střední</option>
                            <option value="3">Ženský</option>
                            <option value="2">Mužský neživotný</option>
                            <option value="1">Mužský životný</option>
                        </select>
                    </td>
                </tr>
                <tr class="row section">
                    <td><label for="adjectivePattern">Vzor</label></td>
                    <td>
                        <select id="adjectivePattern" name="type">
                            <option value="0">Neznámý</option>
                            <optgroup label="Střední">
                                <option value="1">Město</option>
                                <option value="2">Moře</option>
                                <option value="3">Kuře</option>
                                <option value="4">Stavení</option>
                            </optgroup>
                            <optgroup label="Ženský">
                                <option value="5">Žena</option>
                                <option value="6">Růže</option>
                                <option value="7">Píseň</option>
                                <option value="8">Kost</option>
                            </optgroup>
                            <optgroup label="Mužský">
                                <option value="9">Pán</option>
                                <option value="10">Hrad</option>
                                <option value="11">Les</option>
                                <option value="12">Muž</option>
                                <option value="13">Stroj</option>
                                <option value="14">Předseda</option>
                                <option value="15">Soudce</option>
                            </optgroup>
                            <optgroup label="Přídavné">
                                <option value="16">Mladý</option>
                                <option value="17">Jarní</option>
                            </optgroup>
                        </select>
                    </td>
                </tr>
                <tr class="row section">
                    <td><label for="adjectiveUppercase">Velké písmena</label></td>
                    <td>
                        <select id="adjectiveUppercase" name="uppercase">
                            <option value="0">Neznámý</option>
                            <option value="1">malé</option>
                            <option value="2">Počáteční Velké</option>
                            <option value="3">VŠECHNY VELKÉ</option>
                        </select>
                    </td>
                </tr>
            </table>

            <?php echo tagsEditor("adjective_to", [], "Tagy") ?>
            <hr>
            <div style="float: right;">
                <input type="hidden" id="adjectiveId" value="-1">
                <a onclick="currentAdjectiveTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>