<?php
// Rest api - export database
namespace REST;
require_once "help.php";
// constants
$GLOBALS["version"]="v5";

function database_export(): void{
    // Create connection
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // update production ids
    reassignProductionIds($conn);

    // write file
    database_export_file($conn);

    echo json_encode(["status"=>"OK", "file"=>"/rest/".$GLOBALS["version"].".trw_a"]);
}
function reassignProductionIds($conn): void{
    // update regions ids
    reassignProductionIdsInTable($conn, "regions", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "place_regions", "region_id","regions", "id");

    // nations ids
    reassignProductionIdsInTable($conn, "nations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "place_nations", "region_id","nations", "id");

    //todo: update others
}

function reassignProductionIdsInTable($conn, $table, $idname): void{
    // set varible to 0
    {
        $sql="SET @prod_id := 0;";
        $result=$conn->query($sql);
        if (!$result) {
            sqlError($sql, $conn);
            return;
        }
    }

    // ordered set one by one
    {
        $sql="UPDATE $table SET {$idname}_production = (@prod_id := @prod_id + 1) ORDER BY id;";
        $result=$conn->query($sql);
        if (!$result) {
            sqlError($sql, $conn);
            return;
        }
    }
}

/**
 * @param $conn: connection
 * @param $table: source table name
 * @param $sourceId: source original id name
 * @param $tableRelated: edited table name
 * @param $productionId: production id name
 * @return void
 */
function reassignProductionIdsAsForeginKeyInTable($conn, $table, $sourceId, $tableRelated, $relatedId): void{
    // $tableRelated.production_id will be updated according $table.id to match $table.production_id
    $sql= "UPDATE $tableRelated rt JOIN $table mt ON rt.{$relatedId}_production = mt.$sourceId SET rt.{$relatedId}_production = mt.{$sourceId}_production;";
    $result=$conn->query($sql);
    if (!$result) {
        sqlError($sql, $conn);
        exit;
    }
}

function idMapTable($conn, $table): array{
    // $tableRelated.production_id will be updated according $table.id to match $table.production_id
    $sql="SELECT id, production_id FROM $table ORDER BY id;";
    $result=$conn->query($sql);
    if ($result) {
        $list=[];
        while ($row = $result->fetch_assoc()) {
            $list[$row['id']]=$row['production_id'];
        }
        return $list;
    }else{
        sqlError($sql, $conn);
        exit;
    }
}

/**
 * Updates table production ids according map
 * @param array $listIdChange map list [id=>production id, ...]
 * @param string $table editing table
 * @param string $idName changing culumn
 */
function editProductionIdByMapIds_varchar($conn, $listIdChange, $table, $idName): void{
    $sql= "SELECT {$idName} FROM $table;";
    $result=$conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rawlist=$row[$idName];
            // split list split by ","
            $values=explode(",", $rawlist);

            // replace list's values by $listIdChange
            $newListVals=[];
            foreach ($values as $value) {
                $newListVals[]=$listIdChange[$value];
            }

            //list to string
            $newList=join($newListVals);

            // update table
            $sql_update="UPDATE $table SET {$idName}_production='$newList';";
            $result2=$conn->query($sql_update);
            if (!$result2) {
                sqlError($sql, $conn);
                exit;
            }
        }
    }else{
        sqlError($sql, $conn);
        exit;
    }
}

