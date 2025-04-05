<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";

        $sql="SELECT id, label, uppercase FROM noun_patterns_to WHERE `translate` = ".$_SESSION['translate'].";";
        $result = $conn->query($sql);
        $list=[];
        if (!$result) throwError("SQL error: ".$sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $uppercase=$row["uppercase"];
                $label=$row["label"];
                if ($uppercase==2) $label=ucfirst($label);
                $list[]=[$row["id"], $label];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "noun_patterns_to");

        $GLOBALS["onload"].= /** @lang JavaScript */"
noun_patterns_to_changed=function() { 
    let id = flist_noun_patterns_to.getSelectedIdInList();

    // no selected
    if (id==null) return;

    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=noun_pattern_to_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            document.getElementById('nounId').value=id;
            document.getElementById('nounLabel').value=json.label;
            document.getElementById('nounBase').value=json.base;

            if (json.gender==null) json.gender=0;
            document.getElementById('nounGender').value=json.gender;
            
            document.getElementById('nounUppercase').value=json.uppercase;

            let rawShapes=json.shapes;
            let shapes;
            if (rawShapes!=null) {
                shapes=json.shapes.split('|'); 
            } else shapes=[];
            for (let i=0; i<14; i++) {
                let shape=shapes[i];                            
                let textbox=document.getElementById('noun'+i);

                if (shape===undefined) textbox.value='';
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

flist_noun_patterns_to.EventItemSelectedChanged(noun_patterns_to_changed);
";

        $GLOBALS["script"].= /** @lang JavaScript */"
var flist_noun_patterns_to; 
var currentNounTOSave = function() {
    let label=document.getElementById('nounLabel').value;
    let base=document.getElementById('nounBase').value;
    let gender=document.getElementById('nounGender').value;
    let uppercase=document.getElementById('nounUppercase').value;
    let nounId=document.getElementById('nounId').value;
    let tags=document.getElementById('noun_todatatags').value;
    let shapes=[];
    for (let i=0; i<14; i++) {
        let textbox=document.getElementById('noun'+i);
        shapes[i]=textbox.value
    }

    let formData = new URLSearchParams();
    formData.append('action', 'noun_to_update');
    formData.append('id', nounId);
    formData.append('label', label);
    formData.append('base', base);
    formData.append('gender', gender);
    formData.append('uppercase', uppercase);
    formData.append('shapes', shapes.join('|'));
    formData.append('tags', tags);

    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK'){
           flist_noun_patterns_to.getSelectedItemInList().innerText=label;
        }else console.log('error currentRegionSave',json);
    });
};
";

?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label id="name" for="nounLabel">Popis</label><br>
                <input type="text" id="nounLabel" value="" placeholder="pohádKA">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="base" for="nounBase">Základ</label><br>
                <input type="text" id="nounBase" value="" placeholder="pohád">
            </div>

            <div class="section">
                <label id="name" for="nounShapes">Pád</label>
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

            <div class="row section">
                <label for="nounGender">Rod</label>
                <select id="nounGender" name="type">
                    <option value="0">Neznámý</option>
                    <option value="4">Střední</option>
                    <option value="3">Ženský</option>
                    <option value="2">Mužský neživotný</option>
                    <option value="1">Mužský životný</option>
                </select>
                <br>
            </div>

            <div class="row section">
                <label for="nounUppercase">Velké písmena</label>
                <select id="nounUppercase" name="uppercase">
                    <option value="0">Neznámý</option>
                    <option value="1">malé</option>
                    <option value="2">Počáteční Velké</option>
                    <option value="3">VŠECHNY VELKÉ</option>
                </select>
                <br>
            </div>

            <?php echo tagsEditor("noun_to", [], "Tagy") ?>
            <div>
                <input type="hidden" id="nounId" value="-1">
                <a onclick="currentNounTOSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>