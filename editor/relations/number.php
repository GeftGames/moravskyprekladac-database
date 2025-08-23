<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_pattern_to.php";
        tagsEditorDynamic();

        // relations list
        include "components/give_relations_pattern.php";
        $listR=give_relations_pattern($conn,"number", true);

        // side menu
        echo FilteredList($listR, "number_relations", [], $_SESSION['translate']);

        // from list for <select>
        $sqlFrom="SELECT `id`, `label` FROM `number_patterns_cs`;";

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
        number_relations_changed=function() { 
            let id = flist_number_relations.getSelectedIdInList();
        
            // no selected
            if (id==null) return;
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=number_relation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('numberId').value=id;
                    // set custom base
                    document.getElementById('usecustombase').checked = (json.custombase != null);
                    document.getElementById('custombase').value = (json.custombase ?? '');
                    //from
                    filteredSearchList_number_from.selectId(json.from);                   
                    filteredSearchList_number_from.reload();
                    //to
                    to_load(json.to);
                }else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_number_relations.EventItemSelectedChanged(number_relations_changed);
        ";

        $GLOBALS["script"].= /** @lang JavaScript */"
            var flist_number_relations; 
            var currentnumberRelationSave = function() {
                let froms=document.getElementById('listreturnholder_number_from').value;              
                let id=document.getElementById('numberId').value;                   
                let usecustombase = document.getElementById('usecustombase').checked;
                let custombase = document.getElementById('custombase').value;        
    
                let formData = new URLSearchParams();
                formData.append('action', 'number_relation_update');
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
                        flist_number_relations.getSelectedItemsInList()[0].innerText=document.getElementById('selectedLabel_number_from').innerText;
                    }else console.log('error currentnumberRelationSave', json);
                });
            };";
        ?>
    </div>
    <div class="editorView">
        <div id="number">
            <div class="section row">
                <input type="checkbox" id="usecustombase">
                <label for="usecustombase" style="user-select: none;inline-size: -webkit-fill-available;">Jiný základ</label>&nbsp;
                <input type="text" id="custombase" placeholder="zele">
            </div>

            <div class="section row">
                <label for="number_from" id="name">Z</label>&nbsp;
                <div id="select_number_from"></div>
                <?php createSelectList($listFrom, "number_from", $idFrom);?>
            </div>

            <div class="section">
                <label for="number_from" id="name">Na</label>
                <?php echo multiple_pattern_to([], "number"); ?>
            </div>

            <div class="section">
                <input type="hidden" id="numberId" value="-1">
                <a onclick="currentnumberRelationSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>

<?php
$GLOBALS["script"].= /** @lang JavaScript */"

// Load pattern
var showPatternEditor = function(id) { 
    // show popup
    let popup=document.getElementById('popup_numberPattern');
    popup.style.display='flex';
    
    // show loading message
    document.getElementById('popup_numberPattern_loading').style.display='flex';
    document.getElementById('popup_numberPattern_loading').innerText='Načítám...';
    document.getElementById('popupBody').style.display='none';
    
    fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=number_pattern_to_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            // hide loading message
            document.getElementById('popup_numberPattern_loading').style.display='none';
            document.getElementById('popupBody').style.display='block';
            
            // set values
            document.getElementById('numberId').value=id;
            document.getElementById('numberLabel').value=json.label;
            document.getElementById('numberBase').value=json.base;
            document.getElementById('numberUppercase').value=json.uppercase;
            
            if (json.gender==null) json.gender=0;
            document.getElementById('numberGender').value=json.gender; 
            
            let rawShapes=json.shapes;
            let shapes;
            if (rawShapes!=null) {
                shapes=json.shapes.split('|'); 
            } else shapes=[];
            for (let i=0; i<14; i++) {
                let shape=shapes[i];                            
                let textbox=document.getElementById('number'+i);

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
            document.getElementById('popup_numberPattern_loading').innerHTML='ID: \"'+id+'\"<br>'+ JSON.stringify(json);     
            console.log('error sql', json);
        }
    });
};

// Save pattern
var currentnumberTOSave = function() {
    let label=document.getElementById('numberLabel').value;
    let base=document.getElementById('numberBase').value;
    let gender=document.getElementById('numberGender').value;
    let uppercase=document.getElementById('numberUppercase').value;
    let numberId=document.getElementById('numberId').value;
    let tags=document.getElementById('number_todatatags').value;
    let pattern=document.getElementById('numberPattern').value;
    let shapes=[];
    for (let i=0; i<14; i++) {
        let textbox=document.getElementById('number'+i);
        shapes[i]=textbox.value
    }

    let formData = new URLSearchParams();
    formData.append('action', 'number_pattern_to_update');
    formData.append('id', numberId);
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
            let popup=document.getElementById('popup_numberPattern');
            popup.style.display='none';
        }else {
            console.log('error currentRegionSave',json);            
        }
    });
};";
?>

<div id="popup_numberPattern" class="popupBackground" style="display: none; top: 0;">
    <div class="popup">
        <div class="popupHeader"><span class="pupupTitle">Úprava skloňování</span><span onclick="popupClose('numberPattern')" class="popupClose">×</span></div>
        <div id="popup_numberPattern_loading" class="loading">Načítání</div>
        <div class="popupBody" id="popupBody">

            <div class="row section">
                <label id="name" for="numberLabel">Popis</label><br>
                <input type="text" id="numberLabel" value="" placeholder="pohádKA">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base" for="numberBase">Základ</label><br>
                <input type="text" id="numberBase" value="" placeholder="pohád">
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
                            for ($j=0; $j<2; $j++) $html.="<td><input id='number".$g.($j==0 ? $i : 9+$i)."' type='text'></td>";
                            $html.="</tr>";
                        }
                        echo $html.'</table>';
                    }
                    ?>
                </div>
                <input type="hidden" id="numberShapes" value="-1">
            </div>

            <div class="row section">
                <label for="numberCategory">Kategorie</label>
                <select id="numberCategory" name="type">
                    <option value="0">Neznámý</option>
                    <option value="1">Tvrdé</option>
                    <option value="2">Měkké</option>
                    <option value="3">Přivlastňovací</option>
                </select>
                <br>
            </div>

            <?php echo tagsEditor("number_to", [], "Tagy") ?>
            <hr>
            <div style="float: right;">
                <input type="hidden" id="numberId" value="-1">
                <a onclick="currentnumberTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>