<?php 
// Rest api - database global tools 
namespace REST;
require_once "help.php";

function database_init(): void{
    $dev=$GLOBALS["dev"];
    if (!isset($_POST["password"]) || !isset($_POST["email"])) {
        throwError("Přihlašovací údaje jsou nekorektní!");
        return;
    }
    $hashPassword=$_POST["password"];
    
    // Create connection
    $conn_newDB = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"]);
    $conn_newDB->set_charset("utf8mb4");

    // Check connection
    if ($conn_newDB->connect_error) {
        throwError("Connection failed: " . $conn_newDB->connect_error);
        exit();
    }


    header("location: index.php");

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

    $namedef = $conn_newDB->real_escape_string("Výchozí");

    // create tables
    $sqls = [ 
        // users
        "CREATE TABLE users (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username varchar(255) DEFAULT 'User',
            userPassword varchar(255),
            email varchar(255),
            usertype TINYINT,
            activated TINYINT
        );",
        "INSERT INTO users (username, userPassword, email, usertype, activated)
            VALUES ('".$_POST["username"]."', '".$hashPassword."', '".$_POST["email"]."', 1, 1);",

        // From cs tags=[nonstandart, expr. , mor., val., ...]
        "CREATE TABLE noun_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes TEXT,
            gender TINYINT NOT NULL DEFAULT 0,
            pattern TINYINT NOT NULL DEFAULT 0,
            uppercase TINYINT NOT NULL DEFAULT 0,
            tags VARCHAR(255)
        );",
        "CREATE TABLE adjective_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes TEXT,
            category TINYINT DEFAULT 0,
            tags VARCHAR(255)
        );",
        "CREATE TABLE pronoun_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes TEXT,
            category TINYINT DEFAULT 0 NOT NULL,
            syntax TINYINT DEFAULT 0 NOT NULL,
            pattern TINYINT DEFAULT 0 NOT NULL,
            uppercase TINYINT DEFAULT 0 NOT NULL,
            tags VARCHAR(255)
        );",
        "CREATE TABLE number_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes TEXT,
            pattern_type TINYINT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE verb_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT DEFAULT NULL,
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes_infinitive TEXT,
            shapes_continuous TEXT,
            shapes_future TEXT,
            shapes_imperative TEXT,
            shapes_past_active TEXT,
            shapes_past_passive TEXT,
            shapes_transgressive_cont TEXT,
            shapes_transgressive_past TEXT,
            shapes_auxiliary TEXT,
            category TINYINT,
            class TINYINT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE adverbs_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            shape VARCHAR(255),
            tags VARCHAR(255)
        );",
        "CREATE TABLE prepositions_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            shape VARCHAR(255),
            falls VARCHAR(255),
            tags VARCHAR(255)
        );",
        "CREATE TABLE conjunctions_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            shape VARCHAR(255),
            type TINYINT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE particles_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            shape VARCHAR(255),
            tags VARCHAR(255)
        );",
        "CREATE TABLE interjections_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            shape VARCHAR(255),
            tags VARCHAR(255)
        );",

        // Translate to
        "CREATE TABLE noun_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            label varchar(255) DEFAULT '$namedef',
            translate INT,
            base VARCHAR(255) NOT NULL DEFAULT '',
            tags VARCHAR(255),
            uppercase TINYINT,
            pattern TINYINT DEFAULT 0 NOT NULL,
            gender TINYINT DEFAULT 0 NOT NULL,
            shapes TEXT
        );",
        "CREATE TABLE adjective_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            base VARCHAR(255) NOT NULL DEFAULT '',
            label VARCHAR(255) DEFAULT '$namedef',
            translate INT,
            category TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE pronoun_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT DEFAULT NULL,
            label VARCHAR(255) DEFAULT '$namedef',
            translate INT,
            pattern_type TINYINT,
            base VARCHAR(255) NOT NULL DEFAULT '',
            shapes TEXT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE number_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            label varchar(255) DEFAULT '$namedef',
            translate INT,
            pattern_type TINYINT,
            base VARCHAR(255) NOT NULL DEFAULT '',         
            shapes TEXT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE verb_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            pattern_type_show TINYINT,
            translate INT,
            category TINYINT,
            label varchar(255) DEFAULT '$namedef',
            tags TEXT,
            base VARCHAR(255) NOT NULL DEFAULT '',
            shapes_infinitive TEXT, 
            shapes_continuous TEXT, 
            shapes_future TEXT, 
            shapes_imperative TEXT, 
            shapes_past_active TEXT, 
            shapes_past_passive TEXT, 
            shapes_transgressive_cont TEXT, 
            shapes_transgressive_past TEXT, 
            shapes_auxiliary TEXT
        );",
        // to
        "CREATE TABLE nouns_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `relation_production` INT,
            `shape` INT,
            `custombase` VARCHAR(255),
            `cite` VARCHAR(255) NOT NULL,
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT/*,
            `certainty` TiNYINT*/,
            `tmp_pattern_from_body` VARCHAR(255),
            `tmp_imp_from_pattern` VARCHAR(255)
        );",
        "CREATE TABLE adjectives_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `relation_production` INT,
            `shape` INT,
            `custombase` VARCHAR(255),
            `cite` TEXT,
            `comment` VARCHAR(255)/*,
            `certainty` TiNYINT*/,
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `tmp_pattern_from_body` VARCHAR(255),
            `tmp_imp_from_pattern` VARCHAR(255)
        );",
        "CREATE TABLE pronouns_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `relation_production` INT,
            `shape` INT,
            `custombase` VARCHAR(255),
            `cite` TEXT,
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `tmp_pattern_from_body` VARCHAR(255),
            `tmp_imp_from_pattern` VARCHAR(255)
        );",
        "CREATE TABLE numbers_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `relation_production` INT,
            `shape` INT,
            `custombase` VARCHAR(255),
            `cite` TEXT,
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `tmp_pattern_from_body` VARCHAR(255),
            `tmp_imp_from_pattern` VARCHAR(255)
        );",
        "CREATE TABLE verbs_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `relation_production` INT,
            `shape` INT,
            `custombase` VARCHAR(255),
            `cite` TEXT,
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TINYINT,
            `tmp_pattern_from_body` VARCHAR(255),
            `tmp_imp_from_pattern` VARCHAR(255)
        );",
        "CREATE TABLE adverbs_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `relation_production` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT /*,
           `certainty` TINYINT*/
        );",

        "CREATE TABLE prepositions_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `shape` varchar(255),
            `relation` INT,            
            `relation_production` INT,
            `comment` VARCHAR(255),
            `cite` VARCHAR(255),
            `falls` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT/*,
            `certainty` TINYINT*/
        );",

        "CREATE TABLE conjunctions_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `relation_production` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT/*,
            `certainty` TINYINT*/
        );",
        "CREATE TABLE particles_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `relation_production` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT/*,
            `certainty` TINYINT*/
        );",
        "CREATE TABLE interjections_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            `relation` INT,
            `relation_production` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT/*,
            `certainty` TINYINT*/
        );",



        // slovník search
        "CREATE TABLE noun_cs_synonyms (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            `source` INT,
            `another` INT
        );",
        "CREATE TABLE adverb_cs_synonyms (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            `source` INT,
            `another` INT
        );",


        // relations
        "CREATE TABLE noun_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_production` INT NOT NULL,
            `translate` INT,
            `from` INT,
            `from_production` INT NOT NULL,
            `custombase` VARCHAR(255)
        );",

        "CREATE TABLE adjective_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            translate INT,
            `from` INT,
            `from_production` INT NOT NULL,
            `custombase` VARCHAR(255)
        );",
        "CREATE TABLE pronoun_relations (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_production` INT NOT NULL,
            `translate` INT,
            `from` INT,
            `from_production` INT NOT NULL,
            `custombase` VARCHAR(255)
        );",
        "CREATE TABLE number_relations (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_production` INT NOT NULL,
            `translate` INT,
            `from` INT,
            `from_production` INT NOT NULL,
            `custombase` VARCHAR(255)
        );",
        "CREATE TABLE verb_relations (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_production` INT NOT NULL,
            `translate` INT,
            `from` INT,
            `from_production` INT NOT NULL,
            `custombase` VARCHAR(255)
        );",
        "CREATE TABLE adverb_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_production` INT NOT NULL,
            `translate` INT,
            `from` INT,
            `from_production` INT NOT NULL
        );",
        "CREATE TABLE preposition_relations (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_production` INT NOT NULL,
            `label` VARCHAR(255),
            `translate` INT,
            `from` INT,
            `from_production` INT NOT NULL
        );",
        "CREATE TABLE conjunction_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_production` INT NOT NULL,
            `translate` INT,
            `from` INT,
            `from_production` INT NOT NULL
        );",
        "CREATE TABLE particle_relations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            translate INT,
            `from` INT,
            `from_production` INT NOT NULL
        );",
        "CREATE TABLE interjection_relations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT NOT NULL,
            translate INT,
            `from` INT,
            `from_production` INT NOT NULL
        );",

        // translate
        "CREATE TABLE translate (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255),
            nameVariants VARCHAR(255),
            administrativeTown VARCHAR(255),
            gpsX float,
            gpsY float,
            country TINYINT,
            langtype TINYINT,
            quality TINYINT,
            dialect TINYINT,
            editors VARCHAR(255),
            devinfo TEXT,
            options TEXT,
            showInMaps TINYINT
        );",

        // kniha, ..
        "CREATE TABLE cites (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,  
            id_production INT NOT NULL,          
            `label` VARCHAR(255) DEFAULT '$namedef',
            `type` TINYINT,
            `data` JSON
        );",
        
        // Ukázky
        "CREATE TABLE piecesofcite (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,  
            id_production INT NOT NULL,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `parent` INT,
            `translate` INT,
            `people` JSON,
            `data` JSON,
            `text` TEXT,
            `translated` TEXT
        );",

        // log
        "CREATE TABLE logs (
            created DATE,  
            user INT,
            logtext TEXT
        );",
        
        // edits
        "CREATE TABLE translate_edits (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,  
            user INT,
            translate INT,
            edit_time DATETIME
        );",


        // regions
        "CREATE TABLE regions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT,
            label varchar(255) NOT NULL DEFAULT '$namedef' UNIQUE,
            type TINYINT, 
            parent INT,            
            translates TEXT /*json of cs, pl, de, ...*/
        );",

        // regions
        "CREATE TABLE nations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT,
            label varchar(255) NOT NULL DEFAULT '$namedef',
            type TINYINT,
            parent INT,
            translates TEXT /*json of cs, pl, de, ...*/
        );",

        // langs
        "CREATE TABLE langs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT,
            label varchar(255) NOT NULL DEFAULT '$namedef',
            type TINYINT,
            parent INT,
            translates TEXT /*json of cs, pl, de, ...*/
        );",

        // regions ploace
        "CREATE TABLE place_regions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT,
            region_id INT ,
            region_id_production INT, /*for export*/
            translate INT, /*translate parent*/
            zone_type TINYINT NOT NULL,
            confinence TINYINT NOT NULL,
            comment VARCHAR(255) NOT NULL
        );",

        "CREATE TABLE place_nations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT,
            nation_id INT,
            nation_id_production INT, /*for export*/
            translate INT, /*translate parent*/
            confinence TINYINT NOT NULL,
            comment VARCHAR(255) NOT NULL
        );",

        "CREATE TABLE place_langs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_production INT,
            lang_id INT,
            lang_id_production INT, /*for export*/
            translate INT, /*translate parent*/
            confinence TINYINT NOT NULL,
            comment VARCHAR(255) NOT NULL
        );",

        // replaces
        "CREATE TABLE replaces_start (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `from` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` VARCHAR(255)
        );",
        "CREATE TABLE replaces_inside (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `from` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` VARCHAR(255)
        );",
        "CREATE TABLE replaces_end (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `from` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` VARCHAR(255)
        );",
        "CREATE TABLE replaces_defined (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` VARCHAR(255),
            `partOfSpeech` TINYINT,            
            `pos` TINYINT            
        );",
        "CREATE TABLE replaces_defined_noun (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` VARCHAR(255),          
            `fall` TINYINT,          
            `gender` TINYINT,          
            `number` TINYINT,          
            `pattern` TINYINT,          
            `pos` TINYINT            
        );",
        "CREATE TABLE replaces_defined_adjective (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` VARCHAR(255),
            `fall` TINYINT,          
            `pattern` TINYINT,          
            `number` TINYINT,          
            `pos` TINYINT            
        );",
        "CREATE TABLE replaces_defined_verb (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` VARCHAR(255),
            `type` TINYINT, 
            `subtype` VARCHAR(255),     
            `pos` TINYINT            
        );",

        // simpleword_relations
        "CREATE TABLE simpleword_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate int,
            `from` varchar(255),
            display tinyint,
            tags varchar(255) NOT NULL,
            uppercase tinyint
        );",

        // simpleword_to
        "CREATE TABLE simplewords_to (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            relation int,
            `pos` tinyint,
            shape varchar(255),
            tags varchar(255) NOT NULL,
            comment varchar(255) NOT NULL,
            cite varchar(255) NOT NULL
        );",

        // phrase_relations
        "CREATE TABLE phrase_relations (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` int,
            `pos` tinyint, /*start, center or end*/
            `from` varchar(255),
            `display` tinyint,
            `tags` varchar(255) NOT NULL
        );",

        // phrases_to
        "CREATE TABLE phrases_to (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            relation INT,
            `pos` tinyint, /*primary, ...*/
            shape varchar(255) NOT NULL,
            tags varchar(255) NOT NULL,
            comment varchar(255) NOT NULL,
            cite varchar(255) NOT NULL 
        );",
        "CREATE TABLE sentencepart_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `from` VARCHAR(255) DEFAULT '$namedef',
            `translate` INT,
            `display` tinyint NOT NULL,
            `tags` varchar(255) NOT NULL 
        );",
        "CREATE TABLE sentenceparts_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `pos` tinyint NOT NULL ,
            `tags` VARCHAR(255) NOT NULL,
            `shape` VARCHAR(255) NOT NULL,
            `cite` VARCHAR(255) NOT NULL,
            `comment` VARCHAR(255) NOT NULL         
        );",
        "CREATE TABLE sentence_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `from` VARCHAR(255) DEFAULT '$namedef',
            `translate` INT,
            `display` tinyint NOT NULL,
            `tags` varchar(255) NOT NULL 
        );",
        "CREATE TABLE sentences_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `pos` tinyint NOT NULL,
            `shape` VARCHAR(255),
            `tags` VARCHAR(255) NOT NULL,
            `cite` VARCHAR(255) NOT NULL,
            `comment` VARCHAR(255) NOT NULL    
        );",
        "CREATE TABLE sentencepapattern_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `from` INT,
            `translate` INT NOT NULL,
            `display` VARCHAR(255),
            `tags` VARCHAR(255) NOT NULL
        );",
    ];

    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $conn->set_charset("utf8mb4");

    foreach ($sqls as $sql) {
        $result=$conn->query($sql);
        if ($result) {
            // Table created successfully
         //   if ($dev) echo "<p>".$sql."</p>";
        } else {
            sqlError($sql, $conn);
        }
    }

    $_SESSION['username']="admin";
    $_SESSION['usertype']=1;
    $GLOBALS["done"]="Database initialized!";
    header("Location: ../index.php");
}

