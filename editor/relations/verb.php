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
        echo FilteredList($listR, "verb_relations", []);

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
                    document.getElementById('verbId').value=id;
                    //from
                    filteredSearchList_verb_from.selectId(json.from);                   
                    filteredSearchList_verb_from.reload();
                    //to
                    to_load(JSON.parse(json.to));
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
    
                let formData = new URLSearchParams();
                formData.append('action', 'verb_relation_update');
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
                        flist_verb_relations.getSelectedItemsInList()[0].innerText=document.getElementById('selectedLabel_verb_from').innerText;
                    }else console.log('error currentverbRelationSave', json);
                });
            };";
        ?>
    </div>
    <div class="editorView">
        <div id="verb">
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
$GLOBALS["script"].= /** @lang JavaScript */"

// Load pattern
var showPatternEditor = function(id) { 
    // show popup
    let popup=document.getElementById('popup_verbPattern');
    popup.style.display='flex';
    
    // show loading message
    document.getElementById('popup_verbPattern_loading').style.display='flex';
    document.getElementById('popup_verbPattern_loading').innerText='Načítám...';
    document.getElementById('popupBody').style.display='none';
    
    fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=verb_pattern_to_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            // hide loading message
            document.getElementById('popup_verbPattern_loading').style.display='none';
            document.getElementById('popupBody').style.display='block';
            
            // set values
            document.getElementById('verbId').value=id;
            document.getElementById('verbLabel').value=json.label;
            document.getElementById('verbBase').value=json.base;
            document.getElementById('verbUppercase').value=json.uppercase;
            
            if (json.gender==null) json.gender=0;
            document.getElementById('verbGender').value=json.gender; 
            
            let rawShapes=json.shapes;
            let shapes;
            if (rawShapes!=null) {
                shapes=json.shapes.split('|'); 
            } else shapes=[];
            for (let i=0; i<14; i++) {
                let shape=shapes[i];                            
                let textbox=document.getElementById('verb'+i);

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
            document.getElementById('popup_verbPattern_loading').innerHTML='ID: \"'+id+'\"<br>'+ JSON.stringify(json);     
            console.log('error sql', json);
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
};";
?>

<div id="popup_verbPattern" class="popupBackground" style="display: none; top: 0;">
    <div class="popup">
        <div class="popupHeader"><span class="pupupTitle">Úprava skloňování</span><span onclick="popupClose('verbPattern')" class="popupClose">×</span></div>
        <div id="popup_verbPattern_loading" class="loading">Načítání</div>
        <div class="popupBody" id="popupBody">

            <div class="row section">
                <label id="name" for="verbLabel">Popis</label><br>
                <input type="text" id="verbLabel" value="" placeholder="pohádKA">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base" for="verbBase">Základ</label><br>
                <input type="text" id="verbBase" value="" placeholder="pohád">
            </div>

            <div class="section">
                <label id="name">Skloňování</label>
                <div style="display: flex; flex-wrap: wrap;">
                    <?php
                    $arrGenders=["Mužský životný", "Mužský neživotný", "Ženský", "Střední"];
                    for ($g=0; $g<4; $g++) {
                        $html='<table>
                            <caption>'.$arrGenders[$g].'</caption>
                            <tr>
                                <td class="tableHeader">Pád</td>
                                <td class="tableHeader">Jednotné</td>
                                <td class="tableHeader">Množné</td>
                            </tr>';

                        for ($i=0; $i<9; $i++) {
                            // label falls
                            $html.="<tr><td>".($i<7 ? $i+1 : ( $i==7 ? "n" : "a")).".</td>";
                            for ($j=0; $j<2; $j++) $html.="<td><input id='verb".$g.($j==0 ? $i : 9+$i)."' type='text'></td>";
                            $html.="</tr>";
                        }
                        echo $html.'</table>';
                    }
                    ?>
                </div>
                <input type="hidden" id="verbShapes" value="-1">
            </div>

            <div class="row section">
                <label for="verbCategory">Kategorie</label>
                <select id="verbCategory" name="type">
                    <option value="0">Neznámý</option>
                    <option value="1">Tvrdé</option>
                    <option value="2">Měkké</option>
                    <option value="3">Přivlastňovací</option>
                </select>
                <br>
            </div>

            <?php echo tagsEditor("verb_to", [], "Tagy") ?>
            <hr>
            <div style="float: right;">
                <input type="hidden" id="verbId" value="-1">
                <a onclick="currentverbTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>