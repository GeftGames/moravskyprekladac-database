<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
   //     include "components/filter_list.php";
        include "components/tags_editor.php";
        
        $order="ORDER BY LOWER(shape) ASC";
        $sql="SELECT id, shape FROM preposition_cs $order;";
        $result = $conn->query($sql);
        $list=[];
        if (!$result) throwError("SQL error: ".$sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["shape"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "preposition_cs");  

        $GLOBALS["onload"].="preposition_cs_changed=function() { 
            let elementsSelected = flist_preposition_cs.getSelectedItemInList();
        
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
                body: `action=preposition_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('prepositionId').value=id;
                    document.getElementById('prepositionShape').value=json.shape;
                    document.getElementById('prepositionFalls').value=json.falls;

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

        flist_preposition_cs.EventItemSelectedChanged(preposition_cs_changed);
        flist_preposition_cs.EventItemAddedChanged(preposition_cs_added);";
    
        $GLOBALS["script"].="var flist_preposition_cs; 
        var currentprepositionCSSave = function() {
            let shape=document.getElementById('prepositionShape').value;
            let falls=document.getElementById('prepositionFalls').value;
            let prepositionId=document.getElementById('prepositionId').value;
            let tags=document.getElementById('preposition_csdatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'preposition_cs_update');
            formData.append('id', prepositionId);
            formData.append('shape', shape);
            formData.append('falls', falls);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_preposition_cs.getSelectedItemInList().innerText=shape;
                }else console.log('error currentRegionSave',json);
            });
        };

        var preposition_cs_added = function() {
            flist_preposition_cs.lastAddedId;
            preposition_cs_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label for="prepositionShape" id="name">Tvar</label><br> 
                <input type="text" id="prepositionShape" value="" placeholder="z" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label for="prepositionFalls" id="fall">Pád(y)</label><br>
                <input type="text" id="prepositionFalls" value="" placeholder="2,4" style="max-width: 9cm;">
            </div>

            <?php echo tagsEditor("preposition_cs", [], "tagy")?>
            <div> 
                <input type="hidden" id="prepositionId" value="-1">
                <a onclick="currentprepositionCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>