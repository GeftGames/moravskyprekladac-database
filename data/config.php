<?php
$GLOBALS["serverNameDB"] = "localhost";
$GLOBALS["usernameDB"] = "root";
$GLOBALS["passwordDB"] = "";
$GLOBALS["databaseName"] = "mp_translator";
$GLOBALS["dev"] = true;

/*
$conn_check = new mysqli($servername, $username, $password);

// Check database exists
$result = $conn_check->query("SELECT SCHEMA_NAME  FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$databaseName."'");
if ($result->num_rows == 0) {
    header("Location: ./actions/inicialize_new_database.php");
    exit();
}

// Check database exists
$result = $conn->query("SHOW TABLES LIKE '$tableName'");
if ($result->num_rows == 0) {
    header("Location: ./actions/inicialize_new_database.php");
    exit();
}

$conn = new mysqli($servername, $username, $password, $databaseName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}*/
?>