function database_export_file($conn): void{

    // Check connection
    if ($conn->connect_error) {
        throwError("Connection failed: " . $conn->connect_error);
        exit();
    }

    // prepare file
    $filename = $GLOBALS["version"].".trw_a";
    $file = fopen($filename, "w") or die("Unable to open file!");

    /* [File struct]
     * header - version (TW $version)
     * tables
     */

    // header
    fwrite($file, "TW ".$GLOBALS["version"]."\n");

    // global non-translate
    saveTable($conn, $file, "regions", ["production_id"=>"byte", "translates"=>"string"], "production_id");
    saveTable($conn, $file, "nations", ["production_id"=>"byte", "translates"=>"string"], "production_id");
    saveTable($conn, $file, "langs",   ["production_id"=>"byte", "translates"=>"string"], "production_id");

    saveTable($conn, $file, "noun_patterns_cs", [
        "production_id"=>"int",
        "base"=>"varchar(255)",
        "shapes"=>"varchar(255)",
        "gender"=>"byte",
        "uppercase"=>"byte",
        "tags"=>"varchar(255)",
        "pattern"=>"byte"], "production_id");

    saveTable($conn, $file, "adjective_patterns_cs", [
        "production_id"=>"int",
        "base"=>"varchar(255)",
        "shapes"=>"string",
        "category"=>"byte",
        "tags"=>"varchar(255)"], "production_id");

    saveTable($conn, $file, "pronoun_patterns_cs", [
        "production_id"=>"int",
        "base"=>"varchar(255)",
        "shapes"=>"string",
        "syntax"=>"byte",
        "uppercase"=>"byte",
        "tags"=>"varchar(255)",
        "pattern"=>"byte"], "production_id");

    saveTable($conn, $file, "adverb_cs", [
        "production_id"=>"int",
        "shape"=>"varchar(255)",
        "tags"=>"varchar(255)"], "production_id");

    saveTable($conn, $file, "conjunction_cs", [
        "production_id"=>"int",
        "shape"=>"varchar(255)",
        "type"=>"byte",
        "tags"=>"varchar(255)"], "production_id");

    saveTable($conn, $file, "particle_cs", [
        "production_id"=>"int",
        "shape"=>"varchar(255)",
        "tags"=>"varchar(255)"], "production_id");

    saveTable($conn, $file, "interjection_cs", [
        "production_id"=>"int",
        "shape"=>"varchar(255)",
        "tags"=>"varchar(255)"], "production_id");

    saveTable($conn, $file, "translate", [
        "production_id"=>"ushort",
        "name"=>"varchar(255)",
        "administrativeTown"=>"varchar(255)",
        "gpsX"=>"float",
        "gpsY"=>"float",
        "country"=>"byte",
        "langtype"=>"byte",
        "quality"=>"byte",
        "dialect"=>"byte",
        "devinfo"=>"string",
        "options"=>"string",
        "showInMaps"=>"byte"], "production_id");

    fclose($file);

    // download
 /*   header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename='.basename($filename));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename));
    header("Content-Type: text/plain");
    readfile($filename);

    // exported
    $GLOBALS["done"]="Database initialized!";
   // header("Location: ../index.php");*/
}

/**
 * @param - $conn connection to database
 * @param string $tableName table name
 * @param array $params array for example ["label"=>"varchar(255)", "id"=>"byte", "type"=>"byte", ...]
 * @param - $file opened file
 * @param string $idName Name of ID column, e.g. "id"
 */
function saveTable($conn, $file, string $tableName, array $params, string $idName): void{
    // build list of params for sql
    $columnNames = array_keys($params);
    $sqlColumns = implode(", ", $columnNames);

    // id is position with no holes, must be 0,1,2,3,4,5,...
    $sql="SELECT {$sqlColumns} FROM $tableName ORDER BY $idName;";
    $result=$conn->query($sql);
    if ($result) {

        // write total count of rows
        $totalRows=$result->num_rows;
        fwrite($file, pack('n', $totalRows));

        // Fetch all rows into memory
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $totalRows = count($rows);
        fwrite($file, pack('l', $totalRows)); // Write 4-byte row count

        // Check if id column is in strict 0,1,2,... order
        $idOrder = true;
        for ($i = 0; $i < $totalRows; $i++) {
            if (!isset($rows[$i][$idName]) || (int)$rows[$i][$idName] !== $i) {
                $idOrder = false;
                break;
            }
        }

        // If id is ordered, remove it from params to speed up writing
        if ($idOrder) {
            unset($params[$idName]);
        }

        // Write 1-byte flag for id optimization
        fwrite($file, pack('C', $idOrder?"1":"0"));

        // write rows
        foreach ($rows as $row) {

            // foreach params and write it
            foreach ($params as $param=>$type) {
                $value = $row[$param];

                // write by type
                switch ($type) {
                    case 'int': // 4-byte signed integer
                        fwrite($file, pack('l', (int)$value));
                        break;

                    case 'byte': // 1 byte
                        fwrite($file, pack('C', (int)$value));
                        break;

                    case 'ushort': // 2 byte
                        fwrite($file, pack('S', (int)$value));
                        break;

                    case 'float': // 4-byte float
                        fwrite($file, pack('f', (float)$value));
                        break;

                    case 'varchar(255)': // 1-byte length prefix + UTF-8 string
                        $str = (string)$value;
                        $len = strlen($str);
                        if ($len > 255) {
                            throwError("len is too long for varchar(255)");
                        }
                        fwrite($file, pack('C', $len)); // 1-byte length
                        fwrite($file, $str);
                        break;

                    case 'string': // 2-byte length prefix + UTF-8 string
                        $str = (string)$value;
                        $len = strlen($str);
                        if ($len > 65535) {
                            throwError("len is too long for string");
                        }
                        fwrite($file, pack('n', $len)); // 2-byte length
                        fwrite($file, $str);
                        break;
                    default:
                        // Unknown type — skip or return error
                        throwError("unknown type '".$type."'");
                }
            }
        }
    }
}

function throwError($string):void {
    if (!isset($_SESSION["error"])) $_SESSION["error"]="";
    $_SESSION["error"].="<p>".$string."<p>";
}