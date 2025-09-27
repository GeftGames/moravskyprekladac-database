<?php 
// Rest api 
namespace REST;
require_once "help.php";

#region Lists
use mysqli;

function list_add() {
    if (!isset($_POST['table'])) {
        throwError("Chybí list");
        return;
    }
    $table=(string)$_POST['table'];

    $setTranslate=null;
    if (isset($_POST['translate'])) {
        $setTranslate=(int)$_POST['translate'];
    }

    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        echo json_encode(["status" => "ERROR", "message" => $conn->connect_error]);
        return;
    }

    // set default values
    $sql="INSERT INTO $table () VALUES ();";
    // set default values, except "translate"
    if ($setTranslate!=null) $sql="INSERT INTO $table (translate) VALUES ($setTranslate);";

    $result=$conn->query($sql);
    if ($result === TRUE) {
        // return list
        $sqlList="SELECT id, label FROM $table;";
        if ($setTranslate!=null)$sqlList="SELECT id, label FROM $table WHERE translate=$setTranslate;";
        $resultList = $conn->query($sqlList);

        if ($resultList) {
            $list=[];
            while($row = $resultList->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
            echo json_encode(["status"=>"OK", "list"=>$list]);
        } else {
            echo json_encode(["status" => "ERROR", "function"=>"list_add list", "message" => $conn->error, "sql"=>$sqlList]);
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_add", "message" => $conn->error]);
    } 
}

function list_relation_add() {
    $table=(string)$_POST['table'];

    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $conn->set_charset("utf8mb4");

    $result=$conn->query("INSERT INTO $table (translate) VALUES (".$_SESSION['translate'].");");
    if ($result === TRUE) {
        list_relation_items();
    } else {
        echo json_encode(["status" => "ERROR", "function" => "list_relation_add", "message" => $conn->error]);
    }
}

function list_remove() {
    if (!isset($_POST['id'])){
        throwError("Chybí ID");
        return;
    }
    $id = (int)$_POST['id'];

    if (!isset($_POST['table'])){
        throwError("Chybí table");
        return;
    }
    $table=(string)$_POST['table'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $result=$conn->query("DELETE FROM $table WHERE id = $id");    
    if ($result === TRUE) {
        list_items();
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_remove", "message" => $conn->error]);
    }    
}

function list_duplicate() {
    if (!isset($_POST['id'])){
        throwError("Chybí ID");
        return;
    }
    $id = (int)$_POST['id'];

    if (!isset($_POST['table'])){
        throwError("Chybí table");
        return;
    }
    $table=(string)$_POST['table'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $result=$conn->query("INSERT INTO $table (label, type) SELECT label, type FROM $table WHERE id = $id;");
    if ($result === TRUE) {
        list_items();
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_duplicate", "message" => $conn->error]);
    }  
}

function list_items() {
    if (!isset($_POST['table'])){
        throwError("Chybí list");
        return;
    }
    $table=$_POST['table'];
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
   // $order="ORDER BY LOWER(label) ASC";
  //  $filter=$_SESSION["translate"]=$setTranslate;
    $sql="SELECT id, label FROM $table;";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            $list=[];
            while($row = $result->fetch_assoc()) {
                $list[]=[(int)$row["id"], $row["label"]];
            }
            echo json_encode(["status"=>"OK", "list"=>$list]);
        } else {
            echo "{}";
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_items", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function list_relation_items() {
    if (!isset($_POST['table'])){
        throwError("Chybí list");
        return;
    }
    $table=$_POST['table'];
    $translate=$_SESSION['translate'];
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // from

    include "components/give_relations_pattern.php";

    $list=give_relations($conn, "noun");
    echo json_encode(["status" => "OK", "list" => $list]);
  /*      } else echo json_encode([]);
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_items", "message" => $conn->error, "sql"=>$sql]);
    }*/
}
#endregion 

#region region, nation, lang
function region_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['type']) || !isset($_POST['parent']) || !isset($_POST['translates'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $type = (int)$_POST['type'];
    $parent = (int)$_POST['parent'];
    $translates = $_POST['translates'];

    $sql="UPDATE `regions` SET `label` = '$label', `type` = $type, `parent` = $parent, `translates` = '$translates' WHERE `id` = $id";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"region_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function region_item() {
    if (!isset($_POST['idregion'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing", "action"=>"region_item"]);
        return;
    }
    $id = $_POST['idregion'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, type, parent, translates FROM regions WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while ($row = $result->fetch_assoc()) {
                $label=$row["label"];
                $type=$row["type"];
                $parent=$row["parent"];
                $translates=$row["translates"];
                if ($type==null) $type=-1;
                if ($parent==null) $parent=-1;
                echo json_encode(["status"=>"OK", "label"=>$label, "type"=>$type, "parent"=>$parent, "translates"=>$translates]);
                return;
            }
        } else {
            echo '{"status": "EMPTY"}';
        }       
    } else {
        echo json_encode(["status" => "ERROR", "message"=>$conn->error, "type"=>'SQL', "sql"=>$sql]);
    } 
    $conn->close();
}

function nation_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['type']) || !isset($_POST['parent']) || !isset($_POST['translates'])) {
        echo json_encode(["status"=>"ERROR", "message"=>"Nelze aktualizovat ".$_POST['id'].", chybí parametry", "action"=>"nation_update"]);
        return;
    }
    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $type = (int)$_POST['type'];
    $parent = (int)$_POST['parent'];
    $translates = $conn->real_escape_string($_POST['translates']);

    $sql="UPDATE `nations` SET `label` = '$label', `type` = $type, `parent` = $parent, `translates` = '$translates' WHERE `id` = $id";
    $result=$conn->query($sql);
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "action"=>"nation_update", "message" => $conn->error, "sql"=>$sql]);
    }
}

function nation_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing", "action"=>"nation_item"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT label, type, parent, translates FROM nations WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $label=$row["label"];
                $type=$row["type"];
                $parent=$row["parent"];
                $translates=$row["translates"];
                if ($type==null) $type=-1;
                if ($parent==null) $parent=-1;
                echo json_encode(["status"=>"OK", "label"=>$label, "type"=>$type, "parent"=>$parent, "translates"=>$translates]);
                return;
            }
        } else {
            echo '{"status": "EMPTY"}';
        }
    } else {
        echo json_encode(["status" => "ERROR", "message"=>$conn->error, "type"=>'SQL', "sql"=>$sql]);
    }
    $conn->close();
}

function lang_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['type']) || !isset($_POST['parent']) || !isset($_POST['translates'])) {
        echo json_encode(["status"=>"ERROR", "message"=>"Nelze aktualizovat ".$_POST['id'].", chybí parametry", "action"=>"nation_update"]);
        return;
    }
    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $type = (int)$_POST['type'];
    $parent = (int)$_POST['parent'];
    $translates = $conn->real_escape_string($_POST['translates']);

    $sql="UPDATE `langs` SET `label` = '$label', `type` = $type, `parent` = $parent, `translates` = '$translates' WHERE `id` = $id";
    $result=$conn->query($sql);
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "action"=>"nation_update", "message" => $conn->error, "sql"=>$sql]);
    }
}

function lang_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing", "action"=>"nation_item"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT label, type, parent, translates FROM langs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $label=$row["label"];
                $type=$row["type"];
                $parent=$row["parent"];
                $translates=$row["translates"];
                if ($type==null) $type=-1;
                if ($parent==null) $parent=-1;
                echo json_encode(["status"=>"OK", "label"=>$label, "type"=>$type, "parent"=>$parent, "translates"=>$translates]);
                return;
            }
        } else {
            echo '{"status": "EMPTY"}';
        }
    } else {
        echo json_encode(["status" => "ERROR", "message"=>$conn->error, "type"=>'SQL', "sql"=>$sql]);
    }
    $conn->close();
}
#endregion

