<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";

        $filter=$_SESSION['translate'];
        $sql="SELECT id, label FROM replaces_defined_verb WHERE translate=$filter;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        }

        echo FilteredList($list, "replace_defined_verb", [], $filter);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        replace_defined_verb_changed=function() { 
            let elementsSelected = flist_replace_defined_verb.getSelectedItemInList();
        
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
                if (json.status==='OK') {
                    document.getElementById('verbId').value=id;
                    document.getElementById('verbLabel').value=json.label;
                    document.getElementById('verbBase').value=json.base;

                    if (json.category==null) json.category=0;
                    document.getElementById('verbCategory').value=json.category; 

                    // shapes
                    let shapes;
                    if (json.shapes!=null) shapes=json.shapes.split('|'); 
                    else shapes=[];

                    for (let g=0; g<4; g++) {
                        for (let i=0; i<14; i++) {
                            let shape=shapes[g*14+i];                            
                            let textbox=document.getElementById('verb'+g+''+i);

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

                    document.getElementById('verbShapesType').value=shapesType;
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

        flist_replace_defined_verb.EventItemSelectedChanged(replace_defined_verb_changed);
        flist_replace_defined_verb.EventItemAddedChanged(replace_defined_verb_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_verb_replace_def; 
        var currentreplace_defined_verbSave = function() {
            let id=document.getElementById('verbId').value;
            let source=document.getElementById('verbReplaceFrom').value;
            let to=document.getElementById('verbReplaceTo').value;
            
            let tags_includeds=document.getElementById('replace_verb_includes').value;
            let tags_not_includeds=document.getElementById('replace_verb_not_includes').value;
            
            let shapesType=document.getElementById('verbShapesType').value;
            let person=document.getElementById('verbReplacePerson').value;
           
            let label=source+'>'+to;
           
            let formData = new URLSearchParams();
            formData.append('action', 'replaces_defined_verb_update');
            formData.append('id', id);
            formData.append('source', source);
            formData.append('to', to);
            formData.append('tags_includes', tags_includeds);
            formData.append('tags_not_includes', tags_not_includeds);
            formData.append('shapes', shapes.join('|'));

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_verb_replace_def.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };
        var changeVisibility = function() {
            let type=document.getElementById('verbShapesType').value;
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
                for (let i=7; i<14; i++)document.getElementById('verb0'+i).style.display='none';
            }else if (type==2) {
                document.getElementById('tableGender0').style.display='block';
                document.getElementById('tableGender1').style.display='none';
                document.getElementById('tableGender2').style.display='none';
                document.getElementById('tableGender3').style.display='none';
                for (let i=7; i<14; i++)document.getElementById('verb0'+i).style.display='block';
            }else if (type==3) {
                document.getElementById('tableGender0').style.display='block';
                document.getElementById('tableGender1').style.display='block';
                document.getElementById('tableGender2').style.display='block';
                document.getElementById('tableGender3').style.display='block';
                for (let i=7; i<14; i++)document.getElementById('verb0'+i).style.display='block';
            }
        };
        
        var changeVerbType = function() {
            let type=document.getElementById('replaceTypeVerb');
            
            let number=document.getElementById('rowReplaceTypeVerbNumber');
            let person=document.getElementById('rowReplaceTypeVerbPerson');
            let gender=document.getElementById('rowReplaceTypeVerbGender');
            let trans  =document.getElementById('rowReplaceTypeVerbTrans');
            
            let valType=type.value;
            // infinive
            if (valType==='1') {
                number.style.display='none';
                person.style.display='none';
                gender.style.display='none';
                trans.style.display='none';
            // přit + bud
            }else if (valType==='2' || valType==='3') {
                number.style.display='table-row';
                person.style.display='table-row';
                gender.style.display='none';
                trans.style.display='none';
            // rozkazovaci
            }else if (valType==='4') {
                number.style.display='table-row';
                person.style.display='table-row';
                gender.style.display='none';
                trans.style.display='none';
            // min
            }else if (valType==='5' || valType==='6') {
                number.style.display='table-row';
                person.style.display='none';
                gender.style.display='table-row';
                trans.style.display='none';
            // přech
            }else if (valType==='7' || valType==='8') {
                number.style.display='none';
                person.style.display='none';
                gender.style.display='none';
                trans.style.display='table-row';
            // podmi
            }else if (valType==='9' || valType==='10') {
                number.style.display='table-row';
                person.style.display='none';
                gender.style.display='table-row'; 
                trans.style.display='none';
            }else {
                number.style.display='none';
                person.style.display='none';
                gender.style.display='none';
                trans.style.display='none';
               // console.warn('Unknown valType: '+valType);                
            }
        }

        var verb_cs_added = function() {
            flist_verb_replace_def.lastAddedId;
            verb_cs_changed();
        }";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <table>
                <tr>
                    <td><label id="base" for="verbReplaceFrom">Z</label</td>
                    <td><input type="text" id="verbReplaceFrom" value="" placeholder="dí" style="max-width: 9cm;"></td>
                </tr>

                <tr>
                    <td><label id="base" for="verbReplaceTo">Na</label></td>
                    <td><input type="text" id="verbReplaceTo" value="" placeholder="ďijú" style="max-width: 9cm;"></td>
                </tr>

                <tr>
                    <td><label for="replaceTypeVerb">Tvar</label></td>
                    <td><select id="replaceTypeVerb" onchange="changeVerbType()">
                        <option value="0">Jakékoliv</option>
                        <option value="1">Infinitiv</option>
                        <option value="2">Přítomný</option>
                        <option value="3">Budoucí</option>
                        <option value="4">Rozkazovací</option>
                        <option value="5">Minulý činný</option>
                        <option value="6">Minulý trpný</option>
                        <option value="7">Přechodník přit</option>
                        <option value="8">Přechodnik min</option>
                        <option value="9">Podmiňovací přit</option>
                        <option value="9">Podmiňovací min</option>
                    </select></td>
                </tr>

                <tr id="rowReplaceTypeVerbNumber">
                    <td><label for="replaceTypeVerbNumber">Číslo</label></td>
                    <td><select id="replaceTypeVerbNumber">
                        <option value="0">Jakékoliv</option>
                        <option value="1">Jednotné</option>
                        <option value="2">Množné</option>
                    </select>
                    </td>
                </tr>

                <tr id="rowReplaceTypeVerbPerson">
                    <td><label for="replaceTypeVerbPerson">Osoba(y)</label></td>
                    <td><select id="replaceTypeVerbPerson" style="max-width: 9cm;">
                        <option value="0">Jakékoliv</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select></td>
                </tr>

                <tr id="rowReplaceTypeVerbGender">
                    <td><label for="replaceTypeVerbGender">Rod(y)</label></td>
                    <td>
                        <select id="replaceTypeVerbGender" placeholder="3" style="max-width: 9cm;">
                            <option value="0">Nenastaveno</option>
                            <option>Střední</option>
                            <option>Ženský</option>
                            <option>Mužský živ</option>
                            <option>Mužský než</option>
                        </select>
                    </td>
                </tr>
                <tr id="rowReplaceTypeVerbTrans">
                    <td><label for="replaceTypeVerbTrans">Rod(y) podm</label></td>
                    <td>
                        <select id="replaceTypeVerbTrans" placeholder="3" style="max-width: 9cm;">
                            <option value="0">Nenastaveno</option>
                            <option value="1">Střední+Ženský</option>
                            <option value="2">Mužský</option>
                            <option value="3">Množné</option>
                        </select>
                    </td>
                </tr>

                <?php echo tagsEditor("replace_verb_includes", [], "Obsahuje")?>

                <?php echo tagsEditor("replace_verb_not_includes", [], "neobsahuje")?>

            </table>

            <div> 
                <input type="hidden" id="verbId" value="-1">
                <a onclick="currentreplace_defined_verbSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>