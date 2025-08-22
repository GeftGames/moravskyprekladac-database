<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_to.php";

        tagsEditorDynamic();

        $filter=$_SESSION['translate'];
        //$order="ORDER BY LOWER(from) ASC";
        $sql="SELECT `id`, `from` FROM `simpleword_relations` WHERE `translate`=$filter;";
        $result = $conn->query($sql);
        if (!$result) echo "ERROR: ".$conn->error;

        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["from"]];
            }
        } else {
            //echo "0 results";
        }

        echo FilteredList($list, "simplewords", [], $filter);
        
        $GLOBALS["onload"].= /** @lang JavaScript */"
        simpleword_changed=function() { 
          /*  let elementsSelected = flist_simpleword_relations.getSelectedItemInList();
        
            // no selected
            if (!elementsSelected) {
                return;
            }
            //no multiple
             if (Array.isArray(elementsSelected)) return;

            let id=elementsSelected.dataset.id;
            */
          
           let id = flist_simplewords.getSelectedIdInList();
        
            // no selected
            if (id==null) return;  

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=simpleword_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('simplewordId').value=id;
                    document.getElementById('simplewordFrom').value=json.from;

                    to_load(json.to);//JSON.parse()
                    
                    // tags
                   /* if (json.tags!=null) {
                        let arrTags=json.tags.split('|');
                        tagSet(arrTags);
                    } else {
                        tagSet([]);
                    }*/
                   
                } else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_simplewords.EventItemSelectedChanged(simpleword_changed);
        flist_simplewords.EventItemAddedChanged(simpleword_added);
        ";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_simplewords; 
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
                if (json.status==='OK'){
                   flist_simplewords.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

        var simpleword_added = function() {
            flist_simplewords.lastAddedId;
            simpleword_changed();
        };
        ";
        ?>
    </div>
    <div class="editorView">
        <table style="width: -webkit-fill-available;">
            <tr>
                <td><label for="simplewordFrom">Z</label></td>
                <td><textarea id="simplewordFrom" style="width: -webkit-fill-available;"></textarea></td>
            </tr>
        </table>
        <table>
            <tr>
                <td><label for="simplewordTo">Na</label></td>
            </tr>
            <tr>
                <td><?php echo multiple_to([],"simpleword"); ?></td>
            </tr>
        </table>
        <hr>
        <div>
            <input type="hidden" id="simplewordId" value="-1">
            <a onclick="currentsimplewordCSSave()" class="button">Uložit</a>
        </div>

        <div style="color: gray">
            <h4>Info</h4>
            <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
            <p>"?" Neznámý tvar</p>
            <p>"-" Neexistuje tvar</p>
        </div>
    </div>
</div>