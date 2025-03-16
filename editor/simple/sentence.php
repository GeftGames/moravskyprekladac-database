<div class="splitView">
    <div>
        <?php
             
        // Do dashboard stuff
        include "components/tags_editor.php";

        $order="ORDER BY LOWER(shape_from) ASC";
        $sql="SELECT id, shape_from FROM sentences_relations $order;";

        $result = $conn->query($sql);
        if (!$result) echo "ERROR: ".$conn->error;

        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["shape_from"]];
            }
        } else {
            echo "0 results";
        }

        echo FilteredList($list, "sentences_relation");           
        
        $GLOBALS["onload"].="sentences_changed=function() { 
            let elementsSelected = flist_sentences.getSelectedItemInList();
        
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
                body: `action=sentences_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('sentencesId').value=id;
                    document.getElementById('sentencesLabel').value=json.label;

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

        flist_sentences.EventItemSelectedChanged(sentences_changed);
        flist_sentences.EventItemAddedChanged(sentences_added);";
    
        $GLOBALS["script"].="var flist_sentences; 
        var currentsentencesCSSave = function() {
            let label=document.getElementById('sentencesLabel').value;
            let sentencesId=document.getElementById('sentencesId').value;
            let tags=document.getElementById('sentencesdatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'sentences_update');
            formData.append('id', sentencesId);
            formData.append('label', label);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_sentences.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

        var sentences_added = function() {
            flist_sentences.lastAddedId;
            sentences_changed();
        };
        ";
            
        ?>
    </div>
    <div class="editorView">
        <div>
            <div class="row">
                <label id="name">Název</label>
                <input type="text" for="name">
            </div>

            <div class="row">
                <label id="name">Z</label>
                <input type="text" for="name">
            </div>

            <div class="row">
                <label id="name">Na</label>
                <input type="text" for="name">
            </div>
            <hr>
            <div>
                <label id="name">Info</label>
                <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                <p>"?" Neznámý tvar</p>
                <p>"-" Neexistuje tvar</p>
            </div>
        </div>
    </div>
</div>