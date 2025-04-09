<?php
function give_relations($conn, $tableName) : ?array {
    // relations
    $tableNameRelations=$tableName.'_relations';
    $sql="SELECT `id`, `from` FROM `$tableNameRelations` WHERE `translate` = ".$_SESSION['translate'].";";
    $sqlDone=true;
    $resultR = $conn->query($sql);
    if (!$resultR) {
        throwError("SQL error: ".$sql);
        $sqlDone=false;
    }

    // from
    $tableNameFrom=$tableName.'_patterns_cs';
    $sqlFrom="SELECT `id`, `label` FROM `$tableNameFrom`;";
    $resultFrom = $conn->query($sqlFrom);
    if (!$resultFrom) {
        $sqlDone=false;
        throwError("SQL error: ".$sqlFrom);
    }

    $listFrom=[];
    $listR=[];

    if ($sqlDone) {
        // list from
        while ($rowFrom = $resultFrom->fetch_assoc()) {
            $listFrom[]=[$rowFrom["id"], $rowFrom["label"]];
        }

        // list relations
        while ($row = $resultR->fetch_assoc()) {
            $idRelation=$row["id"];
            $idFrom=$row["from"];

            // get from label
            $from=null;
            foreach ($listFrom as $item) {
                if ($item[0]==$idFrom) {
                    $from=$item;
                    break;
                }
            }

            if ($from!=null) {
                $listR[]=[$idRelation, $from[1]];
            }else{
                $listR[]=[$idRelation, "<Nepřiřazené>"];
            }
        }
    }else return null;
    return $listR;
}