<?php
// Rest api - export database
namespace REST;
require_once "help.php";
// constants
$GLOBALS["version"]="v5";

function database_export_file($conn): void{

    // Check connection
    if ($conn->connect_error) {
        throwError("Connection failed: " . $conn->connect_error);
        exit();
    }

    // prepare file
    $filename = $GLOBALS["version"].".trw_a";
    $file = fopen($filename, "w") or die("Unable to open file!");

    /* [File struct]                    *
     * header - version (TW $version)   *
     * tables                           */

    // header
    fwrite($file, "TW ".$GLOBALS["version"]."\n");

    // global non-translate
    saveTable($conn, $file, "regions", ["id_production"=>"byte", "translates"=>"string"], "id_production");
    saveTable($conn, $file, "nations", ["id_production"=>"byte", "translates"=>"string"], "id_production");
    saveTable($conn, $file, "langs",   ["id_production"=>"byte", "translates"=>"string"], "id_production");

    // cs
    saveTable($conn, $file, "noun_patterns_cs", [
        "id_production"=>"int",
        "base"=>"varchar(255)",
        "shapes"=>"varchar(255)",
        "gender"=>"byte",
        "uppercase"=>"byte",
        "tags"=>"varchar(255)",
        "pattern"=>"byte"], "id_production");

    saveTable($conn, $file, "adjective_patterns_cs", [
        "id_production"=>"int",
        "base"=>"varchar(255)",
        "shapes"=>"string",
        "category"=>"byte",
        "tags"=>"varchar(255)"], "id_production");

    saveTable($conn, $file, "pronoun_patterns_cs", [
        "id_production"=>"int",
        "base"=>"varchar(255)",
        "shapes"=>"string",
        "syntax"=>"byte",
        "uppercase"=>"byte",
        "tags"=>"varchar(255)",
        "pattern"=>"byte"], "id_production");

    saveTable($conn, $file, "adverbs_cs", [
        "id_production"=>"int",
        "shape"=>"varchar(255)",
        "tags"=>"varchar(255)"], "id_production");

    saveTable($conn, $file, "conjunctions_cs", [
        "id_production"=>"int",
        "shape"=>"varchar(255)",
        "type"=>"byte",
        "tags"=>"varchar(255)"], "id_production");

    saveTable($conn, $file, "particles_cs", [
        "id_production"=>"int",
        "shape"=>"varchar(255)",
        "tags"=>"varchar(255)"], "id_production");

    saveTable($conn, $file, "interjections_cs", [
        "id_production"=>"int",
        "shape"=>"varchar(255)",
        "tags"=>"varchar(255)"], "id_production");

    saveTable($conn, $file, "translate", [
        "id_production"=>"ushort",
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
        "showInMaps"=>"byte"], "id_production");

    fclose($file);

    // Name of the gz file we're creating
    $gzfile = $GLOBALS["version"].".trw_gz";
    // Open the gz file (w9 is the highest compression)
    $fp = gzopen ($gzfile, 'w9');
    gzwrite ($fp, file_get_contents($filename));
    gzclose($fp);

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

function reassignProductionIds($conn): void{
    // *** region, nation, lang *** //
    // update regions ids
    reassignProductionIdsInTable($conn, "regions", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "place_regions", "region_id","regions", "id");

    // nations ids
    reassignProductionIdsInTable($conn, "nations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "place_nations", "nation_id","nations", "id");

    // langs ids
    reassignProductionIdsInTable($conn, "langs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "place_langs", "lang_id","langs", "id");

    // *** relation *** //
    // noun relation
    reassignProductionIdsInTable($conn, "noun_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "nouns_to", "relation","noun_relations", "id");

    // adjective relation
    reassignProductionIdsInTable($conn, "adjective_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "adjectives_to", "relation","adjective_relations", "id");

    // pronoun relation
    reassignProductionIdsInTable($conn, "pronoun_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "pronouns_to", "relation","pronoun_relations", "id");

    // number relation
    reassignProductionIdsInTable($conn, "number_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "numbers_to", "relation","number_relations", "id");

    // verb relation
    reassignProductionIdsInTable($conn, "verb_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "verbs_to", "relation","verb_relations", "id");

    // adverb relation
    reassignProductionIdsInTable($conn, "adverb_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "adverbs_to", "relation","adverb_relations", "id");

    // preposition relation
    reassignProductionIdsInTable($conn, "preposition_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "prepositions_to", "relation","preposition_relations", "id");

    // conjunction relation
    reassignProductionIdsInTable($conn, "conjunction_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "conjunctions_to", "relation","conjunction_relations", "id");

    // particle relation
    reassignProductionIdsInTable($conn, "particle_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "particles_to", "relation","particle_relations", "id");

    // interjection relation
    reassignProductionIdsInTable($conn, "interjection_relations", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "interjections_to", "relation","interjection_relations", "id");

    // *** CS *** //
    // noun cs
    reassignProductionIdsInTable($conn, "noun_patterns_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "noun_relations", "from","noun_patterns_cs", "id");

    // adjective cs
    reassignProductionIdsInTable($conn, "adjective_patterns_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "adjective_relations", "from","adjective_patterns_cs", "id");

    // pronoun cs
    reassignProductionIdsInTable($conn, "pronoun_patterns_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "pronoun_relations", "from","pronoun_patterns_cs", "id");

    // number cs
    reassignProductionIdsInTable($conn, "number_patterns_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "number_relations", "from","number_patterns_cs", "id");

    // verb cs
    reassignProductionIdsInTable($conn, "verb_patterns_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "verb_relations", "from","verb_patterns_cs", "id");

    // adverb cs
    reassignProductionIdsInTable($conn, "adverbs_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "adverb_relations", "from","adverbs_cs", "id");

    // preposition cs
    reassignProductionIdsInTable($conn, "prepositions_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "preposition_relations", "from","prepositions_cs", "id");

    // conjunction cs
    reassignProductionIdsInTable($conn, "conjunctions_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "conjunction_relations", "from","conjunctions_cs", "id");

    // particle cs
    reassignProductionIdsInTable($conn, "particles_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "particle_relations", "from","particles_cs", "id");

    // interjection cs
    reassignProductionIdsInTable($conn, "interjections_cs", "id");
    reassignProductionIdsAsForeginKeyInTable($conn, "interjection_relations", "from","interjections_cs", "id");


    //todo: update others
    // translate
}

function database_export(): void{
    // Create connection
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // update production ids
    reassignProductionIds($conn);

    // write file
    database_export_file($conn);

    echo json_encode(["status"=>"OK", "file"=>"/rest/".$GLOBALS["version"].".trw_a"]);
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
function reassignProductionIdsAsForeginKeyInTable($conn, string $table, string $sourceId, string $tableRelated, string $relatedId): void{
    // $tableRelated.id_production will be updated according $table.id to match $table.id_production
    $sql= "UPDATE $tableRelated rt JOIN $table mt ON rt.{$relatedId}_production = mt.$sourceId SET rt.{$relatedId}_production = mt.{$sourceId}_production;";
    $result=$conn->query($sql);
    if (!$result) {
        sqlError($sql, $conn);
        exit;
    }
}

function idMapTable($conn, string $table): array{
    // $tableRelated.id_production will be updated according $table.id to match $table.id_production
    $sql="SELECT id, id_production FROM $table ORDER BY id;";
    $result=$conn->query($sql);
    if ($result) {
        $list=[];
        while ($row = $result->fetch_assoc()) {
            $list[$row['id']]=$row['id_production'];
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
    }else{
        echo "table: ";
        echo $tableName;
        echo ", params: ";
        print_r($params);
        throwError($conn->error);
    }
}
/*
function throwError($string):void {
    if (!isset($_SESSION["error"])) $_SESSION["error"]="";
    $_SESSION["error"].="<p>".$string."<p>";
}*/