#region cs shapes
function noun_pattern_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, base, gender, shapes, uppercase, pattern, tags FROM noun_patterns_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK",
                    "label"=>$row["label"],
                    "base"=>$row["base"],
                    "tags"=>$row["tags"],
                    "shapes"=>$row["shapes"],
                    "pattern"=>$row["pattern"],
                    "gender"=>$row["gender"],
                    "uppercase"=>$row["uppercase"]
                ]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"noun_pattern_cs_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function noun_pattern_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['base']) || !isset($_POST['shapes']) || !isset($_POST['gender']) || !isset($_POST['tags']) || !isset($_POST['pattern'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $gender = (int)$_POST['gender'];
    $uppercase = (int)$_POST['uppercase'];
    $shapes = $_POST['shapes'];
    $pattern = $_POST['pattern'];
    $tags = $_POST['tags'];
    $base = $_POST['base'];

    $sql="UPDATE noun_pattern_cs SET 
       label = '$label', 
       base = '$base', 
       gender = $gender, 
       uppercase = $uppercase, 
       pattern = $pattern, 
       shapes = '$shapes', 
       tags = '$tags' 
            WHERE id = $id;";

    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "noun_pattern_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function adjective_pattern_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, base, category, shapes, tags FROM adjective_patterns_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "label"=>$row["label"], "base"=>$row["base"], "tags"=>$row["tags"], "shapes"=>$row["shapes"], "category"=>$row["category"]]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"adjective_pattern_cs_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function adjective_pattern_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['base']) || !isset($_POST['shapes']) || !isset($_POST['category']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $shapes = $_POST['shapes'];
    $category = (int)$_POST['category'];
    $tags = $_POST['tags'];

    $sql="UPDATE adjective_patterns_cs SET label = '$label', base = '$base', category = $category, shapes = '$shapes', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "adjective_pattern_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function pronoun_pattern_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, base, category, syntax, shapes, tags FROM pronoun_patterns_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while ($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK",
                    "label"=>$row["label"],
                    "base"=>$row["base"],
                    "tags"=>$row["tags"],
                    "shapes"=>$row["shapes"],
                    "category"=>$row["category"],
                    "syntax"=>$row["syntax"]]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"pronoun_pattern_cs_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function pronoun_pattern_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['base']) || !isset($_POST['shapes']) || !isset($_POST['category']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $shapes = $_POST['shapes'];
    $category = (int)$_POST['category'];
    $syntax = (int)$_POST['syntax'];
    $tags = $_POST['tags'];

    $sql="UPDATE pronoun_patterns_cs SET label = '$label', base = '$base', shapes = '$shapes', category = $category, syntax = $syntax, tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "pronoun_pattern_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function number_pattern_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, base, shapes, tags FROM number_patterns_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "label"=>$row["label"], "base"=>$row["base"], "tags"=>$row["tags"], "shapes"=>$row["shapes"]]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"number_pattern_cs_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function number_pattern_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['base']) || !isset($_POST['shapes']) || !isset($_POST['category']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $shapes = $_POST['shapes'];
    $tags = $_POST['tags'];

    $sql="UPDATE number_patterns_cs SET label = '$label', base = '$base', shapes = '$shapes', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "number_pattern_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function verb_pattern_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, base, class,
       shapes_infinitive, 
       shapes_continuous, 
       shapes_future, 
       shapes_imperative, 
       shapes_past_active, 
       shapes_past_passive, 
       shapes_transgressive_cont, 
       shapes_transgressive_past, 
       shapes_auxiliary, 
       
       category, tags FROM verb_patterns_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "label"=>$row["label"], "base"=>$row["base"], "tags"=>$row["tags"], "category"=>$row["category"], "class"=>$row["class"],
                    "infinitive"        =>$row["shapes_infinitive"],
                    "continuous"         =>$row["shapes_continuous"],
                    "future"            =>$row["shapes_future"],
                    "imperative"        =>$row["shapes_imperative"],
                    "past_active"       =>$row["shapes_past_active"],
                    "past_passive"      =>$row["shapes_past_passive"],
                    "transgressive_past"=>$row["shapes_transgressive_cont"],
                    "transgressive_cont"=>$row["shapes_transgressive_past"],
                    "auxiliary"         =>$row["shapes_auxiliary"]
                ]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"verb_pattern_cs_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function verb_pattern_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['base']) || !isset($_POST['shapes']) || !isset($_POST['category']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $tags = $_POST['tags'];
    $category = $_POST['category'];
    $shapes_infinitive      = $_POST['shapes_infinitive'];
    $shapes_continuous       = $_POST['shapes_continuous'];
    $shapes_future          = $_POST['shapes_future'];
    $shapes_imperative      = $_POST['shapes_imperative'];
    $shapes_past_active     = $_POST['shapes_past_active'];
    $shapes_past_passive    = $_POST['shapes_past_passive'];
    $shapes_transgressive_cont = $_POST['shapes_transgressive_cont'];
    $shapes_transgressive_past = $_POST['shapes_transgressive_past'];
    $shapes_aluxilary       = $_POST['shapes_aluxilary'];

    $sql="UPDATE verb_pattern_cs SET label = '$label', base = '$base', 
        shapes_infinitive = '$shapes_infinitive', 
        shapes_continuous        = '$shapes_continuous',
        shapes_future           = '$shapes_future',
        shapes_imperative       = '$shapes_imperative', 
        shapes_past_active      = '$shapes_past_active',
        shapes_past_passive     = '$shapes_past_passive',
        shapes_transgressivecont  = '$shapes_transgressive_cont',
        shapes_transgressive_past  = '$shapes_transgressive_past',
        shapes_aluxilary        = '$shapes_aluxilary',       
            category = '$category', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "verb_pattern_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function adverb_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT shape, tags FROM adverbs_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "shape"=>$row["shape"], "tags"=>$row["tags"]]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"adverb_cs_item", "message" => $conn->error, "sql"=>$sql]);
    }
    $conn->close();
}

function adverb_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['shape']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id = (int)$_POST['id'];
    $shape = $conn->real_escape_string($_POST['shape']);
    $tags = $_POST['tags'];

    $sql="UPDATE adverbs_cs SET shape = '$shape', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "adverb_cs_update", "message" => $conn->error, "sql"=>$sql]);
    }
}

function preposition_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT shape, falls, tags FROM prepositions_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "shape"=>$row["shape"], "falls"=>$row["falls"], "tags"=>$row["tags"]]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"preposition_cs_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function preposition_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['shape']) || !isset($_POST['falls']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $shape = $conn->real_escape_string($_POST['shape']);
    $tags = $_POST['tags'];
    $falls = $_POST['falls'];

    $sql="UPDATE verb_pattern_cs SET shape = '$shape', falls = '$falls', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "verb_pattern_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function conjunction_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT shape, tags FROM conjunctions_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "shape"=>$row["shape"], "tags"=>$row["tags"]]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"conjunction_cs_item", "message" => $conn->error, "sql"=>$sql]);
    }
    $conn->close();
}

function conjunction_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['shape']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id = (int)$_POST['id'];
    $shape = $conn->real_escape_string($_POST['shape']);
    $tags = $_POST['tags'];

    $sql="UPDATE conjunction_cs SET shape = '$shape', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "conjunction_cs_update", "message" => $conn->error, "sql"=>$sql]);
    }
}

function particle_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT shape, tags FROM particles_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "shape"=>$row["shape"], "tags"=>$row["tags"]]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"particle_cs_item", "message" => $conn->error, "sql"=>$sql]);
    }
    $conn->close();
}

function particle_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['shape']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id = (int)$_POST['id'];
    $shape = $conn->real_escape_string($_POST['shape']);
    $tags = $_POST['tags'];

    $sql="UPDATE particles_cs SET shape = '$shape', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "particle_cs_update", "message" => $conn->error, "sql"=>$sql]);
    }
}

function interjection_cs_item() :void {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT shape, tags FROM interjections_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "shape"=>$row["shape"], "tags"=>$row["tags"]]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"interjection_cs_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function interjection_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['shape']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $shape = $conn->real_escape_string($_POST['shape']);
    $tags = $_POST['tags'];

    $sql="UPDATE interjections_cs SET shape = '$shape', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "interjection_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

#endregion

#region relation
function noun_relation_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;
    $custombase=null;

    $sql="SELECT `id`, `from`, custombase FROM noun_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
                $custombase=$row["custombase"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`,  `tags`, `tmp_pattern_from_body`, cite, custombase, `tmp_imp_from_pattern` FROM `nouns_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
          //  "certainty"=>$row["certainty"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "custombase"=>$row["custombase"],
            "tmp_pattern_from_body"=>$row["tmp_pattern_from_body"],
            "tmp_imp_from_pattern"=>$row["tmp_imp_from_pattern"],
            "cite"=>$row["cite"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "custombase"=>$custombase,
        "from" =>$from,
        "to"=>$listTo
    ]);
    $conn->close();
}

function noun_relation_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $from  = $conn->real_escape_string($_POST['from']);

    $sql= /** @lang SQL */"UPDATE noun_relations SET `from` = '$from' WHERE id = $id;";
    $result=$conn->query($sql);

    // todo: convert $_POST['to'] json array to sql code multiple rows (id, priority, shape, comment, source) and update table nouns_to
    // check if it's array
    $toData = json_decode($_POST['to'], true);
    if ($toData === null) {
        echo json_encode(["status" => "ERROR", "message" => "Invalid JSON in 'to' parameter"]);
        return;
    }

    $tookdone=true;
    $errorto="";
    foreach ($toData as $to) {
        $toId=$to[0];
        $toPriority=$to[1];
        $toShape=$to[2];
        $toComment=$to[3];
        $toSource=$to[4];
        $toTags=$to[5];
        $sqlTo= /** @lang SQL */"UPDATE nouns_to SET 
            `priority` = '$toPriority', 
            `shape` = '$toShape',  
            `tags` = '$toTags',  
            `comment` = '$toComment',  
            `cite` = '$toSource'  
                WHERE id = $toId;";
        $resultTo=$conn->query($sqlTo);

        if (!$resultTo) {
            $tookdone=false;
            $errorto.=$conn->error.", ";
        }
    }

    if ($result && $tookdone) {
        echo '{ "status": "OK", "idFrom": "'.$id.'"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "cite_update",
            "message1" => $conn->error, "message2" => $errorto,
            "sql"=>$sql
        ]);
    }
}

function adjective_relation_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;
    $custombase=null;

    $sql="SELECT `id`, `from`, custombase FROM adjective_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
                $custombase=$row["custombase"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`,  `tags`, `tmp_pattern_from_body`, cite, custombase, `tmp_imp_from_pattern` FROM `adjectives_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "custombase"=>$row["custombase"],
            "tmp_pattern_from_body"=>$row["tmp_pattern_from_body"],
            "tmp_imp_from_pattern"=>$row["tmp_imp_from_pattern"],
            "cite"=>$row["cite"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "custombase"=>$custombase,
        "from" =>$from,
        "to"=>$listTo
    ]);
    $conn->close();
}

function pronoun_relation_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;
    $custombase=null;

    $sql="SELECT `id`, `from`, custombase FROM pronoun_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
                $custombase=$row["custombase"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`,  `tags`, `tmp_pattern_from_body`, cite, custombase, `tmp_imp_from_pattern` FROM `pronouns_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "custombase"=>$row["custombase"],
            "tmp_pattern_from_body"=>$row["tmp_pattern_from_body"],
            "tmp_imp_from_pattern"=>$row["tmp_imp_from_pattern"],
            "cite"=>$row["cite"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "custombase"=>$custombase,
        "from" =>$from,
        "to"=>$listTo
    ]);
    $conn->close();
}

function number_relation_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;
    $custombase=null;

    $sql="SELECT `id`, `from`, custombase FROM number_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
                $custombase=$row["custombase"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`,  `tags`, `tmp_pattern_from_body`, cite, custombase, `tmp_imp_from_pattern` FROM `numbers_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "custombase"=>$row["custombase"],
            "tmp_pattern_from_body"=>$row["tmp_pattern_from_body"],
            "tmp_imp_from_pattern"=>$row["tmp_imp_from_pattern"],
            "cite"=>$row["cite"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "custombase"=>$custombase,
        "from" =>$from,
        "to"=>$listTo
    ]);
    $conn->close();
}

function verb_relation_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;
    $custombase=null;

    $sql="SELECT `id`, `from`, `custombase` FROM verb_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
                $custombase=$row["custombase"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"verb_relation_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`,  `tags`, `tmp_pattern_from_body`, cite, custombase, `tmp_imp_from_pattern` FROM `verbs_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
            //  "certainty"=>$row["certainty"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "custombase"=>$row["custombase"],
            "tmp_pattern_from_body"=>$row["tmp_pattern_from_body"],
            "tmp_imp_from_pattern"=>$row["tmp_imp_from_pattern"],
            "cite"=>$row["cite"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "data"=>[
            "from" =>$from,
            "custombase"=>$custombase,
            "to"=>$listTo
        ]
    ]);
    $conn->close();
}

function adverb_relation_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;

    $sql="SELECT `id`, `from` FROM adverb_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`, `tags`, `cite` FROM `adverbs_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "cite"=>$row["cite"],
         //   "certainty"=>$row["certainty"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "from" =>$from,
        "to"=>json_encode($listTo)
    ]);
    $conn->close();
}

