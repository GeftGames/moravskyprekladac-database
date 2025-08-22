<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_to.php";
        tagsEditorDynamic();

        // list from
        $sqlFrom="SELECT id, shape FROM particles_cs;";

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
        $sqlR="SELECT `id`, `from` FROM particle_relations WHERE translate = ".$_SESSION['translate'].";";
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
        echo FilteredList($listR, "particle_relations", [], $_SESSION['translate']);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        var particle_relation_changed = function() { 
            let id = flist_particle_relations.getSelectedIdInList();
        
            // no selected
            if (id==null) return;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=particle_relation_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('particleId').value=id;
                    //from
                    filteredSearchList_particle_from.selectId(json.from);                   
                    filteredSearchList_particle_from.reload();
                   
                    //to
                    to_load(JSON.parse(json.to));
                   
                } else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_particle_relations.EventItemSelectedChanged(particle_relation_changed);
        ";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_particle_relations; 
        var currentparticleTOSave = function() {
            let shape=document.getElementById('particleShape').value;
            let particleId=document.getElementById('particleId').value;
            let tags=document.getElementById('particle_todatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'particle_to_update');
            formData.append('id', particleId);
            formData.append('shape', shape);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_particle_relations.getSelectedItemInList().innerText=shape;
                }else console.log('error currentRegionSave',json);
            });
        };
        ";
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="section row">
                <label for="particle_from" id="name">Z</label>&nbsp;
                <div id="select_particle_from"></div>
                <?php createSelectList($listFrom, "particle_from", $idFrom);?>
            </div>

            <div class="section">
                <label for="particle_from" id="name">Na</label>
                <?php echo multiple_to([], "particle"); ?>
            </div>

            <div> 
                <input type="hidden" id="particleId" value="-1">
                <a onclick="currentparticleTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>