<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
      //  include "components/filter_list.php";
        include "components/tags_editor.php";

        $sql="SELECT id, label FROM adjective_patterns_cs;";
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

        echo FilteredList($list, "adjective_patterns_cs");  

        $GLOBALS["onload"].="adjective_cs_changed=function() { 
            let elementsSelected = flist_adjective_patterns_cs.getSelectedItemInList();
        
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
                body: `action=adjective_pattern_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                    document.getElementById('adjectiveId').value=id;
                    document.getElementById('adjectiveLabel').value=json.label;
                    document.getElementById('adjectiveBase').value=json.base;
                    document.getElementById('adjectiveCategory').value=json.category;

                    if (json.category==null) json.category=0;
                    document.getElementById('adjectiveCategory').value=json.category; 

                    let rawShapes=json.shapes;
                    let shapes;
                    if (rawShapes!=null) {
                        shapes=json.shapes.split('|'); 
                    } else shapes=[];
                    for (let g=0; g<4; g++) {
                        for (let i=0; i<14; i++) {
                            let shape=shapes[i];                            
                            let textbox=document.getElementById('adjective'+g+''+i);

                            if (shape==undefined) textbox.value='';
                            else textbox.value=shape;
                        }  
                    }

                    if (json.tags!=null) {
                        let arrTags=json.tags.split('|');
                        tagSet(arrTags);
                    }else{
                        tagSet([]);
                    }
                   
                }else console.log('error sql', json);
            });
        };

        refreshFilteredLists();

        flist_adjective_patterns_cs.EventItemSelectedChanged(adjective_cs_changed);";
    
        $GLOBALS["script"].="var flist_adjective_patterns_cs; 
        var currentadjectiveCSSave = function() {
            let label=document.getElementById('adjectiveLabel').value;
            let base=document.getElementById('adjectiveBase').value;
            let category=document.getElementById('adjectiveCategory').value;
            let adjectiveId=document.getElementById('adjectiveId').value;
            let tags=document.getElementById('adjective_csdatatags').value;
            let shapes=[];
            for (let i=0; i<14; i++) {
                let textbox=document.getElementById('adjective'+i);
                shapes[i]=textbox.value
            }

            let formData = new URLSearchParams();
            formData.append('action', 'adjective_pattern_cs_update');
            formData.append('id', adjectiveId);
            formData.append('label', label);
            formData.append('base', base);
            formData.append('category', category);
            formData.append('shapes', shapes.join('|'));
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_adjective_patterns_cs.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label id="name">Popis</label><br> 
                <input type="text" id="adjectiveLabel" for="name" value="" placeholder="mlaDÝ" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base">Základ</label><br>
                <input type="text" id="adjectiveBase" for="name" value="" placeholder="mla" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label>Kategorie</label>
                <select id="adjectiveCategory" name="type">
                    <option value="0">Neznámý</option>
                    <option value="1">Tvrdé</option>
                    <option value="2">Měkké</option>
                    <option value="3">Přivlastňovací</option>
                </select>
                <br>
            </div>

            <div class="section">
                <label id="name">Skloňování</label> 
                <div style="display: flex; flex-wrap: wrap;">
                    <?php 
                    $arrGenders=["Mužský životný", "Mužský neživotný", "Ženský", "Střední"];
                    for ($g=0; $g<4; $g++) {
                        $html='<table>
                            <caption>'.$arrGenders[$g].'</caption>
                            <tr>
                                <td class="tableHeader">Pád</td>
                                <td class="tableHeader">Jednotné</td>
                                <td class="tableHeader">Množné</td>
                            </tr>';               
                
                        for ($i=0; $i<7; $i++) {
                            $html.="<tr><td>".($i+1).".</td>";
                            for ($j=0; $j<2; $j++) $html.="<td><input id='adjective".$g.($j==0 ? $i : 7+$i)."' type='text'></td>";
                            $html.="</tr>";
                        }
                        echo $html.'</table>';
                    }                 
                    ?>
                </div>
                <input type="hidden" id="adjectiveShapes" value="-1">
            </div>

            <?php echo tagsEditor("adjective_cs", [], "Tagy")?>
            <div> 
                <input type="hidden" id="adjectiveId" value="-1">
                <a onclick="currentadjectiveCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>