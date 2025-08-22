<div class="splitView">
    <div>
        <?php
        $sql="SELECT id, label FROM langs;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        }

        echo FilteredList($list, "langs", [],null);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        lang_changed=function() { 
            let elementsSelected = flist_langs.getSelectedItemInList();
        
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
                body: `action=lang_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status === 'OK'){
                    document.getElementById('langId').value=id;
                    document.getElementById('langName').value=json.label;
                    document.getElementById('langParent').value=json.parent;
                    document.getElementById('langType').value=json.type;
                    document.getElementById('langTranslates').value=json.translates;
                }else console.log('error sql: ', json);
            });
        };

        refreshFilteredLists();

        flist_langs.EventItemSelectedChanged(lang_changed);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_langs; 
        var currentlangSave = function() {
            let label=document.getElementById('langName').value;
            let langParent=document.getElementById('langParent').value;
            let langType=document.getElementById('langType').value;
            let langId=document.getElementById('langId').value;
            let translates=document.getElementById('langTranslates').innerText;

            let formData = new URLSearchParams();
            formData.append('action', 'lang_update');
            formData.append('id', langId);
            formData.append('label', label);
            formData.append('type', langType);
            formData.append('parent', langParent);
            formData.append('translates', translates);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_langs.getSelectedItemInList().innerText=label;
                }else console.log('error currentlangSave: ',json);
            });
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="langsview">
            <table>
                <tr>
                    <td><label for="langName">Název</label></td>
                    <td><input type="text" id="langName" value=""></td>
                </tr>

                <tr>
                    <td><label for="langType">Typ označení</label></td>
                    <td><select id="langType" name="type">
                        <option value="0">Neznámý</option>
                        <option value="1">Jazyk</option>
                        <option value="2">Obecná mluva</option>
                        <option value="3">Různořečí</option>
                        <option value="4">Podřečí</option>
                        <option value="5">Nářečí</option>
                    </select></td>
                </tr>

                <tr>
                    <td><label for="langParent">Nadřazený lang</label></td>
                    <td><select id="langParent" name="type">
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
                <label for="langTranslates">Překlady</label>
                <textarea id="langTranslates" placeholder='{"cs": "Hanáci"}'></textarea>
            </div>

            <div> 
                <input type="hidden" id="langId" value="-1">
                <a onclick="currentlangSave()" class="button">Save</a>
            </div>
        </div>
    </div>
</div>