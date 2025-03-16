<div class="splitView">
    <div>
        <?php
             
        // Do dashboard stuff
        include "components/tags_editor.php";

        $order="ORDER BY LOWER(shape_from) ASC";
        $sql="SELECT id, shape_from FROM simpleword_relations $order;";

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

        echo FilteredList($list, "simpleword_relation");           
        
        $GLOBALS["onload"].="simpleword_changed=function() { 
            let elementsSelected = flist_simpleword.getSelectedItemInList();
        
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
                body: `action=simpleword_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('simplewordId').value=id;
                    document.getElementById('simplewordLabel').value=json.label;

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

        flist_simpleword.EventItemSelectedChanged(simpleword_changed);
        flist_simpleword.EventItemAddedChanged(simpleword_added);";
    
        $GLOBALS["script"].="var flist_simpleword; 
        var currentsimplewordCSSave = function() {
            let label=document.getElementById('simplewordLabel').value;
            let simplewordId=document.getElementById('simplewordId').value;
            let tags=document.getElementById('simpleworddatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'simpleword_update');
            formData.append('id', simplewordId);
            formData.append('label', label);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_simpleword.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

        var simpleword_added = function() {
            flist_simpleword.lastAddedId;
            simpleword_changed();
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