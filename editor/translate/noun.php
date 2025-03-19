 <div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_pattern_to.php";
        
        $list=[];
        $sqlDone=true;
        $sql="SELECT `id`, `from` FROM `noun_relations` WHERE `translate` = ".$_SESSION['translate'].";";
        $result = $conn->query($sql);
        if (!$result) {
            throwError("SQL error: ".$sql);
            $sqlDone=false;
        }

        $sqlFrom="SELECT `id`, `label` FROM `noun_patterns_cs`";
        $resultFrom = $conn->query($sqlFrom);
        if (!$resultFrom) {
            $sqlDone=false;
            throwError("SQL error: ".$sqlFrom);
        }

        if ($sqlDone) {
            while ($row = $result->fetch_assoc()) {
                $idRelation=$row["id"];
                $from=null;

                while ($rowFrom = $resultFrom->fetch_assoc()) {
                    if ($rowFrom['id']==$idRelation){
                        $from=$rowFrom;
                    }
                }

                if ($from!=null) {
                    $list[]=[$idRelation, $from["label"]];
                }else{
                    $list[]=[$idRelation, "<Unknown from>"];
                }
            }
        }

        echo FilteredList($list, "noun_relations");

        $GLOBALS["onload"].= /** @lang JavaScript */
            "noun_relations_changed=function() { 
            let id = flist_noun_relations.getSelectedIdInList();
        
            // no selected
            if (id==null) return;
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=noun_relation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                    document.getElementById('nounId').value=id;
                    document.getElementById('noun_from').value=json.from;
                }else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_noun_relations.EventItemSelectedChanged(noun_relations_changed);";
    
        $GLOBALS["script"].= /** @lang JavaScript */
            "var flist_noun_relation; 
            var currentNounRelationSave = function() {
                let froms=document.getElementById('noun_from').value;              
                let id=document.getElementById('nounId').value;              
    
                let formData = new URLSearchParams();
                formData.append('action', 'noun_relation_update');
                formData.append('id', id);
                formData.append('from', froms);
               
    
                fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData.toString()
                }).then(response => response.json())
                .then(json => {
                    if (json.status==='OK') {
                       flist_noun_relation.getSelectedItemInList().innerText=label;
                    }else console.log('error currentRegionSave',json);
                });
            };";
        ?>
    </div>
    <div class="editorView">
        <div id="noun">
            <div class="row">
                <label for="noun_from" id="name">Z</label>
                <input id="noun_from" type="hidden">

                <datalist id="fromList">
                    <?php
                        $options="";
                        if ($resultFrom->num_rows > 0) {
                            while ($rowFrom = $resultFrom->fetch_assoc()) {
                                $options.="<option value='".$rowFrom['id']."'>".$rowFrom['label']."</option>";
                            }
                        }
                        echo $options;
                    ?>
                </datalist>

                <?php// echo selectfromList($list, "noun_pattern_cs");?>
            </div>

            <div>
                <label for="noun_from" id="name">Na</label>
                <datalist id="listTo">

                </datalist>
            </div>

            <div>
                <input type="hidden" id="nounId" value="-1">
                <a onclick="currentNounRelationSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>