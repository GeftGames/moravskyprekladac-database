<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_to.php";
        tagsEditorDynamic();

        // list from
        $sqlFrom="SELECT id, shape FROM interjections_cs;";

        $listFrom=[];
        $resultFrom = $conn->query($sqlFrom);
        if ($resultFrom) {
            while ($rowFrom = $resultFrom->fetch_assoc()) {
                $listFrom[]=[$rowFrom["id"], $rowFrom["shape"]];
            }
        } else { $sqlDone=false;
            throwError("SQL error: ".$sqlFrom);
        }

        $idFrom=0;

        // relations list
        $listR=[];
        $sqlR="SELECT `id`, `from` FROM interjection_relations WHERE translate = ".$_SESSION['translate'].";";
        $resultR = $conn->query($sqlR);
        if ($resultR) {
            while ($rowR = $resultR->fetch_assoc()) {
                $str="<Neznámé>";
                foreach ($listFrom as $from){
                    if ($from[0]==$rowR["from"]) {
                        $str=$from[1];
                        break;
                    }
                }
                $listR[]=[$rowR["id"], $str];
            }
        } else { $sqlDone=false;
            throwError("SQL error: ".$sqlR);
        }
        $idFrom=0;


        // side menu
        echo FilteredList($listR, "interjection_relations", []);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        var interjection_relation_changed = function() { 
            let id = flist_interjection_relations.getSelectedIdInList();
        
            // no selected
            if (id==null) return;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=interjection_relation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('interjectionId').value=id;
                    //from
                    filteredSearchList_interjection_from.selectId(json.from);                   
                    filteredSearchList_interjection_from.reload();
                   
                    //to
                    to_load(JSON.parse(json.to));
                   
                } else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_interjection_relations.EventItemSelectedChanged(interjection_relation_changed);
        ";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_interjection_relations; 
        var currentinterjectionTOSave = function() {
            let shape=document.getElementById('interjectionShape').value;
            let interjectionId=document.getElementById('interjectionId').value;
            let tags=document.getElementById('interjection_todatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'interjection_to_update');
            formData.append('id', interjectionId);
            formData.append('shape', shape);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_interjection_relations.getSelectedItemInList().innerText=shape;
                }else console.log('error currentRegionSave',json);
            });
        };
        ";
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="section row">
                <label for="interjection_from" id="name">Z</label>&nbsp;
                <div id="select_interjection_from"></div>
                <?php createSelectList($listFrom, "interjection_from", $idFrom);?>
            </div>

            <div class="section">
                <label for="interjection_from" id="name">Na</label>
                <?php echo multiple_to([], "interjection"); ?>
            </div>

            <div> 
                <input type="hidden" id="interjectionId" value="-1">
                <a onclick="currentinterjectionTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>