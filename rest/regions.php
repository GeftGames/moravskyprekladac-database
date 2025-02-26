<?php 
// Rest api 
namespace REST;

#region Lists
function list_add() {
    if (!isset($_POST['table'])){
        throwError("Chybí list");
        return;
    }
    $table=(string)$_POST['table'];

    $conn= new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $namedef = $conn->real_escape_string("Výchozí");
    $result=$conn->query("INSERT INTO $table (label) VALUES ('$namedef');");
    if ($result === TRUE) {
        list_items();
        // todo: get id and def
        // [id =>$conn.lastId, label => $namedef]
    } else {
        echo json_encode(["status" => "ERROR", "function"=>"list_add", "message" => $conn->error]);
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

    $sql="SELECT id, label FROM $table;";
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
  
    $sql="SELECT label, base, gender, shapes, tags FROM noun_pattern_cs WHERE id = '$id';";
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