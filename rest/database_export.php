<?php
// Rest api - export database
namespace REST;
require_once "help.php";
include "../data/config.php";
// constants
$GLOBALS["version"]="v5";
database_export();
/**
 * Exports database tables into a binary file format with a versioned header,
 * then creates a gzipped copy.
 *
 * @param \mysqli $conn Active MySQLi connection
 * @return void
 */
function database_export_file(\mysqli $conn): void{

    // Check connection
    if ($conn->connect_error) {
        throwError("Connection failed: " . $conn->connect_error);
        exit();
    }

    $savefolder=__DIR__ ."/../export/";

    // filename
    $filename = $savefolder.$GLOBALS["version"].".trw_a";

    $file = fopen($filename, "w") or die("Unable to open file!");

    /* [File struct]                    *
     * header - version (TW $version)   *
     * tables                           */

    // header
    fwrite($file, "TW ".$GLOBALS["version"]."\n");

    // write according schemas file
    $schemasfile=$savefolder."export_schemas.json";
    if (!file_exists($schemasfile)) {
        throwError("schemas file not found '".$schemasfile."'");
        exit;
    }
    $jsonfilecontent=file_get_contents($schemasfile);
    $json=json_decode($jsonfilecontent, true);

    // every second update status of process
    $GLOBALS["timelast"]=time();
    function sendProgress($progress){
        $timecur=time();
        if ($timecur-$GLOBALS["timelast"]>=1) {

            echo "data: ".json_encode(["type"=>"export","progress"=>$progress])."\n\n";


            // Padding in case of other browser or network buffers
            //echo(str_repeat(' ', 4096))."\n\n";

            // Now, force this to be immediately displayed:
           // if (ob_get_level() > 0 && ob_end_flush())  ob_start();
            flush();
            $GLOBALS["timelast"]=$timecur;
        }
    }

    // count
    $len=0;
    foreach ($json as $table) {
        $len++;
    }

    // foreach tables and write them
    $i=0;
    foreach ($json as $table=>$data) {
        $id_order= $data["id_order"] ?? null;
        saveTable($conn, $file, $table, $data["rows"], $id_order);
        $i++;
        sendProgress($i/$len);
    }
    unset($GLOBALS["timelast"]);

    fclose($file);

    // Name of the gz file we're creating
    $gzfile = $savefolder.$GLOBALS["version"].".trw_gz";
    // Open the gz file (w9 is the highest compression)
    $fp = gzopen ($gzfile, 'w9');
    gzwrite ($fp, file_get_contents($filename));
    gzclose($fp);
}
/**
 * Recomputes and reassigns production IDs across multiple tables and
 * updates related foreign keys to match the new production IDs.
 *
 * @param \mysqli $conn Active MySQLi connection
 * @return void
 */
function reassignProductionIds(\mysqli $conn): void{
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
    reassignProductionIdsInTable($conn, "noun_patterns_cs", "id");// reorganize production ids
    reassignProductionIdsAsForeginKeyInTable($conn, "noun_patterns_cs", "id","noun_relations", "from");//set reorganized ids

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


    // todo: update others
    // translate
}
/**
 * Reassigns production IDs and writes the export files, returning a JSON
 * payload with the status and file path.
 *
 * @return void
 */
function database_export(): void{
    ini_set('output_buffering', 'off');
    ini_set('zlib.output_compression', '0');

    header('Content-Type: text/event-stream');
    // recommended to prevent caching of event data.
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error !== null) {
            echo "data: " . json_encode([
                    "status" => "error",
                    "message" => $error['message'],
                    "file" => $error['file'],
                    "line" => $error['line']
                ]) . "\n\n";
            flush();
        }
    });

    // Disable any active output buffers
    while (ob_get_level() > 0) {
        ob_end_flush();
    }
    ob_implicit_flush(true);

    // Kickstart stream (padding to force flush through proxies)
    echo ":" . str_repeat(" ", 5048) . "\n\n";

    echo "data: ".json_encode(["type"=>"start","progress"=>"0"])."\n\n";
    flush();

    // Create connection
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // update production ids
    reassignProductionIds($conn);

    echo "data: ".json_encode(["type"=>"reassignProductionIds","progress"=>"1"])."\n\n";
    flush();

    // write file
    database_export_file($conn);

    echo "data: ".json_encode(["status"=>"OK", "file"=>"/rest/".$GLOBALS["version"].".trw_a"])."\n\n";
}
/**
 * Reassigns an incremental production ID column for a table ordered by a given ID.
 *
 * @param \mysqli $conn Active MySQLi connection
 * @param string  $table Table name to update
 * @param string  $idname Name of the original ID column, e.g. "id"
 * @return void
 */
