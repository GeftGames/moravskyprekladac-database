<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
       // include "components/filter_list.php";
        include "components/tags_editor.php";
        
        $order="ORDER BY LOWER(shape) ASC";
        $sql="SELECT id, shape FROM conjunction_cs $order;";
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

        echo FilteredList($list, "conjunction_cs");  

        $GLOBALS["onload"].= /** @lang JavaScript */"
        conjunction_cs_changed=function() { 
            let id = flist_conjunction_cs.getSelectedIdInList();
        
            // no selected
            if (id==null) return;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=conjunction_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('conjunctionId').value=id;
                    document.getElementById('conjunctionLabel').value=json.label;
                    document.getElementById('conjunctionFalls').value=json.falls;

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

        flist_conjunction_cs.EventItemSelectedChanged(conjunction_cs_changed);
        flist_conjunction_cs.EventItemAddedChanged(conjunction_cs_added);";
    
        $GLOBALS["script"].="var flist_conjunction_cs; 
        var currentconjunctionCSSave = function() {
            let label=document.getElementById('conjunctionLabel').value;
            let falls=document.getElementById('conjunctionFalls').value;
            let conjunctionId=document.getElementById('conjunctionId').value;
            let tags=document.getElementById('conjunction_csdatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'conjunction_cs_update');
            formData.append('id', conjunctionId);
            formData.append('label', label);
            formData.append('falls', falls);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_conjunction_cs.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

        var conjunction_cs_added = function() {
            flist_conjunction_cs.lastAddedId;
            conjunction_cs_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label for="conjunctionLabel" id="name">Tvar</label><br> 
                <input type="text" id="conjunctionLabel" value="" placeholder="z" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label for="conjunctionComma">Pozice</label><br>
                <select name="conjunctionComma" id="conjunctionComma">
                    <option value="0">Neznámé</option>
                    <option value="1">S čárkou těsně ..., že...</option>
                    <option value="2">S čárkou volně ...,...xx...</option>
                    <option value="3">Mohou být bez čárky (a, i, nebo, ani)</option>
                    <option value="4">pomlčka -li</option>
                </select>
            </div>

            <?php echo tagsEditor("conjunction_cs", [], "Tagy")?>
            <div> 
                <input type="hidden" id="conjunctionId" value="-1">
                <a onclick="currentconjunctionCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>