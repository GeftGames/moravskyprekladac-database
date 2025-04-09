<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
        include "components/tags_editor.php";

        $filter=$_SESSION['translate'];
        $sql="SELECT id, shape FROM prepositions_to WHERE translate=$filter;";
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

        echo FilteredList($list, "preposition_to");  

        $GLOBALS["onload"].= /** @lang JavaScript */"
        preposition_to_changed=function() { 
            let id = flist_preposition_to.getSelectedIdInList();
        
            // no selected
            if (id==null) return;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=preposition_to_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
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

        flist_preposition_to.EventItemSelectedChanged(preposition_to_changed);
        flist_preposition_to.EventItemAddedChanged(preposition_to_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_preposition_to; 
        var currentprepositionTOSave = function() {
            let shape=document.getElementById('prepositionShape').value;
            let falls=document.getElementById('prepositionFalls').value;
            let prepositionId=document.getElementById('prepositionId').value;
            let tags=document.getElementById('preposition_todatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'preposition_to_update');
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
                if (json.status==='OK'){
                   flist_preposition_to.getSelectedItemInList().innerText=shape;
                }else console.log('error currentRegionSave',json);
            });
        };

        var preposition_to_added = function() {
            flist_preposition_to.lastAddedId;
            preposition_to_changed();
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

            <?php echo tagsEditor("preposition_to", [], "tagy")?>
            <div> 
                <input type="hidden" id="prepositionId" value="-1">
                <a onclick="currentprepositionTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>