 <div class="splitView">
    <div>
        <?php
     //   include "components/filter_list.php";
        include "components/tags_editor.php";
        
        $sql="SELECT id, label FROM noun_relations LIMIT 30;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            echo "0 results ";
        }

        echo FilteredList($list, "noun_relation");    
        
        
        $GLOBALS["onload"].="noun_relation_changed=function() { 
            let elementsSelected = flist_noun_relation.getSelectedItemInList();
        
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
                body: `action=noun_relation_item&id=`+id
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
                            let textbox=document.getElementById('noun'+g+''+i);

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

        flist_noun_relation.EventItemSelectedChanged(adjective_cs_changed);";
    
        $GLOBALS["script"].="var flist_noun_relation; 
        var currentadjectiveCSSave = function() {
            let label=document.getElementById('adjectiveLabel').value;
            let base=document.getElementById('adjectiveBase').value;
            let category=document.getElementById('adjectiveCategory').value;
            let adjectiveId=document.getElementById('adjectiveId').value;
            let tags=document.getElementById('adjective_csdatatags').value;
            let shapes=[];
            for (let i=0; i<14; i++) {
                let textbox=document.getElementById('noun'+i);
                shapes[i]=textbox.value
            }

            let formData = new URLSearchParams();
            formData.append('action', 'noun_relation_update');
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
                   flist_noun_relation.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="noun" style="display:none">
            <div class="row">
                <label id="name">Z</label>
                <input type="text" for="name">
            </div>

            <div>
                <label id="name">Do</label>
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
                    for ($j=0; $j<2; $j++) $html.="<td><input type='text'></td>";
                    $html.="</tr>";
                }
                echo $html;
                ?> 
                </table>
            </div>

            <div>
                <label id="name">Info</label>
                <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                <p>"?" Neznámý tvar</p>
                <p>"-" Neexistuje tvar</p>
            </div>
        </div>
    </div>
</div>