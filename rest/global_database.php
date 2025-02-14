<?php 
// Rest api - database global tools 
namespace REST;

function database_init() {
    if (!isset($_POST["password"]) || !isset($_POST["email"])) {
        throwError("Přihlašovací údaje jsou nekorektní!");
        return;
    }
    $hashPassword=md5($_POST["password"]);
    
    // Create connection
    $conn_newDB = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"]);

    // Check connection
    if ($conn_newDB->connect_error) {
        throwError("Connection failed: " . $conn_newDB->connect_error);
        exit();
    }

    // Check database
    $result = $conn_newDB->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$GLOBALS["databaseName"]."'");
    if ($result->num_rows > 0) {
        throwError("database exist");
        exit();
    }

    // Create database
    $sql = "CREATE DATABASE ".$GLOBALS["databaseName"];
    if ($conn_newDB->query($sql) === TRUE) {
        //echo "Database created successfully";
    } else {
        throwError("Error creating database: " . $conn_newDB->error);
    }

    // create tables
    $sqls = [ 
        // users
        "CREATE TABLE users (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username varchar(255),
            userPassword varchar(255),
            email varchar(255),
            usertype TINYINT,
            activated TINYINT
        );",
        "INSERT INTO users (username, userPassword, email, usertype, activated)
            VALUES ('".$_POST["username"]."', '".$hashPassword."', '".$_POST["email"]."', 1, 1);",

        // From cs
        "CREATE TABLE noun_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            base varchar(255),
            gender TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE adjective_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            base varchar(255),
            pattern_type TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE pronoun_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            base varchar(255),
            pattern_type TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE number_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            base varchar(255),
            pattern_type TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE verb_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            pattern_type_show TINYINT,
            base varchar(255),
            infinitive varchar(255),
            continous varchar(255),
            future varchar(255),
            past_active varchar(255),
            past_passive varchar(255),
            imperative varchar(255),
            transgressive_present varchar(255),
            transgressive_past varchar(255),
            conditional varchar(255),
            shapes TEXT
        );",
        "CREATE TABLE preposition_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shapes varchar(255),
            fall TINYINT
        );",

        // Translate to
        "CREATE TABLE noun_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            base varchar(255),
            gender TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE adjective_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            base varchar(255),
            pattern_type TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE pronoun_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            base varchar(255),
            pattern_type TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE number_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            base varchar(255),
            pattern_type TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE verb_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            pattern_type_show TINYINT,
            reversible TINYINT,
            base varchar(255),
            infinitive varchar(255),
            continous varchar(255),
            future varchar(255),
            past_active varchar(255),
            past_passive varchar(255),
            imperative varchar(255),
            transgressive_present varchar(255),
            transgressive_past varchar(255),
            conditional varchar(255),
            shapes text
        );",
        "CREATE TABLE preposition_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shapes varchar(255),
            fall TINYINT
        );",

        // relations
        "CREATE TABLE noun (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            uppercase TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE adjectives (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            uppercase TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE pronoun (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate int,
            uppercase TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE adverb (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shapes TEXT
        );",
        "CREATE TABLE preposition (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate int,
            shapes TEXT
        );",
        "CREATE TABLE conjuctions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shapes TEXT
        );",
        "CREATE TABLE particle (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shapes TEXT
        );",
        "CREATE TABLE interjection (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shapes TEXT
        );",

        // translate
        "CREATE TABLE translate (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translateName varchar(255),
            administrativeTown varchar(255),
            gpsX float,
            gpsY float,
            region INT,
            country TINYINT,
            langtype TINYINT,
            quality TINYINT,
            dialect TINYINT,
            editors varchar(255),
            devinfo TEXT,
            options TEXT
        );",

        // kniha
        "CREATE TABLE source (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,            
            datatext TEXT
        );",
        // Ukázky
        "CREATE TABLE pieceofsource (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,            
            datatext TEXT
        );",
        // log
        "CREATE TABLE logs (
            created DATE,            
            logtext TEXT
        );"
    ];

    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    foreach ($sqls as $sql) {
        if ($conn->query($sql) === TRUE) {
            // Table created successfully
            if ($dev) echo "<p>".$sql."</p>";
        } else {
            $name=$sql.substr(0,$sql.insdexOf("("));
            throwError("SQL Error: '$name': " . $conn->error);
        }
    }

    $_SESSION['username']="admin";
    $_SESSION['usertype']=1;
    $GLOBALS["done"]="Database initialized!";
    header("Location: ../index.php");
}