function adverb_relation_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $from  = $conn->real_escape_string($_POST['from']);

    $sql= /** @lang SQL */"UPDATE adverb_relations SET `from` = '$from' WHERE id = $id;";
    $result=$conn->query($sql);

    // todo: convert $_POST['to'] json array to sql code multiple rows (id, priority, shape, comment, source) and update table nouns_to
    // check if it's array
    $toData = json_decode($_POST['to'], true);
    if ($toData === null) {
        echo json_encode(["status" => "ERROR", "message" => "Invalid JSON in 'to' parameter"]);
        return;
    }

    $tookdone=true;
    $errorto="";
    foreach ($toData as $to) {
        $toId=$to[0];
        $toPriority=$to[1];
        $toShape=$to[2];
        $toComment=$to[3];
        $toSource=$to[4];
        $toTags=$to[5];
       // $certainty=$to[6];
        $sqlTo= /** @lang SQL */"UPDATE adverbs_to SET 
            `priority` = '$toPriority', 
            `shape` = '$toShape',  
            `tags` = '$toTags',  
            `comment` = '$toComment',  
            `cite` = '$toSource'  
                WHERE id = $toId;";
        $resultTo=$conn->query($sqlTo);

        if (!$resultTo) {
            $tookdone=false;
            $errorto.=$conn->error.", ";
        }
    }

    if ($result && $tookdone) {
        echo '{ "status": "OK", "idFrom": "'.$id.'"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "cite_update",
            "message1" => $conn->error, "message2" => $errorto,
            "sql"=>$sql
        ]);
    }
}

function preposition_relation_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $from  = $conn->real_escape_string($_POST['from']);

    $sql= /** @lang SQL */"UPDATE preposition_relations SET `from` = '$from' WHERE id = $id;";
    $result=$conn->query($sql);

    // todo: convert $_POST['to'] json array to sql code multiple rows (id, priority, shape, comment, source) and update table nouns_to
    // check if it's array
    $toData = json_decode($_POST['to'], true);
    if ($toData === null) {
        echo json_encode(["status" => "ERROR", "message" => "Invalid JSON in 'to' parameter"]);
        return;
    }

    $tookdone=true;
    $errorto="";
    foreach ($toData as $to) {
        $toId=$to[0];
        $toPriority=$to[1];
        $toShape=$to[2];
        $toComment=$to[3];
        $toSource=$to[4];
        $toTags=$to[5];
       // $certainty=$to[6];
        $sqlTo= /** @lang SQL */"UPDATE adverbs_to SET 
            `priority` = '$toPriority', 
            `shape` = '$toShape',  
            `tags` = '$toTags',  
            `comment` = '$toComment',  
            `cite` = '$toSource'  
                WHERE id = $toId;";
        $resultTo=$conn->query($sqlTo);

        if (!$resultTo) {
            $tookdone=false;
            $errorto.=$conn->error.", ";
        }
    }

    if ($result && $tookdone) {
        echo '{ "status": "OK", "idFrom": "'.$id.'"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "cite_update",
            "message1" => $conn->error, "message2" => $errorto,
            "sql"=>$sql
        ]);
    }
}

function preposition_relation_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;

    $sql="SELECT `id`, `from` FROM preposition_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`, `tags`, `cite` FROM `prepositions_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "cite"=>$row["cite"],
         //  "certainty"=>$row["certainty"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "from" =>$from,
        "to"=>json_encode($listTo)
    ]);
    $conn->close();
}

function conjunction_relation_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;

    $sql="SELECT `id`, `from` FROM conjunction_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`, `tags`, `cite` FROM `conjunctions_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "cite"=>$row["cite"],
         //   "certainty"=>$row["certainty"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "from" =>$from,
        "to"=>json_encode($listTo)
    ]);
    $conn->close();
}

function conjunction_relation_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $from  = $conn->real_escape_string($_POST['from']);

    $sql= /** @lang SQL */"UPDATE conjunction_relations SET `from` = '$from' WHERE id = $id;";
    $result=$conn->query($sql);

    // todo: convert $_POST['to'] json array to sql code multiple rows (id, priority, shape, comment, source) and update table nouns_to
    // check if it's array
    $toData = json_decode($_POST['to'], true);
    if ($toData === null) {
        echo json_encode(["status" => "ERROR", "message" => "Invalid JSON in 'to' parameter"]);
        return;
    }

    $tookdone=true;
    $errorto="";
    foreach ($toData as $to) {
        $toId=$to[0];
        $toPriority=$to[1];
        $toShape=$to[2];
        $toComment=$to[3];
        $toSource=$to[4];
        $toTags=$to[5];
        //$certainty=$to[6];
        $sqlTo= /** @lang SQL */"UPDATE conjunctions_to SET 
            `priority` = '$toPriority', 
            `shape` = '$toShape',  
            `tags` = '$toTags',  
            `comment` = '$toComment',  
            `cite` = '$toSource'  
        
                WHERE id = $toId;";
        $resultTo=$conn->query($sqlTo);

        if (!$resultTo) {
            $tookdone=false;
            $errorto.=$conn->error.", ";
        }
    }

    if ($result && $tookdone) {
        echo '{ "status": "OK", "idFrom": "'.$id.'"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "cite_update",
            "message1" => $conn->error, "message2" => $errorto,
            "sql"=>$sql
        ]);
    }
}

function particle_relation_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;

    $sql="SELECT `id`, `from` FROM particle_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`, `tags`, `cite` FROM `particles_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "cite"=>$row["cite"],
         //   "certainty"=>$row["certainty"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "from" =>$from,
        "to"=>json_encode($listTo)
    ]);
    $conn->close();
}

function particle_relation_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $from  = $conn->real_escape_string($_POST['from']);

    $sql= /** @lang SQL */"UPDATE particle_relations SET `from` = '$from' WHERE id = $id;";
    $result=$conn->query($sql);

    // todo: convert $_POST['to'] json array to sql code multiple rows (id, priority, shape, comment, source) and update table nouns_to
    // check if it's array
    $toData = json_decode($_POST['to'], true);
    if ($toData === null) {
        echo json_encode(["status" => "ERROR", "message" => "Invalid JSON in 'to' parameter"]);
        return;
    }

    $tookdone=true;
    $errorto="";
    foreach ($toData as $to) {
        $toId=$to[0];
        $toPriority=$to[1];
        $toShape=$to[2];
        $toComment=$to[3];
        $toSource=$to[4];
        $toTags=$to[5];
       // $certainty=$to[6];
        $sqlTo= /** @lang SQL */"UPDATE adverbs_to SET 
            `priority` = '$toPriority', 
            `shape` = '$toShape',  
            `tags` = '$toTags',  
            `comment` = '$toComment',  
            `cite` = '$toSource'
                WHERE id = $toId;";
        $resultTo=$conn->query($sqlTo);

        if (!$resultTo) {
            $tookdone=false;
            $errorto.=$conn->error.", ";
        }
    }

    if ($result && $tookdone) {
        echo '{ "status": "OK", "idFrom": "'.$id.'"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "cite_update",
            "message1" => $conn->error, "message2" => $errorto,
            "sql"=>$sql
        ]);
    }
}

function interjection_relation_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;

    $sql="SELECT `id`, `from` FROM interjection_relations WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $relation_id=$row["id"];
                $from=$row["from"];
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
            return;
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
        return;
    }

    $listTo=[];
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`, `tags`, `cite` FROM `interjections_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=[
            "id"=>$row["id"],
            "priority"=>$row["priority"],
            "shape"=>$row["shape"],
            "comment"=>$row["comment"],
            "tags"=>$row["tags"],
            "cite"=>$row["cite"],
         //   "certainty"=>$row["certainty"]
        ];
    }

    echo json_encode([
        "status"=>"OK",
        "from" =>$from,
        "to"=>json_encode($listTo)
    ]);
    $conn->close();
}

function interjection_relation_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $from  = $conn->real_escape_string($_POST['from']);

    $sql= /** @lang SQL */"UPDATE interjection_relations SET `from` = '$from' WHERE id = $id;";
    $result=$conn->query($sql);

    // todo: convert $_POST['to'] json array to sql code multiple rows (id, priority, shape, comment, source) and update table nouns_to
    // check if it's array
    $toData = json_decode($_POST['to'], true);
    if ($toData === null) {
        echo json_encode(["status" => "ERROR", "message" => "Invalid JSON in 'to' parameter"]);
        return;
    }

    $tookdone=true;
    $errorto="";
    foreach ($toData as $to) {
        $toId=$to[0];
        $toPriority=$to[1];
        $toShape=$to[2];
        $toComment=$to[3];
        $toSource=$to[4];
        $toTags=$to[5];
      //  $certainty=$to[6];
        $sqlTo= /** @lang SQL */"UPDATE adverbs_to SET 
            `priority` = '$toPriority', 
            `shape` = '$toShape',  
            `tags` = '$toTags',  
            `comment` = '$toComment',  
            `cite` = '$toSource'  
                WHERE id = $toId;";
        $resultTo=$conn->query($sqlTo);

        if (!$resultTo) {
            $tookdone=false;
            $errorto.=$conn->error.", ";
        }
    }

    if ($result && $tookdone) {
        echo '{ "status": "OK", "idFrom": "'.$id.'"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "cite_update",
            "message1" => $conn->error, "message2" => $errorto,
            "sql"=>$sql
        ]);
    }
}
#endregion

#region to
function noun_pattern_to_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT label, base, gender, uppercase, pattern, shapes, tags FROM noun_patterns_to WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK",
                    "label"=>$row["label"],
                    "base"=>$row["base"],
                    "tags"=>$row["tags"],
                    "shapes"=>$row["shapes"],
                    "gender"=>$row["gender"],
                    "uppercase"=>$row["uppercase"],
                    "pattern"=>$row["pattern"]
                ]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"noun_pattern_to_item", "message" => $conn->error, "sql"=>$sql]);
    }
    $conn->close();
}

