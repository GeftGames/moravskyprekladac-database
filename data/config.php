<?php
$GLOBALS["serverNameDB"] = "localhost";
$GLOBALS["usernameDB"] = "root";
$GLOBALS["passwordDB"] = "root";//root
$GLOBALS["databaseName"] = "mp_translator";
$GLOBALS["dev"] = true;

function connectToDatabase(){
    return new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
}

?>