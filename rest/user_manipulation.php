<?php 
// Rest api - login system
namespace REST;

function register() {
	$name=$_POST['username'];
    $password=$_POST['password'];
    $email=$_POST['email'];   

    if (!isset($_POST["password"]) || !isset($_POST["email"])) {
        throwError("Přihlašovací údaje jsou nekorektní!");
        return;
    }
    
    $hashPassword=md5($_POST["password"]);

    // register
    $sql = "INSERT INTO users (username, userPassword, email, usertype, activated)
            VALUES ('admin', '".$hashPassword."', '".$email."', 0, 0);";

    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);  
    if ($conn->query($sql) === TRUE) {
        // Table created successfully
        if ($GLOBALS["dev"]) echo "<p>".$sql."</p>";
    } else {
        $name=$sql.substr(0, strpos($sql,"("));
        throwError("SQL Error: '$name': " . $conn->error);
    }   
}

function login() {
    $username=$_POST['username'];
    $hashPassword=md5($_POST["password"]);
 
    // root
    if (isset($_POST['root'])) {
        $_SESSION["username"]="root";
        $_SESSION["usertype"]=0;
        header("Location: ../index.php");
    }

    if (!isset($_POST['username'])) throwError("Vyplňte jméno");
    if (!isset($_POST['password'])) throwError("Vyplňte heslo");
   

    // check
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    
    if ($conn->connect_error) {
        throwError("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT usertype, activated FROM users
            WHERE username='".$username."' AND userPassword='".$hashPassword."';";
    $result = $conn->query($sql);

    if ($result->num_rows ==1) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            $activated=$row["activated"];
            if ($activated==1) {
                $_SESSION["username"]=$username;
                $_SESSION["usertype"]=$row["usertype"];

                header("Location: ../index.php");
            }else{
                throwError("Účet nebyl aktivován.");
            }
        }
    } else {
        throwError("Účet nebyl nalezen.");
    }
}

function deleteUser() {
	$name=$_POST['name'];
    $password=$_POST['password'];

    // TODO: delete current loginned user
}

?>