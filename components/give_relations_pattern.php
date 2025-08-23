<?php
function give_relations_pattern($conn, $tableName) : ?array {
    // relations
    $tableNameRelations=$tableName.'_relations';
    $sql="SELECT `id`, `from`, custombase FROM `$tableNameRelations` WHERE `translate` = ".$_SESSION['translate'].";";
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

    if ($sqlDone) {
        $listFrom=[];
        $listR=[];

        // list from
        while ($rowFrom = $resultFrom->fetch_assoc()) {
            $listFrom[$rowFrom["id"]]=$rowFrom["label"];
        }

        // list relations
        while ($row = $resultR->fetch_assoc()) {
            $idRelation=$row["id"];
            $idFrom=$row["from"];
            $custombase=$row["custombase"];

            // get from label
            /*$from=null;
            foreach ($listFrom as $item) {
                if ($item[0]==$idFrom) {
                    $from=$item;
                    break;
                }
            }*/
            $from = $listFrom[$idFrom] ?? null;

            if ($from!=null) {
                if ($custombase==null) $listR[]=[$idRelation, $from];
                else {
                    $base=extractBase($from);
                    $ending=substr($from, strlen($base));
                    $listR[]=[$idRelation, $custombase.$ending];
                }
            }else{
                if ($custombase==null) $listR[]=[$idRelation, "<Nepřiřazené>"];
                else $listR[]=[$idRelation, $custombase."<?>"];
            }
        }
    }else return null;
    return $listR;
}

function extractBase(string $str): string {
    preg_match('/^\p{Ll}+/u', $str, $matches);
    return $matches[0] ?? '';
}