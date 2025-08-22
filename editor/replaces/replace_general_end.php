<div class="splitView">
    <div>
        <?php
        // Do dashboard stuff
        include "components/select_cites.php";

        $filter=$_SESSION['translate'];
      //  $order="ORDER BY LOWER(label) ASC";
        $sql="SELECT `id`, `label` FROM `replaces_end` WHERE `translate`=$filter;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        }

        echo FilteredList($list, "replaces_end", ["def noun"=>"sendToDefNoun()", "def adj"=>"sendToDefAdj()", "def verb"=>"sendToDefVerb()"], $filter);

        $GLOBALS["onload"].= /** @lang JavaScript */"
replaces_end_changed=function() { 
    let elementsSelected = flist_replaces_end.getSelectedItemInList();

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
        body: `action=replace_end_item&id=`+id
    }).then(response => response.json())
    .then(json => {
        if (json.status==='OK') {
            document.getElementById('replaces_endId').value=id;
            document.getElementById('replaces_endSource').value=json.from;
            document.getElementById('replaces_endTo').value=json.to;
        } else console.log('error sql', json);
    });
};

refreshFilteredLists();

flist_replaces_end.EventItemSelectedChanged(replaces_end_changed);
flist_replaces_end.EventItemAddedChanged(replaces_end_added);
";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
var flist_replacesend; 
var currentreplaces_endCSSave = function() {
   // let label=document.getElementById('replaces_endLabel').value;
    let source=document.getElementById('replaces_endFrom').value;
    let to=document.getElementById('replaces_endTo').value;
    let replaces_endId=document.getElementById('replaces_endId').value;
   
    let formData = new URLSearchParams();
    formData.append('action', 'replaces_end_update');
    formData.append('id', replaces_endId);
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
           flist_replace_end.getSelectedItemInList().innerText=label;
        }else console.log('error currentRegionSave',json);
    });
};

var replaces_end_added = function() {
    flist_replaces_end.lastAddedId;
    replaces_end_changed();
};";
        ?>
    </div>
    <div class="editorView">
        <table id="regionsview" class="section row">
          <!--  <tr class="section">
                <td><label id="name" for="replaces_endLabel">Popis</label><br></td>
                <td style="display: flex;">
                    <input type="text" id="replaces_endLabel" value="" placeholder="niKDO" style="max-width: 9cm;">
                    <a onclick="" class="button">Sestavit</a>
                </td>
            </tr>-->

            <tr>
                <td><label id="source" for="replaces_endSource">Z</label></td>
                <td><input type="text" id="replaces_endSource" value="" placeholder="ni" style="max-width: 9cm;"></td>
            </tr>

            <tr>
                <td><label id="to" for="replaces_endTo">Na</label></td>
                <td><input type="text" id="replaces_endTo" value="" placeholder="ni" style="max-width: 9cm;"></td>
            </tr>
        </table>

        <div>
            <?php echo selectCites([]) ?>

        <div class="section row">
            <input type="hidden" id="replaces_endId" value="-1">
            <a onclick="currentreplaces_endCSSave()" class="button">Uložit</a>
        </div>
    </div>
</div>