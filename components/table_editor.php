<?php
/**
 * @param $tableName string - smt like id for multiple tables
 * @param $data array - basic format (for example ["cs"=>"Haná", "en"=>"Hanna", ...]), no nested or arrays! converted json
 * @param $cullums array - ["country code"=>"cs", "name"=>"Haná"] header text=>placeholder
 * @return string - html code
 */
function basicTableEditor($tableName, $data, $cullums) : string {
    // Pass column placeholders to JS as JSON
    $placeholdersJson = json_encode(array_values($cullums), JSON_UNESCAPED_UNICODE);

    // js engine
    $GLOBALS["script"].= /** @lang JavaScript */"
        // store placeholders in a JS variable for this table
        var placeholders_{$tableName} = {$placeholdersJson};

        // add row
        function addRow(tableName) {
            var table = document.getElementById(tableName).getElementsByTagName('tbody')[0];
            var tr = document.createElement('tr');
            var placeholders = window['placeholders_' + tableName];
            const cols = 2;
            for (var i = 0; i < cols; i++) {
                var td = document.createElement('td');
                var input = document.createElement('input');
                input.type = 'text';
                input.placeholder = placeholders[i];
                td.appendChild(input);
                tr.appendChild(td);
            }
            var tdAction = document.createElement('td');
            tdAction.innerHTML = '<a href=\"javascript:void(0)\" onclick=\"removeRow(this)\">Remove</a>';
            tr.appendChild(tdAction);
            table.appendChild(tr);
        }
        // remove row
        function removeRow(e) {
            var tr = e.closest('tr');
            tr.parentNode.removeChild(tr);
        } 
        // save - get json output
        function getTableData(tableName) { // call from parent form 
            var table = document.getElementById(tableName);
            var rows = table.getElementsByTagName('tbody')[0].rows;
            var obj = {};
            for (var i = 0; i < rows.length; i++) {
                var key = rows[i].cells[0].querySelector('input').value.trim();
                var val = rows[i].cells[1].querySelector('input').value.trim();
                if (key !== '') {
                    obj[key] = val;
                }
            }
            return JSON.stringify(obj);
        }
        // load json from js 
        function loadTableDataFromJson(tableName, jsondata) {
            console.log(jsondata);
            var tableBody = document.getElementById(tableName).getElementsByTagName('tbody')[0];
            tableBody.innerHTML = ''; // clear old
            var placeholders = window['placeholders_' + tableName];
            const json=JSON.parse(jsondata);
            for (const key in json) {
               // if (!jsondata.hasOwnProperty(key)) continue;
                var tr = document.createElement('tr');
                var tdKey = document.createElement('td');
                var inpKey = document.createElement('input');
                inpKey.type = 'text';
                inpKey.value = key;
                inpKey.placeholder = placeholders[0];
                tdKey.appendChild(inpKey);
                tr.appendChild(tdKey);

                var tdVal = document.createElement('td');
                var inpVal = document.createElement('input');
                inpVal.type = 'text';
                inpVal.value = json[key];
                inpVal.placeholder = placeholders[1];
                tdVal.appendChild(inpVal);
                tr.appendChild(tdVal);

                var tdAction = document.createElement('td');
                tdAction.innerHTML = '<a href=\"javascript:void(0)\" onclick=\"removeRow(this)\">Remove</a>';
                tr.appendChild(tdAction);

                tableBody.appendChild(tr);
            }
        }
    ";

    $html='<table id="'.$tableName.'"><thead><tr>';

    // caption cullumns
    foreach ($cullums as $colName=>$value) {
        $html .= '<th>'.htmlspecialchars($colName).'</th>';
    }
    $html .= '<th></th></tr></thead><tbody>';

    // table
    foreach ($data as $key=>$value) {
        $placeholders = array_values($cullums);
        $html.='
        <tr>    
            <td><input type="text" value="'.$key.'" placeholder="'.htmlspecialchars($key).'"></td>
            <td><input type="text" value="'.$value.'" placeholder=""'.htmlspecialchars($value).'></td>
            <td><a class="button" href="javascript:void(0)" onclick="removeRow(this)">Smazat</a></td>
        </tr>';
    }
    $html.="</tbody></table>";

    // add row
    $html.='<a class="button" href="javascript:void(0)" onclick="addRow(\''.$tableName.'\')" style="width: fit-content;">Přidat řádek</a>';

    // raw data output
    //$html.='<input type="hidden" id="'.$tableName.'_data">';

    return $html;
}