function database_backup() :void{
    if (!isset($_SESSION["username"])) {
        throwError("Néste přehlášené!");
        return;
    }
	exec("mysqldump -u USER -p PASSWORD DATABASE > dump.sql");  // backup
}

function database_load() :void {
    if (!isset($_SESSION["username"])) {
        throwError("Néste přehlášené!");
        return;
    }
    exec("mysql -u USER -p PASSWORD < dump.sql");               // restore
}

function database_importold(): void {
    $dev=$GLOBALS["dev"];

    // check is log in
    if (!isset($_SESSION["username"])) {
        throwError("Nejste přihlášený!");
        return;
    }

    // check is there is files
    if (empty($_FILES["database_files"]["name"][0])) {
        throwError("Chybí soubor databáze!");
        return;
    }

    // mysql connect
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    $conn->set_charset("utf8mb4");

    // get files
    $files=$_FILES["database_files"];

    // foreach files (multiple translate files)
    foreach ($files["tmp_name"] as $key => $tmpName) {

        echo "<b>file: ".$files["name"][$key]."</b><br>";

        // Check before upload if is translate file
        if (pathinfo($files["name"][$key], PATHINFO_EXTENSION) !== "trw") {
            throwError("Neplatný typ souboru!");
            continue;
        }

        $fileContent= file_get_contents($tmpName);
        $lines = preg_split("/\r\n|\n|\r/", $fileContent);

        if ($fileContent === false) {
            throwError("Chyba při čtení souboru: " . htmlspecialchars($files["name"][$key]));
            continue;
        }

        // support only newest version of translate file
        if ($lines[0]!="TW v4") {
            throwError("Databáze není typu 'TW v4': '".$lines[0]."'");
            continue;
        }

        $conn->begin_transaction();

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
        $cites="";
        $i=0;
        $regionsData=[];
        $linesLen=count($lines);

        echo "head: ";
        for (;$i<$linesLen;$i++) {
            $line=$lines[$i];
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
                        $regionsData=explode(">", substr($line, 1));
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
        echo "(adding head...)";


        $devinfo_saveFormat = $conn->real_escape_string($devinfo);
        $options_saveFormat = $conn->real_escape_string($options);

        // insert translate
        $sql="INSERT INTO translate (`name`, `administrativeTown`, `gpsX`, `gpsY`, `country`, `langtype`, `quality`, `dialect`, `editors`, `devinfo`, `options`) SELECT 
        '$name', '$administrativeTown', $gpsX, $gpsY, $country, $langtype, $quality, $dialect, '$editors', '$devinfo_saveFormat', '$options_saveFormat'
        WHERE NOT EXISTS (SELECT 1 FROM translate WHERE name = '$name')";

        if ($conn->query($sql) === TRUE) {
            //ok
            if (mysqli_affected_rows($conn) > 0) {
            } else {
                echo ("Překlad s tímto názvem už v databázi existuje!");
                $conn->rollback();
                continue;
            }
        }else{
            sqlError($sql, $conn);
        }
        $langId=$conn->insert_id;
        echo "done<br>";

        echo "regions";
        // regions (haná, valachy, ...)
        {//echo "a ";
            // > select all regions
            $all_regions=[];
            {
                $sql="SELECT `id`, `label` FROM regions;";
                $result=$conn->query($sql);
               // echo "r ";
                if ($result) {
                    while($row = $result->fetch_assoc()) {
                        $all_regions[$row["label"]]=$row["id"];
                    }
                }else{
                    sqlError($sql, $conn);
                    $conn->rollback();
                }
            }
          //  echo "f ";
            // > foreach all regions and check label to match loading translate $regionsData
            $parent_region_id=null;
            foreach ($regionsData as $region) {
                $found=false;
                $regionId=null;
                if (isset($all_regions[$region])) {
                    $regionId=$all_regions[$region];
                    $found=true;
                }
                /* foreach ($all_regions as $label=>$id) {
                    if ($label==$region) {
                        $found=true;
                        $regionId=$id;
                        break;
                    }
                }*/
                if (!$found) { // echo "nf ";
                    // > insert into regions not found
                    $translates=$conn->escape_string(json_encode(["cs"=>$region]));
                    $sql="INSERT INTO regions (label, parent, translates) VALUES ('$region', ".($parent_region_id==null ? "NULL":$parent_region_id).", '$translates');";
                    $result=$conn->query($sql);
                    if ($result === TRUE) {
                        $regionId=$conn->insert_id;
                        $all_regions[$region]=$regionId;
                    }else{
                        sqlError($sql, $conn);
                        $conn->rollback();
                    }
                }
             //   echo "i ";
                // > insert into place_regions using
                {
                    $comment="Importováné";
                    $sql="INSERT INTO place_regions (`region_id`, `translate`, `comment`, zone_type, confinence) VALUES ($regionId, $langId, '$comment', 0, 2);";
                    $result=$conn->query($sql);
                    if (!$result) {
                        sqlError($sql, $conn);
                        $conn->rollback();
                    }
                }
                $parent_region_id=$regionId;
            }
        }

        echo "cites: ";
        // cites
        $citesRawLines=explode('\\n', $cites);
        foreach ($citesRawLines as $citeLineRaw) {
           // echo $citeLineRaw;
            if (str_starts_with($citeLineRaw, "kniha|")
            ||  str_starts_with($citeLineRaw, "periodikum|")
            ||  str_starts_with($citeLineRaw, "sncj|")
            ||  str_starts_with($citeLineRaw, "web|")
            ||  str_starts_with($citeLineRaw, "inf")
            ||  str_starts_with($citeLineRaw, "cja|")) {
                $citeVars=[];
                $vars=explode("|", $citeLineRaw); //["smt=d", "shgj=df", ...]

                foreach ($vars as $varr) {
                    $var=explode("=", $varr);//["smt","d"]
                    if (count($var)==2){
                        $varCode=$var[0];
                        $varValue=$var[1];
                        // fix
                        if ($varCode=="primeni") $citeVars["prijmeni"]=$varValue;
                        else $citeVars[$varCode]=$varValue;
                    } elseif (count($var)==1) {
                        $citeVars[$var[0]]=true;
                    }
                }

                $citeData=[];
                // more info ./globa/cites.php
                $citetypeRaw=$vars[0];
                $citeType=0;

                // cite type
                if     ($citetypeRaw=="kniha") $citeType=1;
                elseif ($citetypeRaw=="web"  ) $citeType=2;
                elseif ($citetypeRaw=="sncj" ) $citeType=3;
                elseif ($citetypeRaw=="periodikum") $citeType=4;
                elseif ($citetypeRaw=="cja") $citeType=1;

                $shortcut="";
                if (isset($citeVars["shortcut"]))$shortcut=$citeVars["shortcut"];
                else if ($citetypeRaw=="sncj") $shortcut="sncj";
                else if ($citetypeRaw=="cja") $shortcut="cja";
                else if ($citetypeRaw=="inf") $shortcut="inf"; // informátor
                else {
                    throwError("Missing shortcut: ".$citetypeRaw);
                }

                $idcite=-1;

                // Check if cite already exists
                {
                    $sql = "SELECT id FROM cites WHERE JSON_VALUE(data,'$.shortcut') = ?"; //todo fix
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $shortcut);
                    if ($stmt->execute()) {
                        $stmt->bind_result($idCiteExiting);
                        // if shortcut found
                        if ($idCiteExiting!=null) $idcite=$idCiteExiting;
                    }else{
                        sqlError($sql, $conn);
                        $conn->rollback();
                    }
                    $stmt->close();
                }

                // label for cite and subcite
                $label="";
                if ($citetypeRaw=="sncj" || $citetypeRaw=="cja" || $citetypeRaw=="inf") {
                    if ($citetypeRaw=="sncj") $label="SNČJ";
                    if ($citetypeRaw=="cja" ) $label="ČJA";
                    if ($citetypeRaw=="inf" ) $label="informátor";
                }else if (isset($citeVars["nazev"]) && $citeVars["nazev"]!="")    $label=$citeVars["nazev"];
                else if (isset($citeVars["prispevek"]) && $citeVars["prispevek"]!="") $label=$citeVars["prispevek"];
                else if (isset($citeVars["periodikum"]) && $citeVars["periodikum"]!="")$label=$citeVars["periodikum"];
                else if (isset($citeVars["shortcut"]) && $citeVars["shortcut"]!="")  $label=$citeVars["shortcut"];
                else if (isset($citeVars["odkaz"]) && $citeVars["odkaz"]!="")     $label=$citeVars["odkaz"];

                // insert non-existing cite
                if ($idcite==-1) {
                    // save only params
                    $listSame=["shortcut", "nazev", "nazev_webu", "rok_vydani", "strany", "podnazev", "dil", "kapitola", "odkaz", "issn", "ibsn", "autor", "jmeno", "prijmeni", "odkaz", "vydavatel", "spolecnost", "i"/*sncj place*/];

                    foreach ($listSame as $key) {
                        if (isset($citeVars[$key])) {
                            if ($citeVars[$key]!="") $citeData[$key]=$citeVars[$key];
                        }
                    }

                    // only defined cite types
                    $sql = "INSERT INTO cites (label, data, type)
                        SELECT ?, ?, ?
                        WHERE NOT EXISTS (SELECT 1 FROM cites WHERE label = ?)";

                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssis", $label, $data, $citeType, $label);

                    $data=json_encode($citeData,JSON_UNESCAPED_UNICODE);

                  /*  $sql="INSERT INTO cites (label, data, type) SELECT '$label', '$data', $citeType
                        WHERE NOT EXISTS (SELECT 1 FROM cites WHERE label = '$label')";*/

                    if ($stmt->execute()) {
                        $idcite=$conn->insert_id;
                    } else {
                        sqlError($sql, $conn);
                        $conn->rollback();
                    }
                    $stmt->close();
                }

                // Pieces of cite
                $dataPiece=[];
                $listSameP=["strany", "cislo", "rocnik", "odkaz", "kapitola", "shortcut", "zpracovano"];
                foreach ($listSameP as $key) {
                    if (isset($citeVars[$key])) {
                        if ($citeVars[$key]!="") $dataPiece[$key]=$citeVars[$key];
                    }
                }

                //if sncj
                if (!isset($dataPiece["shortcut"])){
                    if ($citetypeRaw=="sncj") $dataPiece["shortcut"]="sncj";
                    if ($citetypeRaw=="inf") $dataPiece["shortcut"]="inf";
                    if ($citetypeRaw=="cja") $dataPiece["shortcut"]="cja";
                }

                $dataPieceSave=json_encode($dataPiece, JSON_UNESCAPED_UNICODE);

                $sqlPiece="INSERT INTO piecesofcite (label, parent, translate, data) VALUES ('$label', '$idcite', '$langId', '$dataPieceSave')";

                if ($conn->query($sqlPiece) === TRUE) {
                    //ok
                } else {
                    sqlError($sqlPiece, $conn);
                    $conn->rollback();
                }
            }
        }

        // get pieces of cite
        $listCites=[];
        {
            $sqlPiece="SELECT id, label, data FROM piecesofcite WHERE translate = '$langId'";
            $resultCites=$conn->query($sqlPiece);
            if ($resultCites) {
                while($row = $resultCites->fetch_assoc()) {
                    $data = json_decode($row["data"]);
                    if ($data && !empty($data->shortcut)) {
                        $listCites[$data->shortcut] = $row["id"]; // set for example ["sncj"=>2, ...]
                    }
                }
            }else{
                sqlError($sqlPiece, $conn);
                $conn->rollback();
            }
        }
        echo "done<br>";

        echo "sentencepattern: ";
        // SentencePattern
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;
           // itemsSentencePatterns.Add(ItemSentencePattern.Load(line));
        }
        echo "done<br>";

        echo "sentencepatternpart: ";
        // SentencePatternPart
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;
           // itemsSentencePatternParts.Add(ItemSentencePatternPart.Load(line));
        }
        echo "done<br>";

        echo "sentences: ";
        // Sentences
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from=$parts[0];
            $display=$parts[1];
           // $pos=$parts[2];
            $tags="";

            $sql="INSERT INTO sentence_relations (`translate`, `from`) VALUES ($langId, '$from');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }

            $idPhrase=$conn->insert_id;

            $sql_to=[];
            $tos=loadListTranslatingToData($parts, 2);
            foreach ($tos as $to) {
                $shape=mysqli_escape_string($conn, $to["Text"]);
                $comment=mysqli_escape_string($conn, $to["Comment"]);
                $source=$to["Source"];
                $tags=join(",", tryToGetTags($comment));
                $sourceIds=getCitesIdsFromString($source, $listCites, $shape);
                $sql_to[]="($idPhrase, '$shape', '$tags', '$comment', '$sourceIds')";
            }

            $sqlTo="INSERT INTO sentences_to (relation, shape, tags, comment, cite) VALUES ".implode(", ", $sql_to).";";
            if ($conn->query($sqlTo) === TRUE) {
                //ok
            }else{
                sqlError($sqlTo,$conn);
                $conn->rollback();
            }
        }
        echo "done<br>";

        echo "sentecepart: ";
        // SentencePart
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from=mysqli_escape_string($conn, $parts[0]);
            if ($parts[1]=="1") $display=1;
            else $display=0;
            //$pos="";//$parts[2];
            //$tags="";

            $sql="INSERT INTO `sentencepart_relations` (`from`, `translate`, `display`) VALUES ('$from', $langId, $display);";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
                $conn->rollback();
            }

            $idSentencePart=$conn->insert_id;

            $sql_to=[];
            $tos=loadListTranslatingToData($parts, 2);
            foreach ($tos as $to) {
                $shape=mysqli_escape_string($conn, $to["Text"]);
                $comment=mysqli_escape_string($conn, $to["Comment"]);
                $source=$to["Source"];
                $tags=join(",", tryToGetTags($comment));
                $sourceIds=getCitesIdsFromString($source, $listCites, $shape);
                $sql_to[]="($idSentencePart, '$shape', '$tags', '$comment', '$sourceIds')";
            }

            $sqlTo="INSERT INTO sentenceparts_to (relation, shape, tags, comment, cite) VALUES ".implode(", ", $sql_to).";";
            if ($conn->query($sqlTo) === TRUE) {
                //ok
            }else{
                sqlError($sqlTo,$conn);
                $conn->rollback();
            }
        }
        echo "done<br>";

        echo "phrase: ";
        // Phrase
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from=mysqli_escape_string($conn, $parts[0]);
            if ($parts[1]=="1")$display=1;
            else $display=0;
            $pos=$parts[2];
            //$tags="";

            $sql="INSERT INTO phrase_relations (`translate`, `from`, `display`, `pos`, `tags`) VALUES ($langId, '$from', $display, $pos, '');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }

            $idPhrase=$conn->insert_id;

            $sql_to=[];
            $tos=loadListTranslatingToData($parts, 3);
            foreach ($tos as $to) {
                $shape=mysqli_escape_string($conn, $to["Text"]);
                $comment=mysqli_escape_string($conn, $to["Comment"]);
                $source=$to["Source"];
                $sourceIds=getCitesIdsFromString($source, $listCites, $shape);

                $tags=join(",", tryToGetTags($comment));

                $sql_to[]="($idPhrase, '$shape', '$tags', '$comment', '$sourceIds')";
            }

            $sqlTo="INSERT INTO phrases_to (relation, shape, tags, comment, cite) VALUES ".implode(", ", $sql_to).";";
            if ($conn->query($sqlTo) === TRUE) {
                //ok
            }else{
                sqlError($sqlTo,$conn);
            }
        }
        echo "done<br>";

        echo "simplewords: ";
        // SimpleWords
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from=$parts[0];
            $display=$parts[1];
            $uppercase=getUpperCaseType($from);
            $tags="";

            $sql="INSERT INTO simpleword_relations (translate, `from`, display, tags, uppercase) VALUES ($langId, '$from', $display, '$tags', $uppercase);";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }

            $idSimpleWord=$conn->insert_id;

            $tos=loadListTranslatingToData($parts,2);
            $sql_to=[];
            foreach ($tos as $to) {
                $shape=mysqli_escape_string($conn, $to["Text"]);
                $comment=$to["Comment"];
                $source=$to["Source"];
                $tags=join(",", tryToGetTags($comment));
                //$sourceId=0;

                $sourceIds=getCitesIdsFromString($source, $listCites, $shape);
                $sql_to[]="($idSimpleWord, '$shape', '$tags', '$comment', '$sourceIds')";
            }

            $sql="INSERT INTO simplewords_to (relation, shape, tags, comment, cite) VALUES".implode(", ", $sql_to).";";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }

        }
        echo "done<br>";

        echo "replaceS: ";
        // ReplaceS
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|', $line);
            $from="";
            $to="";
            if (count($parts)==1) {
                $from=$parts[0];
                $to=$parts[0];
            } else {
                $from=$parts[0];
                $to=$parts[1];
            }
            $label=$from." > ".$to;

            $sql="INSERT INTO replaces_start (`translate`, `from`, `to`, label) VALUES ($langId, '$from', '$to', '$label');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "replaceG: ";
        // ReplaceG
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from="";
            $to="";
            if (count($parts)==1) $from=$to=$parts[0];
            else {
                $from=mysqli_escape_string($conn, $parts[0]);
                $to=mysqli_escape_string($conn, $parts[1]);
            }
            $label=$from." > ".$to;

            $sql="INSERT INTO replaces_inside (`translate`, `from`, `to`, `label`) VALUES ($langId, '$from', '$to', '$label');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "replaceE: ";
        // ReplaceE
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from="";
            $to="";
            if (count($parts)==1) $from=$to=$parts[0];
            else {
                $from=mysqli_escape_string($conn, $parts[0]);
                $to=mysqli_escape_string($conn, $parts[1]);
            }
            $label=$from." > ".$to;

            $sql="INSERT INTO replaces_end (`translate`, `from`, `to`, `label`) VALUES ($langId, '$from', '$to', '$label');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "patternnounfrom: ";
        // PatternNounFrom
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=$parts[0];
            $base=extractBase($label);
            $gender=intval($parts[1])+1;
            $shapes=array_slice($parts, 2);
          // if (str_starts_with($label,) $uppercase but hash has lowecase) $tags[]="název";

            $sql= /** @lang MySQL */
                "INSERT INTO noun_patterns_cs (label, base, gender, shapes) ".
                  "SELECT '$label', '$base', '$gender', '".implode("|",$shapes)."' ".
                  "WHERE NOT EXISTS (SELECT 1 FROM noun_patterns_cs WHERE label = '$label');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "patternnounto: ";
        // PatternNounTo
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|', $line);
            $label=$parts[0];
            $base=extractBase($label);
            $gender=intval($parts[1])+1;
            $shapes=array_slice($parts, 2);

            $shapesSave=implode("|", $shapes);

            $shapesSave_format=$conn->real_escape_string($shapesSave);
            $base_format=$conn->real_escape_string($base);
            $label_format=$conn->real_escape_string($label);

            $sql="INSERT INTO noun_patterns_to (`label`, `translate`, `base`, `gender`, `shapes`)
                VALUES ('$label_format', $langId, '$base_format', $gender, '$shapesSave_format');";

            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "noun: ";
        // Noun
        // get list from
        $listFrom=[];
        {
            $sqlFromP="SELECT `id`, `label`, base FROM noun_patterns_cs;";
            $resultFromP = $conn->query($sqlFromP);
            if ($resultFromP) {
                while($row = $resultFromP->fetch_assoc()) {
                    $listFrom[$row["label"]]=["id"=>$row["id"], "base"=>$row["base"]];
                }
            }else{
                throwError("SQL error: ".$sqlFromP);
            }
        }

        // to list
        $listNounTo=[];
        {
            $sqlToP="SELECT `id`, `label` FROM `noun_patterns_to` WHERE `translate`=$langId;";
            $resultToP = $conn->query($sqlToP);
            if ($resultToP) {
                while($row = $resultToP->fetch_assoc()) {
                    $listNounTo[$row["label"]]=$row["id"];
                }
            }else{
                throwError("SQL error: ".$resultToP);
            }
        }

        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "") continue;

            $parts = explode('|', $line);
            $fromBase=$parts[0];// for example "pampeliš"

            $fromPattern=$parts[1];// for example "pohádKA", this translation has only likely only "pohádKA", but previous can have loaded "pampelišKA" (merging all translations)
            $ending=substr($fromPattern, strlen($fromBase)); // "KA"

            $newPatternName=$fromBase.$ending;// for example "pampelišKA"
            $patternId = $listFrom[$newPatternName]["id"] ?? null;
            $patternBase = $listFrom[$newPatternName]["base"] ?? null;

            $custombase=null;
            if ($patternBase!=$fromBase) {
                $custombase=$fromBase;
            }

            $uppercase=intval($parts[2]);
            $shapes=LoadListTranslatingToDataWithPattern($parts, 3);

            // relations
            {
                $result=sql_insert($conn, "noun_relations", [
                    "translate"=>$langId,
                    "from"=>$patternId,
                    "custombase"=>$custombase
                ]);

                if ($result["status"]=="ERROR") {
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    $conn->rollback();
                }
            }
            $idRelation=$conn->insert_id;

            // update noun_patterns_cs uppercase
            {
                $sql_rel= "UPDATE noun_patterns_cs SET `uppercase` = $uppercase WHERE label='$fromPattern';";
                if ($conn->query($sql_rel) === TRUE) {
                    //ok
                    //throwInfo("noun_relations inserted");
                } else {
                    sqlError($sql_rel, $conn);
                }
            }

            // $shapes = [["Cite"=>...], [], [], ...]
            for ($j = 0; $j < count($shapes); $j++) {
                $shape = $shapes[$j];

                // cite
                $sourceRaw=$shape["Source"];
                $cites=getCitesIdsFromString($sourceRaw, $listCites, $parts[0]);

                // shape
                $pattern=$shape["Pattern"];
                $shape_to="-1";
                if (isset($listNounTo[$pattern])) $shape_to=$listNounTo[$pattern]; // get id from pattern text
                $body=$shape["Body"];

                // find pattern to, update values
                if ($shape_to!="-1") {
                    $sql_pt= /** @lang SQL */"UPDATE noun_patterns_to SET `uppercase` = $uppercase WHERE id = $shape_to;";

                    if ($conn->query($sql_pt) === TRUE) {
                        //ok
                    }else{
                        sqlError($sql_rel, $conn);
                    }
                }

                // comment
                $comment=$shape["Comment"];
                $tags=join("|", tryToGetTags($comment));

                // unresolved, not linked correctly
                $tmp_imp_from_pattern=null;
                $pattern_from_body=null;
                if ($shape_to=="-1") {
                    $tmp_imp_from_pattern=$pattern;
                    $pattern_from_body=$body;
                }

                $comment_format=$conn->real_escape_string($comment);

                {
                    $result=sql_insert($conn, "nouns_to", [
                        "relation"=>$idRelation,
                        "priority"=>$j,
                        "shape"=>$shape_to,
                        "comment"=>$comment_format,
                        "tags"=>$tags,
                        "cite"=>$cites,
                        "tmp_pattern_from_body"=>$pattern_from_body,
                        "tmp_imp_from_pattern"=>$tmp_imp_from_pattern
                    ]);
                    if ($result["status"]=="ERROR") {
                        echo json_encode($result, JSON_UNESCAPED_UNICODE);
                        $conn->rollback();
                        exit();
                    }
                }
            }
        }
        echo "done<br>";

        echo "patternadjectives: ";
        // PatternAdjectives
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=$parts[0];
            $base=extractBase($label);
            $shapesN=array_slice($parts, 2, 18);
            $shapesF=array_slice($parts, 2+18,18);
            $shapesA=array_slice($parts, 2+18*2,18);
            $shapesI=array_slice($parts, 2+18*3,18);
            $category=getCategoryAdjective($shapesA[0]);

            $sql="INSERT INTO adjective_patterns_cs (label, base, category, shapes) ".
                  "SELECT '$label', '$base', '$category', '".implode("|", $shapesA)."|".implode("|", $shapesI)."|".implode("|", $shapesF)."|".implode("|", $shapesN)."' ".
                  "WHERE NOT EXISTS (SELECT 1 FROM adjective_patterns_cs WHERE label = '$label');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "patternAdjectivesTo: ";
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=$parts[0];
           // $base=extractBase($label);
            //$category=$parts[1];
            $shapesN=array_slice($parts, 2, 18);
            $shapesF=array_slice($parts, 2+18,18);
            $shapesA=array_slice($parts, 2+18*2,18);
            $shapesI=array_slice($parts, 2+18*3,18);
            $category=getCategoryAdjective($shapesA[0]);

            $result=sql_insert($conn, "adjective_patterns_to", [
                "translate"=>$langId,
                "label"=>$label,
                "category"=>$category,
                "shapes"=>implode("|", $shapesA)."|".implode("|", $shapesI)."|".implode("|", $shapesF)."|".implode("|", $shapesN)
            ]);
            if ($result["status"]=="ERROR"){
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                $conn->rollback();
                exit;
            }
        }
        echo "done<br>";

        echo "Adjectives: ";
        // Adjectives
        // Noun
        // get list from
        $listAdjectiveFrom=[];
        {
            $sqlFromP="SELECT `id`, `label`, base FROM adjective_patterns_cs;";
            $resultFromP = $conn->query($sqlFromP);
            if ($resultFromP) {
                while($row = $resultFromP->fetch_assoc()) {
                    $listAdjectiveFrom[$row["label"]]=["id"=>$row["id"], "base"=>$row["base"]];
                }
            }else{
                throwError("SQL error: ".$sqlFromP);
            }
        }

        // to list
        $listAdjectivesTo=[];
        {
            $sqlToP="SELECT `id`, `label` FROM `adjective_patterns_to` WHERE `translate`=$langId;";
            $resultToP = $conn->query($sqlToP);
            if ($resultToP) {
                while($row = $resultToP->fetch_assoc()) {
                    $listAdjectivesTo[$row["label"]]=$row["id"];
                }
            }else{
                throwError("SQL error: ".$resultToP);
            }
        }
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|', $line);
            $fromShape=$parts[0];
            $fromPattern=$parts[1];
            $shapes=LoadListTranslatingToDataWithPattern($parts, 2);

            // relation
            {
                // get id from text
                $from = $listAdjectiveFrom[$fromPattern]["id"] ?? null;
                $base = $listAdjectiveFrom[$fromPattern]["base"] ?? null;
                $custombase=$base==$fromShape ? null : $fromShape;

                $result=sql_insert($conn, "adjective_relations", [
                    "translate"=>$langId,
                    "from"=>$from,
                    "custombase"=>$custombase
                ]);

                if ($result["status"]=="ERROR") {
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    $conn->rollback();
                    exit;
                }
            }
            $idRelation=$conn->insert_id;

            // $shapes = [["Cite"=>...], [], [], ...]
            for ($j = 0; $j < count($shapes); $j++) {
                $shape = $shapes[$j];

                // cite
                $sourceRaw=$shape["Source"]; //$sourceRaw="nbdp|sncj|.."

                $cites=getCitesIdsFromString($sourceRaw, $listCites, $parts[0]);

                // shape
                $pattern=$shape["Pattern"];
                $shape_to=-1;
                if (isset($listAdjectivesTo[$pattern])) $shape_to=$listAdjectivesTo[$pattern]; // get id from pattern text
                $body=$shape["Body"];

                // comment
                $comment=$shape["Comment"];
                $tags=join("|", tryToGetTags($comment));

                // unresolved, not linked correctly
                $tmp_imp_from_pattern=null;
                $pattern_from_body=null;
                if ($shape_to==-1) {
                    $tmp_imp_from_pattern=$pattern;
                    $pattern_from_body=$body;
                }

                $comment_format=$conn->real_escape_string($comment);

                $result=sql_insert($conn, "adjectives_to", [
                    "relation"=>$idRelation,
                    "priority"=>$j,
                    "shape"=>$shape_to,
                    "custombase"=>$body,
                    "comment"=>$comment_format,
                    "tags"=>$tags,
                    "cite"=>$cites,
                    "tmp_pattern_from_body"=>$pattern_from_body,
                    "tmp_imp_from_pattern"=>$tmp_imp_from_pattern
                ]);
                if ($result["status"]=="ERROR") {
                    $conn->rollback();
                }
            }
        }
        echo "done<br>";

        echo "pronounFrom: ";
        // PatternPronounsFrom
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=$parts[0];
            $base=extractBase($label);
            $shapes=array_slice($parts, 1);

            $sql="INSERT INTO pronoun_patterns_cs (label, base, shapes) ".
                  "SELECT '$label', '$base', '".implode("|",$shapes)."' ".
                  "WHERE NOT EXISTS (SELECT 1 FROM pronoun_patterns_cs WHERE label = '$label');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "patternpronounto: ";
        // PatternPronounsTo
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=$conn->real_escape_string($parts[0]);
            $base=$conn->real_escape_string(extractBase($label));

            $shapes=array_slice($parts, 1);
            $shapesSQL=$conn->real_escape_string(implode("|", $shapes));
            $sql="INSERT INTO pronoun_patterns_to (translate, label, base, shapes) ".
                "VALUES ($langId, '$label', '$base', '$shapesSQL');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
                $conn->rollback();
            }
        }
        echo "done<br>";

        echo "pronouns: ";
        // Pronouns
        $listPronounsFrom=[];
        {
            $sqlFromP="SELECT `id`, `label`, base FROM pronoun_patterns_cs;";
            $resultFromP = $conn->query($sqlFromP);
            if ($resultFromP) {
                while($row = $resultFromP->fetch_assoc()) {
                    $listPronounsFrom[$row["label"]]=["id"=>$row["id"], "base"=>$row["base"]];
                }
            }else{
                throwError("SQL error: ".$sqlFromP);
            }
        }

        // to list
        $listPronounsTo=[];
        {
            $sqlToP="SELECT `id`, `label` FROM `pronoun_patterns_to` WHERE `translate`=$langId;";
            $resultToP = $conn->query($sqlToP);
            if ($resultToP) {
                while($row = $resultToP->fetch_assoc()) {
                    $listPronounsTo[$row["label"]]=$row["id"];
                }
            }else{
                throwError("SQL error: ".$resultToP);
            }
        }
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
            $parts = explode('|', $line);
            $fromBase=$parts[0];// for example "pampeliš"

            $fromPattern=$parts[1];// for example "pohádKA", this translation has only likely only "pohádKA", but previous can have loaded "pampelišKA" (merging all translations)
            $ending=substr($fromPattern, strlen($fromBase)); // "KA"

            $newPatternName=$fromBase.$ending;// for example "pampelišKA"
            $patternId = $listPronounsFrom[$newPatternName]["id"] ?? null;
            $patternBase = $listPronounsFrom[$newPatternName]["base"] ?? null;

            $custombase=null;
            if ($patternBase!=$fromBase) {
                $custombase=$fromBase;
            }

            $shapes=LoadListTranslatingToDataWithPattern($parts, 2);

            // relations
            {
                $result=sql_insert($conn, "pronoun_relations", [
                    "translate"=>$langId,
                    "from"=>$patternId,
                    "custombase"=>$custombase
                ]);

                if ($result["status"]=="ERROR") {
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    $conn->rollback();
                }
            }
            $idRelation=$conn->insert_id;

            // $shapes = [["Cite"=>...], [], [], ...]
            for ($j = 0; $j < count($shapes); $j++) {
                $shape = $shapes[$j];

                // cite
                $sourceRaw=$shape["Source"];
                $cites=getCitesIdsFromString($sourceRaw, $listCites, $parts[0]);

                // shape
                $pattern=$shape["Pattern"];
                $shape_to="-1";
                if (isset($listPronunsTo[$pattern])) $shape_to=$listPronounsTo[$pattern]; // get id from pattern text
                $body=$shape["Body"];

                // comment
                $comment=$shape["Comment"];
                $tags=join("|", tryToGetTags($comment));

                // unresolved, not linked correctly
                $tmp_imp_from_pattern=null;
                $pattern_from_body=null;
                if ($shape_to=="-1") {
                    $tmp_imp_from_pattern=$pattern;
                    $pattern_from_body=$body;
                }

                $comment_format=$conn->real_escape_string($comment);

                {
                    $result=sql_insert($conn, "pronouns_to", [
                        "relation"=>$idRelation,
                        "priority"=>$j,
                        "shape"=>$shape_to,
                        "comment"=>$comment_format,
                        "tags"=>$tags,
                        "cite"=>$cites,
                        "tmp_pattern_from_body"=>$pattern_from_body,
                        "tmp_imp_from_pattern"=>$tmp_imp_from_pattern
                    ]);
                    if ($result["status"]=="ERROR") {
                        echo json_encode($result, JSON_UNESCAPED_UNICODE);
                        $conn->rollback();
                        exit();
                    }
                }
            }
        }
        echo "done<br>";

        echo "numbersfrom: ";
        // PatternNumbersFrom
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=$parts[0];
            $base=extractBase($label);
            //show type [1]
            $shapes=array_slice($parts, 2);

            $sql="INSERT INTO number_patterns_cs (label, base, shapes) ".
                  "SELECT '$label', '$base', '".implode("|",$shapes)."' ".
                  "WHERE NOT EXISTS (SELECT 1 FROM number_patterns_cs WHERE label = '$label');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql, $conn);
            }
        }
        echo "done<br>";

        echo "numbersto: ";
        // PatternNumbersTo
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=$parts[0];
            $base=extractBase($label);
            //show type [1]
            $shapes=array_slice($parts, 2);

            $sql="INSERT INTO number_patterns_to (translate, label, base, shapes) ".
                "SELECT $langId, '$label', '$base', '".implode("|",$shapes)."';";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql, $conn);
            }
        }
        echo "done<br>";

        echo "numbers: ";
        // Numbers
        $listNumbersFrom=[];
        {
            $sqlFromP="SELECT `id`, `label`, `base` FROM `number_patterns_cs`;";
            $resultFromP = $conn->query($sqlFromP);
            if ($resultFromP) {
                while($row = $resultFromP->fetch_assoc()) {
                    $listNumbersFrom[$row["label"]]=["id"=>$row["id"], "base"=>$row["base"]];
                }
            }else{
                throwError("SQL error: ".$sqlFromP);
            }
        }

        // to list
        $listNumbersTo=[];
        {
            $sqlToP="SELECT `id`, `label` FROM `number_patterns_to` WHERE `translate`=$langId;";
            $resultToP = $conn->query($sqlToP);
            if ($resultToP) {
                while($row = $resultToP->fetch_assoc()) {
                    $listNumbersTo[$row["label"]]=$row["id"];
                }
            }else{
                throwError("SQL error: ".$resultToP);
            }
        }
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
            $parts = explode('|', $line);
            $fromBase=$parts[0];// for example "pampeliš"

            $fromPattern=$parts[1];// for example "pohádKA", this translation has only likely only "pohádKA", but previous can have loaded "pampelišKA" (merging all translations)
            $ending=substr($fromPattern, strlen($fromBase)); // "KA"

            $newPatternName=$fromBase.$ending;// for example "pampelišKA"
            $patternId = $listNumbersFrom[$newPatternName]["id"] ?? null;
            $patternBase = $listNumbersFrom[$newPatternName]["base"] ?? null;

            $custombase=null;
            if ($patternBase!=$fromBase) {
                $custombase=$fromBase;
            }

            $shapes=LoadListTranslatingToDataWithPattern($parts, 2);

            // relations
            {
                $result=sql_insert($conn, "number_relations", [
                    "translate"=>$langId,
                    "from"=>$patternId,
                    "custombase"=>$custombase
                ]);

                if ($result["status"]=="ERROR") {
                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
                    $conn->rollback();
                }
            }
            $idRelation=$conn->insert_id;

            // $shapes = [["Cite"=>...], [], [], ...]
            for ($j = 0; $j < count($shapes); $j++) {
                $shape = $shapes[$j];

                // cite
                $sourceRaw=$shape["Source"];
                $cites=getCitesIdsFromString($sourceRaw, $listCites, $parts[0]);

                // shape
                $pattern=$shape["Pattern"];
                $shape_to="-1";
                if (isset($listPronunsTo[$pattern])) $shape_to=$listNumbersTo[$pattern]; // get id from pattern text
                $body=$shape["Body"];

                // comment
                $comment=$shape["Comment"];
                $tags=join("|", tryToGetTags($comment));

                // unresolved, not linked correctly
                $tmp_imp_from_pattern=null;
                $pattern_from_body=null;
                if ($shape_to=="-1") {
                    $tmp_imp_from_pattern=$pattern;
                    $pattern_from_body=$body;
                }

                $comment_format=$conn->real_escape_string($comment);

                {
                    $result=sql_insert($conn, "numbers_to", [
                        "relation"=>$idRelation,
                        "priority"=>$j,
                        "shape"=>$shape_to,
                        "comment"=>$comment_format,
                        "tags"=>$tags,
                        "cite"=>$cites,
                        "tmp_pattern_from_body"=>$pattern_from_body,
                        "tmp_imp_from_pattern"=>$tmp_imp_from_pattern
                    ]);
                    if ($result["status"]=="ERROR") {
                        echo json_encode($result, JSON_UNESCAPED_UNICODE);
                        $conn->rollback();
                        exit();
                    }
                }
            }
        }
        echo "done<br>";

        echo "verbs from pattern: ";
        // PatternVerbsFrom
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=$parts[0];
            $base=extractBase($label);
            $shapetype=$parts[1];

            // convert shapetype from
            $SContinuous          = ($shapetype &   1) ==  1;
            $SImperative         = ($shapetype &   2) ==  2;
            $SPastActive         = ($shapetype &   4) ==  4;
            $SPastPassive        = ($shapetype &   8) ==  8;
            $SFuture             = ($shapetype &  16) == 16;
            $STransgressiveCont  = ($shapetype &  32) == 32;
            $STransgressivePast  = ($shapetype &  64) == 64;
            $SAuxiliary          = ($shapetype & 128) ==128;

            $Continuous          = "";
            $Imperative         = "";
            $PastActive         = "";
            $PastPassive        = "";
            $Future             = "";
            $TransgressiveCont  = "";
            $TransgressivePast  = "";
            $Auxiliary          = "";

            $shapes=array_slice($parts, 3);
            $Infinitive=$shapes[0];

            $GLOBALS["index"]=1;

            $class=0;
            if ($SContinuous) {
                $arr=GetArray($conn, $shapes, $GLOBALS["index"], 6);
                $Continuous         = implode("|",$arr);

                // 1 -e, 2 -ne, 3 -je , 4 -í, 5-á
                if (str_ends_with("á", $arr[2])) $class=5;
                else if (str_ends_with("í", $arr[2])) $class=4;
                else if (str_ends_with("je", $arr[2])) $class=3;
                else if (str_ends_with("ne", $arr[2])) $class=2;
                else if (str_ends_with("e", $arr[2])) $class=1;
            }
            if ($SFuture)           $Future            = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 6));
            if ($SImperative)       $Imperative        = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 3));
            if ($SPastActive)       $PastActive        = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 8));
            if ($SPastPassive)      $PastPassive       = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 8));
            if ($STransgressiveCont)$TransgressiveCont = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 3));
            if ($STransgressivePast)$TransgressivePast = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 3));
            if ($SAuxiliary)        $Auxiliary         = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 6));

            $category=$parts[2];

            $sql="INSERT INTO verb_patterns_cs (label, base, category, class, 
                    shapes_infinitive, 
                    shapes_continuous, 
                    shapes_future, 
                    shapes_imperative, 
                    shapes_past_active, 
                    shapes_past_passive, 
                    shapes_transgressive_cont, 
                    shapes_transgressive_past, 
                    shapes_auxiliary
                ) 
                SELECT '$label', '$base', '$category', $class, 
                    '$Infinitive', 
                    '$Continuous', 
                    '$Future', 
                    '$Imperative', 
                    '$PastActive', 
                    '$PastPassive', 
                    '$TransgressiveCont',
                    '$TransgressivePast',
                    '$Auxiliary'".
                "WHERE NOT EXISTS (SELECT 1 FROM verb_patterns_cs WHERE label = '$label');";

            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "verbs to pattern: ";
        // PatternVerbsTo
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=mysqli_escape_string($conn, $parts[0]);
            $base=mysqli_escape_string($conn, extractBase($label));
            $shapetype=$parts[1];

            // convert shapetype from
            $SContinuous          = ($shapetype &   1) ==  1;
            $SImperative         = ($shapetype &   2) ==  2;
            $SPastActive         = ($shapetype &   4) ==  4;
            $SPastPassive        = ($shapetype &   8) ==  8;
            $SFuture             = ($shapetype &  16) == 16;
            $STransgressiveCont  = ($shapetype &  32) == 32;
            $STransgressivePast  = ($shapetype &  64) == 64;
            $SAuxiliary          = ($shapetype & 128) ==128;

            $Continuous          = "";
            $Imperative         = "";
            $PastActive         = "";
            $PastPassive        = "";
            $Future             = "";
            $TransgressiveCont  = "";
            $TransgressivePast  = "";
            $Auxiliary          = "";

            $shapes=array_slice($parts, 3);
            $Infinitive=$shapes[0];

            $GLOBALS["index"]=1;

            if ($SContinuous)        $Continuous         = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 6));
            if ($SFuture)           $Future            = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 6));
            if ($SImperative)       $Imperative        = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 3));
            if ($SPastActive)       $PastActive        = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 8));
            if ($SPastPassive)      $PastPassive       = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 8));
            if ($STransgressiveCont)$TransgressiveCont = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 3));
            if ($STransgressivePast)$TransgressivePast = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 3));
            if ($SAuxiliary)        $Auxiliary         = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 6));

            $category=$parts[2];

            /*$sql="INSERT INTO verb_patterns_to (translate, label, base, category,
                    shapes_infinitive, 
                    shapes_continuous,
                    shapes_future, 
                    shapes_imperative, 
                    shapes_past_active, 
                    shapes_past_passive, 
                    shapes_transgressive_cont, 
                    shapes_transgressive_past, 
                    shapes_auxiliary) ".
                "SELECT $langId, '$label', '$base', $category, 
                    '$Infinitive', 
                    '$Continuous',
                    '$Future', 
                    '$Imperative', 
                    '$PastActive', 
                    '$PastPassive', 
                    '$TransgressiveCont',
                    '$TransgressivePast',
                    '$Auxiliary';";*/

            // only defined cite types
           $sql="INSERT INTO verb_patterns_to (translate, label, base, category,
                shapes_infinitive,
                shapes_continuous,
                shapes_future,
                shapes_imperative,
                shapes_past_active,
                shapes_past_passive,
                shapes_transgressive_cont,
                shapes_transgressive_past,
                shapes_auxiliary) ".
                "SELECT ?, ?, ?, ?, 
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    ?, 
                    ?,
                    ?,
                    ?;";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ississsssssss", $langId, $label, $base, $category, $Infinitive,
                $Continuous,
                $Future,
                $Imperative,
                $PastActive,
                $PastPassive,
                $TransgressiveCont,
                $TransgressivePast,
                $Auxiliary
            );

            if ($stmt->execute()) {
            } else {
                sqlError($sql, $conn);
            }
            $stmt->close();

         /*   if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }*/
        }
        echo "done<br>";

        echo "verbs: ";
        // get list of all pattern from and to before looping
        $verbsFrom=[];
        {
            $sqlFromP="SELECT `id`, `label`, `base` FROM `verb_patterns_cs`;";
            $resultFromP = $conn->query($sqlFromP);
            if ($resultFromP) {
                while ($row = $resultFromP->fetch_assoc()) {
                    $verbsFrom[$row["label"]]=["id"=>$row["id"], "base"=>$row["base"]];
                }
            } else {
                throwError("SQL error: ".$sqlFromP);
                $conn->rollback();
            }
        }

        // list to
        $verbsTo=[];
        {
            $sqlToP="SELECT `id`, `label` FROM `verb_patterns_to` WHERE `translate`=$langId;";
            $resultToP = $conn->query($sqlToP);
            if ($resultToP) {
                while ($row = $resultToP->fetch_assoc()) {
                    $verbsTo[$row["label"]]=$row["id"];
                }
            } else {
                throwError("SQL error: ".$resultToP);
                $conn->rollback();
            }
        }

        // Verb
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|', $line);
            $fromBase=$parts[0];
            $fromPattern=$parts[1];
            $patternBase= $verbsFrom[$fromPattern]["id"] ?? "NULL";
            $shapes=LoadListTranslatingToDataWithPattern($parts, 2);
            $custombase = ($fromBase==$patternBase) ? null : $fromBase;

            // add current relation to database
            {
                // get id from text of pattern (label)
                $from = $verbsFrom[$fromPattern]["id"] ?? "NULL";
                $result=sql_insert($conn, "verb_relations", ["translate"=>$langId, "from"=>$from, "custombase"=>$custombase]);
                //$sql_rel="INSERT INTO verb_relations (`translate`, `from`, `custombase`) VALUES ($langId, $from, $custombase);";
               // $result=$conn->query($sql_rel);
                if ($result["status"]=="ERROR") {
                    sqlError($sql_rel, $conn);
                    $conn->rollback();
                    exit;
                }
            }
            $idRelation=$conn->insert_id;

            // ser order of options of translate (position list A,B,C, ... is now can same level A, A, A, ... so set relative priority A=1, B=2, C=3, ...)
            //$resolvePriority=count($shapes)>0;

            // $shapes = [["Cite"=>...], [], [], ...]
            for ($j = 0; $j < count($shapes); $j++) {
                $shape = $shapes[$j];

                // cite
                $sourceRaw=$shape["Source"];
                $cites=getCitesIdsFromString($sourceRaw, $listCites, $parts[0]);

                // priority
                $priority=$j+1;

                // shape
                $pattern=$shape["Pattern"];
                $shape_to=null;
                if (isset($verbsTo[$pattern])) $shape_to=$verbsTo[$pattern]; // get id from pattern text
                $body=$shape["Body"];

                // comment
                $comment=$shape["Comment"];
                $tags=join("|", tryToGetTags($comment));

                // unresolved patterns, not linked correctly
                $tmp_imp_from_pattern=null;
                $pattern_from_body=null;
                if ($shape_to==null) {
                    $tmp_imp_from_pattern=$pattern;
                    $pattern_from_body=$body;
                }

                // insert into database
                $result=sql_insert($conn,"verbs_to", [
                    "relation"=>$idRelation,
                    "shape"=>$shape_to,
                    "comment"=>$comment,
                    "tags"=>$tags,
                    "cite"=>$cites,
                    "priority"=>$priority,
                    "tmp_pattern_from_body"=>$pattern_from_body,
                    "tmp_imp_from_pattern"=>$tmp_imp_from_pattern,
                ]);
                if ($result["status"]=="ERROR") {
                    $conn->rollback();
                    echo json_encode($result);
                    exit;
                }
            }
        }
        echo "done<br>";

        echo "adverbs: ";
        // Adverb
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line); // "teď,nyní|..."
            $rawfrom=explode(",", $parts[0]); // "teď,nyní" -> ["teď", "nyní"]
            $tos = loadListTranslatingToData($parts, 1);

            for ($s=0; $s<count($rawfrom); $s++) {
                $from=$rawfrom[$s]; // "teď", "nyní"

                // check if exists
                $adverbFromId = -1;
                $sql = "SELECT id FROM adverbs_cs WHERE shape = ? LIMIT 1";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("s", $from);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result) {
                        if ($row = $result->fetch_assoc()) {
                            $adverbFromId = $row["id"];
                        }
                    } else {
                        sqlError($sql, $conn);
                    }

                    $stmt->close();
                } else {
                    sqlError($sql, $conn);
                }
                //echo "adverbFromId: $adverbFromId<br>";

                // insert into from if not exists
                if ($adverbFromId==-1) {
                    $sql="INSERT INTO adverbs_cs (shape) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                        die();
                    }
                    $stmt->bind_param("s", $from);

                    if ($stmt->execute()) {
                        // set id
                        $adverbFromId=$conn->insert_id;
                    }else{
                        sqlError($sql, $conn);
                    }
                    $stmt->close();
                }

                // relation
                $adverbRelationId=-1;
                {
                    $sql="INSERT INTO adverb_relations (`translate`, `from`) VALUES (?, ?);";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                    }
                    $stmt->bind_param("ii", $langId, $adverbFromId);

                    if ($stmt->execute()) {
                        //ok
                        $adverbRelationId=$conn->insert_id;
                    }else{
                        sqlError($sql,$conn);
                    }
                    $stmt->close();
                }

                // to
                $values = [];

                for ($t = 0; $t < count($tos); $t++) {
                    $to = $tos[$t];

                    $shape = $conn->real_escape_string($to["Text"]);
                    $comment = $conn->real_escape_string($to["Comment"]);
                    $rawCite = $to["Source"];
                    $cites = getCitesIdsFromString($rawCite, $listCites, $parts[0]); // "90,35,78,.."
                    $tags = implode(',', tryToGetTags($comment));// "0,5,8,.."

                    // Add a row of values to the $values array
                    $values[] = "('$shape', $adverbRelationId, '$cites', '$comment', '$tags')";
                }

                if (count($values)>0) {
                    // Concatenate all the rows into the SQL statement
                    $sql = "INSERT INTO adverbs_to (shape, relation, cite, comment, tags) VALUES ".implode(", ", $values).";";

                    // Execute the single SQL statement
                    if ($conn->query($sql) === TRUE) {
                        // ok
                    } else {
                        sqlError($sql, $conn);
                    }
                }
            }
        }
        echo "done<br>";

        echo "prepositions: ";
        // Preposition
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|', $line); // "teď,nyní|..."
            $rawfrom=explode(",", $parts[0]); // "teď,nyní" -> ["teď", "nyní"]
            $falls=$parts[1];
            $tos = loadListTranslatingToData($parts, 2);

            for ($s=0; $s<count($rawfrom); $s++) {
                $from=$rawfrom[$s]; // "teď", "nyní"

                // check if exists
                $prepositionFromId = -1;
                $sql = "SELECT id FROM prepositions_cs WHERE shape = ? LIMIT 1";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("s", $from);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result) {
                        if ($row = $result->fetch_assoc()) {
                            $prepositionFromId = $row["id"];
                        }
                    } else {
                        sqlError($sql, $conn);
                    }

                    $stmt->close();
                } else {
                    sqlError($sql, $conn);
                }

                // insert into from if not exists
                if ($prepositionFromId==-1) {
                    $sql="INSERT INTO prepositions_cs (shape) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                        die();
                    }
                    $stmt->bind_param("s", $from);

                    if ($stmt->execute()) {
                        // set id
                        $prepositionFromId=$conn->insert_id;
                    }else{
                        sqlError($sql, $conn);
                    }
                    $stmt->close();
                }

                // relation
                $prepositonRelationId=-1;
                {
                    $sql="INSERT INTO preposition_relations (`translate`, `from`, `label`) VALUES (?, ?, ?);";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                    }
                    $stmt->bind_param("iis", $langId, $prepositionFromId, $from);

                    if ($stmt->execute()) {
                        //ok
                        $prepositionRelationId=$conn->insert_id;
                    }else{
                        sqlError($sql,$conn);
                    }
                    $stmt->close();
                }

                // to
                $values = [];

                for ($t = 0; $t < count($tos); $t++) {
                    $to = $tos[$t];

                    $shape = $conn->real_escape_string($to["Text"]);
                    $comment = $conn->real_escape_string($to["Comment"]);
                    $rawCite = $to["Source"];
                    $cites = getCitesIdsFromString($rawCite, $listCites, $shape); // "90,35,78,.."
                    $tags = implode(',', tryToGetTags($comment));// "0,5,8,.."

                    // Add a row of values to the $values array
                    $values[] = "('$shape', $prepositionRelationId, '$cites', '$comment', '$tags')";
                }

                if (count($values)>0) {
                    // Concatenate all the rows into the SQL statement
                    $sql = "INSERT INTO prepositions_to (shape, relation, cite, comment, tags) VALUES ".implode(", ", $values).";";

                    // Execute the single SQL statement
                    if ($conn->query($sql) === TRUE) {
                        // ok
                    } else {
                        sqlError($sql, $conn);
                    }
                }
            }
        }
        echo "done<br>";

        echo "conjunction: ";
        // Conjunction
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line); // "teď,nyní|..."
            $rawfrom=explode(",", $parts[0]); // "teď,nyní" -> ["teď", "nyní"]
            $tos = loadListTranslatingToData($parts, 1);

            for ($s=0; $s<count($rawfrom); $s++) {
                $from=$rawfrom[$s]; // "teď", "nyní"

                // check if exists
                $conjunctionFromId = -1;
                $sql = "SELECT id FROM conjunctions_cs WHERE shape = ? LIMIT 1";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("s", $from);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result) {
                        if ($row = $result->fetch_assoc()) {
                            $conjunctionFromId = $row["id"];
                        }
                    } else {
                        sqlError($sql, $conn);
                        exit;
                    }

                    $stmt->close();
                } else {
                    sqlError($sql, $conn);
                    exit;
                }
                //echo "adverbFromId: $adverbFromId<br>";

                // insert into from if not exists
                if ($conjunctionFromId==-1) {
                    $sql="INSERT INTO conjunctions_cs (shape) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                        die();
                    }
                    $stmt->bind_param("s", $from);

                    if ($stmt->execute()) {
                        // set id
                        $conjunctionFromId=$conn->insert_id;
                    }else{
                        sqlError($sql, $conn);
                        exit;
                    }
                    $stmt->close();
                }

                // relation
                $conjunctionRelationId=-1;
                {
                    $sql="INSERT INTO conjunction_relations (`translate`, `from`) VALUES (?, ?);";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                    }
                    $stmt->bind_param("ii", $langId, $conjunctionFromId);

                    if ($stmt->execute()) {
                        //ok
                        $conjunctionRelationId=$conn->insert_id;
                    }else{
                        sqlError($sql,$conn);
                        exit;
                    }
                    $stmt->close();
                }

                // to
                $values = [];

                for ($t = 0; $t < count($tos); $t++) {
                    $to = $tos[$t];

                    $shape = $conn->real_escape_string($to["Text"]);
                    $comment = $conn->real_escape_string($to["Comment"]);
                    $rawCite = $to["Source"];
                    $cites = getCitesIdsFromString($rawCite, $listCites, $shape); // "90,35,78,.."
                    $tags = implode(',', tryToGetTags($comment));// "0,5,8,.."

                    // Add a row of values to the $values array
                    $values[] = "('$shape', $conjunctionRelationId, '$cites', '$comment', '$tags')";
                }

                if (count($values)>0) {
                    // Concatenate all the rows into the SQL statement
                    $sql = "INSERT INTO conjunctions_to (shape, relation, cite, comment, tags) VALUES ".implode(", ", $values).";";

                    // Execute the single SQL statement
                    if ($conn->query($sql) === TRUE) {
                        // ok
                    } else {
                        sqlError($sql, $conn);
                    }
                }
            }
        }

        // Particle
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line); // "teď,nyní|..."
            $rawfrom=explode(",", $parts[0]); // "teď,nyní" -> ["teď", "nyní"]
            $tos = loadListTranslatingToData($parts, 1);

            for ($s=0; $s<count($rawfrom); $s++) {
                $from=$rawfrom[$s]; // "teď", "nyní"

                // check if exists
                $particleFromId = -1;
                $sql = "SELECT id FROM particles_cs WHERE shape = ? LIMIT 1";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("s", $from);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result) {
                        if ($row = $result->fetch_assoc()) {
                            $particleFromId = $row["id"];
                        }
                    } else {
                        sqlError($sql, $conn);
                    }

                    $stmt->close();
                } else {
                    sqlError($sql, $conn);
                }
                //echo "adverbFromId: $adverbFromId<br>";

                // insert into from if not exists
                if ($particleFromId==-1) {
                    $sql="INSERT INTO particles_cs (shape) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                        die();
                    }
                    $stmt->bind_param("s", $from);

                    if ($stmt->execute()) {
                        // set id
                        $particleFromId=$conn->insert_id;
                    }else{
                        sqlError($sql, $conn);
                    }
                    $stmt->close();
                }

                // relation
                $particleRelationId=-1;
                {
                    $sql="INSERT INTO particle_relations (`translate`, `from`) VALUES (?, ?);";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                    }
                    $stmt->bind_param("ii", $langId, $particleFromId);

                    if ($stmt->execute()) {
                        //ok
                        $particleRelationId=$conn->insert_id;
                    }else{
                        sqlError($sql,$conn);
                    }
                    $stmt->close();
                }

                // to
                $values = [];

                for ($t = 0; $t < count($tos); $t++) {
                    $to = $tos[$t];

                    $shape = $conn->real_escape_string($to["Text"]);
                    $comment = $conn->real_escape_string($to["Comment"]);
                    $rawCite = $to["Source"];
                    $cites = getCitesIdsFromString($rawCite, $listCites, $shape); // "90,35,78,.."
                    $tags = implode(',', tryToGetTags($comment));// "0,5,8,.."

                    // Add a row of values to the $values array
                    $values[] = "('$shape', $particleRelationId, '$cites', '$comment', '$tags')";
                }

                if (count($values)>0) {
                    // Concatenate all the rows into the SQL statement
                    $sql = "INSERT INTO particles_to (shape, relation, cite, comment, tags) VALUES ".implode(", ", $values).";";

                    // Execute the single SQL statement
                    if ($conn->query($sql) === TRUE) {
                        // ok
                    } else {
                        sqlError($sql, $conn);
                    }
                }
            }
        }

        // Interjection
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line); // "teď,nyní|..."
            $rawfrom=explode(",", $parts[0]); // "teď,nyní" -> ["teď", "nyní"]
            $tos = loadListTranslatingToData($parts, 1);

            for ($s=0; $s<count($rawfrom); $s++) {
                $from=$rawfrom[$s]; // "teď", "nyní"

                // check if exists
                $interjectionFromId = -1;
                $sql = "SELECT `id` FROM interjections_cs WHERE shape = ? LIMIT 1";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("s", $from);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result) {
                        if ($row = $result->fetch_assoc()) {
                            $interjectionFromId = $row["id"];
                        }
                    } else {
                        sqlError($sql, $conn);
                    }

                    $stmt->close();
                } else {
                    sqlError($sql, $conn);
                }
                //echo "adverbFromId: $adverbFromId<br>";

                // insert into from if not exists
                if ($interjectionFromId==-1) {
                    $sql="INSERT INTO interjections_cs (shape) VALUES (?)";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                        die();
                    }
                    $stmt->bind_param("s", $from);

                    if ($stmt->execute()) {
                        // set id
                        $interjectionFromId=$conn->insert_id;
                    }else{
                        sqlError($sql, $conn);
                    }
                    $stmt->close();
                }

                // relation
                $interjectionRelationId=-1;
                {
                    $sql="INSERT INTO interjection_relations (`translate`, `from`) VALUES (?, ?);";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        sqlError($sql, $conn);
                    }
                    $stmt->bind_param("ii", $langId, $interjectionFromId);

                    if ($stmt->execute()) {
                        //ok
                        $interjectionRelationId=$conn->insert_id;
                    }else{
                        sqlError($sql,$conn);
                    }
                    $stmt->close();
                }

                // to
                $values = [];

                for ($t = 0; $t < count($tos); $t++) {
                    $to = $tos[$t];

                    $shape = $conn->real_escape_string($to["Text"]);
                    $comment = $conn->real_escape_string($to["Comment"]);
                    $rawCite = $to["Source"];
                    $cites = getCitesIdsFromString($rawCite, $listCites, $shape); // "90,35,78,.."
                    $tags = implode(',', tryToGetTags($comment));// "0,5,8,.."

                    // Add a row of values to the $values array
                    $values[] = "('$shape', $interjectionRelationId, '$cites', '$comment', '$tags')";
                }

                if (count($values)>0) {
                    // Concatenate all the rows into the SQL statement
                    $sql = "INSERT INTO interjections_to (shape, relation, cite, comment, tags) VALUES ".implode(", ", $values).";";

                    // Execute the single SQL statement
                    if ($conn->query($sql) === TRUE) {
                        // ok
                    } else {
                        sqlError($sql, $conn);
                    }
                }
            }
        }

        // PhrasePattern
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
            //  itemsPhrasePattern.Add(ItemPhrasePattern.Load(line));
        }

        // commit sql
        $conn->commit();
    }

    //header("location: index.php");
}