function noun_pattern_to_update() {
    // Check if all required parameters are set
    if (!isset($_POST['id'])
        || !isset($_POST['label'])
        || !isset($_POST['base'])
        || !isset($_POST['shapes'])
        || !isset($_POST['uppercase'])
        || !isset($_POST['gender'])
        || !isset($_POST['pattern'])
        || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }

    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // Get the values from the POST request
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $gender = (int)$_POST['gender'];
    $uppercase = (int)$_POST['uppercase'];
    $shapes = $_POST['shapes'];
    $tags = $_POST['tags'];
    $pattern = $_POST['pattern'];
    $base = $_POST['base'];

    // Update the noun_pattern_to table
    /*$sql="UPDATE noun_pattern_to SET".
        "label = '$label',".
        "base = '$base',".
        "gender = $gender,".
        "uppercase = $uppercase,".
        "shapes = '$shapes',".
        "tags = '$tags' WHERE id = $id;";

    $result=$conn->query($sql);*/

    $stmt = $conn->prepare("UPDATE noun_patterns_to SET 
        label = ?, 
        base = ?, 
        gender = ?, 
        uppercase = ?, 
        shapes = ?, 
        tags = ?, 
        pattern = ?
            WHERE id = ?");

    if ($stmt === false) {
        die(json_encode(["status" => "ERROR", "message" => $conn->error]));
    }

    // Bind parameters: 'ssiiisi' corresponds to the types:
    // s = string, i = integer
    $stmt->bind_param("ssiissi", $label, $base, $gender, $uppercase, $shapes, $tags, $pattern, $id);

    $result = $stmt->execute();

    // Display result
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "noun_pattern_to_update", "message" => $conn->error]);
    }
}

function adjective_pattern_to_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $result=sql_get_one($conn, "adjective_patterns_to", ["label", "base", "category", "shapes"], ["id"=>$id]);

    if ($result["status"]=="OK") {
        echo json_encode($result);
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"adjective_pattern_to_item"]);
    }
    $conn->close();
}

function adjective_pattern_to_update() {
    // Check if all required parameters are set
    if (!isset($_POST['id'])
        || !isset($_POST['label'])
        || !isset($_POST['base'])
        || !isset($_POST['shapes'])
        || !isset($_POST['pattern'])
        || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }

    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // Get the values from the POST request
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $shapes = $_POST['shapes'];
    $tags = $_POST['tags'];
    $pattern = $_POST['pattern'];
    $base = $_POST['base'];

    $result=sql_update($conn, "adjective_patterns_to",[
        "label" => $label,
        "base" => $base,
        "shapes" => $shapes,
        "tags" => $tags,
        "pattern" => $pattern
    ], ["id"=>$id]);

    // Display result
    if ($result["status"]=="OK") {
        echo json_encode($result);
    } else {
        echo json_encode(["status" => "ERROR", "function" => "adjective_pattern_to_update", "message" => $result]);
    }
}

function pronoun_pattern_to_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $result=sql_get_one($conn, "pronoun_patterns_to", ["label", "base", "pattern_type", "shapes", "tags"], ["id"=>$id]);

    if ($result["status"]=="OK") {
        echo json_encode($result);
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"pronoun_pattern_to_item"]);
    }
    $conn->close();
}

function pronoun_pattern_to_update() {
    // Check if all required parameters are set
    if (!isset($_POST['id'])
        || !isset($_POST['label'])
        || !isset($_POST['base'])
        || !isset($_POST['shapes'])
        || !isset($_POST['pattern'])
        || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }

    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // Get the values from the POST request
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $shapes = $_POST['shapes'];
    $tags = $_POST['tags'];
    $pattern = $_POST['pattern'];
    $base = $_POST['base'];

    $result=sql_update($conn, "adjective_patterns_to",[
        "label" => $label,
        "base" => $base,
        "shapes" => $shapes,
        "tags" => $tags,
        "pattern" => $pattern
    ], ["id"=>$id]);

    // Display result
    if ($result["status"]=="OK") {
        echo json_encode($result);
    } else {
        echo json_encode(["status" => "ERROR", "function" => "adjective_pattern_to_update", "message" => $result]);
    }
}

function number_pattern_to_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $result=sql_get($conn, "number_patterns_to", ["label", "base", "shapes", "tags"], ["id"=>$id]);

    if ($result["status"]=="OK") {
        echo json_encode($result);
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"number_pattern_to_item", "detail" => $result]);
    }
    $conn->close();
}

function number_pattern_to_update() {
    // Check if all required parameters are set
    if (!isset($_POST['id'])
        || !isset($_POST['label'])
        || !isset($_POST['base'])
        || !isset($_POST['shapes'])
        || !isset($_POST['pattern'])
        || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }

    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // Get the values from the POST request
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $shapes = $_POST['shapes'];
    $tags = $_POST['tags'];
    $pattern = $_POST['pattern'];
    $base = $_POST['base'];

    $result=sql_update($conn, "number_patterns_to",[
        "label" => $label,
        "base" => $base,
        "shapes" => $shapes,
        "tags" => $tags,
        "pattern" => $pattern
    ], ["id"=>$id]);

    // Display result
    if ($result["status"]=="OK") {
        echo json_encode($result);
    } else {
        echo json_encode(["status" => "ERROR", "function" => "number_pattern_to_update", "message" => $result]);
    }
}

function verb_pattern_to_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $result = sql_get_one(
        $conn,
        "verb_patterns_to",
        [
            "label", "base", "category", "tags",
            "shapes_infinitive",
            "shapes_continuous",
            "shapes_future",
            "shapes_imperative",
            "shapes_past_active",
            "shapes_past_passive",
            "shapes_transgressive_cont",
            "shapes_transgressive_past",
            "shapes_auxiliary"
        ],
        ["id"=>$id]
    );
    echo json_encode($result);
    /*
    $sql="SELECT label, base, category, shapes_infinitive, shapes_continous, shapes_future, shapes_imperative, shapes_past_active, shapes_past_passive, shapes_past_passive, shapes_transgressive_cont, shapes_transgressive_past, shapes_auxiliary, tags FROM verb_patterns_to WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK",
                    "label"=>$row["label"],
                    "base"=>$row["base"],
                    "tags"=>$row["tags"],
                    "category"=>$row["category"],
                    "shapes_infinitive"=>$row["shapes_infinitive"],
                    "shapes_continous"=>$row["shapes_continous"],
                    "shapes_future"=>$row["shapes_future"],
                    "shapes_imperative"=>$row["shapes_imperative"],
                    "shapes_past_active"=>$row["shapes_past_active"],
                    "shapes_past_passive"=>$row["shapes_past_passive"],
                    "shapes_transgressive_cont"=>$row["shapes_transgressive_cont"],
                    "shapes_transgressive_past"=>$row["shapes_transgressive_past"],
                    "shapes_auxiliary"=>$row["shapes_auxiliary"]
                ]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"verb_pattern_to_item", "message" => $conn->error, "sql"=>$sql]);
    }*/
    $conn->close();
}

function verb_pattern_to_update() {
    // Check if all required parameters are set
    if (!isset($_POST['id'])
        || !isset($_POST['label'])
        || !isset($_POST['base'])
        || !isset($_POST['shapes'])
        || !isset($_POST['uppercase'])
        || !isset($_POST['gender'])
        || !isset($_POST['pattern'])
        || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }

    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // Get the values from the POST request
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $gender = (int)$_POST['gender'];
    $uppercase = (int)$_POST['uppercase'];
    $shapes = $_POST['shapes'];
    $tags = $_POST['tags'];
    $pattern = $_POST['pattern'];
    $base = $_POST['base'];

    // Update the noun_pattern_to table
    /*$sql="UPDATE noun_pattern_to SET".
        "label = '$label',".
        "base = '$base',".
        "gender = $gender,".
        "uppercase = $uppercase,".
        "shapes = '$shapes',".
        "tags = '$tags' WHERE id = $id;";

    $result=$conn->query($sql);*/

    $stmt = $conn->prepare("UPDATE noun_patterns_to SET 
        label = ?, 
        base = ?, 
        gender = ?, 
        uppercase = ?, 
        shapes = ?, 
        tags = ?, 
        pattern = ?
            WHERE id = ?");

    if ($stmt === false) {
        die(json_encode(["status" => "ERROR", "message" => $conn->error]));
    }

    // Bind parameters: 'ssiiisi' corresponds to the types:
    // s = string, i = integer
    $stmt->bind_param("ssiissi", $label, $base, $gender, $uppercase, $shapes, $tags, $pattern, $id);

    $result = $stmt->execute();

    // Display result
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "noun_pattern_to_update", "message" => $conn->error]);
    }
}
#endregion

#region basic
function simpleword_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT `from`, tags FROM simpleword_relations WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {  
        if ($result->num_rows > 0) {   
            while($row = $result->fetch_assoc()) {  
                // to translates rows
                $sqlTo="SELECT `id`, `shape`, `tags`, `comment`, `cite` FROM simplewords_to WHERE `relation` = '$id';";
                $resultTo = $conn->query($sqlTo);
                $to=[];
                if ($resultTo) {  
                    if ($resultTo->num_rows > 0) {   
                        while ($rowTo = $resultTo->fetch_assoc()) {   
                            $to[]=["id"=>$rowTo["id"], "shape"=>$rowTo["shape"], "tags"=>$rowTo["tags"], "comment"=>$rowTo["comment"], "cite"=>$rowTo["cite"]];
                        }
                    }

                    echo json_encode(["status"=>"OK", "from"=>$row["from"], "tags"=>$row["tags"], "to"=>$to]);
                    return;
                }
                echo json_encode(["status" => "ERROR", "message" => $conn->error, "sql"=>$sqlTo]);
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"simpleword_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function simpleword_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['falls']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $tags = $_POST['tags'];

    $sql= "UPDATE simpleword_relations SET `from` = '$label', pos = '$base', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "simpleword_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function phrase_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT `from`, pos, tags FROM phrase_relations WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // to translates rows
                $sqlTo="SELECT `id`, `shape`, `tags`, `comment`, `cite` FROM phrases_to WHERE `relation` = '$id';";
                $resultTo = $conn->query($sqlTo);
                $to=[];
                if ($resultTo) {
                    if ($resultTo->num_rows > 0) {
                        while ($rowTo = $resultTo->fetch_assoc()) {
                            $to[]=["id"=>$rowTo["id"], "shape"=>$rowTo["shape"], "tags"=>$rowTo["tags"], "comment"=>$rowTo["comment"], "cite"=>$rowTo["cite"]];
                        }
                    }

                    echo json_encode(["status"=>"OK", "from"=>$row["from"], "pos"=>$row["pos"], "tags"=>$row["tags"], "to"=>$to]);
                    return;
                }
                echo json_encode(["status" => "ERROR", "message" => $conn->error, "sql"=>$sqlTo]);
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"phrase_item", "message" => $conn->error, "sql"=>$sql]);
    }
    $conn->close();
}

