<?php

function loadFromSQL($arr_select, $table, $filter) {
    $sel= implode("`, `", $arr_select);
    $filter2=" ".$filter;
    if (strlen($filter)>0) $filter2=" WHERE $filter";

    $sql="SELECT `$sel` FROM $table$filter2;";

    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $result = $conn->query($sql);

    $ret=[];
    if (!$result) throwError("SQL error: ".$sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $item=[];
            if ($row["text"]!=null) {
                foreach ($arr_select as $item) {
                    $item[]=$row["text"];
                }
            }
            $ret[]=$item;
        }
    }
    return $ret;
}