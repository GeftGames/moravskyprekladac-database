<div class="splitView">
    <div>
        <?php
        $sql="SELECT `id`, `label` FROM `regions`;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "regions", []);

        $GLOBALS["onload"].= /** @lang JavaScript */"
            region_changed=function() { 
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
                if (json.status === 'OK') {
                    document.getElementById('regionId').value=id;
                    document.getElementById('regionName').value=json.name;
                    document.getElementById('regionParent').value=json.parent;
                    document.getElementById('regionType').value=json.type;
                    document.getElementById('regionTranslates').value=json.translates;
                }else console.log('error sql: ',json);
            });
        };

        refreshFilteredLists();

        flist_regions.EventItemSelectedChanged(region_changed);
        ";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_regions; 
        var currentRegionSave = function() {
            let regionId=document.getElementById('regionId').value;
            let regionLabel=document.getElementById('regionLabel').value;
            let regionType=document.getElementById('regionType').value;
            let regionParent=document.getElementById('regionParent').value;
            let translates=document.getElementById('regionTranslates').innerText;

            let formData = new URLSearchParams();
            formData.append('action', 'region_update');
            formData.append('id', regionId);
            formData.append('label', regionLabel);
            formData.append('type', regionType);
            formData.append('parent', regionParent);
            formData.append('translates', translates);
            console.log(formData.toString());
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status === 'OK') {
                   flist_regions.getSelectedItemInList().innerText=regionLabel;
                } else console.log('error currentRegionSave: ', json);
            });
        };";
        ?>
    </div>
    <div class="editorView">
        <table id="regionsview">
            <tr>
                <td><label for="regionLabel">Název</label></td>
                <td><input type="text" id="regionLabel" name="label" placeholder="Haná" value="" style="max-width: 7cm;"></td>
            </tr>

            <tr>
                <td><label for="regionType">Typ</label></td>
                <td><select id="regionType" name="type">
                    <option value="0">Neznámý</option>
                    <option value="1">Žemě</option>
                    <option value="2">Region</option>
                    <option value="3">Subregion</option>
                    <option value="4">Oblast</option>
                    <option value="5">Lokalita</option>
                </select></td>
            </tr>
                        
            <tr>
                <td><label for="regionParent">Nadřazený region</label>
                <td><select id="regionParent" name="parent">
                    <option value="null">{Neznámé}</option>
                    <option value="-1">{Žádný}</option>
                    <?php
                        $htmloptions="";
                        foreach ($list as $item) {
                            $id=$item[0];
                            $label=$item[1];
                            $htmloptions.="<option value='$id'>$label</option>";
                        }
                        echo $htmloptions;
                    ?>
                </select></td>
            </tr>
        </table>
        <div style="display: flex;flex-direction: column;">
            <label for="regionTranslates">Překlady</label>
            <textarea id="regionTranslates" placeholder='{"cs": "Haná"}' name="translates"></textarea>
        </div>
        <a onclick="currentRegionSave()" class="button">Save</a>
        <input type="hidden" id="regionId" name="id" value="-1">
    </div>
</div>