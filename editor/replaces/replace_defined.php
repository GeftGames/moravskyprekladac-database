<div class="splitView">
    <div>
        <?php        
        // Do dashboard stuff
        include "components/tags_editor.php";
        
        $order="ORDER BY LOWER(label) ASC";
        $filter=$_SESSION['translate'];
        $sql="SELECT id, label FROM replaces_defined WHERE $filter $order;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        }

        echo FilteredList($list, "replaces_defined", [], $filter);

        $GLOBALS["onload"].= /** @lang JavaScript */"            
        replaces_defined_changed=function() { 
            let elementsSelected = flist_replaces_defined.getSelectedItemInList();
        
            // no selected
            if (!elementsSelected) {
                return;
            }
            //no multiple
            if (Array.isArray(elementsSelected)) return;
        
            let id=elementsSelected.dataset.id;
        
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=replaces_defined_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('replaceId').value=id;
                    document.getElementById('replaceSource').value=json.base;
        
                    if (json.category==null) json.category=0;
                    document.getElementById('replaceCategory').value=json.category; 
        
                    document.getElementById('pronounShapesType').value=shapesType;
        
                    // tags
                    if (json.tags!=null) {
                        let arrTags=json.tags.split('|');
                        tagSet(arrTags);
                    } else {
                        tagSet([]);
                    }
                   
                } else console.log('error sql', json);
            });
        };
        
        refreshFilteredLists();
        
        flist_replaces_defined.EventItemSelectedChanged(replaces_defined_changed);
        flist_replaces_defined.EventItemAddedChanged(replaces_defined_added);
        ";
    
    $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_replaces_defined; 
        var currentreplaces_definedSave = function() {
            let replaceId=document.getElementById('pronounId').value;
            let source=document.getElementById('replaceSource').value;
            let to=document.getElementById('replaceTo').value;
            let category=document.getElementById('pronounCategory').value;
            let tags=document.getElementById('pronoun_csdatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'replaces_defined_update');
            formData.append('id', replaceId);
            formData.append('source', source);
            formData.append('to', to);
            formData.append('category', category);
            formData.append('tags', tags);
        
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_replaces_defined.getSelectedItemInList().innerText=label;
                }else console.log('error currentreplaces_definedSave',json);
            });
        };
        
        var replaces_defined_added = function() {
            flist_replaces_defined.lastAddedId;
            replaces_defined_changed();
        }
                
        var createLabel = function() {
            let from=document.getElementById('replaceSource');
            let to=document.getElementById('replaceTo');
            let partOfSpeech=document.getElementById('replacePartOfSpeech').innerText;
            document.getElementById('replaceLabel').value=from+'>'+to+' ('+partOfSpeech.substring(0,4)+'.)';
        }
        ";
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <table>
               <!-- <tr class="section">
                    <td><label id="name" for="replaceLabel">Popis</label></td>
                    <td class="row">
                        <input type="text" id="replaceLabel" name="label" value="" placeholder="ka>kaj (přís.)" style="max-width: 9cm;">
                        <a onclick="createLabel()" class="button">Sestavit</a>
                    </td>
                </tr>-->

                <tr class="section">
                    <td><label id="base" for="replaceSource">Z</label><br></td>
                    <td><input type="text" id="replaceSource" value="" placeholder="ka" style="max-width: 9cm;"></td>
                </tr>

                <tr class="section">
                    <td><label id="base" for="replaceTo">Na</label><br></td>
                    <td><input type="text" id="replaceTo" value="" placeholder="kaj" style="max-width: 9cm;"></td>
                </tr>

                 <tr class="section">
                     <td><label for="replacePartOfSpeech">Druh</label></td>
                     <td><select id="replacePartOfSpeech">
                        <option value="0">Jakékoliv</option>
                        <option value="1">Podstatné jméno</option>
                        <option value="2">Přídavné jméno</option>
                        <option value="3">Zájmeno</option>
                        <option value="4">Číslovka</option>
                        <option value="5">Sloveso</option>
                        <option value="6">Příslovce</option>
                        <option value="7">Předložka</option>
                        <option value="8">Spojka</option>
                        <option value="9">Částice</option>
                        <option value="10">Citoslovce</option>
                    </select></td>
                </tr>

                <tr class="section">
                    <td><label for="replacePos">Pozice</label></td>
                    <td><select id="replacePos" name="pos">
                        <option value="0">Nesklonné</option>
                        <option value="1">na začátku</option>
                        <option value="2">kdekoliv</option>
                        <option value="3">na konci</option>
                    </select></td>
                </tr>

                <tr class="row section">
                    <?php echo tagsEditor("replace_noun_includes", [], "Obsahuje")?>
                </tr>

                <tr class="row section">
                    <?php echo tagsEditor("replace_noun_not_includes", [], "Neobsahuje")?>
                </tr>
            </table>
            <div> 
                <input type="hidden" id="replaceId" value="-1">
                <a onclick="current_replaces_definedSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>