function reassignProductionIdsInTable(\mysqli $conn, string $table, string $idname): void{
    echo "id order: ".$table." ".$idname."\n";
    /* Example
     * [$idname] [$idname+"_production"]
     * 0    0
     * 8    1
     * 16   2
     * 17   3
     * 25   4
     * ...*/
    /*

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
      }*/
    $sql = "
        SET @prod_id := 0;
        UPDATE $table t
        JOIN (
            SELECT {$idname}, (@prod_id := @prod_id + 1) AS new_pid
            FROM $table
            ORDER BY {$idname}
        ) x ON t.{$idname} = x.{$idname}
        SET t.{$idname}_production = x.new_pid;  
    ";
/*
    $result = $conn->query($sql);
    if (!$result) {
        sqlError($sql, $conn);
    }
*/
    if (!$conn->multi_query($sql)) {
        sqlError($sql, $conn);
        return;
    }

    // flush multiple results
    while ($conn->more_results() && $conn->next_result()) {;}
}

/**
 * @param \mysqli $conn: connection
 * @param $table: source table name
 * @param $sourceId: source original id name
 * @param $tableRelated: edited table name
 * @param $productionId: production id name
 * @return void
 */
function reassignProductionIdsAsForeginKeyInTable(\mysqli $conn, string $table, string $sourceId, string $tableRelated, string $relatedId): void{
    // $tableRelated.{$relatedId} will be updated according $table.id to match $table.id_production
    /* example
     * [source table - param] [source table - production param]
     * 4                       1
     * 5                       2
     * 9                       3
     * ...
     * [related table - param] [related table - edited production param]
     * 4                       1
     * 9                       3
     * 5                       2
     * 4                       1
     * ...*/
    // $relatedId
    $sql= "UPDATE $tableRelated rt JOIN $table mt ON rt.{$relatedId}_production = mt.$sourceId SET rt.{$relatedId}_production = mt.{$sourceId}_production;";
    $result=$conn->query($sql);
    if (!$result) {
        sqlError($sql, $conn);
        exit;
    }
}
/**
 * Builds a map of original ID => production ID for a given table.
 *
 * @param \mysqli $conn  Active MySQLi connection
 * @param string  $table Table name
 * @return array<int,int> Map in the form [id => id_production]
 */
