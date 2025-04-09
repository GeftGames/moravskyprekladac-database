<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
      //  include "components/filter_list.php";
        include "components/tags_editor.php";

        $filter=$_SESSION['translate'];
        $sql="SELECT id, label FROM replaces_defined_noun WHERE translate=$filter;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "replaces_defined_noun");  

        $GLOBALS["onload"].= /** @lang JavaScript */
            "pronoun_cs_changed=function() { 
            let elementsSelected = flist_pronoun_pattern_cs.getSelectedItemInList();
        
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
                body: `action=pronoun_pattern_cs_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK') {
                    document.getElementById('pronounId').value=id;
                    document.getElementById('pronounLabel').value=json.label;
                    document.getElementById('pronounBase').value=json.base;

                    if (json.category==null) json.category=0;
                    document.getElementById('pronounCategory').value=json.category; 

                    // shapes
                    let shapes;
                    if (json.shapes!=null) shapes=json.shapes.split('|'); 
                    else shapes=[];

                    for (let g=0; g<4; g++) {
                        for (let i=0; i<14; i++) {
                            let shape=shapes[g*14+i];                            
                            let textbox=document.getElementById('pronoun'+g+''+i);

                            if (shape===undefined) textbox.value='';
                            else textbox.value=shape;
                        }  
                    }

                    // shape type
                    let shapesType=0;
                    if (shapes.length==0) shapesType=0;
                    else if (shapes.length==7) shapesType=1;
                    else if (shapes.length==14) shapesType=2;
                    else if (shapes.length==14*4) shapesType=3;

                    document.getElementById('pronounShapesType').value=shapesType;
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

        flist_pronoun_pattern_cs.EventItemSelectedChanged(pronoun_cs_changed);
        flist_pronoun_pattern_cs.EventItemAddedChanged(pronoun_cs_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */
            "var flist_pronoun_pattern_cs; 
        var currentpronounCSSave = function() {
            let label=document.getElementById('pronounLabel').value;
            let base=document.getElementById('pronounBase').value;
            let category=document.getElementById('pronounCategory').value;
            let pronounId=document.getElementById('pronounId').value;
            let tags=document.getElementById('pronoun_csdatatags').value;
            let shapesType=document.getElementById('pronounShapesType').value;
            let shapes=[];
            if (shapesType==0) shapes=[];
            else if (shapesType==1) {
                for (let i=0; i<7; i++) {
                    let textbox=document.getElementById('pronoun0'+i);
                    shapes[i]=textbox.value;
                }
            }else if (shapesType==2) {
                for (let i=0; i<14; i++) {
                    let textbox=document.getElementById('pronoun0'+i);
                    shapes[i]=textbox.value;
                }
            }else if (shapesType==3) {
                for (let g=0; g<4; g++) {
                    for (let i=0; i<14; i++) {
                        let textbox=document.getElementById('pronoun'+g+''+i);
                        shapes[g*14+i]=textbox.value;
                    }
                }
            }

            let formData = new URLSearchParams();
            formData.append('action', 'pronoun_pattern_cs_update');
            formData.append('id', pronounId);
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
                   flist_pronoun_pattern_cs.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };
        var changeVisibility = function() {
            let type=document.getElementById('pronounShapesType').value;
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
                for (let i=7; i<14; i++)document.getElementById('pronoun0'+i).style.display='none';
            }else if (type==2) {
                document.getElementById('tableGender0').style.display='block';
                document.getElementById('tableGender1').style.display='none';
                document.getElementById('tableGender2').style.display='none';
                document.getElementById('tableGender3').style.display='none';
                for (let i=7; i<14; i++)document.getElementById('pronoun0'+i).style.display='block';
            }else if (type==3) {
                document.getElementById('tableGender0').style.display='block';
                document.getElementById('tableGender1').style.display='block';
                document.getElementById('tableGender2').style.display='block';
                document.getElementById('tableGender3').style.display='block';
                for (let i=7; i<14; i++)document.getElementById('pronoun0'+i).style.display='block';
            }
        };

        var pronoun_cs_added = function() {
            flist_pronoun_pattern_cs.lastAddedId;
            pronoun_cs_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label id="name">Popis</label><br> 
                <input type="text" id="pronounLabel" for="name" value="" placeholder="kami>kama" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base">Z</label><br>
                <input type="text" id="pronounBase" for="name" value="" placeholder="kami" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label id="base">Na</label><br>
                <input type="text" id="pronounBase" for="name" value="" placeholder="kama" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label for="replaceTypeNounGender">Rod</label>
                <select id="replaceTypeNounGender" name="replaceTypeNounGender">
                    <option value="0">Jakékoliv</option>
                    <option value="1">Mužský životný</option>
                    <option value="2">Mužský neživotný</option>
                    <option value="3">Ženský</option>
                    <option value="4">Střední</option>
                </select>
                <br>
            </div>

            <div class="row section">
                <label for="replaceTypeNounGender">Číslo</label>
                <select id="replaceTypeNounGender" name="replaceTypeNounGender">
                    <option value="0">Jakékoliv</option>
                    <option value="1">jednotné</option>
                    <option value="2">množné</option>
                </select>
                <br>
            </div>

            <div class="row section">
                <label for="replaceTypeNounGender">Pád(y)</label>
                <input type="text" id="pronounLabel" for="name" value="" placeholder="7" style="max-width: 9cm;">
                <br>
            </div>
              
            <?php echo tagsEditor("replace_noun_includes", [], "Obsahuje")?>

            
            <?php echo tagsEditor("replace_noun_not_includes", [], "neobsahuje")?>

            <div> 
                <input type="hidden" id="pronounId" value="-1">
                <a onclick="currentpronounCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>