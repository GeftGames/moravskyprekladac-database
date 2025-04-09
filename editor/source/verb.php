<div class="splitView">
    <div>
        <?php
        // side list
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

        $arrShapeTables=[
            //[name, show, len, code, display]
            ["Infinitiv", false, 1, "infinitive"],
            ["Přítomný", true, 6, "continous"],
            ["Budoucí", true, 6, "future"],
            ["Rozkazovací", true, 3, "imperative"],
            ["Minulý činný", true, 8, "past_active"],
            ["Minulý trpný", true, 8, "past_passive"],
            ["Přechodník přítomný", true, 3, "transgressive_cont"],
            ["Přechodník minulý", true, 3, "transgressive_past"],
            ["Podmiňovací", true, 6, "auxiliary"],
        ];
        $jsArrShapeTables=json_encode($arrShapeTables, JSON_UNESCAPED_UNICODE);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        verb_cs_changed=function() {             
            let id = flist_verb_pattern_cs.getSelectedIdInList();
        
            // no selected
            if (id==null) return;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=verb_pattern_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('verbId').value=id;
                    document.getElementById('verbLabel').value=json.label;
                    document.getElementById('verbBase').value=json.base;

                    if (json.category===null) json.category=0;
                    document.getElementById('verbCategory').value=json.category; 

                    let shapesTypes=$jsArrShapeTables;
                    
                    // shapes load
                    let shapeTypeIndex=0;
                    for (let s of shapesTypes) {
                        let code=s[3]; // 'contonous', 'future', ...              
                        let exists=json[code]!==''; // if this type of table conjunction exists
                        let checkbox=s[1];// if exists checkbox
                        
                        // checkbox
                        if (checkbox) {
                            document.getElementById('verbforms'+shapeTypeIndex).checked=exists;
                        }
                      
                        // textboxs
                        if (exists) {
                            let shapes=json[code].split('|');
                            let shapesLen=s[2];
                            console.log(shapes);
                            for (let i=0; i<shapesLen; i++) { 
                                let shape1=shapes[i];                            
                             //   console.log(shape1,shapeTypeIndex,i);
                                let textbox=document.getElementById('verbShape'+shapeTypeIndex+''+i);
                                if (textbox==null) console.error('\"verbShape'+shapeTypeIndex+''+i+'\" does not exists');
                                if (shape1===undefined) textbox.value='';
                                else textbox.value=shape1;
                            }
                        }
                        shapeTypeIndex++;
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
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_verb_pattern_cs;
        var currentverbCSSave = function() {
            let label=document.getElementById('verbLabel').value;
            let base=document.getElementById('verbBase').value;
            let category=document.getElementById('verbCategory').value;
            let verbId=document.getElementById('verbId').value;
            let tags=document.getElementById('verb_csdatatags').value;
            let shapes=[];
            let st=0;
            let shapesTypes=$jsArrShapeTables;
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
            formData.append('shapes_infinitive', shapetype);
            formData.append('shapes_future', shapetype);
            formData.append('shapes_imperative', shapetype);
            formData.append('shapes_past_active', shapetype);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                   flist_verb_pattern_cs.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

       
        var changeVisibility = function() {
            let shapesTypes=$jsArrShapeTables;
            
            for (let i=0; i<shapesTypes.length; i++) {
                // checkboxl
                if (shapesTypes[i][1]) {
                    let display=document.getElementById('verbforms'+i).checked;
                    document.getElementById('verbtable'+i).style.display=display ? 'inline' : 'none';
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
                <label id="name" for="verbLabel" >Popis</label><br>
                <input type="text" id="verbLabel" value="" placeholder="dělAT" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base" for="verbBase">Základ</label><br>
                <input type="text" id="verbBase" value="" placeholder="děl" style="max-width: 9cm;">
            </div>

            <div class="section">
                <label id="name">Časování</label> 
                <style>#verbTables > div{ min-width:14cm; }</style>
                <div style="column-count: 2;" id="verbTables">
                    <?php 
                    $arrGenders=["Mužský životný", "Mužský neživotný", "Ženský", "Střední"];
                    for ($g=0; $g<count($arrShapeTables); $g++) {
                        $html='<div><p>';
                        if ($arrShapeTables[$g][1])$html.='<input type="checkbox" id="verbforms'.$g.'" onclick="changeVisibility()">';
                        $html.='<label for="verbforms'.$g.'" style="user-select:none">'.$arrShapeTables[$g][0].'</label></p>';
                        $html.='<table id="verbtable'.$g.'">';
                        $typeTable=$arrShapeTables[$g][3];

                        // Infinitive
                        if ($typeTable=="infinitive") {
                            $html.= '<input id="verbShape'.$g.'0" type="text">';

                        // Continous, Future, Podmiňovací
                        } else if ($typeTable=="continous" || $typeTable=="future" || $typeTable=="auxiliary") {
                            // table header
                            $html.='<tr>
                                    <td class="tableHeader">Osoba</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                                </tr>';               

                            // table body 3×2
                            for ($i=0; $i<3; $i++) {
                                $html.="<tr><td>".($i+1).".</td>"; // label person
                                for ($j=0; $j<2; $j++) $html.="<td><input id='verbShape".$g.($j==0 ? $i : 3+$i)."' type='text'></td>";
                                $html.="</tr>";
                            }

                        // Imperative
                        } else if ($typeTable=="imperative") {
                            // table header
                             $html.='<tr>
                                    <td class="tableHeader">Rod</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                                </tr>';               

                             // table body 2×2 (-1)
                            $html.="<tr><td>1.</td>";
                            $html.="<td><input disabled style='opacity: .5;' type='text'></td>";
                            $html.="<td><input id='verbShape".$g."1' type='text'></td>";
                            $html.="</tr>";
                            $html.="<tr><td>2.</td>";
                            $html.="<td><input id='verbShape".$g."0' type='text'></td>";
                            $html.="<td><input id='verbShape".$g."2' type='text'></td>";
                            $html.="</tr>";

                        // Past Passive and active
                        } else if ($typeTable=="past_active" || $typeTable=="past_passive") {
                            // table header
                            $html.='<tr>
                                    <td class="tableHeader">Rod</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                                </tr>';               

                            // table body 4×2
                            for ($i=0; $i<4; $i++) {
                                $html.="<tr><td>".($i==0 ? "m. ž" : ($i==1 ? "m. n" : ($i==2 ? "žen" : "stř"))).".</td>"; // gender label
                                for ($j=0; $j<2; $j++) $html.="<td><input id='verbShape".$g.($j==0 ? $i : 4+$i)."' type='text'></td>";
                                $html.="</tr>";
                            }

                        //transgressive
                        }else if ($typeTable=="transgressive_cont" || $typeTable=="transgressive_past") {
                            // table header
                            $html.='<tr>
                                    <td class="tableHeader">Rod</td>
                                    <td class="tableHeader">Jednotné</td>
                                </tr>';               

                            // table body 3×1
                            $html.="<tr><td>j. m</td><td><input id='verbShape".$g."0' type='text'></td></tr>";
                            $html.="<tr><td>j. f+n</td><td><input id='verbShape".$g."1' type='text'></td></tr>";
                            $html.="<tr><td>Množný</td><td><input id='verbShape".$g."2' type='text'></td></tr>";
                        }else echo "<p>ERROR: Table does not exists: $typeTable</p>";
                        echo $html.'</table></div>';
                    }                 
                    ?>
                </div>
                <input type="hidden" id="verbShapes" value="-1">
            </div>

            <div class="row section">
                <label for="verbCategory">Zvratné</label>
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