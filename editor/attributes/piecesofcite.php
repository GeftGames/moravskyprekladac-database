<div class="splitView">
    <div>
        <?php
        
        // include "components/filter_list.php";
        include("components/param_editor.php");
        include("components/param_editor_multiple.php");

        $sql="SELECT id, label FROM piecesofcite;";
        $result = $conn->query($sql);
        $list=[];
        if (!$result) throwError("SQL error: ".$sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "piecesofcite");  

        $GLOBALS["onload"].="region_changed=function() { 
            let elementsSelected = flist_piecesofcite.getSelectedItemInList();
        
            // no selected
            if (!elementsSelected) {
                return;
            }
            //no multiple
            if (Array.isArray(elementsSelected)) return;

            let id=elementsSelected.dataset.id;console.log(id);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=pieceofcite_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                    document.getElementById('pieceofciteId').value=id;
                    document.getElementById('pieceofciteLabel').value=json.label;
                    document.getElementById('pieceofciteParent').value=json.parent;
                    document.getElementById('pieceofciteText').value=json.text;
                    
                    PELoadJSON(JSON.parse(json.cite));
                    PEMLoadJSON(JSON.parse(json.people));
                }else console.log('error sql: ', json);
            });
        };

        refreshFilteredLists();

        flist_piecesofcite.EventItemSelectedChanged(region_changed);";
    
        $GLOBALS["script"].="var flist_piecesofcite; 
        var currentPieceOfCiteSave = function() {
            let label=document.getElementById('pieceofciteLabel').value;
            let pieceofciteId=document.getElementById('pieceofciteId').value;
            let pieceofciteParent=document.getElementById('pieceofciteParent').value;
            let pieceofcitePText=document.getElementById('pieceofciteText').value;

            let paramsCite=PEGetJSON();
            console.log(paramsCite);

            let paramsPeople=PEMGetJSON();
            console.log(paramsPeople);

            let formData = new URLSearchParams();
            formData.append('action', 'pieceofcite_update');
            formData.append('id', pieceofciteId);
            formData.append('label', label);
            formData.append('cite', paramsCite);
            formData.append('people', paramsPeople);
            formData.append('parent', pieceofciteParent);
            formData.append('text', pieceofcitePText);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_piecesofcite.getSelectedItemInList().innerText=label;
                }else console.log('error currentPieceOfCiteSave: ', json);
            });
        };";
            
        ?>
    </div>
    <div class="editorView">
        <div class="editorView">
            <div class="row section">
                <label for="pieceofciteLabel">Label</label><br> 
                <input type="text" id="pieceofciteLabel" value="" placeholder="" style="max-width: 9cm;">
                <a onclick="" class="button">Sestavit</a>
            </div>
            
            <div class="row section">
                <label id="name" for="pieceofciteParent">Patří k</label><br> 
                <select id="pieceofciteParent">
                    <?php   
                        $sql="SELECT id, label FROM cites;";
                        $result = $conn->query($sql);
                        $list=[];
                        if (!$result) throwError("SQL error: ".$sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<option value='".$row["id"]."'>".$row["label"]."</option>";
                            }
                        } else {
                            echo "<option value='-1'>neexistují citace</option>";
                        }
                    ?>
                </select>
            </div>

           
            <?php echo paramEditor(
                [], 
                [
                    ["born_place",      "datum pořízení",       "",         "text",     ""], 
                    ["writer_name",     "jméno zapisovatele",   "",         "text",     ""],
                    ["strany",          "strany publikace",     "",         "text",     ""],
                    ["kapitola",        "kapitoly publikace",   "",         "text",     ""],
                    ["odkaz",           "url odkaz",            "",         "text",     ""],
                    ["cislo_periodika", "číslo periodika",      "",         "text",     ""],
                    ["ročník_periodika", "ročník periodika",    "",         "text",     ""],
                    ["rok_zapisu",      "rok zápisu",           "",         "text",     "1951"],            
                    ["mesic_zapisu",    "měsíc zápisu",         "",         "text",     "12"],            
                    ["den_zapisu",      "den zápisu",           "",         "text",     "3"],            
                    ["jmeno_zapisovatele", "jméno zapisovatele",  "",       "text",     ""],
                    ["lokalni_zapisovatel", "lokální zapisovatel","",       "boolean", ""],
                    ["lokalni_zapisovatel", "lokální zapisovatel","",       ["slovník", "rozhovor", "píseň", "báseň", "próza"], ""],
                    ["transkripce", "transkripce ukázky",       "",         ["fonetická", "česká zjednodušená", "česká obvyklá", "zpřešková"], ""],
                ],
                "pieceofsource",
                "Část zdroje"
            ); ?>
          
            <?php echo paramMultipleEditor(
                [],
                [
                    ["jmeno",           "jméno",            "",         "text",   ""], 
                    ["prijmeni",        "příjmení",         "",         "text",   ""], 
                    ["misto_narozeni",  "místo narození",   "",         "text",     "Brno"], 
                    ["mista_bydliště",  "místa bydlení",    "",         "text",     ""],
                    ["rok_narozeni",    "rok narození",     "",         "number",     ""],
                    ["vek",             "věk",              "",         "number",     "70"],
                    ["mluva",           "mluva",            "nářeční",  ["nářeční", "polonářeční"], ""],
                    ["oznaceni",        "označení",         "",        "text", "A"],
                    ["pohlavi",        "pohlavi",           "",        ["muž", "žena"], ""],
                ],
                "sample",
                "Ukázka - osoby"
            ); ?>

            <div class="section" style="width: -webkit-fill-available">
                <label for="pieceofciteText" id="name">Text ukázky</label><br>
                <textarea id="pieceofciteText" value="" placeholder="" style="max-width: calc(100% - 15px);width: 100%"></textarea>
            </div>
          

            <input type="hidden" id="pieceofciteId" value="-1">
            <a class='button' onclick="currentPieceOfCiteSave()">Uložit</a>             
        </div>
    </div>
</div>