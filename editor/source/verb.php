<div class="splitView">
    <div>
        <?php
        
        $arrShapeTables=[
            //[name, show, len]
            ["Infinitiv", false, 1], 
            ["Přítomný", true, 6], 
            ["Budoucí", true, 6], 
            ["Rozkazovací", true, 3], 
            ["Minulý činný", true, 8], 
            ["Minulý trpný", true, 8], 
            ["Přechodník přítomný", true, 3],
            ["Přechodník minulý", true, 3],
            ["Podmiňovací", true, 6]
        ];
        
        // Do dashboard stuff
      //  include "components/filter_list.php";
        include "components/tags_editor.php";
        
        $order="ORDER BY LOWER(label) ASC";
        $sql="SELECT id, label FROM verb_patterns_cs $order;";
        $result = $conn->query($sql);
        if (!$result) throwError("SQL error: ".$sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "verb_pattern_cs");  

        $GLOBALS["onload"].="verb_cs_changed=function() { 
            let elementsSelected = flist_verb_pattern_cs.getSelectedItemInList();
        
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
                body: `action=verb_pattern_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('verbId').value=id;
                    document.getElementById('verbLabel').value=json.label;
                    document.getElementById('verbBase').value=json.base;

                    if (json.category==null) json.category=0;
                    document.getElementById('verbCategory').value=json.category; 

                    let shapesTypes=".json_encode($arrShapeTables, JSON_UNESCAPED_UNICODE).";
                    let shapetypes = json.shapetype;
                    let arrbooleans=[];
                    let readerE=0;
                    for (let i=0; i<shapesTypes.length; i++) {
                        if (!shapesTypes[i][1]) arrbooleans.push(null);
                        arrbooleans.push((shapetypes>>readerE & 1) == 1);
                        readerE++;
                    }   
                    console.log('arrbooleans', arrbooleans);

                    // shapes
                    let shapes;
                    if (json.shapes!=null) shapes=json.shapes.split('|'); 
                    else shapes=[];

                    let reader=0;
                    for (let s=0; s<shapesTypes.length; s++) {
                        // checkbox
                        if (shapesTypes[s][1]) {
                            document.getElementById('verbforms'+s).checked=arrbooleans[s];
                        }
                      

                        // textboxs
                        if (arrbooleans[s] || arrbooleans[s]==null){  
                            console.log(shapesTypes[s][0]);
                            for (let i=0; i<shapesTypes[s][2]; i++) { 
                                let shape=shapes[reader];                            
                                let textbox=document.getElementById('verbShape'+s+''+i);
                                console.log(shape);

                                if (shape==undefined) textbox.value='';
                                else textbox.value=shape;

                                reader++;
                            }
                        }
                    }

                    changeVisibility();

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

        flist_verb_pattern_cs.EventItemSelectedChanged(verb_cs_changed);
        flist_verb_pattern_cs.EventItemAddedChanged(verb_cs_added);";
    
        $GLOBALS["script"].="var flist_verb_pattern_cs; 
        var currentverbCSSave = function() {
            let label=document.getElementById('verbLabel').value;
            let base=document.getElementById('verbBase').value;
            let category=document.getElementById('verbCategory').value;
            let verbId=document.getElementById('verbId').value;
            let tags=document.getElementById('verb_csdatatags').value;
            let shapes=[];
            let st=0;
            let shapesTypes=".json_encode($arrShapeTables, JSON_UNESCAPED_UNICODE).";
            console.log(shapesTypes);
            let shapetype=0;
            for (let s=0; s<shapesTypes.length; s++) {
                let display=true;
                if (shapesTypes[s][1]) {
                    display=document.getElementById('verbforms'+s).checked;
                    if (display) shapetype+=2**st;
                    st++;
                }
                if (display){
                    console.log('saving', shapesTypes[s][0]);
                    for (let i=0; i<shapesTypes[s][2]; i++) {
                        let textbox=document.getElementById('verbShape'+s+''+i);
                        shapes.push(textbox.value);
                    }
                }
            }
         
            let formData = new URLSearchParams();
            formData.append('action', 'verb_pattern_cs_update');
            formData.append('id', verbId);
            formData.append('label', label);
            formData.append('base', base);
            formData.append('shapes', shapes.join('|'));
            formData.append('category', category);
            formData.append('shapetype', shapetype);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_verb_pattern_cs.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

       
        var changeVisibility = function() {
            let shapesTypes=".json_encode($arrShapeTables, JSON_UNESCAPED_UNICODE).";
            
            for (let i=0; i<shapesTypes.length; i++) {
                // checkbox
                if (shapesTypes[i][1]) {
                    let display=document.getElementById('verbforms'+i).checked;
                    document.getElementById('verbtable'+i).style.display=display ? 'block' : 'none';
                }
            }
        };

        var verb_cs_added = function() {
            flist_verb_pattern_cs.lastAddedId;
            verb_cs_changed();
        }";            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label id="name">Popis</label><br> 
                <input type="text" id="verbLabel" for="name" value="" placeholder="dělAT" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base">Základ</label><br>
                <input type="text" id="verbBase" for="name" value="" placeholder="děl" style="max-width: 9cm;">
            </div>

            <div class="section">
                <label id="name">Časování</label> 
                <style>#verbTables > div{ min-width:14cm; }</style>
                <div style="display: flex; flex-wrap: wrap;" id="verbTables">
                    <?php 
                    // $arrShapeTables=["Infinitiv", "Přítomný", "Budoucí", "Rozkazovací", "Minulý činný", "Minulý trpný", "Podmiňovací"];
                  
                    $arrGenders=["Mužský životný", "Mužský neživotný", "Ženský", "Střední"];
                    for ($g=0; $g<count($arrShapeTables); $g++) {
                        $html='<div><p>';
                        if ($arrShapeTables[$g][1])$html.='<input type="checkbox" id="verbforms'.$g.'" onclick="changeVisibility()">';
                        $html.='<label for="verbforms'.$g.'" style="user-select:none">'.$arrShapeTables[$g][0].'</label></p>';
                        $html.='<table id="verbtable'.$g.'">';
                        if ($g==1 || $g==2 || $g==6) {
                            $html.='<tr>
                                    <td class="tableHeader">Osoba</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                                </tr>';               
                    
                            for ($i=0; $i<3; $i++) {
                                $html.="<tr><td>".($i+1).".</td>";
                                for ($j=0; $j<2; $j++) $html.="<td><input id='verbShape".$g.($j==0 ? $i : 3+$i)."' type='text'></td>";
                                $html.="</tr>";
                            }
                        }else if ($g==3) {
                             $html.='<tr>
                                    <td class="tableHeader">Rod</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                                </tr>';               
                    
                            $html.="<tr><td>1.</td>";
                            $html.="<td><input disabled style='opacity: .5;' type='text'></td>";
                            $html.="<td><input id='verbShape".$g."0' type='text'></td>";
                            $html.="</tr>";
                            $html.="<tr><td>2.</td>";
                            $html.="<td><input id='verbShape".$g."1' type='text'></td>";
                            $html.="<td><input id='verbShape".$g."2' type='text'></td>";
                            $html.="</tr>";
                           
                        }else if ($g==4 || $g==5) {
                             $html.='<tr>
                                    <td class="tableHeader">Rod</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                                </tr>';               
                    
                            for ($i=0; $i<4; $i++) {
                                $html.="<tr><td>".($i+1).".</td>";
                                for ($j=0; $j<2; $j++) $html.="<td><input id='verbShape".$g.($j==0 ? $i : 4+$i)."' type='text'></td>";
                                $html.="</tr>";
                            }
                        }else if ($g==0) {
                            $html.="<tr><input id='verbShape".$g."0' type='text'></tr>";
                        }else if ($g==7 || $g==8) {
                            $html.='<tr>
                                    <td class="tableHeader">Rod</td>
                                    <td class="tableHeader">Jednotné</td>
                                </tr>';               
                    
                            $html.="<tr><td>j. m</td><td><input id='verbShape".$g."0' type='text'></td></tr>";
                            $html.="<tr><td>j. f+n</td><td><input id='verbShape".$g."1' type='text'></td></tr>";
                            $html.="<tr><td>Množný</td><td><input id='verbShape".$g."2' type='text'></td></tr>";
                        }
                        echo $html.'</table></div>';
                    }                 
                    ?>
                </div>
                <input type="hidden" id="verbShapes" value="-1">
            </div>

            <div class="row section">
                <label>Zvratné</label>
                <select id="verbCategory" name="type">
                    <option value="0">Neznámý</option>
                    <option value="1">BEZ</option>
                    <option value="2">SI</option>
                    <option value="3">SE</option>
                </select>
                <br>
            </div>

            <?php echo tagsEditor("verb_cs", [], "Tagy")?>
            <div> 
                <input type="hidden" id="verbId" value="-1">
                <a onclick="currentverbCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>