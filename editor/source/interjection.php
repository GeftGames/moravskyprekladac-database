<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
      //  include "components/filter_list.php";
        include "components/tags_editor.php";
        
        $order="ORDER BY LOWER(shape) ASC";
        $sql="SELECT id, shape FROM interjection_cs $order;";
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

        echo FilteredList($list, "interjection_cs");  

        $GLOBALS["onload"].="interjection_cs_changed=function() { 
            let elementsSelected = flist_interjection_cs.getSelectedItemInList();
        
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
                body: `action=interjection_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('interjectionId').value=id;
                    document.getElementById('interjectionShape').value=json.shape;

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

        flist_interjection_cs.EventItemSelectedChanged(interjection_cs_changed);
        flist_interjection_cs.EventItemAddedChanged(interjection_cs_added);";
    
        $GLOBALS["script"].="var flist_interjection_cs; 
        var currentinterjectionCSSave = function() {
            let shape=document.getElementById('interjectionShape').value;
            let falls=document.getElementById('interjectionFalls').value;
            let interjectionId=document.getElementById('interjectionId').value;
            let tags=document.getElementById('interjection_csdatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'interjection_cs_update');
            formData.append('id', interjectionId);
            formData.append('shape', shape);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_interjection_cs.getSelectedItemInList().innerText=shape;
                }else console.log('error currentRegionSave',json);
            });
        };

        var interjection_cs_added = function() {
            flist_interjection_cs.lastAddedId;
            interjection_cs_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label for="interjectionShape" id="name">Tvar</label><br> 
                <input type="text" id="interjectionShape" value="" placeholder="z" style="max-width: 9cm;">
            </div>

            <?php echo tagsEditor("interjection_cs", [], "Tagy")?>
            <div> 
                <input type="hidden" id="interjectionId" value="-1">
                <a onclick="currentinterjectionCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>