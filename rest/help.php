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

/**
 * Selects records in a database table with given data and conditions.
 *
 * @param mysqli $conn       The MySQLi database connection object.
 * @param string $tablename  The name of the table to update.
 * @param array  $data       Associative array of column => value pairs to update. [param => value, ...]
 * @param array  $where      SQL WHERE clause as a string, e.g. [id => 5].
 *
 * @return array Returns an associative array with keys:
 *               - status: "OK" or "ERROR"
 *               - data (if OK and rows found)
 *               - message, function, sql (if ERROR)
 */
function sql_get(\mysqli $conn, string $tablename, array $columns, array $where) {
    // columns
    $cols = implode(", ", array_map(function($col) { return "`".$col."`"; }, $columns));

    // where
    $conditions = [];
    $values = [];
    $types = "";

    foreach ($where as $col => $val) {
        $conditions[] = "`".$col."` = ?";
        $values[] = $val;
        // determine bind type
        if (is_int($val)) {
            $types .= "i";
        } elseif (is_float($val)) {
            $types .= "d";
        } elseif (is_null($val)) {
            $types .= "s"; // treat NULL as string, handle separately below
        } else {
            $types .= "s";
        }
    }

    $whereClause = count($conditions) > 0 ? " WHERE " . implode(" AND ", $conditions) : "";

    $sql = "SELECT {$cols} FROM `{$tablename}`{$whereClause};";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return ["status" => "ERROR", "function" => "sql_get", "message" => $conn->error, "sql" => $sql];
    }

    // bind params if needed
    $stmt->bind_param($types, ...$values);

    if (!$stmt->execute()) {
        return ["status" => "ERROR", "function" => "sql_get", "message" => $stmt->error, "sql" => $sql];
    }

    $result = $stmt->get_result();
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    $stmt->close();
    return ["status" => "OK", "data" => $data];
}

function sql_get_one(\mysqli $conn, string $tablename, array $columns, array $where): array{
    $result=sql_get($conn, $tablename, $columns, $where);
    if ($result["status"]=="OK"){
        if (count($result["data"])==1){
            return ["status"=>"OK", "data"=>$result["data"][0]];
        }else{
            return ["status"=>"ERROR", "message"=>"More than one row found!"];
        }
    }else{
        return $result;
    }
}

/*
function sql_get_one(\mysqli $conn, string $tablename, array $columns, array $where): array{
    $cols = implode(", ", array_map(function($col) {
        return "`" . addslashes($col) . "`";
    }, $columns));

    // where
    $conditions = [];
    foreach ($where as $col => $val) {
        $conditions[] = "`" . $col . "` = ".$val." ";
    }
    $whereClause = implode(" AND ", $conditions);



    $sql="SELECT {$cols} FROM `{$tablename}` $whereClause;";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                return ["status"=>"OK", "data"=>$row];
            }
        } else {
            // empty
            return [];//["status" => "EMPTY"];
        }
    } else {
        // error connect
        return ["status" => "ERROR", "function"=>"sql_get", "message" => $conn->error, "sql"=>$sql];
    }
}
*/

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
/*function sql_update($conn, $tablename, $data, $where) {
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
}*/

/**
 * Updates records in a database table with given data and conditions.
 *
 * @param mysqli $conn       The MySQLi database connection object.
 * @param string $tablename  The name of the table to update.
 * @param array  $data       Associative array of column => value pairs to update. [param => value, ...]
 * @param array  $where      SQL WHERE clause as a string, e.g. [id => 5].
 *
 * @return array Returns an associative array with keys:
 *               - status: "OK" or "ERROR"
*                - for faster dev debug if error:
 *                 message (string),
 *                 function (string),
 *                 sql (string)
 */
function sql_update($conn, $tablename, $data, $where) {
    // verify parameters
    if (empty($data) || empty($where)) {
        return ["status" => "ERROR", "function"=>"sql_update", "message" => "Empty data or where clause."];
    }

    // Build SET part
    $set = [];
    foreach ($data as $col => $val) {
        $set[] = "`" . $conn->real_escape_string($col) . "` = ?";
    }
    $setClause = implode(", ", $set);

    // Build WHERE part
    $conditions = [];
    foreach ($where as $col => $val) {
        $conditions[] = "`" . $conn->real_escape_string($col) . "` = ?";
    }
    $whereClause = implode(" AND ", $conditions);

    // prepare sql string
    $sql = "UPDATE `$tablename` SET $setClause WHERE $whereClause";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            "status" => "ERROR",
            "function" => "sql_update",
            "message" => $conn->error,
            "sql" => $sql
        ];
    }

    // Build types and values
    $types = "";
    $values = [];

    foreach ($data as $val) {
        $types .= is_int($val) ? "i" : (is_double($val) ? "d" : "s");
        $values[] = $val;
    }
    foreach ($where as $val) {
        $types .= is_int($val) ? "i" : (is_double($val) ? "d" : "s");
        $values[] = $val;
    }

    $stmt->bind_param($types, ...$values);

    // execute
    if (!$stmt->execute()) {
        return [
            "status" => "ERROR",
            "function" => "sql_update",
            "message" => $stmt->error,
            "sql" => $sql
        ];
    }

    // verify
    if ($stmt->affected_rows > 0) {
        return ["status" => "OK"];
    } else {
        return ["status" => "NOT UPDATED"];
    }
}

/**
 * Inserts a record into a database table with given data.
 *
 * @param mysqli $conn       The MySQLi database connection object.
 * @param string $tablename  The name of the table to insert into.
 * @param array $data        Associative array of column => value pairs to insert. [param => value, ...]
 *
 * @return array Returns an associative array with keys:
 *               - status: "OK" or "ERROR"
 *               - for debug:
 *                 message (string),
 *                 function (string),
 *                 sql (string)
 */
function sql_insert(mysqli $conn, string $tablename, array $data): array {
    if (empty($data)) {
        return ["status" => "ERROR", "function"=>"sql_insert", "message" => "Empty data array."];
    }

    // Columns and placeholders
    $columns = "`" . implode("`, `", array_keys($data)) . "`"; // tested it's OK, print_r($columns);
    $placeholders = implode(", ", array_fill(0, count($data), "?"));


    $sql = "INSERT INTO `$tablename` ($columns) VALUES ($placeholders)";

    //print_r($sql);

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [
            "status" => "ERROR",
            "function" => "sql_insert",
            "message" => $conn->error,
            "sql" => $sql,
            "part"=>"prepare"
        ];
    }

    // Build types
    $types = "";
    foreach ($data as $key=>$val) {
        if (is_array($val)){
            return [
                "status" => "ERROR",
                "function" => "sql_insert",
                "message" => $key." has array. Array cannot be pushed into database!",
                "sql" => $sql,
                "part"=>"build types"
            ];
        } elseif (is_int($val)) {
            $types .= "i";
        } elseif (is_float($val)) {
            $types .= "d";
        } elseif (is_null($val)) {
            $types .= "s"; // still bind as string
        } else {
            $types .= "s";
        }
    }

    if (!$stmt->bind_param($types, ...array_values($data))) {
        return [
            "status" => "ERROR",
            "function" => "sql_insert",
            "message" => $stmt->error,
            "sql" => $sql,
            "part"=>"bind"
        ];
    }

    if (!$stmt->execute()) {
        $stmt->close();
        return [
            "status" => "ERROR",
            "function" => "sql_insert",
            "message" => $stmt->error,
            "sql" => $sql,
            "part"=>"execute"
        ];
    }

    $stmt->close();

    return [
        "status" => "OK"
    ];
}