function select_lang() : void {
    if (isset($_POST['id'])){
        $_SESSION['translate']=intval($_POST['id']);
        echo '{"status": "OK"}';
    }else{
        echo '{"status": "ERROR"}';
    }
}

function getResultSql($result, $id) : int{ 
    while ($row = $result->fetch_assoc()) {
        if (is_array($row)) return $row[$id];
        else return $row;
    } 
    throwError("Error getResultSql");
    return -1;
}
/*
function extractBase($str) : string{
    preg_match('/^[a-z]+/', $str, $matches);
    return $matches[0] ?? '';
}*/
function extractBase(string $str): string {
    preg_match('/^\p{Ll}+/u', $str, $matches);
    return $matches[0] ?? '';
}

// return: 0 = unknown?, 1=lower, 2=first uppercase, 3=all uppercase
function getUpperCaseType(string $str): int {
    if ($str === '') {
        return 0; // Unknown or empty string
    }

    if (mb_strtoupper($str, "UTF-8") === $str) {
        return 3; // All uppercase
    }

    if (mb_strtolower($str, "UTF-8") === $str) {
        return 1; // All lowercase
    }

    $firstLetter = mb_substr($str, 0, 1, "UTF-8");
    $rest = mb_substr($str, 1, null, "UTF-8");

    if (
        mb_strtoupper($firstLetter, "UTF-8") === $firstLetter &&
        mb_strtolower($rest, "UTF-8") === $rest
    ) {
        return 2; // First uppercase, rest lowercase
    }

    return 0; // Unknown case (e.g., mixed case)
}