function phrase_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['falls']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $tags = $_POST['tags'];

    $sql= "UPDATE phrase_relations SET `from` = '$label', pos = '$base', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "phrase_update", "message" => $conn->error, "sql"=>$sql]);
    }
}

function sentence_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT `from`, tags FROM sentence_relations WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // to translates rows
                $sqlTo="SELECT `id`, `shape`, `tags`, `comment`, pos, `cite` FROM sentences_to WHERE `relation` = '$id';";
                $resultTo = $conn->query($sqlTo);
                $to=[];
                if ($resultTo) {
                    if ($resultTo->num_rows > 0) {
                        while ($rowTo = $resultTo->fetch_assoc()) {
                            $to[]=["id"=>$rowTo["id"], "shape"=>$rowTo["shape"], "pos"=>$rowTo["pos"], "tags"=>$rowTo["tags"], "comment"=>$rowTo["comment"], "cite"=>$rowTo["cite"]];
                        }
                    }

                    echo json_encode(["status"=>"OK", "from"=>$row["from"], "tags"=>$row["tags"], "to"=>$to]);
                    return;
                }
                echo json_encode(["status" => "ERROR", "message" => $conn->error, "sql"=>$sqlTo]);
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"sentence_item", "message" => $conn->error, "sql"=>$sql]);
    }
    $conn->close();
}

function sentence_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['falls']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $tags = $_POST['tags'];

    $sql= "UPDATE sentence_relations SET `from` = '$label', pos = '$base', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "sentence_update", "message" => $conn->error, "sql"=>$sql]);
    }
}

function sentencepart_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT `from`, tags FROM sentencepart_relations WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                // to translates rows
                $sqlTo="SELECT `id`, `shape`, `tags`, `comment`, `cite`, pos FROM sentenceparts_to WHERE `relation` = '$id';";
                $resultTo = $conn->query($sqlTo);
                $to=[];
                if ($resultTo) {
                    if ($resultTo->num_rows > 0) {
                        while ($rowTo = $resultTo->fetch_assoc()) {
                            $to[]=["id"=>$rowTo["id"], "shape"=>$rowTo["shape"], "pos"=>$rowTo["pos"], "tags"=>$rowTo["tags"], "comment"=>$rowTo["comment"], "cite"=>$rowTo["cite"]];
                        }
                    }

                    echo json_encode(["status"=>"OK", "from"=>$row["from"], "tags"=>$row["tags"], "to"=>$to]);
                    return;
                }
                echo json_encode(["status" => "ERROR", "message" => $conn->error, "sql"=>$sqlTo]);
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"sentencepart_item", "message" => $conn->error, "sql"=>$sql]);
    }
    $conn->close();
}

function sentencepart_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['falls']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $tags = $_POST['tags'];

    $sql= "UPDATE sentencepart_relations SET `from` = '$label', pos = '$base', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);
    if ($result) {
        echo '{"status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "sentencepart_update", "message" => $conn->error, "sql"=>$sql]);
    }
}
#endregion

#region cites
function cite_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, data, type FROM cites WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {  
        if ($result->num_rows > 0) {   
            while($row = $result->fetch_assoc()) {  
                echo json_encode(["status"=>"OK", "label"=>$row["label"], "type"=>$row["type"], "params"=>$row["data"]]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function cite_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['type']) && !isset($_POST['params'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id = (int)$_POST['id'];
    $type = (int)$_POST['type'];
    $label = $conn->real_escape_string($_POST['label']);
    $params = $_POST['params'];

    // Ensure valid JSON
    if ($params !== null && json_decode($params) === null) {
        echo json_encode(["status" => "ERROR", "message" => "Not valid JSON."]);
        return;
    }

    $sql="UPDATE cites SET label = '$label', type = '$type', data = '$params' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "cite_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function pieceofcite_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT `label`, `parent`, `data`, `people`, `text`, `translated` FROM piecesofcite WHERE id = '$id';";

    $result = $conn->query($sql);
    if ($result) {  
        if ($result->num_rows > 0) {   
            while($row = $result->fetch_assoc()) {  
                echo json_encode([ 
                    "status"=>"OK", 
                    "label" =>$row["label"], 
                    "parent"=>$row["parent"], 
                    "data"  =>$row["data"],
                    "people"=>$row["people"],
                    "text"  =>$row["text"],
                    "translated"  =>$row["translated"]
                ]);
                return;
            }
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }       
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"cite_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function pieceofcite_update() {
    if (!isset($_POST['id']) || !isset($_POST['label'])&& !isset($_POST['parent'])  && !isset($_POST['text']) && !isset($_POST['people']) && !isset($_POST['cite'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id     = (int)$_POST['id'];
    $parent = (int)$_POST['parent'];
    $label  = $conn->real_escape_string($_POST['label']);
    $cite   = $_POST['cite'];
    $people = $_POST['people'];
    $text = $conn->real_escape_string($_POST['text']);
    $translated = $conn->real_escape_string($_POST['translated']);

    // Ensure valid JSON
    if ($cite !== null && json_decode($cite) === null) {
        echo json_encode(["status" => "ERROR", "message" => "Not valid cite JSON."]);
        return;
    } 
    if ($people !== null && json_decode($people) === null) {
        echo json_encode(["status" => "ERROR", "message" => "Not valid people JSON."]);
        return;
    }

    $sql="UPDATE piecesofcite SET label = '$label', parent = '$parent', people = '$people', cite = '$cite', `text` = '$text', `translated` = '$translated' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "cite_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}
#endregion

#region replaces
function replace_start_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT `from`, `to`, `label`  FROM replaces_start WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        // OK
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $label=$row["label"];
                $from=$row["from"];
                $to=$row["to"];

                echo json_encode(["status" => "OK", "from"=>$from, "to"=>$to, "label"=>$label]);
            }
        // EMPTY
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
    // ERROR
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"replace_start_item", "message" => $conn->error, "sql"=>$sql]);
    }
}

function replace_start_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $from  = $conn->real_escape_string($_POST['from']);
    $to    = $conn->real_escape_string($_POST['to']);
    $label = $from." > ".$to;

    $sql= /** @lang SQL */"UPDATE replaces_start SET `label` = '$label', `from` = '$from', `to` = '$to' WHERE id = $id;";
    $result=$conn->query($sql);

    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "replace_start_update",
            "sql"=>$sql
        ]);
    }
}

function replace_inside_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT `from`, `to`, `label`  FROM replaces_inside WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        // OK
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $label=$row["label"];
                $from=$row["from"];
                $to=$row["to"];

                echo json_encode(["status" => "OK", "from"=>$from, "to"=>$to, "label"=>$label]);
            }
            // EMPTY
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
        // ERROR
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"replace_inside_item", "message" => $conn->error, "sql"=>$sql]);
    }
}

function replace_inside_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $from  = $conn->real_escape_string($_POST['from']);
    $to    = $conn->real_escape_string($_POST['to']);
    $label = $from." > ".$to;

    $sql= /** @lang SQL */"UPDATE replaces_start SET `label` = '$label', `from` = '$from', `to` = '$to' WHERE id = $id;";
    $result=$conn->query($sql);

    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "replace_inside_update",
            "sql"=>$sql
        ]);
    }
}

function replace_end_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT `from`, `to`, `label`  FROM replaces_end WHERE id = '$id';";
    $result = $conn->query($sql);
    if ($result) {
        // OK
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $label=$row["label"];
                $from=$row["from"];
                $to=$row["to"];

                echo json_encode(["status" => "OK", "from"=>$from, "to"=>$to, "label"=>$label]);
            }
            // EMPTY
        } else {
            echo json_encode(["status" => "EMPTY"]);
        }
        // ERROR
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"replace_end_item", "message" => $conn->error, "sql"=>$sql]);
    }
}

function replace_end_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $from  = $conn->real_escape_string($_POST['from']);
    $to    = $conn->real_escape_string($_POST['to']);
    $label = $from." > ".$to;

    $sql= /** @lang SQL */"UPDATE replaces_end SET `label` = '$label', `from` = '$from', `to` = '$to' WHERE id = $id;";
    $result=$conn->query($sql);

    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "replace_end_update",
            "sql"=>$sql
        ]);
    }
}
#endregion

function replace_defined_noun_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    echo json_encode(
        sql_get($conn, "replaces_defined_noun", ["source", "to", "label", "fall", "gender", "number"/*, "tags_inc", "tags_not"*/],"`id` = '$id'")
    );
}

function replace_defined_noun_update() {
    if (!isset($_POST['id']) || !isset($_POST['source']) || !isset($_POST['to']) || !isset($_POST['label']) || !isset($_POST['fall']) || !isset($_POST['number']) || !isset($_POST['gender'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id     = (int)$_POST['id'];
    $source = $conn->real_escape_string($_POST['source']);
    $to     = $conn->real_escape_string($_POST['to']);
    $number = $conn->real_escape_string($_POST['number']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $fall   = $conn->real_escape_string($_POST['fall']);
    $label  = $source." > ".$to." ".$fall.".p/č.".$number;

    $sql= /** @lang SQL */"UPDATE replaces_defined_noun SET `label` = '$label', `source` = '$source', `to` = '$to', `label` = '$to' WHERE id = $id;";
    $result=$conn->query($sql);

    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "replace_defined_noun_update",
            "sql"=>$sql
        ]);
    }
}

function replace_defined_verb_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    echo json_encode(
        sql_get($conn, "replaces_defined_verb", ["source", "to", "label", "person", "gender", "number"/*, "tags_inc", "tags_not"*/],"`id` = '$id'")
    );
}

function replace_defined_verb_update() {
    if (!isset($_POST['id']) || !isset($_POST['source']) || !isset($_POST['to']) || !isset($_POST['label']) || !isset($_POST['fall']) || !isset($_POST['number']) || !isset($_POST['gender'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id     = (int)$_POST['id'];
    $source = $conn->real_escape_string($_POST['source']);
    $to     = $conn->real_escape_string($_POST['to']);
    $number = $conn->real_escape_string($_POST['number']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $fall   = $conn->real_escape_string($_POST['fall']);
    $label  = $source." > ".$to." ".$fall.".p/č.".$number;

    $sql= /** @lang SQL */"UPDATE replaces_defined_verb SET `label` = '$label', `source` = '$source', `to` = '$to', `label` = '$to' WHERE id = $id;";
    $result=$conn->query($sql);

    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "replace_defined_noun_update",
            "sql"=>$sql
        ]);
    }
}

function replace_defined_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    echo json_encode(
        sql_get($conn, "replaces_defined", ["source", "to", "label", "person", "gender", "number"/*, "tags_inc", "tags_not"*/],"`id` = '$id'")
    );
}

