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

        echo FilteredList($list, "cites", ["Sloučit s..."=>"merge()"], null);

        $GLOBALS["onload"].= /** @lang JavaScript */"            
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
                    document.getElementById('citeType').value=json.type;
                    
                    PELoadJSON(JSON.parse(json.params));
                }else console.log('error sql: ', json);
            });
        };

        refreshFilteredLists();

        flist_cites.EventItemSelectedChanged(cites_changed);";

    
        $GLOBALS["script"].= /** @lang JavaScript */"
        var flist_cites; 
        var currentciteSave = function() {
            let label=document.getElementById('citeLabel').value;
            let type=document.getElementById('citeType').value;
            let citeId=document.getElementById('citeId').value;

            let params=PEGetJSON();
            console.log(params);

            let formData = new URLSearchParams();
            formData.append('action', 'cite_update');
            formData.append('id', citeId);
            formData.append('label', label);
            formData.append('type', type);
            formData.append('params', params);

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK'){
                   flist_cites.getSelectedItemsInList()[0].innerText=label;
                }else console.warn('error currentciteSave: ',json);
            });
        };
        
        function merge() {
            // selected container
            let elementsSelected = flist_cites.getSelectedItemsInList();
    
            // no selected
            if (!elementsSelected) {
                return;
            }
            document.getElementById('mergeName').innerText=elementsSelected[0].innerText;
            document.getElementById('mergeId').value=elementsSelected[0].dataset.id;
    
            // merge with
            let mergeWith=document.getElementById('mergeWith');
            let list=flist_cites.list;
            for (let item of list) {
                let option=document.createElement('option');
                option.value=item[0];
                option.innerText=item[1];
                console.log(option);
                mergeWith.appendChild(option);
            }
    
            popupShow('merge');
        }

        // send merge request
        function mergeSubmit() {
            let mergeId=document.getElementById('mergeId').value;
            let mergeWith=document.getElementById('mergeWith').value;
    
            // useless
            if (mergeId===mergeWith) {
                popupClose('merge');
                return;
            }
    
            let formData = new URLSearchParams();
            formData.append('action', 'pieceofcite_merge');
            formData.append('current', mergeId);
            formData.append('with', mergeWith);
    
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    // update list
                    flist_cites.list_remove();
                  //  let jsonList = JSON.parse(json.list);
                  //  flist_cites.generateList(jsonList);
                }else console.warn('warn currentciteSave: ',json);
            }).then(text => console.error('error currentciteSave: ',text));
            popupClose('merge');
        }
        ";?>

    </div>
    <div class="editorView">
         <div class="row section">
            <label id="name" for="citeLabel">Popiska</label><br>
            <input type="text" id="citeLabel" value="" placeholder="" style="max-width: 9cm;">
            <a onclick="" class="button">Sestavit</a>
        </div>

        <div class="row section">
            <label id="name" for="citeType">Typ</label><br>
            <select id="citeType">
                <option value="0">{nevastaveno}</option>
                <option value="1">kniha</option>
                <option value="2">web</option>
                <option value="3">sncj</option>
                <option value="4">periodikum</option>
            </select>
        </div>

        <div id="citesview" style="width: fit-content;">
            <?php echo paramEditor(
                [
                   // ["nazev", "Nážečí na Břeclavsku a v dolním pomoraví"], 
                   // ["jmeno", "František"], 
                   // ["prijmeni", "Svěrák"], 
                ],
                [ //[save code,     label,         default,     type,       example]
                   // ["typ",         "typ",     "kniha",         ['kniha', 'periodikum', 'web', 'sncj'],   "kniha"],

                    ["nazev",       "název",        "",         "text",     "Nářečí na Břeclavsku a v dolním..."], 
                    ["podnazev",    "podnázev",     "",         "text",     ""],
                    ["periodikum",  "periodikum",   "",         "text",     ""],
                    ["dil",         "díl",   "",         "text",     ""],

                    ["titul_pred",  "titul před jménem",        "",         "text",     "Ph. Dr."], 
                    ["jmeno",       "jméno",        "",         "text",     "František"], 
                    ["prijmeni",    "příjmení",     "",         "text",     "Svěrák"],
                    ["autor",       "autor",       "",         "text",     "František Svěrák"],
                    ["titul_za",    "titul za jménem","",       "text",     "Ph. Dr."],
                    ["spoluautori", "spoluautoři",  "",         "text",     "PRIJMENI, Jmeno; PRIJMENI, Jmeno"], 
                    ["spolecnost",  "společnost",   "",         "text",     "Masarykova universita"],
            
                    ["vydani",      "číslo vydání", "",         "number",   "1"], 
                    ["rok_vydani",  "rok pořízení", "",         "number",   "1900"], 
                    ["vydavatel",   "vydavatel",    "",         "text",     "Nakladatelství XX"], 
                    ["misto",       "místo vydání", "",         "text",     ""],
                    ["i",           "sncj misto",   "",         "text",     ""],
            
                    ["odkaz",       "url odkaz",    "",         "url",      "https://..."],
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
                    ["shortcut",    "zkratka",      "",         "text",   ""],

                    ["nazev_webu",  "název webu",      "",         "text",   ""],
                ],
                "cite",
                "Data citace"
            ); ?>
            
            <input type="hidden" id="citeId" value="-1">
            <a class='button' onclick="currentciteSave()">Uložit</a>
        </div>
    </div>
</div>

<div id="popup_merge" class="popupBackground" style="display: none">
    <div class="popup"'>
        <div class="popupHeader">Sloučit<span onclick="popupClose('merge')" class="popupClose">×</span></div>
        <div class="popupBody">
            <div class="section">
                <span id="mergeName" style="font-style: italic;">?</span>
                <label id="mergeWithName" for="mergeWith"> sloučit s </label>
                <select id="mergeWith"></select>
                <input type="hidden" id="mergeId">
            </div>
            <button onclick="mergeSubmit()">Provést</button>
        </div>
    </div>
</div>