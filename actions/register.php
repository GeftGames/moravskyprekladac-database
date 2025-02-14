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
        <h2>Register</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="hidden" name="action" value="register">
            <label>Username:</label>
            <input type="text" name="username" required><br>
            <label>Password:</label>
            <input type="new-password" name="password" required><br>
            <label>Email:</label>
            <input type="new-email" name="email" required><br>
            <p>Nové uživatele schvaluje admin.</p>  
            <label>Zpráva:</label><br>
            <textarea type="text" name="message" required></textarea><br>
            <button type="submit">Register</button>
        </form>
        <a href="./login.php">login</a>
    </div>
</body>
</html>