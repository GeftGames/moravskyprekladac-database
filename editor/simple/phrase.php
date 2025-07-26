<div class="splitView">
    <div>
        <?php
        
        include "components/tags_editor.php";
        include "components/multiple_to.php";

        tagsEditorDynamic();

        $filter=$_SESSION['translate'];
       // $order="ORDER BY LOWER(from) ASC";
        $sql="SELECT `id`, `from` FROM phrase_relations WHERE `translate`=$filter;";
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

        echo FilteredList($list, "phrase", []);

        $GLOBALS["onload"].= /** @lang JavaScript */"
        phrase_changed=function() {
            let id = flist_phrase.getSelectedIdInList();
        
            // no selected
            if (id==null) return; 
            
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=phrase_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('phraseId').value=id;
                    document.getElementById('phraseFrom').value=json.from;
                    
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

        flist_phrase.EventItemSelectedChanged(phrase_changed);
        flist_phrase.EventItemAddedChanged(phrase_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_phrase; 
        var currentphraseCSSave = function() {
            let shape_from=document.getElementById('phraseFrom').value;
            let phraseId=document.getElementById('phraseId').value;
            let tags=document.getElementById('phrasedatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'phrase_update');
            formData.append('id', phraseId);
            formData.append('shape_from', shape_from);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_phrase.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

        var phrase_added = function() {
            flist_phrase.lastAddedId;
            phrase_changed();
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div id="regionsview">
            <table>
                <div class="row section">
                    <label for="phraseFrom">Z&nbsp;</label><br>
                    <input type="text" id="phraseFrom" value="" placeholder="ušel jsi" style="max-width: 9cm;">
                </div>

                <tr>
                    <td><label for="quality">Zobrazit</label></td>
                    <td><label class="switch">
                            <input id="quality" type="checkbox">
                            <span class="slider"></span>
                        </label></td>
                </tr>

                <tr>
                    <?php echo tagsEditor("phrase", [], "Tagy");?>
                </tr>
            </table>

            <div class="section">
                <label for="phraseFrom">Na&nbsp;</label><br>
                <!-- Translate to -->
                <div>
                <?php echo multiple_to([], "phrase"); ?>
                </div>
            </div>
            <div>
                <hr>
                <input type="hidden" id="phraseId" value="-1">
                <a onclick="currentphraseCSSave()" class="button">Uložit</a>
            </div>
        </div>
    </div>
</div>