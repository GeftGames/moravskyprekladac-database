<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
     //   include "components/filter_list.php";
        include "components/tags_editor.php";
        
        $order="ORDER BY LOWER(shape) ASC";
        $sql="SELECT id, shape FROM adverb_cs $order;";
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

        echo FilteredList($list, "adverb_cs");  

        $GLOBALS["onload"].="adverb_cs_changed=function() { 
            let elementsSelected = flist_adverb_cs.getSelectedItemInList();
        
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
                body: `action=adverb_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('adverbId').value=id;
                    document.getElementById('adverbShape').value=json.shape;

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

        flist_adverb_cs.EventItemSelectedChanged(adverb_cs_changed);
        flist_adverb_cs.EventItemAddedChanged(adverb_cs_added);";
    
        $GLOBALS["script"].="var flist_adverb_cs; 
        var currentadverbCSSave = function() {
            let shape=document.getElementById('adverbShape').value;
            let adverbId=document.getElementById('adverbId').value;
            let tags=document.getElementById('adverb_csdatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'adverb_cs_update');
            formData.append('id', adverbId);
            formData.append('shape', shape);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_adverb_cs.getSelectedItemInList().innerText=shape;
                }else console.log('error currentRegionSave',json);
            });
        };

        var adverb_cs_added = function() {
            flist_adverb_cs.lastAddedId;
            adverb_cs_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label for="adverbShape" id="name">Tvar</label><br> 
                <input type="text" id="adverbShape" value="" placeholder="dneska" style="max-width: 9cm;">
            </div>

            <?php echo tagsEditor("adverb_cs", [], "Tagy")?>
            <div> 
                <input type="hidden" id="adverbId" value="-1">
                <a onclick="currentadverbCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>