<div class="splitView">
    <div>
        <?php
        
        // Do dashboard stuff
        include("components/param_editor.php");

        $sql="SELECT id, label FROM cites;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
        } else {
            // TODO: echo "0 results ";
        }

        echo FilteredList($list, "cites");  

        $GLOBALS["onload"].= /** @lang JavaScript */
            "
        cites_changed=function() { 
           let id = flist_cites.getSelectedIdInList();
        
            // no selected
            if (id==null) return;

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=cite_item&id=`+id
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                    document.getElementById('citeId').value=id;
                    document.getElementById('citeLabel').value=json.label;
                    
                    PELoadJSON(JSON.parse(json.params));
                }else console.log('error sql: ', json);
            });
        };

        refreshFilteredLists();

        flist_cites.EventItemSelectedChanged(cites_changed);";

    
        $GLOBALS["script"].="var flist_cites; 
        var currentciteSave = function() {
            let label=document.getElementById('citeLabel').value;
            let citeId=document.getElementById('citeId').value;

            let params=PEGetJSON();
            console.log(params);

            let formData = new URLSearchParams();
            formData.append('action', 'cite_update');
            formData.append('id', citeId);
            formData.append('label', label);
            formData.append('params', params);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status=='OK'){
                   flist_cites.getSelectedItemsInList()[0].innerText=label;
                }else console.log('error currentciteSave: ',json);
            });
        };";
            
        ?>
    </div>
    <div class="editorView">
         <div class="row section">
            <label id="name">Label</label><br> 
            <input type="text" id="citeLabel" for="name" value="" placeholder="" style="max-width: 9cm;">
            <a onclick="" class="button">Sestavit</a>
        </div>

        <div id="citesview" style="width: fit-content;">
            <?php echo paramEditor(
                [
                   // ["nazev", "Nážečí na Břeclavsku a v dolním pomoraví"], 
                   // ["jmeno", "František"], 
                   // ["prijmeni", "Svěrák"], 
                ],
                [ //[save code,     label,         default,     type,       example]
                    ["typ",         "typ",     "kniha",         ['kniha', 'periodikum', 'web', 'sncj'],   "kniha"],

                    ["nazev",       "název",        "",         "text",     "Nářečí na Břeclavsku a v dolním..."], 
                    ["podnazev",    "podnázev",     "",         "text",     ""],
                    ["periodikum",  "periodikum",   "",         "text",     ""],

                    ["titul_pred",  "titul před jménem",        "",         "text",     "Ph. Dr."], 
                    ["jmeno",       "jméno",        "",         "text",     "František"], 
                    ["prijmeni",    "příjmení",     "",         "text",     "Svěrák"], 
                    ["titul_za",    "titul za jménem","",       "text",     "Ph. Dr."], 
                    ["spoluautori", "spoluautoři",  "",         "text",     "PRIJMENI, Jmeno; PRIJMENI, Jmeno"], 
                    ["spolecnost",  "společnost",   "",         "text",     "Masarykova universita"],
            
                    ["vydani",      "číslo vydání", "",         "number",   "1"], 
                    ["rok_vydani",  "rok pořízení", "",         "number",   "1900"], 
                    ["vydavatel",   "vydavatel",    "",         "text",     "Nakladatelství XX"], 
                    ["misto",       "místo vydání", "",         "text",     ""],
                    ["i",           "sncj misto",   "",         "text",     ""],
            
                    ["odkaz",       "url odkaz",    "",         "text",      "https://..."], 
                    ["format",      "formát",       "online",   "text",     "online"], 
            
                    ["legalnost",   "legálnost",    true,       "checkbox",  true        ],
                    ["licence",     "licence",      "",         "text",     "Volné dílo"],
            
                    ["kapitola",    "kapitola",     "",         "text",     ""],
                    ["strany",      "strany",       "",         "text",     ""],
            
                    ["issn",        "issn",         "",         "text",     ""],
                    ["ibsn",        "ibsn",         "",         "text",     ""],

                    ["cislo",       "číslo",        "",         "text",     ""],
                    ["rocnik",      "ročník",       "",         "text",     ""],

                    ["poznámka",    "poznámka",     "",        "text",     ""],

                    ["rok_pristupu","rok přístupu", "",         "number",   ""],
                    ["mesic_pristupu","měsíc přístupu", "",     "number",   ""],
                    ["den_pristupu","den přístupu", "",         "number",   ""],
                ],
                "cite",
                "Data citace"
            ); ?>
            
            <input type="hidden" id="citeId" value="-1">
            <a class='button' onclick="currentciteSave()">Uložit</a>
        </div>
    </div>
</div>