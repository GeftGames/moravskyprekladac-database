<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        
        $order="ORDER BY LOWER(shape) ASC";
        $sql="SELECT id, shape FROM particles_cs $order;";
        $result = $conn->query($sql);
        $list=[];
        if (!$result) throwError("SQL error: ".$sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["shape"]];
            }
        }

        echo FilteredList($list, "particles_cs", [], null);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        particle_cs_changed=function() { 
            let id = flist_particles_cs.getSelectedIdInList();
        
            // no selected
            if (id==null) return;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=particle_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status === 'OK') {
                    document.getElementById('particleId').value=id;
                    document.getElementById('particleShape').value=json.shape;

                    // tags
                    if (json.tags!=null) {
                        let arrTags=json.tags.split('|');
                        tagSet(arrTags, 'particle_cs');
                    } else {
                        tagSet([], 'particle_cs');
                    }
                   
                } else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_particles_cs.EventItemSelectedChanged(particle_cs_changed);
        flist_particles_cs.EventItemAddedChanged(particle_cs_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_particles_cs; 
        var currentparticleCSSave = function() {
            let shape=document.getElementById('particleShape').value;
            let particleId=document.getElementById('particleId').value;
            let tags=document.getElementById('particle_csdatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'particle_cs_update');
            formData.append('id', particleId);
            formData.append('shape', shape);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status === 'OK'){
                   flist_particles_cs.getSelectedItemInList().innerText=shape;
                }else console.log('error currentRegionSave',json);
            });
        };

        var particle_cs_added = function() {
            flist_particles_cs.lastAddedId;
            particle_cs_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <table>
                <tr>
                    <td><label for="particleShape">Tvar</label></td>
                    <td><input type="text" id="particleShape" value="" placeholder="z" style="max-width: 9cm;"></td>
                </tr>

                <?php echo tagsEditor("particle_cs", [], "Tagy")?>
            </table>
            <div> 
                <input type="hidden" id="particleId" value="-1">
                <a onclick="currentparticleCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>