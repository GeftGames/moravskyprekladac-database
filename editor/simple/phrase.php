<div class="splitView">
    <div>
        <?php
        
        include "components/tags_editor.php";
        include "components/multiple_simple_to.php";
        
        $order="ORDER BY LOWER(shape_from) ASC";
        $sql="SELECT id, shape_from FROM phrase_relations $order;";
        $result = $conn->query($sql);
        if (!$result) echo "ERROR: ".$conn->error;

        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["shape_from"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "phrase");  

        $GLOBALS["onload"].= /** @lang JavaScript */"
            phrase_changed=function() { 
            let elementsSelected = flist_phrase.getSelectedItemInList();
        
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
                body: `action=phrase_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('phraseId').value=id;
                    document.getElementById('phraseFrom').value=json.shape_from;
                    to_load(json.to);
                    
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

        flist_phrase.EventItemSelectedChanged(phrase_changed);
        flist_phrase.EventItemAddedChanged(phrase_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_phrase; 
        var currentphraseCSSave = function() {
            let shape_from=document.getElementById('phraseFrom').value;
            let phraseId=document.getElementById('phraseId').value;
            let tags=document.getElementById('phrasedatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'phrase_update');
            formData.append('id', phraseId);
            formData.append('shape_from', shape_from);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_phrase.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

        var phrase_added = function() {
            flist_phrase.lastAddedId;
            phrase_changed();
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label for="phraseFrom" id="name">Z</label><br> 
                <input type="text" id="phraseFrom" value="" placeholder="ušel jsi" style="max-width: 9cm;">
            </div>
            <?php echo tagsEditor("phrase", [], "Tagy");?>
            
            <!-- Translate to -->
            <?php echo multiple_simple_to([[]]); ?>

            <div> 
                <input type="hidden" id="phraseId" value="-1">
                <a onclick="currentphraseCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>