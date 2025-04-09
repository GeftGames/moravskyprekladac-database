<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
       // include "components/filter_list.php";
        include "components/tags_editor.php";

        $filter=$_SESSION['translate'];
        $sql="SELECT id, label FROM number_patterns_to WHERE translate=$filter;";
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

        echo FilteredList($list, "number_pattern_to");  

        $GLOBALS["onload"].= /** @lang JavaScript */"
        number_to_changed=function() { 
            let id = flist_number_pattern_to.getSelectedIdInList();
        
            // no selected
            if (id==null) return;
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=number_pattern_to_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('numberId').value=id;
                    document.getElementById('numberLabel').value=json.label;
                    document.getElementById('numberBase').value=json.base;

                    // shapes
                    let shapes;
                    if (json.shapes!=null) shapes=json.shapes.split('|'); 
                    else shapes=[];

                    for (let g=0; g<4; g++) {
                        for (let i=0; i<14; i++) {
                            let shape=shapes[g*14+i];                            
                            let textbox=document.getElementById('number'+g+''+i);

                            if (shape==undefined) textbox.value='';
                            else textbox.value=shape;
                        }  
                    }

                    // shape type
                    console.log(shapes.length);
                    let shapesType=0;
                    if (shapes.length==0) shapesType=0;
                    else if (shapes.length==7) shapesType=1;
                    else if (shapes.length==14) shapesType=2;
                    else if (shapes.length==14*4) shapesType=3;

                    document.getElementById('numberShapesType').value=shapesType;
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

        flist_number_pattern_to.EventItemSelectedChanged(number_to_changed);
        flist_number_pattern_to.EventItemAddedChanged(number_to_added);";
    
        $GLOBALS["script"].="var flist_number_pattern_to; 
        var currentnumberTOSave = function() {
            let label=document.getElementById('numberLabel').value;
            let base=document.getElementById('numberBase').value;
            let numberId=document.getElementById('numberId').value;
            let tags=document.getElementById('number_todatatags').value;
            let shapesType=document.getElementById('numberShapesType').value;
            let shapes=[];
            if (shapesType==0) shapes=[];
            else if (shapesType==1) {
                for (let i=0; i<7; i++) {
                    let textbox=document.getElementById('number0'+i);
                    shapes[i]=textbox.value;
                }
            }else if (shapesType==2) {
                for (let i=0; i<14; i++) {
                    let textbox=document.getElementById('number0'+i);
                    shapes[i]=textbox.value;
                }
            }else if (shapesType==3) {
                for (let g=0; g<4; g++) {
                    for (let i=0; i<14; i++) {
                        let textbox=document.getElementById('number'+g+''+i);
                        shapes[g*14+i]=textbox.value;
                    }
                }
            }

            let formData = new URLSearchParams();
            formData.append('action', 'number_pattern_to_update');
            formData.append('id', numberId);
            formData.append('label', label);
            formData.append('base', base);
            formData.append('shapes', shapes.join('|'));
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_number_pattern_to.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };
        var changeVisibility = function() {
            let type=document.getElementById('numberShapesType').value;
            if (type==0) {
                document.getElementById('tableGender0').style.display='none';
                document.getElementById('tableGender1').style.display='none';
                document.getElementById('tableGender2').style.display='none';
                document.getElementById('tableGender3').style.display='none';
            }else if (type==1) {
                document.getElementById('tableGender0').style.display='block';
                document.getElementById('tableGender1').style.display='none';
                document.getElementById('tableGender2').style.display='none';
                document.getElementById('tableGender3').style.display='none';
                for (let i=7; i<14; i++)document.getElementById('number0'+i).style.display='none';
            }else if (type==2) {
                document.getElementById('tableGender0').style.display='block';
                document.getElementById('tableGender1').style.display='none';
                document.getElementById('tableGender2').style.display='none';
                document.getElementById('tableGender3').style.display='none';
                for (let i=7; i<14; i++)document.getElementById('number0'+i).style.display='block';
            }else if (type==3) {
                document.getElementById('tableGender0').style.display='block';
                document.getElementById('tableGender1').style.display='block';
                document.getElementById('tableGender2').style.display='block';
                document.getElementById('tableGender3').style.display='block';
                for (let i=7; i<14; i++)document.getElementById('number0'+i).style.display='block';
            }
        };

        var number_to_added = function() {
            flist_number_pattern_to.lastAddedId;
            number_to_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label id="name">Popis</label><br> 
                <input type="text" id="numberLabel" for="name" value="" placeholder="niKDO" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base">Základ</label><br>
                <input type="text" id="numberBase" for="name" value="" placeholder="ni" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label>Ohebnost</label>
                <select id="numberShapesType" name="type" onchange="changeVisibility()">
                    <option value="0">Nesklonné</option>
                    <option value="1">7 pádů, bez čísla, bez rodu</option>
                    <option value="2">7 pádů, číslo, bez rodu</option>
                    <option value="3">Čísla s rod</option>
                </select>
                <br>
            </div>

            <div class="section">
                <label id="name">Skloňování</label> 
                <div style="display: flex; flex-wrap: wrap;">
                    <?php 
                    $arrGenders=["Mužský životný", "Mužský neživotný", "Ženský", "Střední"];
                    for ($g=0; $g<4; $g++) {
                        $html='<table id="tableGender'.$g.'">
                            <caption>'.$arrGenders[$g].'</caption>
                            <tr>
                                <td class="tableHeader">Pád</td>
                                <td class="tableHeader">Jednotné</td>
                                <td class="tableHeader">Množné</td>
                            </tr>';               
                
                        for ($i=0; $i<7; $i++) {
                            $html.="<tr><td>".($i+1).".</td>";
                            for ($j=0; $j<2; $j++) $html.="<td><input id='number".$g.($j==0 ? $i : 7+$i)."' type='text'></td>";
                            $html.="</tr>";
                        }
                        echo $html.'</table>';
                    }                 
                    ?>
                </div>
                <input type="hidden" id="numberShapes" value="-1">
            </div>

            <?php echo tagsEditor("number_to", [], "Tagy")?>
            <div> 
                <input type="hidden" id="numberId" value="-1">
                <a onclick="currentnumberTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>