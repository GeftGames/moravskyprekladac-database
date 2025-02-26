<div class="splitView">
    <div>
        <?php
        
        include "components/filter_list.php";
        include("components/param_editor.php");

        $sql="SELECT id, name FROM piecesofcite;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["name"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "piecesofcite");  

        $GLOBALS["onload"].="region_changed=function() { 
            let elementsSelected = flist_regions.getSelectedItemInList();
        
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

        flist_regions.SelectedItemChanged(region_changed);";
    
        $GLOBALS["script"].="var flist_regions; 
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
                   flist_regions.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave: '+json);
            });
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row">
                <label id="name">Název</label><br>
                <input type="text" id="regionName" for="name" value="">
            </div>

            <div class="row">
                <labeĺ>Typ</labeĺ>
                <select id="regionType" name="type">
                    <option value="0">Neznámý</option>
                    <option value="1">Žemě</option>
                    <option value="2">Region</option>
                    <option value="3">Subregion</option>
                    <option value="4">Oblast</option>
                    <option value="5">Lokalita</option>
                </select>
            </div>
                        
            <div class="row">
                <label>Nadřazený region</label>
                <select id="regionParent" name="type">
                    <option value="null">Neznámé</option>
                    <option value="-1">Žádný</option>
                    <?php
                    $htmloptions="";
                    foreach ($list as $item) {
                        $id=$item[0];
                        $label=$item[1];
                        $htmloptions.="<option value='$id'>$label</option>";
                    }
                    echo $htmloptions;
                    ?>
                </select>
                <br>
            </div>
            <div style="display: flex;flex-direction: column;">
                <label>Překlady</label>
                    <textarea id="regiontranslates" placeholder='{"cs": "Haná"}'></textarea>
                <br>
            </div>
            <div> 
                <input type="hidden" id="regionId" value="-1">
                <a onclick="currentPieceOfCiteSave()" class="button">Save</a>
            </div>
        </div>
    </div>
</div>