 <div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_pattern_to.php";
        tagsEditorDynamic();

        // relations list
        include "components/give_relations.php";
        $listR=give_relations($conn,"noun");

        // side menu
        echo FilteredList($listR, "noun_relations");

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

        $idFrom=0
        ;

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
                    //from
                    filteredSearchList_noun_from.selectId(json.from);                   
                    filteredSearchList_noun_from.reload();
                    //to
                    to_load(JSON.parse(json.to));
                }else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_noun_relations.EventItemSelectedChanged(noun_relations_changed);";

        $GLOBALS["script"].= /** @lang JavaScript */
            "
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