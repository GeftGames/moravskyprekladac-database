<?php
/**
 * @param $conn: connection
 * @param $name: "region", "nation" or "language"
 * @param $filter: current translate
 * @param $otherParams: custom options for placement, format: ", `str`, `str`, `str`, ..."
 * @return string
 */
function generateSelectMultipleWithSort($conn, $name, $filter, $otherParams) {
    // get all types
    $listAllTypes=[];
    {
        $sql = "SELECT `id`, `label` FROM `{$name}s`;";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $listAllTypes[] = [$row['id'], $row['label']];
            }
        }
    }

    // get placed list
    $listSelected=[];
    {
        if ($otherParams!="" && !str_starts_with($otherParams, ",")) {
            echo "Problem with '$otherParams'";
            exit;
        }
        $sql = "SELECT `id`, `{$name}_id`, `confinence`, `comment`$otherParams FROM `place_{$name}s` WHERE `translate`={$filter};";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {

                $label_id=$row[$name.'_id'];
                $label="{not found: ".$label_id."}";
                foreach ($listAllTypes as $type) {
                    if ($type[0]==$label_id){
                        $label=$type[1];
                        break;
                    }
                }
                $listSelected[]=[
                    "id"=>$row['id'],
                    "parent"=>$row[$name.'_id'],
                    "label"=>$label
                ];
            }
        }
    }

    $GLOBALS["script"].= /** @lang JavaScript */"
        function Select(e, name) {
            const value = e.getAttribute('data-value');
            
            let exists=false;
            let selectedItems=document.getElementById(name+'_selectedItems');
            for (let child of selectedItems.childNodes) {
                if (child.getAttribute('data-value') === value) {
                    exists=true;
                }
            }
            
            if (!exists) {
               // fetch rest api to add 
                fetch('index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=place_add&table='+name+'&translate={$filter}&parent='+value
                }).then(response => response.json())
                .then(json => {
                    if (json.status==='OK') {
                        let newSelectedItem=document.createElement('span');
                        newSelectedItem.setAttribute('data-id', value);// id of placed
                        newSelectedItem.setAttribute('data-parent', json.insert_id);// belongs to parent (placed region of region)
                        newSelectedItem.addEventListener('click', ()=>{
                           SelectInSelected(newSelectedItem, name); 
                        });
                        newSelectedItem.innerText=e.getAttribute('data-label');
                        selectedItems.appendChild(newSelectedItem);
                    } else console.log('error sql', json);
                });
            }            
        }
        function SelectInSelected(e, name) {
            // deselect
            let selectedItems=document.getElementById(name+'_selectedItems');
            for (let child of selectedItems.childNodes) {
                if (child.classList.contains('selected')) {
                    child.classList.remove('selected');
                }
            }
            
            // select
            e.classList.add('selected');   
        }
        
        function MoveUp(name) {
            let selectedItems = document.getElementById(name+'_selectedItems');
            let selected = selectedItems.querySelector('.selected');
            if (selected && selected.previousElementSibling) {
                selectedItems.insertBefore(selected, selected.previousElementSibling);
            }            
        }
        
        function MoveDown(name) {
            let selectedItems = document.getElementById(name+'_selectedItems');
            let selected = selectedItems.querySelector('.selected');
            if (selected && selected.nextElementSibling) {
                selectedItems.insertBefore(selected.nextElementSibling, selected);
            }
        }
                
        function Remove(name) {
            let selectedItems = document.getElementById(name+'_selectedItems');
            let selected = selectedItems.querySelector('.selected');
            if (selected) {
                selectedItems.removeChild(selected);
            }
        }
        var selectedId;
        function showOptions(name) {
            popupShow(name);
            
            // get selected item
            let selected = document.getElementById(name+'_selectedItems').querySelector('.selected');
            if (!selected) {
                alert('Vyberte prvek pro nastavení.');
                return;
            }
            selectedId=selected.getAttribute('data-id');
       
            // fetch rest api and show popup, with params $otherParams
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=place_'+name+'_item&id='+selectedId
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                    // json has confinence, comment + otherparams
                    document.getElementById(name+'comment').innerText=json.comment;
                    document.getElementById(name+'confidence').selectedIndex=json.confinence;
                    let zone_typeE=document.getElementById(name+'zone_type');
                    if (zone_type!=undefined) zone_type.selectedIndex=json.zone_type;
                   
                } else console.log('error sql', json);
            });
            
            // set div 'options_{$name}' with comment, confinence and $otherParams
            let box=document.getElementById('options_'+name);
            box.innerHTML='';
            
            // comment
            let commentLabel = document.createElement('label');
            commentLabel.innerText = 'Komentář';
            box.appendChild(commentLabel);
            
            let comment=document.createElement('textarea');
            comment.name='comment';
            box.appendChild(comment);
            
            // confinence
            let confLabel = document.createElement('label');
            confLabel.innerText = 'Pravděpodobnost';
            box.appendChild(confLabel);
            
            let confinence=document.createElement('select');
            confinence.name = 'confinence';
            for (let [text, val] of Object.entries({'neznámé': 0, 'možné': 1, 'pravděpodobné': 2, 'jisté': 3})) {
                let opt = document.createElement('option');
                opt.value = val;
                opt.text = text;
                confinence.appendChild(opt);
            }
            box.appendChild(confinence);            
        }
        
        function saveOptions(name) {
            popupClose(name);
            
            // get values
            let comment = document.getElementById(name + 'comment').innerText;
            let confinence = document.getElementById(name + 'confidence').value;
            let zone_type = document.getElementById(name + 'zone_type')?.value;
            
            if (!selectedId) return;
            
            // fetch rest api and hide popup            
            let formData = new URLSearchParams();
            formData.append('action', 'place_'+name+'_update');
            formData.append('id', selectedId);
            formData.append('comment', comment);
            formData.append('confinence', confinence);
            if (zone_type!=undefined) formData.append('zone_type', zone_type); // zone_type can be 0,1,2,3
        
            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            }).then(response => response.json())
            .then(json => {
                if (json.status==='OK') {
                   
                }else console.warn('error save option atributes', json);
            });
        }
    ";

    // -- Elements -- //
    $html="<div style='display:flex'>";

    // Left box
    $html.="<div style='width: 50%;'>";
    $html.="<strong>Všechny</strong>";
    $html.="<div id='{$name}_allItems' style='min-height: 50px; border: solid 1px gray;display: grid;overflow-y: scroll'>";
    // all items, by click move to selected box
    foreach ($listAllTypes as $item) {
        // by click move to selected if not already exists there
        $html.='<a data-value="'.$item[0]/*id*/.'" data-label="'.$item[1].'" onclick="Select(this, `'.$name.'`)">'.$item[1]/*label*/.' →</a>';
    }
    $html.="</div>";

    // btns move
    $html .= "</div>";

    // selected items
    $html.="<div style='    width: 50%;'>";
    $html.="<strong>Vybrané</strong>";
    $html.="<div>";
    // list of selected items
    $html.="<div id='{$name}_selectedItems' class='boxwithitems' style='min-height: 50px; border: solid 1px gray;display: grid;overflow-y: scroll;'>";
    foreach ($listSelected as $item) {
        $html.='<a data-id="'.$item["id"].'"  data-parent="'.$item["parent"].'"onclick="SelectInSelected(this, '.$item["label"].');">'.$item["label"].'</a>';
    }
    $html.="</div>";

    // btns move
    $html.="<div>
        <a class=\"button\" onclick=\"MoveUp('$name');\">🡩</a>
        <a class=\"button\" onclick=\"MoveDown('$name');\">🡫</a>
        <a class=\"button\" onclick=\"showOptions('$name');\">Options</a>
        <a class=\"button\" onclick=\"Remove('$name');\">Smazat</a>
    </div>";

    $html.="</div>";

    // popup
    $html.="<div id=\"popup_{$name}\" class=\"popupBackground\" style=\"display:none\">
        <div class=\"popup\">
            <div class=\"popupHeader\"><span onclick=\"popupClose('{$name}')\" class=\"popupClose\">×</span></div>
            <div class=\"popupBody\">
                <h1>Nastavení prvku</h1>
                <div id=\"options_{$name}\"></div>                
                <a class=\"button\" onclick=\"saveOptions('{$name}')\">OK</a>
            </div>
        </div>
    </div>";

    return $html;
}