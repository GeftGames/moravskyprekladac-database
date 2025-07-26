<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_pattern_to.php";
        tagsEditorDynamic();

        // relations list
        include "components/give_relations_pattern.php";
        $listR=give_relations_pattern($conn,"pronoun", true);

        // side menu
        echo FilteredList($listR, "pronoun_relations", []);

        // from list for <select>
        $sqlFrom="SELECT `id`, `label` FROM `pronoun_patterns_cs`;";

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
        pronoun_relations_changed=function() { 
            let id = flist_pronoun_relations.getSelectedIdInList();
        
            // no selected
            if (id==null) return;
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=pronoun_relation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('pronounId').value=id;
                    //from
                    filteredSearchList_pronoun_from.selectId(json.from);                   
                    filteredSearchList_pronoun_from.reload();
                    //to
                    to_load(JSON.parse(json.to));
                }else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_pronoun_relations.EventItemSelectedChanged(pronoun_relations_changed);
        ";

        $GLOBALS["script"].= /** @lang JavaScript */"
            var flist_pronoun_relations; 
            var currentpronounRelationSave = function() {
                let froms=document.getElementById('listreturnholder_pronoun_from').value;              
                let id=document.getElementById('pronounId').value;              
    
                let formData = new URLSearchParams();
                formData.append('action', 'pronoun_relation_update');
                formData.append('id', id);
                formData.append('from', froms);
                
                formData.append('to', to_save());
               
                fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                }).then(response => response.json())
                .then(json => {
                    if (json.status==='OK') {                          
                        flist_pronoun_relations.getSelectedItemsInList()[0].innerText=document.getElementById('selectedLabel_pronoun_from').innerText;
                    }else console.log('error currentpronounRelationSave', json);
                });
            };";
        ?>
    </div>
    <div class="editorView">
        <div id="pronoun">
            <div class="section row">
                <label for="pronoun_from" id="name">Z</label>&nbsp;
                <div id="select_pronoun_from"></div>
                <?php createSelectList($listFrom, "pronoun_from", $idFrom);?>
            </div>

            <div class="section">
                <label for="pronoun_from" id="name">Na</label>
                <?php echo multiple_pattern_to([], "pronoun"); ?>
            </div>

            <div class="section">
                <input type="hidden" id="pronounId" value="-1">
                <a onclick="currentpronounRelationSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>

<?php
$GLOBALS["script"].= /** @lang JavaScript */"

// Load pattern
var showPatternEditor = function(id) { 
    // show popup
    let popup=document.getElementById('popup_pronounPattern');
    popup.style.display='flex';
    
    // show loading message
    document.getElementById('popup_pronounPattern_loading').style.display='flex';
    document.getElementById('popup_pronounPattern_loading').innerText='Načítám...';
    document.getElementById('popupBody').style.display='none';
    
    fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=pronoun_pattern_to_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            // hide loading message
            document.getElementById('popup_pronounPattern_loading').style.display='none';
            document.getElementById('popupBody').style.display='block';
            
            // set values
            document.getElementById('pronounId').value=id;
            document.getElementById('pronounLabel').value=json.label;
            document.getElementById('pronounBase').value=json.base;
            document.getElementById('pronounUppercase').value=json.uppercase;
            
            if (json.gender==null) json.gender=0;
            document.getElementById('pronounGender').value=json.gender; 
            
            let rawShapes=json.shapes;
            let shapes;
            if (rawShapes!=null) {
                shapes=json.shapes.split('|'); 
            } else shapes=[];
            for (let i=0; i<14; i++) {
                let shape=shapes[i];                            
                let textbox=document.getElementById('pronoun'+i);

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
            document.getElementById('popup_pronounPattern_loading').innerHTML='ID: \"'+id+'\"<br>'+ JSON.stringify(json);     
            console.log('error sql', json);
        }
    });
};

// Save pattern
var currentpronounTOSave = function() {
    let label=document.getElementById('pronounLabel').value;
    let base=document.getElementById('pronounBase').value;
    let gender=document.getElementById('pronounGender').value;
    let uppercase=document.getElementById('pronounUppercase').value;
    let pronounId=document.getElementById('pronounId').value;
    let tags=document.getElementById('pronoun_todatatags').value;
    let pattern=document.getElementById('pronounPattern').value;
    let shapes=[];
    for (let i=0; i<14; i++) {
        let textbox=document.getElementById('pronoun'+i);
        shapes[i]=textbox.value
    }

    let formData = new URLSearchParams();
    formData.append('action', 'pronoun_pattern_to_update');
    formData.append('id', pronounId);
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
            let popup=document.getElementById('popup_pronounPattern');
            popup.style.display='none';
        }else {
            console.log('error currentRegionSave',json);            
        }
    });
};";
?>

<div id="popup_pronounPattern" class="popupBackground" style="display: none; top: 0;">
    <div class="popup">
        <div class="popupHeader"><span class="pupupTitle">Úprava skloňování</span><span onclick="popupClose('pronounPattern')" class="popupClose">×</span></div>
        <div id="popup_pronounPattern_loading" class="loading">Načítání</div>
        <div class="popupBody" id="popupBody">

            <div class="row section">
                <label id="name" for="pronounLabel">Popis</label><br>
                <input type="text" id="pronounLabel" value="" placeholder="pohádKA">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base" for="pronounBase">Základ</label><br>
                <input type="text" id="pronounBase" value="" placeholder="pohád">
            </div>

            <div class="section">
                <label id="name" for="pronounShapes">Pád</label>
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
                        for ($j=0; $j<2; $j++) $html.="<td><input id='pronoun".($j==0 ? $i : 7+$i)."' type='text'></td>";
                        $html.="</tr>";
                    }
                    echo $html;
                    ?>
                </table>
                <input type="hidden" id="pronounShapes" value="-1">
            </div>

            <div class="row section">
                <label for="pronounGender">Rod</label>
                <select id="pronounGender" name="type">
                    <option value="0">Neznámý</option>
                    <option value="4">Střední</option>
                    <option value="3">Ženský</option>
                    <option value="2">Mužský neživotný</option>
                    <option value="1">Mužský životný</option>
                </select>
                <br>
            </div>




            <div class="row section">
                <label for="pronounPattern">Vzor</label>
                <select id="pronounPattern" name="type">
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
            </div>

            <div class="row section">
                <label for="pronounUppercase">Velké písmena</label>
                <select id="pronounUppercase" name="uppercase">
                    <option value="0">Neznámý</option>
                    <option value="1">malé</option>
                    <option value="2">Počáteční Velké</option>
                    <option value="3">VŠECHNY VELKÉ</option>
                </select>
                <br>
            </div>

            <?php echo tagsEditor("pronoun_to", [], "Tagy") ?>
            <hr>
            <div style="float: right;">
                <input type="hidden" id="pronounId" value="-1">
                <a onclick="currentpronounTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>