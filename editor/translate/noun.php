 <div class="splitView">
    <div>
        <?php
        $sql="SELECT shape_from FROM simpleword_relations LIMIT 30;";
        $result = $conn->query($sql);
        $list=[];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $list[]=$row["shape_from"];
            }
        } else {
            echo "0 results ";
        }

        echo FilteredList($list, "noun simpleword_relation");                 
        ?>
        <a>Smazat</a>
        <a>Přidat</a>
        Přidat z internetu
        <a>Duplikovat</a>
        <a>Setřídit ABC</a>
    </div>
    <div class="editorView">
        <div id="noun" style="display:none">
            <div class="row">
                <label id="name">Název</label>
                <input type="text" for="name">
            </div>

            <div>
                <label id="name">Pád</label>
                <table>
                    <tr>
                        <td class="tableHeader">Pád</td>
                        <td class="tableHeader">Jednotné</td>
                        <td class="tableHeader">Množné</td>
                    </tr>
                <?php 
                $html="";
                for ($i=0; $i<7; $i++) {
                    $html.="<tr><td>".($i+1).".</td>";
                    for ($j=0; $j<2; $j++) $html.="<td><input type='text'></td>";
                    $html.="</tr>";
                }
                echo $html;
                ?> 
                </table>
            </div>

            <div>
                <label id="name">Info</label>
                <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                <p>"?" Neznámý tvar</p>
                <p>"-" Neexistuje tvar</p>
            </div>
        </div>
    </div>
</div>