<?php 
function throwError($string) {
    $GLOBALS["error"]="ERROR: ".$string;
    echo "ERROR: ".$string;
}

function rest(){   
   
    require_once "global_database.php";
    require_once "user_manipulation.php";
    require_once "regions.php";

    $action=$_POST['action'];

    if ($action=="") {
        throwError("Invalid empty action!");
        return;
    }

    $namespace = "rest\\";
 
    // List of all allowed functions in namespace
    $allFunctions=get_defined_functions()["user"];
    $allowedActions=[];
    foreach ($allFunctions as $function) {
        if (str_starts_with($function, $namespace)) {
            $allowedActions[] = $function;
        }
    }

    // Simulate user input
    $fullFunctionName = $namespace.$action;
   
    if (in_array($fullFunctionName, $allowedActions)) {
        // Check login
        if ($action!="register" && $action!="login" && $action!="database_init") {
            if (!isset($_SESSION["username"])) {
                throwError("Néste přehlášené!");
               return;
            } 
        }

        // call functions in files
        $ret=call_user_func($fullFunctionName);
    } else { 
        throwError("Invalid action: ".$fullFunctionName);
        return;
    }
}
?>