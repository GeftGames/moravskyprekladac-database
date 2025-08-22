<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/filter_list_relation.php";

        $filter=$_SESSION['translate'];
        $sql="SELECT id, label FROM replaces_defined_noun WHERE translate=$filter;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        }

        echo FilteredList($list, "replaces_defined_noun", [], $filter);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        replaces_defined_noun_changed=function() { 
            let elementsSelected = flist_replaces_defined_noun.getSelectedItemInList();
        
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
                body: 'action=replace_defined_noun_item&id='+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    let data=json.data;
                    console.log(json);
                    document.getElementById('replaceId').value=id;
                    document.getElementById('replaceFrom').value=data.source;
                    document.getElementById('replaceTo').value=data.to; 
                    document.getElementById('replaceFall').value=parseInt(data.fall); 
                    document.getElementById('replaceGender').value=parseInt(data.gender); 
                    document.getElementById('replaceNumber').value=parseInt(data.number); 

                 /*   // tags included
                    if (json.tags_inc!=null) {
                        let arrTags=json.tags_inc.split('|');
                        tagSet(arrTags, 'replace_noun_includes');
                    } else {
                        tagSet([], 'replace_noun_includes');
                    }
                    
                    // tags included
                    if (json.tags_not_inc!=null) {
                        let arrTags=json.tags_not_inc.split('|');
                        tagSet(arrTags, 'replace_noun_not_includes');
                    } else {
                        tagSet([], 'replace_noun_not_includes');
                    }*/
                   
                } else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_replaces_defined_noun.EventItemSelectedChanged(replaces_defined_noun_changed);
        flist_replaces_defined_noun.EventItemAddedChanged(replaces_defined_noun_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_replaces_defined_noun; 
        var current_replaces_defined_nounSave = function() {
            let replaceId=document.getElementById('replaceId').value;
            let source   =document.getElementById('replaceFrom').value;
            let to       =document.getElementById('replaceTo').value;
            let gender   =document.getElementById('replaceGender').value;
            let number   =document.getElementById('replaceNumber').value;
            let fall     =document.getElementById('replaceFall').value;
            let tagsI    =document.getElementById('replace_noun_includes').value;
            let tagsN    =document.getElementById('replace_noun_not_includes').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'replace_defined_noun_update');
            formData.append('id', replaceId);
            formData.append('source', source);
            formData.append('to', to);
            formData.append('gender', gender);
            formData.append('number', number);
            formData.append('fall', fall);
            /*formData.append('tags_inc', tagsI);
            formData.append('tags_n_i', tagsN);*/

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status === 'OK'){
                   flist_replaces_defined_noun.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave', json);
            });
        };
      
        var replaces_defined_noun_added = function() {
       /*  fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=list'+this.type+'_add&table='+this.TableName
            }).then(response => response.json())
            .then(json => {
                if (json.status==='ERROR'){
                    console.log(json); 
                    return; 
                }
    
                this.generateList(json.list);
                this.ListContainer.lastChild.classList.add('selectedSideItem');
               // this.ItemAdded_dispatch();
            });*/
        
            flist_replaces_defined_noun.lastAddedId;
            replaces_defined_noun_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <table>
                <tr>
                    <td><label for="replaceFrom">Z</label></td>
                    <td><input type="text" id="replaceFrom" value="" placeholder="kami" style="max-width: 9cm;"></td>
                </tr>

                <tr>
                    <td><label for="replaceTo">Na</label></td>
                    <td><input type="text" id="replaceTo" value="" placeholder="kama" style="max-width: 9cm;"></td>
                </tr>

                <tr>
                    <td><label for="replaceGender">Rod</label></td>
                    <td><select id="replaceGender" name="replaceGender">
                        <option value="0">Jakékoliv</option>
                        <option value="1">Mužský životný</option>
                        <option value="2">Mužský neživotný</option>
                        <option value="3">Ženský</option>
                        <option value="4">Střední</option>
                    </select></td>
                </tr

                <tr>
                    <td><label for="replaceNumber">Číslo</label></td>
                    <td><select id="replaceNumber" name="replaceNumber">
                        <option value="0">Jakékoliv</option>
                        <option value="1">jednotné</option>
                        <option value="2">množné</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><label for="replaceFall">Pád</label></td>
                    <td><input id="replaceFall" type="number" value="" placeholder="7" style="max-width: 9cm;"></td>
                </tr>

            <!--    <tr>
                    <?php echo tagsEditor("replace_noun_includes", [], "Obsahuje")?>
                </tr>

                <tr>
                    <?php echo tagsEditor("replace_noun_not_includes", [], "neobsahuje")?>
                </tr>-->
            </table>

            <div class="section">
                <input type="hidden" id="replaceId" value="-1">
                <a onclick="current_replaces_defined_nounSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>