function idMapTable(\mysqli $conn, string $table): array{
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
function editProductionIdByMapIds_varchar(\mysqli $conn, $listIdChange, $table, $idName): void{
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
 * @param array $params array for example [{name: "label", type: "varchar(255)"}, {name: "id", type: "byte"}, {name: "type", type: "byte"}, ...]
 * @param - $file opened file
 * @param ?string $idName Name of ID column, e.g. "id"
 */
function saveTable(\mysqli $conn, $file, string $tableName, array $params, ?string $idName): void{
    //echo "table: ".$tableName."\n";
    // build list of params for sql
    $columnNames = [];
    foreach ($params as $param) {
        //verify culumn existence
        if (!isset($param['name'])) {
            throwError("table: ".$tableName." name not found in:".json_encode($param));
        }
        if (true) {
            $column=$param["name"];
            $sql= "SELECT 1  
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = '$tableName' 
              AND COLUMN_NAME = '$column'
              AND TABLE_SCHEMA = DATABASE();";

            $result=$conn->query($sql);
            if (!$result || $result->num_rows === 0) {
                throwError("table: ".$tableName." column: ".$column." not found");
            }
        }
        $columnNames[] = "`".addslashes($param["name"])."`";
    }
    $sqlColumns = implode(", ", $columnNames);

    // id stored as position in list: id is position with no holes, must be 0,1,2,3,4,5,...
    $sql="SELECT {$sqlColumns} FROM $tableName";
    if ($idName!=null) $sql.=" ORDER BY $idName;"; else $sql.=";";
    //echo $sql;
    $result=$conn->query($sql);
    if ($result) {

        // Fetch all rows into memory
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $totalRows = count($rows);
        fwrite($file, pack('l', $totalRows)); // Write 4-byte row count
     //   echo "rows: '".$totalRows."'";

        // id order
        // Check if id column is in strict 0,1,2,... order
        $idOrder = true;
        if ($idName==null) $idOrder = false;
        else{
            //check if is id order correctly done
            for ($i = 1; $i < $totalRows; $i++) {
                if (/*!isset($rows[$i][$idName]) || */(int)$rows[$i][$idName] !== $i) {
                    $idOrder = false;
                    echo "error: ".json_encode(["message"=>$tableName." id order is wrong at ".$i."!"])."\n";
                    break;
                }
            }
        }

        // If id is ordered, remove it from params to speed up writing
        if ($idOrder) {
            unset($params[$idName]);
        }

        // Write 1-byte flag for id optimization
        fwrite($file, $idOrder ? 1 : 0);

        // write rows
        foreach ($rows as $row) {

            // foreach params and write it
            //for ($i=0; $i<count($params); $i++) {
            foreach ($params as $param) {
                $name=$param["name"];
                $type=$param["type"];
                $compress=null;
                if (isset($param["compress"])) $compress=$param["compress"];

             //   if (!array_key_exists($name, $row)) throwError("table: ".$tableName.", column: ".$name." not found");
                $value = $row[$name];
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

                    case 'shortstring': // 1-byte length prefix + UTF-8 string
                        $str = (string)$value;
                        $len = strlen($str);
                        if ($len > 255) {
                            throwError("len is too long for shortstring");
                        }
                        fwrite($file, pack('C', $len)); // 1-byte length
                        fwrite($file, $str);
                        break;

                    case 'string': // 2-byte length prefix + UTF-8 string
                        $str = (string)$value;
                        if ($compress=="pattern"){
                            $arr=explode("|",$str);
                            $str=compress_pattern($arr);
                           // echo $value." >>> ".$str."\n";
                        }
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
        echo "data: ".json_encode(["table: "=> $tableName, "params: "=> $params])."\n\n";
        throwError($conn->error);
    }
}

function compress_pattern(array $parts): string {
    // "blabla?" >>> "?"
    $optimized=compress_shapes($parts);

    // "?|?|?" >>> "?×3"
    return compress_same($optimized);
}

function compress_shapes(array $parts): array {
    foreach ($parts as &$part) {
        if (str_contains($part, "?")) { $part="?"; continue; }
        if (str_contains($part, "*")) { $part="?"; continue; }
        if (str_contains($part, "!")) { $part="?"; continue; }
        if (str_contains($part, " ")) { $part="?"; continue; }
        if (str_contains($part, "†")) { $part="?"; continue; }
    }
    unset($part);
    return $parts;
}

function compress_same(array $parts): string {
    $optimized = [];

    $count = 1;
    $prev = $parts[0] ?? null;

    for ($i = 1; $i < count($parts); $i++) {
        if ($parts[$i] === $prev) {
            $count++;
        } else {
            // Save optimized form
            $optimized[] = $count > 1 ? $prev . "×" . $count : $prev;
            $prev = $parts[$i];
            $count = 1;
        }
    }

    // Add last group
    if ($prev !== null) {
        $optimized[] = $count > 1 ? $prev . "×" . $count : $prev;
    }

    return implode("|", $optimized);
}