/*
function loadListTranslatingToData(array $rawData, int $start) : array {
    $list=[];
         
    $len=count($rawData);
    for ($i=$start; $i<$len; $i+=3) {
        if ($i<$len-1) {
            $list[]=["Text"=>$rawData[$i], "Comment"=>$rawData[$i+1], "Source"=>$i+2>$len ? $rawData[$i+2]:""];
        }
        else if (($i-$start)%2==0 && $i==$len-1) 
            $list[]=["Text"=>$rawData[$i], "Comment"=>$rawData[$i+1], "Source"=>$rawData[$i+2]];
    }
          
    return $list; //[["Text"=>..., "Source"=>..., "Comment"=>...], [...], ...]
}*/
function loadListTranslatingToData(array $rawData, int $start): array {
    $list = [];
    $len = count($rawData);

    for ($i = $start; $i < $len; $i += 3) {
        $text = $rawData[$i] ?? '';
        $comment = $rawData[$i + 1] ?? '';
        $source = $rawData[$i + 2] ?? '';
        $list[] = ["Text" => $text, "Comment" => $comment, "Source" => $source];
    }

    return $list;
}
/*
function loadListTranslatingToDataWithPattern($rawData, $start) : array{
    $list=[];

    $len=count($rawData);
    for ($i=$start; $i<$len ; $i+=4) {
        if ($i<$len-1) $list[]=["Body"=>$rawData[$i], "Pattern"=>$rawData[$i+1], "Comment"=>$rawData[$i+2], "Source"=>""];
        else if (($i-$start)%2==0 && $i==$len-1)
            $list[]=["Body"=>$rawData[$i], "Pattern"=>$rawData[$i+1], "Comment"=>$rawData[$i+2], "Source"=>$rawData[$i+3]];
    }

    return $list;
}*/

