<?php
function throwInfo($string):void {
    if (!isset($_SESSION["error"])) $_SESSION["error"]="";
    $_SESSION["info"].="<p>".$string."<p>";
}

function sqlError($sql, $conn) :void{
    $maxLen=50;
    $len=strlen($sql);
    if ($len>$maxLen)$sql=substr($sql,0,$maxLen)."...";

    if (!isset($_SESSION["error"])) $_SESSION["error"]="";
    $_SESSION["error"].="<p class='error'>Problém s SQL: ".$sql."; ".$conn->error."</p>";
    die();
}

function throwError($string) :void{
    if (!isset($_SESSION["error"])) $_SESSION["error"]="";
    $_SESSION["error"].=$string;
    echo "<p>$string</p>";
    die();
}

function sql_get($conn, $tablename, $columns, $where) {
    // columns
    $cols = implode(", ", array_map(function($col) {
        return "`" . addslashes($col) . "`";
    }, $columns));

    // where
    $bWhere="";
    if ($where!="") {
        $bWhere=" WHERE $where";
    }

    $sql="SELECT {$cols} FROM `{$tablename}`$bWhere;";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            // return data
            $data =[
                "status"=>"OK",
            ];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            return $data;
        } else {
            // empty
            return ["status" => "EMPTY"];
        }
    } else {
        // error connect
        return ["status" => "ERROR", "function"=>"sql_get", "message" => $conn->error, "sql"=>$sql];
    }
}

/**
 * Updates records in a database table with given data and conditions.
 *
 * @param mysqli $conn       The MySQLi database connection object.
 * @param string $tablename  The name of the table to update.
 * @param array  $data       Associative array of column => value pairs to update. [param => value, ...]
 * @param string $where      SQL WHERE clause as a string (e.g., "id = 5").
 *
 * @return array Returns an associative array with keys:
 *               - status: "OK", "EMPTY", or "ERROR"
 *               - affected_rows (int, if OK)
 *               - message (string, optional)
 *               - function (string, if error)
 *               - sql (string, if error)
 */
function sql_update($conn, $tablename, $data, $where) {
    if (empty($data)) {
        return ["status" => "ERROR", "message" => "No data to update."];
    }

    // Escape and prepare SET part
    $setParts = [];
    foreach ($data as $key => $value) {
        $safeKey = addslashes($key);
        $safeVal = $conn->real_escape_string($value);
        $setParts[] = "`$safeKey` = '$safeVal'";
    }
    $setClause = implode(", ", $setParts);

    // Prepare WHERE clause
    $whereClause = $where ? " WHERE $where" : "";

    $sql = "UPDATE `$tablename` SET $setClause$whereClause;";
    $result = $conn->query($sql);

    if ($result) {
        if ($conn->affected_rows > 0) {
            return ["status" => "OK", "affected_rows" => $conn->affected_rows];
        } else {
            return ["status" => "EMPTY", "message" => "No rows updated."];
        }
    } else {
        return [
            "status" => "ERROR",
            "function" => "sql_update",
            "message" => $conn->error,
            "sql" => $sql
        ];
    }
}