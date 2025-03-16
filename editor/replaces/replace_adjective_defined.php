<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
      //  include "components/filter_list.php";
        include "components/tags_editor.php";
        
        $order="ORDER BY LOWER(label) ASC";
        $sql="SELECT id, label FROM replace $order;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "adjective_pattern_cs");  

        $GLOBALS["onload"].="adjective_cs_changed=function() { 
            let elementsSelected = flist_adjective_pattern_cs.getSelectedItemInList();
        
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
                if (json.status=='OK') {
                    document.getElementById('adjectiveId').value=id;
                    document.getElementById('adjectiveLabel').value=json.label;
                    document.getElementById('adjectiveBase').value=json.base;

                    if (json.category==null) json.category=0;
                    document.getElementById('adjectiveCategory').value=json.category; 

                    // shapes
                    let shapes;
                    if (json.shapes!=null) shapes=json.shapes.split('|'); 
                    else shapes=[];

                    for (let g=0; g<4; g++) {
                        for (let i=0; i<14; i++) {
                            let shape=shapes[g*14+i];                            
                            let textbox=document.getElementById('adjective'+g+''+i);

                            if (shape==undefined) textbox.value='';
                            else textbox.value=shape;
                        }  
                    }

                    // shape type
                    let shapesType=0;
                    if (shapes.length==0) shapesType=0;
                    else if (shapes.length==7) shapesType=1;
                    else if (shapes.length==14) shapesType=2;
                    else if (shapes.length==14*4) shapesType=3;

                    document.getElementById('adjectiveShapesType').value=shapesType;
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

        flist_adjective_pattern_cs.EventItemSelectedChanged(adjective_cs_changed);
        flist_adjective_pattern_cs.EventItemAddedChanged(adjective_cs_added);";
    
        $GLOBALS["script"].="var flist_adjective_pattern_cs; 
        var currentadjectiveCSSave = function() {
            let label=document.getElementById('adjectiveLabel').value;
            let base=document.getElementById('adjectiveBase').value;
            let category=document.getElementById('adjectiveCategory').value;
            let adjectiveId=document.getElementById('adjectiveId').value;
            let tags=document.getElementById('adjective_csdatatags').value;
            let shapesType=document.getElementById('adjectiveShapesType').value;
            let shapes=[];
            if (shapesType==0) shapes=[];
            else if (shapesType==1) {
                for (let i=0; i<7; i++) {
                    let textbox=document.getElementById('adjective0'+i);
                    shapes[i]=textbox.value;
                }
            }else if (shapesType==2) {
                for (let i=0; i<14; i++) {
                    let textbox=document.getElementById('adjective0'+i);
                    shapes[i]=textbox.value;
                }
            }else if (shapesType==3) {
                for (let g=0; g<4; g++) {
                    for (let i=0; i<14; i++) {
                        let textbox=document.getElementById('adjective'+g+''+i);
                        shapes[g*14+i]=textbox.value;
                    }
                }
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
                   flist_adjective_pattern_cs.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };
        var changeVisibility = function() {
            let type=document.getElementById('adjectiveShapesType').value;
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
                for (let i=7; i<14; i++)document.getElementById('adjective0'+i).style.display='none';
            }else if (type==2) {
                document.getElementById('tableGender0').style.display='block';
                document.getElementById('tableGender1').style.display='none';
                document.getElementById('tableGender2').style.display='none';
                document.getElementById('tableGender3').style.display='none';
                for (let i=7; i<14; i++)document.getElementById('adjective0'+i).style.display='block';
            }else if (type==3) {
                document.getElementById('tableGender0').style.display='block';
                document.getElementById('tableGender1').style.display='block';
                document.getElementById('tableGender2').style.display='block';
                document.getElementById('tableGender3').style.display='block';
                for (let i=7; i<14; i++)document.getElementById('adjective0'+i).style.display='block';
            }
        };

        var adjective_cs_added = function() {
            flist_adjective_pattern_cs.lastAddedId;
            adjective_cs_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label id="name">Popis</label><br> 
                <input type="text" id="adjectiveLabel" for="name" value="" placeholder="ré>réj" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base">Z</label><br>
                <input type="text" id="adjectiveBase" for="name" value="" placeholder="ré" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label id="base">Na</label><br>
                <input type="text" id="adjectiveBase" for="name" value="" placeholder="réj" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label for="replaceTypeAdjectiveGender">Rod</label>
                <select id="replaceTypeAdjectiveGender" name="replaceTypeAdjectiveGender">
                    <option value="0">Jakékoliv</option>
                    <option value="1">Mužský životný</option>
                    <option value="2">Mužský neživotný</option>
                    <option value="3">Ženský</option>
                    <option value="4">Střední</option>
                </select>
                <br>
            </div>

            <div class="row section">
                <label for="replaceTypeAdjectiveGender">Číslo</label>
                <select id="replaceTypeAdjectiveGender" name="replaceTypeAdjectiveGender">
                    <option value="0">Jakékoliv</option>
                    <option value="1">Jednotné</option>
                    <option value="2">Množné</option>
                </select>
                <br>
            </div>

            <div class="row section">
                <label for="replaceTypeAdjectiveGender">Pád(y)</label>
                <input type="text" id="adjectiveLabel" for="name" value="" placeholder="7" style="max-width: 9cm;">
                <br>
            </div>
              
            <?php echo tagsEditor("replace_adjective_includes", [], "Obsahuje")?>

            
            <?php echo tagsEditor("replace_adjective_not_includes", [], "neobsahuje")?>

            <div> 
                <input type="hidden" id="adjectiveId" value="-1">
                <a onclick="currentadjectiveCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>