function replace_defined_update() {
    if (!isset($_POST['id']) || !isset($_POST['source']) || !isset($_POST['to']) || !isset($_POST['label']) || !isset($_POST['fall']) || !isset($_POST['number']) || !isset($_POST['gender'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id     = (int)$_POST['id'];
    $source = $conn->real_escape_string($_POST['source']);
    $to     = $conn->real_escape_string($_POST['to']);
    $number = $conn->real_escape_string($_POST['number']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $fall   = $conn->real_escape_string($_POST['fall']);
    $label  = $source." > ".$to." ".$fall.".p/č.".$number;

    $sql= /** @lang SQL */"UPDATE replaces_defined SET `label` = '$label', `source` = '$source', `to` = '$to', `label` = '$to' WHERE id = $id;";
    $result=$conn->query($sql);

    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "replace_defined_noun_update",
            "sql"=>$sql
        ]);
    }
}

function pieceofcite_merge(): void{
    if (!isset($_POST['current']) || !isset($_POST['with'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $current = (int)$_POST['current'];
    $with    = (int)$_POST['with'];

    // update parent of piece of cites
    {
        $sql= /** @lang SQL */"UPDATE `piecesofcite` SET `parent`=$with WHERE `parent`=$current;";
        $result=$conn->query($sql);

        if (!$result) {
            echo json_encode([
                "status" => "ERROR",
                "function" => "pieceofcite_merge",
                "sql"=>$sql
            ]);
            return;
        }
    }

    // remove cite
    {
        $sql= /** @lang SQL */"DELETE FROM `cites` WHERE `id`=$current LIMIT 1;";
        $result=$conn->query($sql);

        if (!$result) {
            echo json_encode([
                "status" => "ERROR",
                "function" => "pieceofcite_merge",
                "sql"=>$sql
            ]);
            return;
        }
    }
    echo '{ "status": "OK"}';
}

function place_region_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    echo json_encode(
        sql_get($conn, "place_regions", ["comment", "zone_type", "confinence"],"`id` = '$id'")
    );

    $conn->close();
}

function place_region_update() {
    if (!isset($_POST['id']) || !isset($_POST['comment']) || !isset($_POST['zone_type']) || !isset($_POST['confinence'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $comment  = $conn->real_escape_string($_POST['comment']);
    $zone_type  = (int)($_POST['zone_type']);
    $confinence  = (int)($_POST['confinence']);

    $sql= /** @lang SQL */"UPDATE place_regions SET `comment` = '$comment', `zone_type` = '$zone_type', `confinence` = '$confinence' WHERE id = $id;";
    $result=$conn->query($sql);

    if ($result) {
        echo '{ "status": "OK", "id": "'.$id.'"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "place_region_update",
            "message" => $conn->error,
            "sql"=>$sql
        ]);
    }
}

function place_lang_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    return json_encode(
        sql_get($conn, "place_langs", ["comment", "confinence"],"`id` = '$id'")
    );

    $conn->close();
}

function place_lang_update() {
    if (!isset($_POST['id']) || !isset($_POST['comment']) || !isset($_POST['confinence'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $comment  = $conn->real_escape_string($_POST['comment']);
    $confinence  = (int)($_POST['confinence']);

    $sql= /** @lang SQL */"UPDATE place_langs SET `comment` = '$comment',`confinence` = '$confinence' WHERE id = $id;";
    $result=$conn->query($sql);

    if ($result) {
        echo '{ "status": "OK", "id": "'.$id.'"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "place_lang_update",
            "message" => $conn->error,
            "sql"=>$sql
        ]);
    }
}

function place_nation_item() {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    return json_encode(
        sql_get($conn, "place_nations", ["comment", "confinence"],"`id` = '$id'")
    );

    $conn->close();
}

function place_nation_update() {
    if (!isset($_POST['id']) || !isset($_POST['comment']) || !isset($_POST['confinence'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $id    = (int)$_POST['id'];
    $comment  = $conn->real_escape_string($_POST['comment']);
    $zone_type  = (int)($_POST['zone_type']);
    $confinence  = (int)($_POST['confinence']);

    $sql= /** @lang SQL */"UPDATE place_regions SET `comment` = '$comment', `confinence` = '$confinence' WHERE id = $id;";
    $result=$conn->query($sql);

    if ($result) {
        echo '{ "status": "OK", "id": "'.$id.'"}';
    } else {
        echo json_encode([
            "status" => "ERROR",
            "function" => "place_region_update",
            "message" => $conn->error,
            "sql"=>$sql
        ]);
    }
}

function place_add() {
    if (!isset($_POST['table']) || !isset($_POST['translate']) || !isset($_POST['parent'])) {
        echo json_encode(["status" => "ERROR", "messgae"=>"chybí parametry"]);
        return;
    }
    $table=(string)$_POST['table']; // table name
    $parent=(int)$_POST['parent']; // belongs to
    $translate=(int)$_POST['translate']; // column asociated translate

    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $result=$conn->query("INSERT INTO place_{$table}s SET translate={$translate}, {$table}_id={$parent};");
    if ($result === TRUE) {
        echo json_encode(["status" => "OK", "insert_id"=>$conn->insert_id]);
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_add", "message" => $conn->error]);
    }
}

function place_remove() {
    if (!isset($_POST['table']) || !isset($_POST['translate']) || !isset($_POST['id'])) {
        echo json_encode(["status" => "ERROR", "messgae"=>"chybí parametry"]);
        return;
    }
    $table=(string)$_POST['table']; // table name
    $id=(int)$_POST['id']; // id of row
    $translate=(int)$_POST['translate']; // not necessary, asociated translate (if something happened then removed only for this translation)

    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $result=$conn->query("DELETE FROM place_{$table}s WHERE translate={$translate} AND id={$id};");
    if ($result === TRUE) {
        echo json_encode(["status" => "OK"]);
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_add", "message" => $conn->error]);
    }
}

function search_endings_noun(){
    if (
           !isset($_POST['gender'])
        || !isset($_POST['pattern'])
        || !isset($_POST['fall'])
        || !isset($_POST['number'])
        || !isset($_POST['translate'])
    ) {
        echo json_encode(["status"=>"ERROR", "message"=>"Missing values"]);
        return;
    }

    // parse input values
    $gender=(int)$_POST['gender'];
    $fall=(int)$_POST['fall'];
    $number=(int)$_POST['number'];
    $pattern=(int)$_POST['pattern'];
    $translate=(int)$_POST['translate'];

    // check if all are numbers
    if (!is_numeric($gender) || !is_numeric($fall) || !is_numeric($number) || !is_numeric($pattern)/**/ || !is_numeric($translate)) {
        echo json_encode(["status"=>"ERROR", "message"=>"Invalid input types"]);
        return;
    }

    // connect
    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $conn->set_charset("utf8mb4");

    // - load from database - //
    $arrShapeTranslates=[];
    {
        // relations
        $sql="SELECT `id`, `from` FROM `noun_relations` WHERE `translate`=$translate";
        $result=$conn->query($sql);
        $listFromIds=[];
        $listRelIds=[];
        if ($result === false) {
            echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
            return;
        }
        while ($row=$result->fetch_assoc()) {
            if ($row['from']!=null) {// if from is defined (=source of translate is set)
                $listFromIds[]=$row['from'];
                $listRelIds[]=$row['id'];
                $arrShapeTranslates[]=["id"=>$row['id'], "source_id"=>$row['from']];
            }
        }

        // cs source
        // build filte -> for sql IN (that filter)
        sort($listFromIds); // optional
        $strListFromIds = implode(',', $listFromIds);
        if (mb_strlen($strListFromIds)==0) {
            echo json_encode(["status"=> "ERROR", "message"=>"not found any src", "len"=>count($listFromIds)]);
            return;
        }

        $sql="SELECT `id`, `base`, `shapes` FROM `noun_patterns_cs` WHERE `id` IN ($strListFromIds)";
        if ($gender>0) $sql.="AND `gender`=$gender";
        if ($pattern>0)$sql.="AND `pattern`=$pattern";
        $sql.=";";

        $result=$conn->query($sql);
        if ($result === false) {
            echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
            return;
        }
        while ($row=$result->fetch_assoc()) {
            // find objects for link
            $search_id=$row['id'];
            foreach ($arrShapeTranslates as &$t) {
                if ($t["source_id"] == $search_id) {
                    $t["source"]=$row['shapes'];
                    $t["source_base"]=$row['base'];
                    break;
                }
            }
            unset($t);
        }

        // to ids
        $strListRelIds=implode(',', array_filter($listRelIds, function($v) {
            return $v !== null && $v !== '';
        }));
        $sql="SELECT `relation`, custombase, `shape` FROM `nouns_to` WHERE `relation` IN ($strListRelIds)";
        $result=$conn->query($sql);
        if ($result === false) {
            echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
            return;
        }
        $listTo=[];
        while ($row=$result->fetch_assoc()) {
           // if ($row['shape']!=null) { // no int id (not defined)
                $listTo[]=$row['shape'];

                foreach ($arrShapeTranslates as &$t) {
                    if ($t["id"]==$row['relation']) {
                        $t["to_id"]=$row['shape'];
                        $t["to_custombase"]=$row['custombase'];
                    }
                }
                unset($t);
          //  }
        }

        // to shapes
        $strListShapes=join(',', array_unique($listTo));
        $sql="SELECT `id`, base, `shapes` FROM `noun_patterns_to` WHERE `id` IN ($strListShapes)";
        $result=$conn->query($sql);
        if ($result === false) {
            echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
            return;
        }
        while ($row=$result->fetch_assoc()) {
            foreach ($arrShapeTranslates as &$t) {
                if (isset($t["to_id"]) && $t["to_id"]==$row['id']) {
                    $t["to_base"]=$row['base'];
                    $t["to"]=$row['shapes'];
                }
            }
            unset($t);
        }
    }

    // validate data, remove with empty "from" or "to"
    foreach ($arrShapeTranslates as $k => $t) {
        if (!isset($t["to"]) || !isset($t["source"])) {
            unset($arrShapeTranslates[$k]);
        }
    }


    // - Prepare values - //
    // split shapes
    // for example: $translateArr=[["source"=>"pánové", "to"=>"páňi"], ["source"=>"pohádky", "to"=>"pohátkê"]...];
    $translateArr=[];
    $index=($fall-1)/*1-7 -> 0-6*/+($number==1 ? 0 : 7); // index pos of pattern (7 falls singular + 7 falls multiple)
    $minstrlen=100;
    foreach ($arrShapeTranslates as &$t) {
        // falls, numbers = 14
        $splitFrom=explode('|', $t['source']);
        if (count($splitFrom)!=14){
            echo json_encode(["status"=>"ERROR", "message"=>"not 14 len src"]);
            return;
        }

        // select source variants of fall
        $splittedFrom=explode(',', $splitFrom[$index]);

        foreach ($splittedFrom as $fromVariant) {// dups by variants
            // falls, numbers = 14
            $splitTo=explode('|', $t['to']);
            if (count($splitTo)!=14) {
                echo json_encode(["status"=>"ERROR", "message"=>"not 14 len to"]);
                return;
            }

            // variants of fall
            $splittedTo=explode(',', $splitTo[$index]);

            $source=$t['source_base'].$fromVariant;

            if ($source == "") continue; // skip wrong data

            foreach ($splittedTo as $variant) {// dups by variants
                if ($variant!="-" && !str_contains($variant, "?")) { // not existing shape "-" (for example singular of word kalhoty does not exist, singular is filled with "-"); and not sure "?" (need to be edited) - ignore
                    // set parsed

                    if ($t['to_custombase']!=null) $to=$t['to_custombase'].$variant; // overwrited value (not use value from pattern)
                    else $to=$t['to_base'].$variant; // defined in pattern (fall and number)

                    if ($to == "") continue; // skip wrong data
                    if (mb_strlen($to)==1) continue; // skip wrong data, noun that have only one letter???

                    $translateArr[]=['source'=>$source, 'to'=>$to];

                    // set int min by smallest len by strings to and source
                    if ($minstrlen>mb_strlen($source)) $minstrlen=mb_strlen($source);
                    if ($minstrlen>mb_strlen($to)) $minstrlen=mb_strlen($to);
                }
            }
        }
    }
    unset($t);

    unset($arrShapeTranslates); // technical stuff not needed anymore

    // - Calculate stats - //
    // stats

    $stats=[];
    for ($ending_len=1; $ending_len<5 && $ending_len<$minstrlen; $ending_len++) { // for example: pohádkY-pohátkÊ, pohádKY-pohátKÊ, poháDKY-poháTKÊ, ...

        $statsOfEndings=[]; //for exmaple: $statsOfEndings=[["source"=>"ky", count=>3], ...]
        $total=0;
        foreach ($translateArr as $t) {
            $e=mb_substr($t["source"], mb_strlen($t["source"])-$ending_len); // current ending (for example "ky")

            // add if exists
            $notfound=true;
            foreach ($statsOfEndings as &$s) {
                if ($s["source"]==$e){
                    $s["count"]++;
                    $total++;
                    $notfound=false;
                    break;
                }
                unset($s);
            }
            // add new
            if ($notfound) {
                $statsOfEndings[]=["source"=>$e, "count"=>1];
                $total++;
            }
        }

        // sort $listFromStatsOfEndings by source_count
        usort($statsOfEndings, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        // if low count of occurences same source ending, then break $ending_len loop
        // tmp disable limits
      /*  {
            $someAboveLimit=false;
            foreach ($statsOfEndings as $s) {
                if ($s["count"]>2){
                    $someAboveLimit=true;
                    break;
                }
            }
            if (!$someAboveLimit) break;
        }*/

        // percent of source
        foreach ($statsOfEndings as &$tStats) {
            // calculate percents source
            $tStats["percent"]=$tStats["count"]/$total;
            unset($tStats["count"]);// not needed in output
        }

        // compute to
        foreach ($statsOfEndings as &$tStats) { //for exmaple: $statsOfEndings=[["source"=>"ky", count=>3], ...]

            // get array of to shape
            $toStatsEndings=[];
            $totalTo=0;
            foreach ($translateArr as &$s) { // for example: $translateArr=[["source"=>"pánové", "to"=>"páňi"], ["source"=>"pohádky", "to"=>"pohátkê"]...]; foreach and check if ends with "ky"
                if (mb_substr($s["source"], mb_strlen($s["source"])-$ending_len) == $tStats["source"]) {

                    $ending=mb_substr($s["to"], mb_strlen($s["to"])-$ending_len);

                    if ($tStats["source"] != $ending) { // only different, it's useless to replace "ky" to "ky"
                        $notfound=true;
                        foreach ($toStatsEndings as &$e) {
                            if ($ending == $e["ending"]) { //find existing ending stats to add
                                $e["count"]++;
                                $e["tr"][]=$s;
                                $notfound=false;
                                $totalTo++;
                                break;
                            }
                        }
                        unset($e);

                        if ($notfound) { // add new
                            $toStatsEndings[]=["ending"=>$ending, "count"=>1, "tr"=>[$s]];
                            $totalTo++;
                        }
                    }
                }
            }
            unset($s);

            // constant of min occurences
            $minPercent=0.90;
            $mincount=2;

            foreach ($toStatsEndings as &$tse) {
                // calculate percent to
                $tse["percent"]=$tse["count"]/$totalTo;

                // remove low occurences from list
                 if ($tse["percent"]<$minPercent) unset($tse);
                 else if ($tse["count"]<$mincount) unset($tse);

                 else unset($tse["count"]);// not needed in output
            }
            unset($tse);

            // attach to
          //  $tStats["to"]=$toStatsEndings;

            // add into array for example [["source"=>"ky", "percent"=>80, to=>[["ending"=>"kê", percent=>95], ...]]
            $stats[]=[
                "source_ending" => $tStats["source"],
                "percent" => round($tStats["percent"], 4),
                "to" => $toStatsEndings
            ];
        }
        unset($tStats);
    }

    // todo: check if not already exists

    // json output
    // for exemple: $data=[[...], [["source"=>"ky", "percent"=>80, to=>[["ending"=>"kê", percent=>95], ...]], ["source"=>"ke", "percent"=>6, to=>[...]], ...], ...]
    $json=json_encode(["status"=>"OK", "data"=>$stats,/* "_tr"=> $translateArr,"_ar"=>$arrShapeTranslates, "_s"=> $strListFromIds, */"srlen"=>$minstrlen],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        echo json_encode(["status"=>"ERROR", "JSON ERROR"=>json_last_error_msg()]);
        return;
    }
    echo $json;
}

function add_ending_noun(){
    if (
        !isset($_POST['gender'])
        || !isset($_POST['pattern'])
        || !isset($_POST['fall'])
        || !isset($_POST['number'])
        || !isset($_POST['translate'])
    ) {
        echo json_encode(["status"=>"ERROR", "message"=>"Missing values"]);
        return;
    }

    // parse input values
    $gender=(int)$_POST['gender'];
    $fall=(int)$_POST['fall'];
    $number=(int)$_POST['number'];
    $pattern=(int)$_POST['pattern'];
    $translate=(int)$_POST['translate'];


    // check if all are numbers
    if (!is_numeric($gender) || !is_numeric($fall) || !is_numeric($number) || !is_numeric($pattern)/**/ || !is_numeric($translate)) {
        echo json_encode(["status"=>"ERROR", "message"=>"Invalid input types"]);
        return;
    }

    // connect
    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $conn->set_charset("utf8mb4");

    $source=$conn->real_escape_string($_POST['source']);
    $to=$conn->real_escape_string($_POST['to']);

    // relations
    $label=$source.'>'.$to;
    $sql="INSERT INTO `replaces_defined_noun` (label, `source`, `to`, `translate`, `pattern`, `gender`, `fall`, `number`) VALUES ('$label', '$source', '$to', $translate, $pattern, $gender, $fall, $number)";
    $result=$conn->query($sql);

    if ($result) {
        echo json_encode(["status"=>"OK", "message"=>"added"]);
    } else {
        echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
        return;
    }
}

function search_endings_verb(){
    if(!isset($_POST['translate'])
    || !isset($_POST['class'])
    || !isset($_POST['type'])
    || !isset($_POST['gender'])
    || !isset($_POST['person'])
    || !isset($_POST['trans'])
    || !isset($_POST['number'])
    ) {
        echo json_encode(["status"=>"ERROR", "message"=>"Missing values"]);
        return;
    }

    // parse input values
    $type=(int)$_POST['type'];
    $gender=(int)$_POST['gender'];
    $person=(int)$_POST['person'];
    $number=(int)$_POST['number'];
    $trans=(int)$_POST['trans'];
    $class=(int)$_POST['class'];
    $translate=(int)$_POST['translate'];

    // check if all are numbers
    if(!is_numeric($gender)
    || !is_numeric($person)
    || !is_numeric($number)
    || !is_numeric($trans)
    || !is_numeric($type)
    || !is_numeric($class)
    || !is_numeric($translate)) {
        echo json_encode(["status"=>"ERROR", "message"=>"Invalid input types"]);
        return;
    }

    // connect
    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $conn->set_charset("utf8mb4");

    // - load from database - //
    $arrShapeTranslates=[];
    {
        // relations
        $sql="SELECT `id`, `from` FROM `verb_relations` WHERE `translate`=$translate";
        $result=$conn->query($sql);
        $listFromIds=[];
        $listRelIds=[];
        if ($result === false) {
            echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
            return;
        }
        while ($row=$result->fetch_assoc()) {
            if ($row['from']!=null) {// if from is defined (=source of translate is set)
                $listFromIds[]=$row['from'];
                $listRelIds[]=$row['id'];
                $arrShapeTranslates[]=["id"=>$row['id'], "source_id"=>$row['from']];
            }
        }

        // cs source
        // build filte -> for sql IN (that filter)
        sort($listFromIds); // optional
        $strListFromIds = implode(',', $listFromIds);
        if (mb_strlen($strListFromIds)==0) {
            echo json_encode(["status"=> "ERROR", "message"=>"not found any src", "len"=>count($listFromIds)]);
            return;
        }

        // shype pos (index of array)
        $sh_type="";
        $index=null; // index pos of shapes
        switch ($type){
            //   case 0: $index=-1; break;
            case 1: $index=0; $sh_type="infinitive"; break;
            case 2: $index=$number==1 ? $person : $person+3; $sh_type="continouns"; break;
            case 3: $index=$number==1 ? $person : $person+3; $sh_type="future"; break;
            case 4: $index=$number==1 ? $person : $person+3; break;
            case 5: $index=$number==1 ? $gender : $gender+4; break;
            case 6: $index=$number==1 ? $gender : $gender+4; break;
            case 7: $index=$trans; break;
            case 8: $index=$trans; break;
            case 9: $index=$number==1 ? $person : $person+3; break;
            default:
                echo json_encode(["status"=>"ERROR", "message"=>"not set type"]);
                return;
        }

        $sql="SELECT `id`, `base`, `shapes_{$sh_type}` FROM `verb_patterns_cs` WHERE `id` IN ($strListFromIds)";
        if ($class>0) $sql.="AND `class`=$class";
        $sql.=";";

        $result=$conn->query($sql);
        if ($result === false) {
            echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
            return;
        }
        while ($row=$result->fetch_assoc()) {
            // find objects for link
            $search_id=$row['id'];
            foreach ($arrShapeTranslates as &$t) {
                if ($t["source_id"] == $search_id) {
                    $t["source"]=$row['shapes_'.$sh_type];
                    $t["source_base"]=$row['base'];
                    break;
                }
            }
            unset($t);
        }

        // to ids
        $strListRelIds=implode(',', array_filter($listRelIds, function($v) {
            return $v !== null && $v !== '';
        }));
        $sql="SELECT `relation`, custombase, `shape` FROM `verbs_to` WHERE `relation` IN ($strListRelIds)";
        $result=$conn->query($sql);
        if ($result === false) {
            echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
            return;
        }
        $listTo=[];
        while ($row=$result->fetch_assoc()) {
            if ($row['shape']!=null)  // no int id (not defined)
                $listTo[]=$row['shape'];

            foreach ($arrShapeTranslates as &$t) {
                if ($t["id"]==$row['relation']) {
                    $t["to_id"]=$row['shape'];
                    $t["to_custombase"]=$row['custombase'];
                }
            }
            unset($t);
            //  }
        }

        // to shapes
        $strListShapes=join(',', array_unique($listTo));
        $sql="SELECT `id`, `base`, `shapes_{$sh_type}` FROM `verb_patterns_to` WHERE `id` IN ($strListShapes)";
     //   print_r($sql);
       // exit();
        $result=$conn->query($sql);
        if ($result === false) {
            echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
            return;
        }
        while ($row=$result->fetch_assoc()) {
            foreach ($arrShapeTranslates as &$t) {
                if (isset($t["to_id"]) && $t["to_id"]==$row['id']) {
                    $t["to_base"]=$row['base'];
                    $t["to"]=$row['shapes_'.$sh_type];
                }
            }
            unset($t);
        }
    }

    // validate data, remove with empty "from" or "to"
    foreach ($arrShapeTranslates as $k => $t) {
        if (!isset($t["to"]) || !isset($t["source"])) {
            unset($arrShapeTranslates[$k]);
        }
    }


    // - Prepare values - //
    // split shapes
    // for example: $translateArr=[["source"=>"pánové", "to"=>"páňi"], ["source"=>"pohádky", "to"=>"pohátkê"]...];
    $translateArr=[];


    $minstrlen=100;
    foreach ($arrShapeTranslates as &$t) {
        // falls, numbers = 14
        $splitFrom=explode('|', $t['source']);
        if (count($splitFrom)!=14){
            echo json_encode(["status"=>"ERROR", "message"=>"not 14 len src"]);
            return;
        }

        // select source variants of fall
        $splittedFrom=explode(',', $splitFrom[$index]);

        foreach ($splittedFrom as $fromVariant) {// dups by variants
            // falls, numbers = 14
            $splitTo=explode('|', $t['to']);
            if (count($splitTo)!=14) {
                echo json_encode(["status"=>"ERROR", "message"=>"not 14 len to"]);
                return;
            }

            // variants of fall
            $splittedTo=explode(',', $splitTo[$index]);

            $source=$t['source_base'].$fromVariant;

            if ($source == "") continue; // skip wrong data

            foreach ($splittedTo as $variant) {// dups by variants
                if ($variant!="-" && !str_contains($variant, "?")) { // not existing shape "-" (for example singular of word kalhoty does not exist, singular is filled with "-"); and not sure "?" (need to be edited) - ignore
                    // set parsed

                    if ($t['to_custombase']!=null) $to=$t['to_custombase'].$variant; // overwrited value (not use value from pattern)
                    else $to=$t['to_base'].$variant; // defined in pattern (fall and number)

                    if ($to == "") continue; // skip wrong data
                    if (mb_strlen($to)==1) continue; // skip wrong data, noun that have only one letter???

                    $translateArr[]=['source'=>$source, 'to'=>$to];

                    // set int min by smallest len by strings to and source
                    if ($minstrlen>mb_strlen($source)) $minstrlen=mb_strlen($source);
                    if ($minstrlen>mb_strlen($to)) $minstrlen=mb_strlen($to);
                }
            }
        }
    }
    unset($t);

    unset($arrShapeTranslates); // technical stuff not needed anymore

    // - Calculate stats - //
    // stats

    $stats=[];
    for ($ending_len=1; $ending_len<5 && $ending_len<$minstrlen; $ending_len++) { // for example: pohádkY-pohátkÊ, pohádKY-pohátKÊ, poháDKY-poháTKÊ, ...

        $statsOfEndings=[]; //for exmaple: $statsOfEndings=[["source"=>"ky", count=>3], ...]
        $total=0;
        foreach ($translateArr as $t) {
            $e=mb_substr($t["source"], mb_strlen($t["source"])-$ending_len); // current ending (for example "ky")

            // add if exists
            $notfound=true;
            foreach ($statsOfEndings as &$s) {
                if ($s["source"]==$e){
                    $s["count"]++;
                    $total++;
                    $notfound=false;
                    break;
                }
                unset($s);
            }
            // add new
            if ($notfound) {
                $statsOfEndings[]=["source"=>$e, "count"=>1];
                $total++;
            }
        }

        // sort $listFromStatsOfEndings by source_count
        usort($statsOfEndings, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        // if low count of occurences same source ending, then break $ending_len loop
        // tmp disable limits
        /*  {
              $someAboveLimit=false;
              foreach ($statsOfEndings as $s) {
                  if ($s["count"]>2){
                      $someAboveLimit=true;
                      break;
                  }
              }
              if (!$someAboveLimit) break;
          }*/

        // percent of source
        foreach ($statsOfEndings as &$tStats) {
            // calculate percents source
            $tStats["percent"]=$tStats["count"]/$total;
            unset($tStats["count"]);// not needed in output
        }

        // compute to
        foreach ($statsOfEndings as &$tStats) { //for exmaple: $statsOfEndings=[["source"=>"ky", count=>3], ...]

            // get array of to shape
            $toStatsEndings=[];
            $totalTo=0;
            foreach ($translateArr as &$s) { // for example: $translateArr=[["source"=>"pánové", "to"=>"páňi"], ["source"=>"pohádky", "to"=>"pohátkê"]...]; foreach and check if ends with "ky"
                if (mb_substr($s["source"], mb_strlen($s["source"])-$ending_len) == $tStats["source"]) {

                    $ending=mb_substr($s["to"], mb_strlen($s["to"])-$ending_len);

                    if ($tStats["source"] != $ending) { // only different, it's useless to replace "ky" to "ky"
                        $notfound=true;
                        foreach ($toStatsEndings as &$e) {
                            if ($ending == $e["ending"]) { //find existing ending stats to add
                                $e["count"]++;
                                $e["tr"][]=$s;
                                $notfound=false;
                                $totalTo++;
                                break;
                            }
                        }
                        unset($e);

                        if ($notfound) { // add new
                            $toStatsEndings[]=["ending"=>$ending, "count"=>1, "tr"=>[$s]];
                            $totalTo++;
                        }
                    }
                }
            }
            unset($s);

            // constant of min occurences
            $minPercent=0.90;
            $mincount=2;

            foreach ($toStatsEndings as &$tse) {
                // calculate percent to
                $tse["percent"]=$tse["count"]/$totalTo;

                // remove low occurences from list
                if ($tse["percent"]<$minPercent) unset($tse);
                else if ($tse["count"]<$mincount) unset($tse);

                else unset($tse["count"]);// not needed in output
            }
            unset($tse);

            // attach to
            //  $tStats["to"]=$toStatsEndings;

            // add into array for example [["source"=>"ky", "percent"=>80, to=>[["ending"=>"kê", percent=>95], ...]]
            $stats[]=[
                "source_ending" => $tStats["source"],
                "percent" => round($tStats["percent"], 4),
                "to" => $toStatsEndings
            ];
        }
        unset($tStats);
    }

    // todo: check if not already exists

    // json output
    // for exemple: $data=[[...], [["source"=>"ky", "percent"=>80, to=>[["ending"=>"kê", percent=>95], ...]], ["source"=>"ke", "percent"=>6, to=>[...]], ...], ...]
    $json=json_encode(["status"=>"OK", "data"=>$stats,/* "_tr"=> $translateArr,"_ar"=>$arrShapeTranslates, "_s"=> $strListFromIds, */"srlen"=>$minstrlen],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        echo json_encode(["status"=>"ERROR", "JSON ERROR"=>json_last_error_msg()]);
        return;
    }
    echo $json;
}

function add_ending_verb(){
    if (
        !isset($_POST['gender'])
        || !isset($_POST['pattern'])
        || !isset($_POST['fall'])
        || !isset($_POST['number'])
        || !isset($_POST['translate'])
    ) {
        echo json_encode(["status"=>"ERROR", "message"=>"Missing values"]);
        return;
    }

    // parse input values
    $gender=(int)$_POST['gender'];
    $fall=(int)$_POST['fall'];
    $number=(int)$_POST['number'];
    $pattern=(int)$_POST['pattern'];
    $translate=(int)$_POST['translate'];


    // check if all are numbers
    if (!is_numeric($gender) || !is_numeric($fall) || !is_numeric($number) || !is_numeric($pattern)/**/ || !is_numeric($translate)) {
        echo json_encode(["status"=>"ERROR", "message"=>"Invalid input types"]);
        return;
    }

    // connect
    $conn= new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $conn->set_charset("utf8mb4");

    $source=$conn->real_escape_string($_POST['source']);
    $to=$conn->real_escape_string($_POST['to']);

    // relations
    $label=$source.'>'.$to;
    $sql="INSERT INTO `replaces_defined_noun` (label, `source`, `to`, `translate`, `pattern`, `gender`, `fall`, `number`) VALUES ('$label', '$source', '$to', $translate, $pattern, $gender, $fall, $number)";
    $result=$conn->query($sql);

    if ($result) {
        echo json_encode(["status"=>"OK", "message"=>"added"]);
    } else {
        echo json_encode(["status"=>"ERROR", "message"=>"SQL failed: ".$conn->error]);
        return;
    }
}