<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
       // include "components/filter_list.php";
        include "components/tags_editor.php";

        $sql="SELECT id, label FROM noun_patterns_cs;";
        $result = $conn->query($sql);
        $list=[];
        if (!$result) throwError("SQL error: ".$sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "noun_patterns_cs");  

        $GLOBALS["onload"].= /** @lang JavaScript */"
            noun_cs_changed=function() { 
            let id = flist_noun_patterns_cs.getSelectedIdInList();
        
            // no selected
            if (id==null) return;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=noun_pattern_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                    document.getElementById('nounId').value=id;
                    document.getElementById('nounLabel').value=json.label;
                    document.getElementById('nounBase').value=json.base;
                    document.getElementById('nounGender').value=json.gender;

                    if (json.gender==null) json.gender=0;
                    document.getElementById('nounGender').value=json.gender; 

                    let rawShapes=json.shapes;
                    let shapes;
                    if (rawShapes!=null) {
                        shapes=json.shapes.split('|'); 
                    } else shapes=[];
                    for (let i=0; i<14; i++) {
                        let shape=shapes[i];                            
                        let textbox=document.getElementById('noun'+i);

                        if (shape==undefined) textbox.value='';
                        else textbox.value=shape;
                    }                   

                    if (json.tags!=null) {
                        let arrTags=json.tags.split('|');
                        tagSet(arrTags);
                    }else{
                        tagSet([]);
                    }
                   
                } else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_noun_patterns_cs.EventItemSelectedChanged(noun_cs_changed);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_noun_patterns_cs; 
        var currentNounCSSave = function() {
            let label=document.getElementById('nounLabel').value;
            let base=document.getElementById('nounBase').value;
            let gender=document.getElementById('nounGender').value;
            let nounId=document.getElementById('nounId').value;
            let tags=document.getElementById('noun_csdatatags').value;
            let shapes=[];
            for (let i=0; i<14; i++) {
                let textbox=document.getElementById('noun'+i);
                shapes[i]=textbox.value
            }

            let formData = new URLSearchParams();
            formData.append('action', 'noun_pattern_cs_update');
            formData.append('id', nounId);
            formData.append('label', label);
            formData.append('base', base);
            formData.append('gender', gender);
            formData.append('shapes', shapes.join('|'));
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_noun_patterns_cs.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label id="name">Popis</label><br> 
                <input type="text" id="nounLabel" for="name" value="" placeholder="pohádKA">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base">Základ</label><br>
                <input type="text" id="nounBase" for="name" value="" placeholder="pohád">
            </div>

            <div class="row section">
                <label>Rod</label>
                <select id="nounGender" name="type">
                    <option value="0">Neznámý</option>
                    <option value="4">Střední</option>
                    <option value="3">Ženský</option>
                    <option value="2">Mužský neživotný</option>
                    <option value="1">Mužský životný</option>
                </select>
                <br>
            </div>

            <div class="section">
                <label id="name">Pád</label>
                <table>
                    <tr>
                        <td class="tableHeader">Pád</td>
                        <td class="tableHeader">Jednotné</td>
                        <td class="tableHeader">Množné</td>
                    </tr>
                <?php 
                $html="";
                for ($i=0; $i<7; $i++) {
                    $html.="<tr><td>".($i+1).".</td>";
                    for ($j=0; $j<2; $j++) $html.="<td><input id='noun".($j==0 ? $i : 7+$i)."' type='text'></td>";
                    $html.="</tr>";
                }
                echo $html;
                ?> 
                </table> 
                <input type="hidden" id="nounShapes" value="-1">
            </div>

            <?php echo tagsEditor("noun_cs", [], "Tagy")?>
            <div> 
                <input type="hidden" id="nounId" value="-1">
                <a onclick="currentNounCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>