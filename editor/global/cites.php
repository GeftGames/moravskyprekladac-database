<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
        include "components/filter_list.php";
        include("components/param_editor.php");

        $sql="SELECT id, name FROM source;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["name"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "source");  

        $GLOBALS["onload"].="cites_changed=function() { 
            let elementsSelected = flist_cites.getSelectedItemInList();
        
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
                body: `action=region_item&idregion=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                    document.getElementById('regionId').value=id;
                    document.getElementById('regionName').value=json.name;
                    document.getElementById('regionParent').value=json.parent;
                    document.getElementById('regionType').value=json.type;
                    document.getElementById('regiontranslates').value=json.translates;
                }else console.log('error sql: '+json);
            });
        };

        refreshFilteredLists();

        flist_cites.SelectedItemChanged(region_changed);";
    
        $GLOBALS["script"].="var flist_cites; 
        var currentRegionSave = function() {
            let label=document.getElementById('regionName').value;
            let regionParent=document.getElementById('regionParent').value;
            let regionType=document.getElementById('regionType').value;
            let regionId=document.getElementById('regionId').value;
            let translates=document.getElementById('regiontranslates').innerText;

            let formData = new URLSearchParams();
            formData.append('action', 'region_update');
            formData.append('id', regionId);
            formData.append('name', label);
            formData.append('type', regionType);
            formData.append('parent', regionParent);
            formData.append('parent', translates);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_cites.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave: '+json);
            });
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">

            <?php 
            $allParams=editorSourceSample();

            $htmlParams=paramEditor([], $allParams);
            echo $htmlParams;
            ?>>

        </div>
    </div>
</div>