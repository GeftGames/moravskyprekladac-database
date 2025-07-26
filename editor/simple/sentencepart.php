<div class="splitView">
    <div>
        <?php
        
        include "components/tags_editor.php";
        include "components/multiple_to.php";

        tagsEditorDynamic();

        $filter=$_SESSION['translate'];
       // $order="ORDER BY LOWER(from) ASC";
        $sql="SELECT `id`, `from` FROM sentencepart_relations WHERE `translate`=$filter;";
        $result = $conn->query($sql);
        if (!$result) echo "ERROR: ".$conn->error;

        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["from"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "sentencepart", []);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        sentencepart_changed=function() { 
            let elementsSelected = flist_sentencepart.getSelectedItemInList();
        
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
                body: `action=sentencepart_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('sentencepartId').value=id;
                    document.getElementById('sentencepartFrom').value=json.from;
                    to_load(json.to);
                    
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

        flist_sentencepart.EventItemSelectedChanged(sentencepart_changed);
        flist_sentencepart.EventItemAddedChanged(sentencepart_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_sentencepart; 
        var currentsentencepartCSSave = function() {
            let shape_from=document.getElementById('sentencepartFrom').value;
            let sentencepartId=document.getElementById('sentencepartId').value;
            let tags=document.getElementById('sentencepartdatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'sentencepart_update');
            formData.append('id', sentencepartId);
            formData.append('shape_from', shape_from);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_sentencepart.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

        var sentencepart_added = function() {
            flist_sentencepart.lastAddedId;
            sentencepart_changed();
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <div class="row section">
                <label for="sentencepartFrom" id="name">Z&nbsp;</label><br>
                <input type="text" id="sentencepartFrom" value="" placeholder="ušel jsi" style="max-width: 9cm;">
            </div>
            <?php echo tagsEditor("sentencepart", [], "Tagy");?>

            <!-- Translate to -->
                <label for="sentencepartFrom" id="name">Na&nbsp;</label><br>
            <?php echo multiple_to([], "sentencepart"); ?>

            <hr>
            <div> 
                <input type="hidden" id="sentencepartId" value="-1">
                <a onclick="currentsentencepartCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>