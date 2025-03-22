<?php 
// Rest api 
namespace REST;

#region Lists
function list_add() {
    if (!isset($_POST['table'])) {
        throwError("Chybí list");
        return;
    }
    $table=(string)$_POST['table'];

    $conn= new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $result=$conn->query("INSERT INTO $table () VALUES ();");
    if ($result === TRUE) {
        list_items();
        // todo: get id and def
        // [id =>$conn.lastId, label => $namedef]
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_add", "message" => $conn->error]);
    } 
}

function list_relation_add() {
    $table=(string)$_POST['table'];

    $conn= new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

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

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
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

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $result=$conn->query("INSERT INTO $table (label, type, parent) SELECT label, type, parent FROM $table WHERE id = $id;");    
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $order="ORDER BY LOWER(label) ASC";
    $sql="SELECT id, label FROM $table $order;";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            $list=[];
            while($row = $result->fetch_assoc()) {
                $list[]=[$row["id"], $row["label"]];
            }
            echo json_encode($list);
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $sql="SELECT id FROM $table WHERE translate = $translate;";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $sqlFrom="SELECT `id`, `label` FROM `".$table."_cs_patterns`";
            $resultFrom = $conn->query($sql);
            if (!$resultFrom) {
                throwError("SQL error: ".$sqlFrom);
                return;
            }

            $list=[];
            while ($row = $result->fetch_assoc()) {
                $idRelation=$row["id"];
                $from=null;

                while ($rowFrom = $resultFrom->fetch_assoc()) {
                    if ($rowFrom['id']==$idRelation){
                        $from=$rowFrom;
                    }
                }

                if ($from!=null) {
                    $list[]=[$idRelation, $from["label"]];
                } else {
                    $list[]=[$idRelation, "<Neznámé>"];
                }
            }

            echo json_encode(["status" => "OK", "list" => $list]);
        } else echo json_encode([]);
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_items", "message" => $conn->error, "sql"=>$sql]);
    }
}
#endregion 

