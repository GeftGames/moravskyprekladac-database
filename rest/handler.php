<?php 
function throwError($string) {
    $GLOBALS["error"]="ERROR: ".$string;
}

function rest(){
    require_once "global_database.php";
    require_once "user_manipulation.php";

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
            //if ($GLOBALS["dev"]) echo $function.", ";
        }
    }

    // Simulate user input
    $fullFunctionName = $namespace.$action;

    if (in_array($fullFunctionName, $allowedActions)) {
        $ret=call_user_func($fullFunctionName);
    } else {
        throwError("Invalid action: ".$fullFunctionName);
        return;
    }
}
?>