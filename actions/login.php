<?php
session_start();
// rest api
require "../data/config.php";
include "../rest/handler.php";
if (isset($_POST["action"])) rest();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../data/style.css">
</head>
<body>
    <div class="centerForm">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="hidden" name="action" value="login">
            <label>Username:</label>
            <input type="text" name="username" required><br>
            <label>Password:</label>
            <input type="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
        <a href="./register.php">register</a>
    </div>
</body>
</html>