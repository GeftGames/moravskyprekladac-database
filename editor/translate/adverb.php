<div class="splitView">
    <div>
        <?php
        // Do dashboard stuff
        include "components/tags_editor.php";

        $list=[];
        {
            $filter=$_SESSION['translate'];
            $sql="SELECT `id`, `label` FROM `adverb_relations` WHERE `translate`=$filter;";
            $result = $conn->query($sql);
            if (!$result) throwError("SQL error: ".$sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $list[]=[$row["id"], $row["label"]];
                }
            } else {
                // TODO: echo "0 results ";
            }
        }

        echo FilteredList($list, "adverb_to", [], $filter);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        adverb_to_changed=function() { 
            let id = flist_adverb_to.getSelectedIdInList();
        
            // no selected
            if (id==null) return;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=adverb_to_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
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

        flist_adverb_to.EventItemSelectedChanged(adverb_to_changed);
        flist_adverb_to.EventItemAddedChanged(adverb_to_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_adverb_to; 
        var currentadverbTOSave = function() {
            let shape=document.getElementById('adverbShape').value;
            let adverbId=document.getElementById('adverbId').value;
            let tags=document.getElementById('adverb_todatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'adverb_to_update');
            formData.append('id', adverbId);
            formData.append('shape', shape);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_adverb_to.getSelectedItemInList().innerText=shape;
                }else console.log('error currentRegionSave',json);
            });
        };

        var adverb_to_added = function() {
            flist_adverb_to.lastAddedId;
            adverb_to_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label for="adverbShape" id="name">Tvar</label><br> 
                <input type="text" id="adverbShape" value="" placeholder="z" style="max-width: 9cm;">
            </div>

            <?php echo tagsEditor("adverb_to", [], "tagy")?>
            <div> 
                <input type="hidden" id="adverbId" value="-1">
                <a onclick="currentadverbTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>