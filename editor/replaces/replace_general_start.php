<div class="splitView">
    <div>
        <?php
        // Do dashboard stuff
        include "components/select_cites.php";
        $filter=$_SESSION['translate'];
        $order="ORDER BY LOWER(label) ASC";
        $sql="SELECT id, label FROM replaces_start WHERE `translate`=$filter $order;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        }

        echo FilteredList($list, "replaces_start", [], $filter);

        $GLOBALS["onload"].= /** @lang JavaScript */"
replaces_start_changed=function() { 
    let elementsSelected = flist_replaces_start.getSelectedItemInList();

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
        body: `action=replace_start_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            document.getElementById('replaces_startId').value=id;
            document.getElementById('replaces_startSource').value=json.from;
            document.getElementById('replaces_startTo').value=json.to;
        } else console.log('error sql', json);
    });
};

refreshFilteredLists();

flist_replaces_start.EventItemSelectedChanged(replaces_start_changed);
flist_replaces_start.EventItemAddedChanged(replaces_start_added);
";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
var flist_replacesstart; 
var currentreplaces_startCSSave = function() {
   // let label=document.getElementById('replaces_startLabel').value;
    let source=document.getElementById('replaces_startFrom').value;
    let to=document.getElementById('replaces_startTo').value;
    let replaces_startId=document.getElementById('replaces_startId').value;
   
    let formData = new URLSearchParams();
    formData.append('action', 'replaces_start_update');
    formData.append('id', replaces_startId);
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
           flist_replace_start.getSelectedItemInList().innerText=label;
        }else console.log('error currentRegionSave',json);
    });
};

var replaces_start_added = function() {
    flist_replaces_start.lastAddedId;
    replaces_start_changed();
};";
        ?>
    </div>
    <div class="editorView">
        <table id="regionsview" class="section row">
          <!--  <tr class="section">
                <td><label id="name" for="replaces_startLabel">Popis</label><br></td>
                <td style="display: flex;">
                    <input type="text" id="replaces_startLabel" value="" placeholder="niKDO" style="max-width: 9cm;">
                    <a onclick="" class="button">Sestavit</a>
                </td>
            </tr>-->

            <tr>
                <td><label id="source" for="replaces_startSource">Z</label></td>
                <td><input type="text" id="replaces_startSource" value="" placeholder="ni" style="max-width: 9cm;"></td>
            </tr>

            <tr>
                <td><label id="to" for="replaces_startTo">Na</label></td>
                <td><input type="text" id="replaces_startTo" value="" placeholder="ni" style="max-width: 9cm;"></td>
            </tr>
        </table>

        <div>
        <?php echo selectCites([]) ?>

        <div class="section row">
            <input type="hidden" id="replaces_startId" value="-1">
            <a onclick="currentreplaces_startCSSave()" class="button">Uložit</a>
        </div>
    </div>
</div>