<div class="splitView">
    <div>
        <?php
        // Do dashboard stuff
        include "components/tags_editor.php";
        include "components/select_cites.php";
        
        $order="ORDER BY LOWER(label) ASC";
        $sql="SELECT id, label FROM replaces_start $order;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "replaces_start");

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
            document.getElementById('replaces_startLabel').value=json.label;
            document.getElementById('replace_source').value=json.source;
            document.getElementById('replace_to').value=json.to;
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
    let label=document.getElementById('replaces_startLabel').value;
    let base=document.getElementById('replaces_startBase').value;
    let category=document.getElementById('replaces_startCategory').value;
    let replaces_startId=document.getElementById('replaces_startId').value;
    let tags=document.getElementById('replaces_startdatatags').value;
    let cites=document.getElementById('replaces_startCites').value;
   
    let formData = new URLSearchParams();
    formData.append('action', 'replaces_start_update');
    formData.append('id', replaces_startId);
    formData.append('label', label);
    formData.append('source', source);
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
        <div id="regionsview">
            <div class="row section">
                <label id="name" for="replaces_startLabel">Popis</label><br>
                <input type="text" id="replaces_startLabel" value="" placeholder="niKDO" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>

            <div class="row section">
                <label id="source" for="replaces_startSource">Z</label><br>
                <input type="text" id="replaces_startSource" value="" placeholder="ni" style="max-width: 9cm;">
            </div>

            <div class="row section">
                <label id="to" for="replaces_startTo">Na</label><br>
                <input type="text" id="replaces_startTo" value="" placeholder="ni" style="max-width: 9cm;">
            </div>

            <?php echo selectCites([]) ?>
       
            <div> 
                <input type="hidden" id="replaces_startId" value="-1">
                <a onclick="currentreplace_startCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>