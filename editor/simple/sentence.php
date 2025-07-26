<div class="splitView">
    <div>
        <?php
        include "components/tags_editor.php";
        include "components/multiple_to.php";

        tagsEditorDynamic();

        // Do dashboard stuff
        $filter=$_SESSION['translate'];
        //$order="ORDER BY LOWER(from) ASC";
        $sql="SELECT `id`, `from` FROM `sentence_relations` WHERE `translate`=$filter;";
        $result = $conn->query($sql);
        if (!$result) echo "ERROR: ".$conn->error;

        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["from"]];
            }
        } else {
            //echo "0 results";
        }

        echo FilteredList($list, "sentence", []);
        
        $GLOBALS["onload"].= /** @lang JavaScript */"
        sentences_changed=function() { 
           /* let elementsSelected = flist_sentence.getSelectedItemInList();
        
            // no selected
            if (!elementsSelected) {
                return;
            }
            //no multiple
            if (Array.isArray(elementsSelected)) return;

            let id=elementsSelected.dataset.id;*/
            let id = flist_sentence.getSelectedIdInList();
        
            // no selected
            if (id==null) return; 

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=sentence_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    document.getElementById('sentenceId').value=id;
                    document.getElementById('sentenceFrom').value=json.from;

                    to_load(json.to);//JSON.parse(
                    
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

        flist_sentence.EventItemSelectedChanged(sentences_changed);
        flist_sentence.EventItemAddedChanged(sentence_added);";
    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_sentence; 
        var currentsentencesCSSave = function() {
            let label=document.getElementById('sentencesLabel').value;
            let sentenceId=document.getElementById('sentenceId').value;
            let tags=document.getElementById('sentencesdatatags').value;
           
            let formData = new URLSearchParams();
            formData.append('action', 'sentences_update');
            formData.append('id', sentenceId);
            formData.append('label', label);
            formData.append('tags', tags);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_sentence.getSelectedItemInList().innerText=label;
                }else console.log('error currentRegionSave',json);
            });
        };

        var sentence_added = function() {
            flist_sentence.lastAddedId;
            sentences_changed();
        };
        ";
            
        ?>
    </div>
    <div class="editorView">
        <table>
            <tr>
                <td><label for="sentenceFrom">Z</label></td>
                <td><input type="text" id="sentenceFrom"></td>
            </tr>

            <tr>
                <?php echo tagsEditor("sentence", [], "Tagy");?>
            </tr>


            <tr>
                <td><label for="name">Na</label></td>
        </table>
        <div>
                <div><?php echo multiple_to([], "sentence"); ?></div>
            </div>
<hr>
        <div>
            <input type="hidden" id="sentenceId" value="-1">
            <a onclick="currentphraseCSSave()" class="button">Uložit</a>
        </div>

            <div style="color: gray">
                <label for="name">Info</label>
                <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                <p>"?" Neznámý tvar</p>
                <p>"-" Neexistuje tvar</p>
            </div>
        </div>
    </div>
</div>