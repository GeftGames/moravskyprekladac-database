<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_pattern_to.php";
        tagsEditorDynamic();

        // relations list
        include "components/give_relations_pattern.php";
        $listR=give_relations_pattern($conn,"noun", true);

        // side menu
        echo FilteredList($listR, "noun_relations", [], $_SESSION['translate']);

        // from list for <select>
        $sqlFrom="SELECT `id`, `label` FROM `noun_patterns_cs`;";

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
        noun_relations_changed=function() { 
            let id = flist_noun_relations.getSelectedIdInList();
        
            // no selected
            if (id==null) return;
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=noun_relation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('nounId').value=id;
                    document.getElementById('usecustombase').value=json.custombase!=null;
                    document.getElementById('custombase').value=json.custombase;
                    //from
                    filteredSearchList_noun_from.selectId(json.from);                   
                    filteredSearchList_noun_from.reload();
                    //to
                   // console.log(json.to);
                    to_load(json.to);
                }else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_noun_relations.EventItemSelectedChanged(noun_relations_changed);
        ";

        $GLOBALS["script"].= /** @lang JavaScript */"
            var flist_noun_relations; 
            var currentNounRelationSave = function() {
                let froms=document.getElementById('listreturnholder_noun_from').value;              
                let id=document.getElementById('nounId').value;              
    
                let formData = new URLSearchParams();
                formData.append('action', 'noun_relation_update');
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
                        flist_noun_relations.getSelectedItemsInList()[0].innerText=document.getElementById('selectedLabel_noun_from').innerText;
                    }else console.log('error currentNounRelationSave', json);
                });
            };";
        ?>
    </div>
    <div class="editorView">
        <div id="noun">
            <div class="section row">
                <label for="noun_from" id="name">Z</label>&nbsp;
                <div id="select_noun_from"></div>
                <?php createSelectList($listFrom, "noun_from", $idFrom);?>
            </div>

            <div class="section row">
                <input type="checkbox" id="usecustombase">
                <label for="usecustombase" style="user-select: none;inline-size: -webkit-fill-available;">Jiný základ</label>&nbsp;
                <input type="text" id="custombase" placeholder="pampeliš">
            </div>

            <div class="section">
                <label for="noun_from" id="name">Na</label>
                <?php echo multiple_pattern_to([], "noun"); ?>
            </div>

            <div class="section">
                <input type="hidden" id="nounId" value="-1">
                <a onclick="currentNounRelationSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>

<?php
$GLOBALS["script"].= /** @lang JavaScript */"

// Load pattern
var showPatternEditor = function(id) { 
    // show popup
    let popup=document.getElementById('popup_nounPattern');
    popup.style.display='flex';
    
    // show loading message
    document.getElementById('popup_nounPattern_loading').style.display='flex';
    document.getElementById('popup_nounPattern_loading').innerText='Načítám...';
    document.getElementById('popupBody').style.display='none';
    
    fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=noun_pattern_to_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            // hide loading message
            document.getElementById('popup_nounPattern_loading').style.display='none';
            document.getElementById('popupBody').style.display='block';
            
            // set values
            document.getElementById('nounId').value=id;
            document.getElementById('nounLabel').value=json.label;
            document.getElementById('nounBase').value=json.base;
            document.getElementById('nounUppercase').value=json.uppercase;
            
            if (json.gender==null) json.gender=0;
            document.getElementById('nounGender').value=json.gender; 
            
            let rawShapes=json.shapes;
            let shapes;
            if (rawShapes!=null) {
                shapes=json.shapes.split('|'); 
            } else shapes=[];
            for (let i=0; i<14; i++) {
                let shape=shapes[i];                            
                let textbox=document.getElementById('noun'+i);

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
            document.getElementById('popup_nounPattern_loading').innerHTML='ID: \"'+id+'\"<br>'+ JSON.stringify(json);     
            console.log('error sql', json);
        }
    });
};

// Save pattern
var currentNounTOSave = function() {
    let label=document.getElementById('nounLabel').value;
    let base=document.getElementById('nounBase').value;
    let gender=document.getElementById('nounGender').value;
    let uppercase=document.getElementById('nounUppercase').value;
    let nounId=document.getElementById('nounId').value;
    let tags=document.getElementById('noun_todatatags').value;
    let pattern=document.getElementById('nounPattern').value;
    let shapes=[];
    for (let i=0; i<14; i++) {
        let textbox=document.getElementById('noun'+i);
        shapes[i]=textbox.value
    }

    let formData = new URLSearchParams();
    formData.append('action', 'noun_pattern_to_update');
    formData.append('id', nounId);
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
            let popup=document.getElementById('popup_nounPattern');
            popup.style.display='none';
        }else {
            console.log('error currentRegionSave',json);            
        }
    });
};";
?>

<div id="popup_nounPattern" class="popupBackground" style="display: none; top: 0;">
    <div class="popup">
        <div class="popupHeader"><span class="pupupTitle">Úprava skloňování</span><span onclick="popupClose('nounPattern')" class="popupClose">×</span></div>
        <div id="popup_nounPattern_loading" class="loading">Načítání</div>
        <div class="popupBody" id="popupBody">

            <div class="row section">
                <label id="name" for="nounLabel">Popis</label><br>
                <input type="text" id="nounLabel" value="" placeholder="pohádKA">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base" for="nounBase">Základ</label><br>
                <input type="text" id="nounBase" value="" placeholder="pohád">
            </div>

            <div class="section">
                <label id="name" for="nounShapes">Pád</label>
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
                        for ($j=0; $j<2; $j++) $html.="<td><input id='noun".($j==0 ? $i : 7+$i)."' type='text'></td>";
                        $html.="</tr>";
                    }
                    echo $html;
                    ?>
                </table>
                <input type="hidden" id="nounShapes" value="-1">
            </div>

            <div class="row section">
                <label for="nounGender">Rod</label>
                <select id="nounGender" name="type">
                    <option value="0">Neznámý</option>
                    <option value="4">Střední</option>
                    <option value="3">Ženský</option>
                    <option value="2">Mužský neživotný</option>
                    <option value="1">Mužský životný</option>
                </select>
                <br>
            </div>




            <div class="row section">
                <label for="nounPattern">Vzor</label>
                <select id="nounPattern" name="type">
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
                <label for="nounUppercase">Velké písmena</label>
                <select id="nounUppercase" name="uppercase">
                    <option value="0">Neznámý</option>
                    <option value="1">malé</option>
                    <option value="2">Počáteční Velké</option>
                    <option value="3">VŠECHNY VELKÉ</option>
                </select>
                <br>
            </div>

            <?php echo tagsEditor("noun_to", [], "Tagy") ?>
            <hr>
            <div style="float: right;">
                <input type="hidden" id="nounId" value="-1">
                <a onclick="currentNounTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>