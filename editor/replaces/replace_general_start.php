<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
     //   include "components/filter_list.php";
        include "components/tags_editor.php";
        include "components/select_cites.php";
        
        $order="ORDER BY LOWER(label) ASC";
        $sql="SELECT id, label FROM replace_start $order;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "replace_start");  

        $GLOBALS["onload"].="
        replace_start_changed=function() { 
            let elementsSelected = flist_replace_start.getSelectedItemInList();
        
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
                body: `action=replace_start_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('replace_startId').value=id;
                    document.getElementById('replace_startLabel').value=json.label;
                    document.getElementById('replace_source').value=json.source;
                    document.getElementById('replace_to').value=json.to;
                } else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_replace_start.EventItemSelectedChanged(replace_start_changed);
        flist_replace_start.EventItemAddedChanged(replace_start_added);";
    
        $GLOBALS["script"].="
        var flist_replace_start; 
        var currentreplace_startCSSave = function() {
            let label=document.getElementById('replace_startLabel').value;
            let base=document.getElementById('replace_startBase').value;
            let category=document.getElementById('replace_startCategory').value;
            let replace_startId=document.getElementById('replace_startId').value;
            let tags=document.getElementById('replace_startdatatags').value;
            let cites=document.getElementById('replace_startCites').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'replace_start_update');
            formData.append('id', replace_startId);
            formData.append('label', label);
            formData.append('source', source);
            formData.append('to', to);
            formData.append('cites', cites);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_replace_start.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

        var replace_start_added = function() {
            flist_replace_start.lastAddedId;
            replace_start_changed();
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label id="name">Popis</label><br> 
                <input type="text" id="replace_startLabel" for="name" value="" placeholder="niKDO" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="source">Z</label><br>
                <input type="text" id="replace_startSource" for="source" value="" placeholder="ni" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label id="to">Na</label><br>
                <input type="text" id="replace_startTo" for="to" value="" placeholder="ni" style="max-width: 9cm;">
            </div>

            <?php echo selectCites([]) ?>
       
            <div> 
                <input type="hidden" id="replace_startId" value="-1">
                <a onclick="currentreplace_startCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>