function region_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['type']) || !isset($_POST['parent']) || !isset($_POST['translates'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn= new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['label']);
    $type = (int)$_POST['type'];
    $parent = (int)$_POST['parent'];
    $translates = $_POST['translates'];

    $sql="UPDATE regions SET label = '$name', type = $type, parent = $parent, translates = $translates WHERE id = $id";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"region_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function region_item() {
    if (!isset($_POST['idregion'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['idregion'];

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, type, parent, translates FROM regions WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while($row = $result->fetch_assoc()) {
                $name=$row["label"];
                $type=$row["type"];
                $parent=$row["parent"];
                $translates=$row["translates"];
                if ($type==null)$type=-1;
                if ($parent==null)$parent=-1;
                echo '{"status": "OK", "label": "'.$name.'", "type": '.$type.', "parent": '.$parent.', "translates": '.$translates.'}';
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

function noun_pattern_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, base, gender, shapes, tags FROM noun_patterns_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "label"=>$row["label"], "base"=>$row["base"], "tags"=>$row["tags"], "shapes"=>$row["shapes"], "gender"=>$row["gender"]]);
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
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['base']) || !isset($_POST['shapes']) || !isset($_POST['gender']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn= new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $gender = (int)$_POST['gender'];
    $shapes = $_POST['shapes'];
    $tags = $_POST['tags'];
    $base = $_POST['base'];

    $sql="UPDATE noun_pattern_cs SET label = '$label', base = '$base', gender = $gender, shapes = '$shapes', tags = '$tags' WHERE id = $id;";
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

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
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
    $conn= new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
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

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, base, category, shapes, tags FROM pronoun_patterns_cs WHERE id = '$id';";
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
        echo json_encode(["status" => "ERROR", "function"=>"pronoun_pattern_cs_item", "message" => $conn->error, "sql"=>$sql]);
    } 
    $conn->close();
}

function pronoun_pattern_cs_update() {
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['base']) || !isset($_POST['shapes']) || !isset($_POST['category']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $shapes = $_POST['shapes'];
    $category = (int)$_POST['category'];
    $tags = $_POST['tags'];

    $sql="UPDATE pronoun_patterns_cs SET label = '$label', base = '$base', shapes = '$shapes', category = $category, tags = '$tags' WHERE id = $id;";
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

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
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

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, base, shapetype, shapes, category, tags FROM verb_patterns_cs WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {    
        if ($result->num_rows > 0) {    
            while($row = $result->fetch_assoc()) {
                echo json_encode(["status"=>"OK", "label"=>$row["label"], "base"=>$row["base"], "shapetype"=>$row["shapetype"], "tags"=>$row["tags"], "category"=>$row["category"], "shapes"=>$row["shapes"]]);
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
    if (!isset($_POST['id']) || !isset($_POST['label']) || !isset($_POST['base']) || !isset($_POST['shapes']) || !isset($_POST['shapetype']) || !isset($_POST['category']) || !isset($_POST['tags'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $shapetype = $_POST['shapetype'];
    $shapes = $_POST['shapes'];
    $category = $_POST['category'];
    $tags = $_POST['tags'];

    $sql="UPDATE verb_pattern_cs SET label = '$label', base = '$base', shapetype = '$shapetype', shapes = '$shapes', category = '$category', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "verb_pattern_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function preposition_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT shape, falls, tags FROM preposition_cs WHERE id = '$id';";
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
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

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT shape, tags FROM conjunction_cs WHERE id = '$id';";
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
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

function adverb_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT shape, tags FROM adverb_cs WHERE id = '$id';";
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $shape = $conn->real_escape_string($_POST['shape']);
    $tags = $_POST['tags'];

    $sql="UPDATE adverb_cs SET shape = '$shape', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "adverb_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function interjection_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT shape, tags FROM interjection_cs WHERE id = '$id';";
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $shape = $conn->real_escape_string($_POST['shape']);
    $tags = $_POST['tags'];

    $sql="UPDATE interjection_cs SET shape = '$shape', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "interjection_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function particle_cs_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT shape, tags FROM particle_cs WHERE id = '$id';";
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $shape = $conn->real_escape_string($_POST['shape']);
    $tags = $_POST['tags'];

    $sql="UPDATE particle_cs SET shape = '$shape', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "particle_cs_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function phrase_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT shape_from, pos, tags FROM phrase_relations WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {  
        if ($result->num_rows > 0) {   
            while($row = $result->fetch_assoc()) {  
                // to translates rows
                $sqlTo="SELECT id, shape, tags, comment, source FROM phrase_to WHERE parent = '$id';";
                $resultTo = $conn->query($sqlTo);
                $to=[];
                if ($resultTo) {  
                    if ($resultTo->num_rows > 0) {   
                        while ($rowTo = $resultTo->fetch_assoc()) {   
                            $to[]=["id"=>$rowTo["id"], "shape"=>$rowTo["shape"], "tags"=>$rowTo["tags"], "comment"=>$rowTo["comment"], "source"=>$rowTo["source"]];
                        }
                    }

                    echo json_encode(["status"=>"OK", "shape_from"=>$row["shape_from"], "pos"=>$row["pos"], "tags"=>$row["tags"], "to"=>$to]);
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $base = $_POST['base'];
    $tags = $_POST['tags'];

    $sql="UPDATE phrase_relationship SET shape_from = '$label', pos = '$base', tags = '$tags' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "phrase_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function cite_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT label, data FROM cites WHERE id = '$id';";
    $result = $conn->query($sql);

    if ($result) {  
        if ($result->num_rows > 0) {   
            while($row = $result->fetch_assoc()) {  
                echo json_encode(["status"=>"OK", "label"=>$row["label"], "params"=>$row["data"]]);
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
    if (!isset($_POST['id']) || !isset($_POST['label']) && !isset($_POST['params'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id = (int)$_POST['id'];
    $label = $conn->real_escape_string($_POST['label']);
    $params = $_POST['params'];

    // Ensure valid JSON
    if ($params !== null && json_decode($params) === null) {
        echo json_encode(["status" => "ERROR", "message" => "Not valid JSON."]);
        return;
    }

    $sql="UPDATE cites SET label = '$label', data = '$params' WHERE id = $id;";
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

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
  
    $sql="SELECT `label`, `parent`, `cite`, `people`, `text` FROM piecesofcite WHERE id = '$id';";

    $result = $conn->query($sql);
    if ($result) {  
        if ($result->num_rows > 0) {   
            while($row = $result->fetch_assoc()) {  
                echo json_encode([ 
                    "status"=>"OK", 
                    "label" =>$row["label"], 
                    "parent"=>$row["parent"], 
                    "cite"  =>$row["cite"], 
                    "people"=>$row["people"],
                    "text"  =>$row["text"]
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
    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    $id     = (int)$_POST['id'];
    $parent = (int)$_POST['parent'];
    $label  = $conn->real_escape_string($_POST['label']);
    $cite   = $_POST['cite'];
    $people = $_POST['people'];
    $text = $conn->real_escape_string($_POST['text']);

    // Ensure valid JSON
    if ($cite !== null && json_decode($cite) === null) {
        echo json_encode(["status" => "ERROR", "message" => "Not valid cite JSON."]);
        return;
    } 
    if ($people !== null && json_decode($people) === null) {
        echo json_encode(["status" => "ERROR", "message" => "Not valid people JSON."]);
        return;
    }

    $sql="UPDATE piecesofcite SET label = '$label', parent = '$parent', people = '$people', cite = '$cite', `text` = '$text' WHERE id = $id;";
    $result=$conn->query($sql);    
    if ($result) {
        echo '{ "status": "OK"}';
    } else {
        echo json_encode(["status" => "ERROR", "function" => "cite_update", "message" => $conn->error, "sql"=>$sql]);
    } 
}

function noun_relation_item() {
    if (!isset($_POST['id'])){
        echo json_encode(["status" => "ERROR", "message" => "ID is missing"]);
        return;
    }
    $id = $_POST['id'];

    $conn=new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    $relation_id="";
    $from=-1;

    $sql="SELECT `id`, `from` FROM noun_relations WHERE id = '$id';";
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
    $sqlTo="SELECT `id`, `priority`, `shape`,`comment`, `cite` FROM `noun_to` WHERE `relation` = '$relation_id';";
    $result = $conn->query($sqlTo);
    if (!$result) {
        throwError("SQL error: ".$sqlTo);
        return;
    }
    while ($row = $result->fetch_assoc()) {
        $listTo[]=["id"=>$row["id"], "priority"=>$row["priority"], "shape"=>$row["shape"], "comment"=>$row["comment"], "cite"=>$row["cite"]];
    }

    echo json_encode([
        "status"=>"OK",
        "from" =>$from,
        "to"=>json_encode($listTo)
    ]);
    $conn->close();
}

function noun_relation_update() {
    if (!isset($_POST['id']) || !isset($_POST['from']) || !isset($_POST['to'])) {
        echo '{ "status": "ERROR", "message": "Nelze aktualizovat '.$_POST['id'].', chybí parametry."}';
        return;
    }
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

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
        $sqlTo= /** @lang SQL */"UPDATE noun_to SET `priority` = '$toPriority' WHERE id = $toId;";
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