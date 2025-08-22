<div class="splitView">
    <div>
        <?php
        $sql="SELECT id, label FROM nations;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        }

        echo FilteredList($list, "nations", [], null);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        nation_changed=function() { 
            let elementsSelected = flist_nations.getSelectedItemInList();
        
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
                body: `action=nation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status === 'OK'){
                    document.getElementById('nationId').value=id;
                    document.getElementById('nationName').value=json.label;
                    document.getElementById('nationParent').value=json.parent;
                    document.getElementById('nationType').value=json.type;
                    document.getElementById('nationTranslates').value=json.translates;
                }else console.log('error sql: ', json);
            });
        };

        refreshFilteredLists();

        flist_nations.EventItemSelectedChanged(nation_changed);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_nations; 
        var currentnationSave = function() {
            let label=document.getElementById('nationName').value;
            let nationParent=document.getElementById('nationParent').value;
            let nationType=document.getElementById('nationType').value;
            let nationId=document.getElementById('nationId').value;
            let translates=document.getElementById('nationTranslates').innerText;

            let formData = new URLSearchParams();
            formData.append('action', 'nation_update');
            formData.append('id', nationId);
            formData.append('label', label);
            formData.append('type', nationType);
            formData.append('parent', nationParent);
            formData.append('translates', translates);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_nations.getSelectedItemInList().innerText=label;
                }else console.log('error currentnationSave: ',json);
            });
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="nationsview">
            <table>
                <tr>
                    <td><label for="nationName">Název</label></td>
                    <td><input type="text" id="nationName" value=""></td>
                </tr>

                <tr>
                    <td><label for="nationType">Typ označení obyvatel</label></td>
                    <td><select id="nationType" name="type">
                        <option value="0">Neznámý</option>
                        <option value="2">Národní</option>
                        <option value="3">Zemská</option>
                        <option value="4">Kulturní/jazyková</option>
                        <option value="ř">Posměšné označení</option>
                    </select></td>
                </tr>

                <tr>
                    <td><label for="nationParent">Nadřazený nation</label></td>
                    <td><select id="nationParent" name="type">
                        <option value="null">Neznámé</option>
                        <option value="-1">Žádný</option>
                        <?php
                        $htmloptions="";
                        foreach ($list as $item) {
                            $id=$item[0];
                            $label=$item[1];
                            $htmloptions.="<option value='$id'>$label</option>";
                        }
                        echo $htmloptions;
                        ?>
                    </select></td>
                </tr>
            </table>

            <div>
                <label for="nationTranslates">Překlady</label>
                <textarea id="nationTranslates" placeholder='{"cs": "Hanáci"}'></textarea>
            </div>

            <div> 
                <input type="hidden" id="nationId" value="-1">
                <a onclick="currentnationSave()" class="button">Save</a>
            </div>
        </div>
    </div>
</div>