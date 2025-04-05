<?php
session_start();

// Check database exists
include "../data/config.php";
$conn_check = new mysqli($serverNameDB, $usernameDB, $passwordDB);

$result = $conn_check->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$databaseName."'");
if ($result->num_rows > 0) {
    $empty=false;  
}else $empty=true;

// rest api
include "../rest/handler.php";
if (isset($_POST["action"])) rest();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicializace databáze</title>
    <link rel="stylesheet" href="../data/style.css">
</head>
<body>
    <div class="centerForm">
        <h2>Inicializace databáze</h2>
        <p>Chcete vytvořit novou databázi?</p>
        <?php if (isset($SESSION["error"])) echo "<p class='error'>".$SESSION["error"]."</p>"; ?>
        <?php if (isset($SESSION["done"])) echo "<p class='done'>".$SESSION["done"]."</p>"; ?>
        <form method="POST">
            <?php if ($empty) :?>
                <input type="hidden" name="action" value="database_init">
                <p style="font-weight: bold;">Nový účet admina</p>
                <label>Uživatelské jméno:</label>
                <input type="text" name="username" value="root" placeholder="admin" autocomplete="on" required><br>
                <label>Heslo:</label>
                <input type="password" name="password" autocomplete="on" required><br>
                <label>Email:</label>
                <input type="email" name="email" autocomplete="on"><br>
               <!-- <label>Importovat databázi:</label>
                <input type="file" accept="*.sql">-->
                <button type="submit">Inicializovat</button>
            <?php else: ?>
               <p class="error">Databáze není prázná, nejdříve ji smažte, jinak nepůjde inicializovat nová!</p>
               <p>„DROP DATABASE `mp_translator`“</p>
               <a href="../index.php">dashboard</a>
            <?php endif; ?>       
        </form>
    </div>
</body>
</html>