function database_backup() {
    if (!isset($_SESSION["username"])) {
        throwError("Néste přehlášené!");
        return;
    }
	exec("mysqldump -u USER -p PASSWORD DATABASE > dump.sql");  // backup
}

function database_load() {
    if (!isset($_SESSION["username"])) {
        throwError("Néste přehlášené!");
        return;
    }
    exec("mysql -u USER -p PASSWORD < dump.sql");               // restore
	
}

function database_importold() {
    if (!isset($_SESSION["username"])) {
        throwError("Nejste přehlášený!");
        return;
    }

    if (empty($_FILES["database_files"]["name"][0])) {
        throwError("Chybí soubor databáze!");
        return;
    }  
    
    // mysql connect
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // enums
    require "./data/enum_region.php";

    // File settings
    $files=$_FILES["database_files"];

    // Files
    foreach ($files["tmp_name"] as $key => $tmpName) {
        // Check before upload
        if (pathinfo($files["name"][$key], PATHINFO_EXTENSION) !== "trw") {
            throwError("Neplatný typ souboru!");
            return;
        }
      
        $fileContent= file_get_contents($tmpName); 
        $lines = preg_split("/\r\n|\n|\r/", $fileContent);

        if ($fileContent === false) {
            throwError("Chyba při čtení souboru: " . htmlspecialchars($files["name"][$key]));
            continue;
        }

        if ($lines[0]!="TW v4") {
            throwError("Databáze není typu 'TW v4': '".$lines[0]."'");
            return;
        }

        // head atributes
        $name="";
        $gpsX=0;
        $gpsY=0;
        $comment="";
        $editors="";
        $region="";
        $country=-1;
        $devinfo="";
        $original="";
        $administrativeTown="";
        $quality=0;
        $recTranscription=-1;
        $options="";
        $langtype=1;
        $dialect=0;

        foreach ($lines as $line) {
            if (is_string($line) && strlen($line) > 0) {
                if ($line[0]=="-") break;
                switch ($line[0]){
                    case "t": 
                        $name=substr($line,1);
                        break;

                    case "c": 
                        $comment=substr($line,1);
                        break;

                    case "a": 
                        $editors=substr($line,1);
                        break;

                    case "b": 
                        $cites=substr($line,1);
                        break;

                    case "o": 
                        $region=GetRegionCode(explode(">", substr($line, 1)));
                        break;

                    case "u": 
                        $country=intval(substr($line,1));
                        break;

                    case "i": 
                        $devinfo=substr($line,1);
                        break;

                    case "r": 
                        $original=substr($line,1);
                        break;

                    case "s": 
                        $administrativeTown=substr($line,1);
                        break;

                    case "q": 
                        $quality=intval(substr($line, 1));
                        break;

                    case "y": 
                        $recTranscription=intval(substr($line, 1));
                        break;
                        
                    case "e": 
                        $options=substr($line,1);
                        break;

                    case "g":
                        $gps=substr($line, 1);
                        if (str_contains($gps, ",")) {
                            $gpsRaw = explode(",", $gps);
                            $gpsX = floatval($gpsRaw[0]);
                            $gpsY = floatval($gpsRaw[1]);
                        }
                        break;
                }
            }
        }

        // sql
        $sql="INSERT INTO translate (translateName, administrativeTown, gpsX, gpsY, region, country, langtype, quality, dialect, editors, devinfo, options) SELECT ".
        "'".$name."', '".$administrativeTown."', ".$gpsX.", ".$gpsY.", ".$region.", ".$country.", ".$langtype.", ".$quality.", ".$dialect.", '".$editors."', '".$devinfo."', '".$options."'".
        "WHERE NOT EXISTS (SELECT 1 FROM translate WHERE translateName = '".$name."')";
        //  echo $sql;
     //   $result = $conn->query($sql);
        if ($conn->query($sql) === TRUE) {            
            //ok
            if (mysqli_affected_rows($conn) > 0) {
            } else {
                throwError("Překlad s tímto názvem už v databázi existuje!");
            }
        }else{
            throwError("Chyba vkládání do databáze!");
        }

    }/**/
}

function throwError($string) {
    $GLOBALS["error"]=$string;
}
?>