function loadListTranslatingToDataWithPattern(array $rawData, int $start): array{
    $list = [];
    $len = count($rawData);

    for ($i = $start; $i < $len; $i += 4) {
        $body    = $rawData[$i  ] ?? '';
        $pattern = $rawData[$i+1] ?? '';
        $comment = $rawData[$i+2] ?? '';
        $source  = $rawData[$i+3] ?? '';
        $list[] = ["Body" => $body, "Pattern" => $pattern, "Comment" => $comment, "Source" => $source];
    }

    return $list;
}

function tryToGetTags(string $comment): array{
    $list=[];
         
    if (str_contains($comment, "expr.")) $list[]="expr.";
    if (str_contains($comment, "val.")) $list[]="val.";
    if (str_contains($comment, "han.")) $list[]="han.";
    if (str_contains($comment, "staří")) $list[]="staří";
    if (str_contains($comment, "mladí")) $list[]="mladí";
          
    return $list;
}

function GetArray(\mysqli $conn, $source, $pos, $len) : array {
    $arr = [];
    for ($i=0; $i<$len; $i++) $arr[$i]=mysqli_escape_string($conn, $source[$pos+$i]);
    $GLOBALS["index"]+=$len;
    return $arr;
}

function getCategoryAdjective(string $shapeA1) {
    // tvrdé
    if (str_ends_with($shapeA1,"é"))    return 1;
    if (str_ends_with($shapeA1,"ej"))   return 1;
    if (str_ends_with($shapeA1,"ý"))    return 1;
    if (str_ends_with($shapeA1,"y"))    return 1;
    // měkké
    if (str_ends_with($shapeA1,""))     return 2;
    if (str_ends_with($shapeA1,"í"))    return 2;
    if (str_ends_with($shapeA1,"i"))    return 2;
    // otcův
    if (str_ends_with($shapeA1,"ův"))   return 4;
    if (str_ends_with($shapeA1,"uv"))   return 4;
    if (str_ends_with($shapeA1,"új"))   return 4;
    if (str_ends_with($shapeA1,"uj"))   return 4;
    //matčin
    if (str_ends_with($shapeA1,"n"))    return 3;
    // neznámé
    return 0;
}

function getCitesIdsFromString(string $string, array $listCites, string $word="") : string {
    // parse
    $string=trim($string);
    if (strlen($string)==0) return '';
    $sources=explode(",", $string); // $sources=["nbdp", "sncj", ...]
    $citeIds=[]; // $citeIds=[0,3,7, ...]

    // check "id string" (shortcut) and convert into id list
    foreach ($sources as $source) {// "nbdp", "sncj", ...
        //$source = trim($source);
        if ($source!=''){
            if (isset($listCites[$source])) $citeIds[]=$listCites[$source];
            else{
                throwError("Cite not found '$source' in list listCites for $word!");
            }
        }
    }
    if (strlen($string)>0 && count($citeIds)==0) {
        throwError("cites not resolved!");
    }
    return implode(",", $citeIds); //"0,3,7,..."
}
