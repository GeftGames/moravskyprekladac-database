<div class="splitView">
    <div>
        <?php
        // Do dashboard stuff
        include "components/select_cites.php";
        
        $order="ORDER BY LOWER(label) ASC";
        $filter=$_SESSION['translate'];
        $sql="SELECT id, label FROM replaces_inside WHERE `translate`=$filter $order;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "replaces_inside", [], $filter);

        $GLOBALS["onload"].= /** @lang JavaScript */"
replaces_inside_changed=function() { 
    let elementsSelected = flist_replaces_inside.getSelectedItemInList();

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
        body: `action=replace_inside_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            document.getElementById('replaces_insideId').value=id;
            document.getElementById('replaces_insideSource').value=json.from;
            document.getElementById('replaces_insideTo').value=json.to;
        } else console.log('error sql', json);
    });
};

refreshFilteredLists();

flist_replaces_inside.EventItemSelectedChanged(replaces_inside_changed);
flist_replaces_inside.EventItemAddedChanged(replaces_inside_added);
";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
var flist_replacesinside; 
var currentreplaces_insideCSSave = function() {
   // let label=document.getElementById('replaces_insideLabel').value;
    let source=document.getElementById('replaces_insideFrom').value;
    let to=document.getElementById('replaces_insideTo').value;
    let replaces_insideId=document.getElementById('replaces_insideId').value;
   
    let formData = new URLSearchParams();
    formData.append('action', 'replaces_inside_update');
    formData.append('id', replaces_insideId);
    formData.append('from', source);
    formData.append('to', to);
    formData.append('cites', cites);

    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK'){
           flist_replace_inside.getSelectedItemInList().innerText=label;
        }else console.log('error currentRegionSave',json);
    });
};

var replaces_inside_added = function() {
    flist_replaces_inside.lastAddedId;
    replaces_inside_changed();
};";
        ?>
    </div>
    <div class="editorView">
        <table id="regionsview" class="section row">
          <!--  <tr class="section">
                <td><label id="name" for="replaces_insideLabel">Popis</label><br></td>
                <td style="display: flex;">
                    <input type="text" id="replaces_insideLabel" value="" placeholder="niKDO" style="max-width: 9cm;">
                    <a onclick="" class="button">Sestavit</a>
                </td>
            </tr>-->

            <tr>
                <td><label id="source" for="replaces_insideSource">Z</label></td>
                <td><input type="text" id="replaces_insideSource" value="" placeholder="ni" style="max-width: 9cm;"></td>
            </tr>

            <tr>
                <td><label id="to" for="replaces_insideTo">Na</label></td>
                <td><input type="text" id="replaces_insideTo" value="" placeholder="ni" style="max-width: 9cm;"></td>
            </tr>
        </table>

        <div>
        <?php echo selectCites([]) ?>

        <div class="section row">
            <input type="hidden" id="replaces_insideId" value="-1">
            <a onclick="currentreplaces_insideCSSave()" class="button">Uložit</a>
        </div>
    </div>
</div>