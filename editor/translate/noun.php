 <div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
      //  include "components/select_fromlist.php";
        include "components/multiple_pattern_to.php";

        $sqlDone=true;

        // relations
        $sql="SELECT `id`, `from` FROM `noun_relations` WHERE `translate` = ".$_SESSION['translate'].";";
        $result = $conn->query($sql);
        if (!$result) {
            throwError("SQL error: ".$sql);
            $sqlDone=false;
        }

        // from
        $sqlFrom="SELECT `id`, `label` FROM `noun_patterns_cs`;";
        $resultFrom = $conn->query($sqlFrom);
        if (!$resultFrom) {
            $sqlDone=false;
            throwError("SQL error: ".$sqlFrom);
        }

        $listFrom=[];
        $list=[];

        if ($sqlDone) {
            // list from
            while ($rowFrom = $resultFrom->fetch_assoc()) {
                $listFrom[]=[$rowFrom["id"], $rowFrom["label"]];
            }

            // list relations
            while ($row = $result->fetch_assoc()) {
                $idRelation=$row["id"];
                $idFrom=$row["from"];

                // get from label
                $from=null;
                foreach ($listFrom as $item) {
                    if ($item[0]==$idFrom) {
                        $from=$item;
                        break;
                    }
                }

                if ($from!=null) {
                    $list[]=[$idRelation, $from[1]];
                }else{
                    $list[]=[$idRelation, "<Nepřiřazené>"];
                }
            }
        }

        echo FilteredList($list, "noun_relations");

        $GLOBALS["onload"].= /** @lang JavaScript */"
        noun_relations_changed=function() { 
            let id = flist_noun_relations.getSelectedIdInList();
        
            // no selected
            if (id==null) return;
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=noun_relation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('nounId').value=id;
                    //from
                    filteredSearchList_noun_from.selectId(json.from);                   
                    filteredSearchList_noun_from.reload();
                    //to
                    to_load(JSON.parse(json.to));
                }else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_noun_relations.EventItemSelectedChanged(noun_relations_changed);";

        $GLOBALS["script"].= /** @lang JavaScript */"
            var flist_noun_relations; 
            var currentNounRelationSave = function() {
                let froms=document.getElementById('listreturnholder_noun_from').value;              
                let id=document.getElementById('nounId').value;              
    
                let formData = new URLSearchParams();
                formData.append('action', 'noun_relation_update');
                formData.append('id', id);
                formData.append('from', froms);
                
                formData.append('to', to_save());
               
                fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                }).then(response => response.json())
                .then(json => {
                    if (json.status==='OK') {
                        let from_label=document.getElementById('selectedLabel_noun_from').innerText;  
                        flist_noun_relations.getSelectedItemsInList()[0].innerText=from_label;
                    }else console.log('error currentNounRelationSave', json);
                });
            };";
        ?>
    </div>
    <div class="editorView">
        <div id="noun">
            <div class="section">
                <label for="noun_from" id="name">Z</label>
                <div id="select_noun_from"></div>
                <?php createSelectList($listFrom, "noun_from", null);?>
            </div>

            <div class="section">
                <label for="noun_from" id="name">Na</label>
                <?php
              /*  $listTo=[];
                $sqlTo="SELECT `id`, `priority`, `shape`,`comment`, `cite` FROM `noun_to`;";
                $result = $conn->query($sqlTo);
                if (!$result) {
                    throwError("SQL error: ".$sqlTo);
                }
                while ($row = $result->fetch_assoc()) {
                    $listTo[]=[$row["id"], $row["priority"], $row["shape"], $row["comment"], $row["cite"]];
                }*/
                echo multiple_pattern_to([]); ?>
            </div>

            <div class="section">
                <input type="hidden" id="nounId" value="-1">
                <a onclick="currentNounRelationSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>