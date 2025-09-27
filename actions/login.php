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
    <script>
        async function submit() {
            document.getElementById("password_hash").value=await sha256(document.getElementById("password").value);
            document.getElementById("form_login").submit();

            async function sha256(message) {
                // encode as UTF-8
                const msgBuffer = new TextEncoder().encode(message);

                // hash the message
                const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);

                // convert ArrayBuffer to Array
                const hashArray = Array.from(new Uint8Array(hashBuffer));

                // convert bytes to hex string
                const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                return hashHex;
            }
        }
    </script>
</head>
<body>
    <div class="centerForm">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form id="form_login" method="POST" action="./login.php">
            <input type="hidden" name="action" value="login">
            <label>Username:</label>
            <input type="text" name="username" required><br>
            <label>Password:</label>
            <input type="password" required id="password"><input type="hidden" name="password" id="password_hash"><br>
            <button onclick="submit()">Login</button>
        </form>
        <!-- debug
        <form method="POST">
            <input type="hidden" name="action" value="login">           
            <input type="hidden" name="root" value="yes">           
            <button type="submit">dev root</button>
        </form> -->
        <a href="./register.php">register</a>
    </div>
</body>
</html>