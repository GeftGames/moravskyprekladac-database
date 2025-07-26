<?php 
// Rest api - database global tools 
namespace REST;
require_once "help.php";
use function Safe\mysql_real_escape_string;

function database_init(): void{
    $dev=$GLOBALS["dev"];
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
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes TEXT,
            gender TINYINT DEFAULT 0,
            pattern TINYINT DEFAULT 0,
            uppercase TINYINT DEFAULT 0,
            tags VARCHAR(255)
        );",
        "CREATE TABLE adjective_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes TEXT,
            category TINYINT DEFAULT 0,
            tags VARCHAR(255)
        );",
        "CREATE TABLE pronoun_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes TEXT,
            category TINYINT DEFAULT 0,
            syntax TINYINT DEFAULT 0,
            pattern TINYINT DEFAULT 0,
            uppercase TINYINT DEFAULT 0,
            tags VARCHAR(255)
        );",
        "CREATE TABLE number_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes TEXT,
            pattern_type TINYINT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE verb_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes_infinitive TEXT,
            shapes_continous TEXT,
            shapes_future TEXT,
            shapes_imperative TEXT,
            shapes_past_active TEXT,
            shapes_past_passive TEXT,
            shapes_transgressive_cont TEXT,
            shapes_transgressive_past TEXT,
            shapes_auxiliary TEXT,
            category TINYINT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE adverbs_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shape VARCHAR(255),
            tags VARCHAR(255)
        );",
        "CREATE TABLE prepositions_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shape VARCHAR(255),
            falls VARCHAR(255),
            tags VARCHAR(255)
        );",
        "CREATE TABLE conjunctions_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shape VARCHAR(255),
            type TINYINT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE particles_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shape VARCHAR(255),
            tags VARCHAR(255)
        );",
        "CREATE TABLE interjections_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shape VARCHAR(255),
            tags VARCHAR(255)
        );",

        // Translate to
        "CREATE TABLE noun_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255) DEFAULT '$namedef',
            translate INT,
            base VARCHAR(255),
            tags VARCHAR(255),
            uppercase TINYINT,
            pattern TINYINT,
            gender TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE adjective_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT '$namedef',
            translate INT,
            category TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE pronoun_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT '$namedef',
            translate INT,
            pattern_type TINYINT,
            base VARCHAR(255),
            shapes TEXT
        );",
        "CREATE TABLE number_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255) DEFAULT '$namedef',
            translate INT,
            pattern_type TINYINT,
            base VARCHAR(255),
            shapes TEXT
        );",
        "CREATE TABLE verb_patterns_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            pattern_type_show TINYINT,
            translate INT,
            category TINYINT,
            label varchar(255) DEFAULT '$namedef',
            base VARCHAR(255),
            shapes_infinitive TEXT, 
            shapes_continous TEXT, 
            shapes_future TEXT, 
            shapes_imperative TEXT, 
            shapes_past_active TEXT, 
            shapes_past_passive TEXT, 
            shapes_transgressive_cont TEXT, 
            shapes_transgressive_past TEXT, 
            shapes_auxiliary TEXT
        );",

        "CREATE TABLE adverbs_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `certainty` TINYINT
        );",

        "CREATE TABLE prepositions_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `shape` varchar(255),
            `relation` INT,
            `comment` VARCHAR(255),
            `cite` VARCHAR(255),
            `falls` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `certainty` TINYINT
        );",

        "CREATE TABLE conjunctions_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `certainty` TINYINT
        );",
        "CREATE TABLE particles_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `certainty` TINYINT
        );",
        "CREATE TABLE interjections_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `certainty` TINYINT
        );",

        // to
        "CREATE TABLE nouns_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` INT,
            `custombase` VARCHAR(255),
            `cite` TEXT,
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `certainty` TiNYINT,
            `tmp_pattern_from_body` VARCHAR(255),
            `tmp_imp_from_pattern` VARCHAR(255)
        );",
        "CREATE TABLE adjectives_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` INT,
            `custombase` VARCHAR(255),
            `cite` TEXT,
            `comment` VARCHAR(255),
            `certainty` TiNYINT,
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `tmp_pattern_from_body` VARCHAR(255),
            `tmp_imp_from_pattern` VARCHAR(255)
        );",

        // slovník search
        "CREATE TABLE noun_cs_synonyms (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `source` INT,
            `another` INT
        );",
        "CREATE TABLE adverb_cs_synonyms (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `source` INT,
            `another` INT
        );",


        // relations
        "CREATE TABLE noun_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `from` INT
        );",

        "CREATE TABLE adjective_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            `from` INT
        );",
        "CREATE TABLE pronoun_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            `from` INT
        );",
        "CREATE TABLE number_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            `from` INT
        );",
        "CREATE TABLE verb_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            `from` INT
        );",
        "CREATE TABLE adverb_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `from` VARCHAR(255)
        );",
        "CREATE TABLE preposition_relations (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `label` VARCHAR(255),
            `translate` INT,
            `from` INT
        );",
        "CREATE TABLE conjunction_relations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            `from` INT
        );",
        "CREATE TABLE particle_relations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            `from` INT
        );",
        "CREATE TABLE interjection_relations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            `from` VARCHAR(255)
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
            `label` VARCHAR(255) DEFAULT '$namedef',
            `type` TINYINT,
            `data` JSON
        );",
        
        // Ukázky
        "CREATE TABLE piecesofcite (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,  
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
            label varchar(255) NOT NULL DEFAULT '$namedef',
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
            region_id INT ,
            region_id_production INT, /*for export*/
            translate INT, /*translate parent*/
            zone_type TINYINT NOT NULL,
            confinence TINYINT NOT NULL,
            comment VARCHAR(255) NOT NULL
        );",

        "CREATE TABLE place_nations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            nation_id INT,
            nation_id_production INT, /*for export*/
            translate INT, /*translate parent*/
            confinence TINYINT NOT NULL,
            comment VARCHAR(255) NOT NULL
        );",

        "CREATE TABLE place_langs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
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
            `cite` INT
        );",
        "CREATE TABLE replaces_inside (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `from` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` INT
        );",
        "CREATE TABLE replaces_end (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `from` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` INT
        );",
        "CREATE TABLE replaces_defined (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` INT,
            `partOfSpeech` TINYINT,            
            `pos` TINYINT            
        );",
        "CREATE TABLE replaces_defined_noun (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` INT,          
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
            `cite` INT,
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
            `cite` INT,
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
            `from` varchar(255),
            `display` tinyint,
            `tags` varchar(255) NOT NULL
        );",

        // phrases_to
        "CREATE TABLE phrases_to (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            relation INT,
            `pos` tinyint,
            shape varchar(255) NOT NULL ,
            tags varchar(255) NOT NULL ,
            comment varchar(255) NOT NULL ,
            cite varchar(255) NOT NULL 
        );",
        "CREATE TABLE sentencepart_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `from` VARCHAR(255) DEFAULT '$namedef',
            `translate` INT,
            `display` tinyint NOT NULL ,
            `tags` varchar(255) NOT NULL 
        );",
        "CREATE TABLE sentenceparts_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `pos` tinyint NOT NULL ,
            `tags` VARCHAR(255) NOT NULL ,
            `shape` VARCHAR(255) NOT NULL ,
            `cite` VARCHAR(255) NOT NULL ,
            `comment` VARCHAR(255) NOT NULL         
        );",
        "CREATE TABLE sentence_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `from` VARCHAR(255) DEFAULT '$namedef',
            `translate` INT,
            `display` tinyint NOT NULL ,
            `tags` varchar(255) NOT NULL 
        );",
        "CREATE TABLE sentences_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `pos` tinyint NOT NULL ,
            `shape` VARCHAR(255),
            `tags` VARCHAR(255) NOT NULL ,
            `cite` VARCHAR(255) NOT NULL,
            `comment` VARCHAR(255) NOT NULL    
        );",
    ];

    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
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

function database_importold() :void {
    $dev=$GLOBALS["dev"];
    if (!isset($_SESSION["username"])) {
        throwError("Nejste přihlášený!");
        return;
    }

    if (empty($_FILES["database_files"]["name"][0])) {
        throwError("Chybí soubor databáze!");
        return;
    }

    // mysql connect
    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);

    // enums
    //require "./data/enum_region.php";

    // File settings
    $files=$_FILES["database_files"];

    // Files
    foreach ($files["tmp_name"] as $key => $tmpName) {

        echo "file: ".htmlspecialchars($files["name"][$key])."<br>";

        // Check before upload
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

        if ($lines[0]!="TW v4") {
            throwError("Databáze není typu 'TW v4': '".$lines[0]."'");
            continue;
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
        $cites="";
        $i=0;
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
                     //   $region=GetRegionCode(explode(">", substr($line, 1)));
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
            }
        }else{
            sqlError($sql, $conn);
        }
        $langId=$conn->insert_id;
        echo "done<br>";

        echo "cites: ";
        // cites
        $citesRawLines=explode('\\n', $cites);
        foreach ($citesRawLines as $citeLineRaw) {
            echo $citeLineRaw;
            if (str_starts_with($citeLineRaw, "kniha|")
            ||  str_starts_with($citeLineRaw, "periodikum|")
            ||  str_starts_with($citeLineRaw, "sncj|")
            ||  str_starts_with($citeLineRaw, "web|")
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

                $dataPieceSave=json_encode($dataPiece, JSON_UNESCAPED_UNICODE);

                $sqlPiece="INSERT INTO piecesofcite (label, parent, translate, data) VALUES ('$label', '$idcite', '$langId', '$dataPieceSave')";

                if ($conn->query($sqlPiece) === TRUE) {
                    //ok
                } else {
                    sqlError($sqlPiece, $conn);
                }
            }
        }

        $listCites=[];
        {
            $sqlPiece="SELECT id, label FROM piecesofcite WHERE translate = '$langId'";
            $resultCites=$conn->query($sqlPiece);
            if ($resultCites) {
                while($row = $resultCites->fetch_assoc()) {
                    if ($row["label"]!="") $listCites[$row["label"]]=$row["id"];
                }
            }else{
                sqlError($sqlPiece, $conn);
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
            $pos=$parts[2];
            $tags="";

            $sql="INSERT INTO sentence_relations (`translate`, `from`) VALUES ($langId, '$from');";
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
                $tags=join(",", tryToGetTags($comment));
                $sourceIds=convertCites($source,$listCites);

                $sql_to[]="($idPhrase, '$shape', '$tags', '$comment', '$sourceIds')";
            }

            $sqlTo="INSERT INTO sentences_to (relation, shape, tags, comment, cite) VALUES ".implode(", ", $sql_to).";";
            if ($conn->query($sqlTo) === TRUE) {
                //ok
            }else{
                sqlError($sqlTo,$conn);
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
            }

            $idSentencePart=$conn->insert_id;

            $sql_to=[];
            $tos=loadListTranslatingToData($parts, 2);
            foreach ($tos as $to) {
                $shape=mysqli_escape_string($conn, $to["Text"]);
                $comment=mysqli_escape_string($conn, $to["Comment"]);
                $source=$to["Source"];
                $tags=join(",", tryToGetTags($comment));
                $sourceIds=convertCites($source,$listCites);
                $sql_to[]="($idSentencePart, '$shape', '$tags', '$comment', '$sourceIds')";
            }

            $sqlTo="INSERT INTO sentenceparts_to (relation, shape, tags, comment, cite) VALUES ".implode(", ", $sql_to).";";
            if ($conn->query($sqlTo) === TRUE) {
                //ok
            }else{
                sqlError($sqlTo,$conn);
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

            $sql="INSERT INTO phrase_relations (`translate`, `from`, `display`, `pos`, `tags`) VALUES ($langId, '$from', $display, $pos, '$tags');";
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
                $sourceIds=convertCites($source, $listCites);
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

                $sourceIds=convertCites($source,$listCites);
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
            $gender=$parts[1];
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
            $gender=intval($parts[1]);
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
            $sqlFromP="SELECT `id`, `label` FROM noun_patterns_cs;";
            $resultFromP = $conn->query($sqlFromP);
            if ($resultFromP) {
                while($row = $resultFromP->fetch_assoc()) {
                    $listFrom[$row["label"]]=$row["id"];
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
            if ($line == "")  continue;

            $parts = explode('|', $line);
            //$fromShape=$parts[0];
            $fromPattern=$parts[1];
            $uppercase=intval($parts[2]);
            $shapes=LoadListTranslatingToDataWithPattern($parts, 3);

            // relations
            {
                // get id from text
                $from = $listFrom[$fromPattern] ?? "NULL";

                $sql_rel="INSERT INTO noun_relations (`translate`, `from`) VALUES ($langId, $from);";

                if ($conn->query($sql_rel) === TRUE) {
                    //ok
                    //throwInfo("noun_relations inserted");
                }else{
                    sqlError($sql_rel, $conn);
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

            // to
            $resolvePriority=(count($shapes)>0);

            // $shapes = [["Cite"=>...], [], [], ...]
            for ($j = 0; $j < count($shapes); $j++) {
                $shape = $shapes[$j];

                // cite
                $sourceRaw=$shape["Source"]; //$sourceRaw="nbdp|sncj|.."
                $sources=explode("|", $sourceRaw);// $sources=["nbdp", "sncj", ...]
                $citeIds=[]; // $citeIds=[0,3,7, ...]
                foreach ($sources as $source) {// "nbdp", "sncj", ...
                    if (isset($listCites[$source])) $citeIds[]=$listCites[$source];
                }
                $cite=join("|", $citeIds); //"0,3,7,..."

                // priority
                $prioriry=0;
                if ($resolvePriority) {
                    if ($j==0) $prioriry=1;
                } else {
                    if ($j>1) $prioriry=-1;
                }

                // shape
                $pattern=$shape["Pattern"];
                $shape_to="-1";
                if (isset($listNounTo[$pattern])) $shape_to=$listNounTo[$pattern]; // get id from pattern text
                $body=$shape["Body"];

                // find pattern to, update values
                if ($shape_to!="-1") {
                    $sql_pt= /** @lang SQL */
                        "UPDATE noun_patterns_to SET `uppercase` = $uppercase WHERE id = $shape_to;";

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

                $sqlTo = /** @lang SQL */
                "INSERT INTO nouns_to (`relation`, `priority`, `shape`, `comment`, `tags`, `cite`, `tmp_pattern_from_body`, `tmp_imp_from_pattern`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sqlTo);
                if ($stmt === false) {
                    sqlError($sqlTo, $conn);
                } else {
                    $stmt->bind_param("iissssss", $idRelation, $priority, $shape_to, $comment_format, $tags, $cite, $pattern_from_body, $tmp_imp_from_pattern);

                    if ($stmt->execute()) {
                        // ok   //ok
                    } else {
                        sqlError($sqlTo, $conn);
                    }

                    $stmt->close();
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
          //  $category=$parts[1];
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
        // PatternAdjectivesTo
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

            $sql="INSERT INTO adjective_patterns_to (translate, label, category, shapes) ".
                "SELECT $langId, '$label', '$category', '".implode("|", $shapesA)."|".implode("|", $shapesI)."|".implode("|", $shapesF)."|".implode("|", $shapesN)."';";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "Adjectives: ";
        // Adjectives
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                //itemsAdjectives.Add(ItemAdjective.Load(line));
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
            $label=$parts[0];
            $base=mysqli_escape_string($conn,extractBase($label));
            $shapes=array_slice($parts, 1);

            $sql="INSERT INTO pronoun_patterns_to (translate, label, base, shapes) ".
                "SELECT $langId, '$label', '$base', '".mysqli_escape_string($conn, implode("|",$shapes))."';";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "pronouns: ";
        // Pronouns
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
          //      itemsPronouns.Add(ItemPronoun.Load(line));
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
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
            //    itemsNumbers.Add(ItemNumber.Load(line));
        }
        echo "done<br>";

        echo "verbs: ";
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
            $SContinous          = ($shapetype &   1) ==  1;
            $SImperative         = ($shapetype &   2) ==  2;
            $SPastActive         = ($shapetype &   4) ==  4;
            $SPastPassive        = ($shapetype &   8) ==  8;
            $SFuture             = ($shapetype &  16) == 16;
            $STransgressiveCont  = ($shapetype &  32) == 32;
            $STransgressivePast  = ($shapetype &  64) == 64;
            $SAuxiliary          = ($shapetype & 128) ==128;

            $Continous          = "";
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

            if ($SContinous)        $Continous         = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 6));
            if ($SFuture)           $Future            = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 6));
            if ($SImperative)       $Imperative        = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 3));
            if ($SPastActive)       $PastActive        = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 8));
            if ($SPastPassive)      $PastPassive       = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 8));
            if ($STransgressiveCont)$TransgressiveCont = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 3));
            if ($STransgressivePast)$TransgressivePast = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 3));
            if ($SAuxiliary)        $Auxiliary         = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 6));

            $category=$parts[2];

            $sql="INSERT INTO verb_patterns_cs (label, base, category, 
                    shapes_infinitive, 
                    shapes_continous, 
                    shapes_future, 
                    shapes_imperative, 
                    shapes_past_active, 
                    shapes_past_passive, 
                    shapes_transgressive_cont, 
                    shapes_transgressive_past, 
                    shapes_auxiliary) ".
                "SELECT '$label', '$base', '$category', 
                    '$Infinitive', 
                    '$Continous', 
                    '$Future', 
                    '$Imperative', 
                    '$PastActive', 
                    '$PastPassive', 
                    '$TransgressiveCont',
                    '$TransgressivePast',
                    '$Auxiliary' ".
                "WHERE NOT EXISTS (SELECT 1 FROM verb_patterns_cs WHERE label = '$label');";

            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }
        echo "done<br>";

        echo "verbsto: ";
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
            $SContinous          = ($shapetype &   1) ==  1;
            $SImperative         = ($shapetype &   2) ==  2;
            $SPastActive         = ($shapetype &   4) ==  4;
            $SPastPassive        = ($shapetype &   8) ==  8;
            $SFuture             = ($shapetype &  16) == 16;
            $STransgressiveCont  = ($shapetype &  32) == 32;
            $STransgressivePast  = ($shapetype &  64) == 64;
            $SAuxiliary          = ($shapetype & 128) ==128;

            $Continous          = "";
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

            if ($SContinous)        $Continous         = implode("|", GetArray($conn, $shapes, $GLOBALS["index"], 6));
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
                    shapes_continous, 
                    shapes_future, 
                    shapes_imperative, 
                    shapes_past_active, 
                    shapes_past_passive, 
                    shapes_transgressive_cont, 
                    shapes_transgressive_past, 
                    shapes_auxiliary) ".
                "SELECT $langId, '$label', '$base', $category, 
                    '$Infinitive', 
                    '$Continous', 
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
                shapes_continous,
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
                $Continous,
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
        // Verb
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
            //    itemsVerbs.Add(ItemVerb.Load(line));
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
                    $cites = getCitesIdsFromString($rawCite, $listCites); // "90,35,78,.."
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
                    $cites = getCitesIdsFromString($rawCite, $listCites); // "90,35,78,.."
                    $tags = implode(',', tryToGetTags($comment));// "0,5,8,.."

                    // Add a row of values to the $values array
                    $values[] = "('$shape', $adverbRelationId, '$cites', '$comment', '$tags')";
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
                    $cites = getCitesIdsFromString($rawCite, $listCites); // "90,35,78,.."
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
                    $cites = getCitesIdsFromString($rawCite, $listCites); // "90,35,78,.."
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
                    $cites = getCitesIdsFromString($rawCite, $listCites); // "90,35,78,.."
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
    }

    header("location: index.php");
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

function extractBase($str) : string{
    preg_match('/^[a-z]+/', $str, $matches);
    return $matches[0] ?? '';
}

function getUpperCaseType($str) : int {
    // return: 0 = unknown?, 1=lower, 2=first uppercase, 3=all uppercase
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

function loadListTranslatingToData($rawData, $start) : array{
    $list=[];
         
    $len=count($rawData);
    for ($i=$start; $i<$len; $i+=3) {
        if ($i<$len-1) {
            $list[]=["Text"=>$rawData[$i], "Comment"=>$rawData[$i+1], "Source"=>$i+2>$len ? $rawData[$i+2]:""];
        }
        else if (($i-$start)%2==0 && $i==$len-1) 
            $list[]=["Text"=>$rawData[$i], "Comment"=>$rawData[$i+1], "Source"=>$rawData[$i+2]];
    }
          
    return $list;
}

function loadListTranslatingToDataWithPattern($rawData, $start) : array{
    $list=[];

    $len=count($rawData);
    for ($i=$start; $i<$len ; $i+=4) {
        if ($i<$len-1) $list[]=["Body"=>$rawData[$i], "Pattern"=>$rawData[$i+1], "Comment"=>$rawData[$i+2], "Source"=>""];
        else if (($i-$start)%2==0 && $i==$len-1)
            $list[]=["Body"=>$rawData[$i], "Pattern"=>$rawData[$i+1], "Comment"=>$rawData[$i+2], "Source"=>$rawData[$i+3]];
    }

    return $list;
}

function tryToGetTags($comment) : array{
    $list=[];
         
    if (str_contains($comment, "expr.")) $list[]="expr.";
    if (str_contains($comment, "val.")) $list[]="val.";
    if (str_contains($comment, "han.")) $list[]="han.";
    if (str_contains($comment, "staří")) $list[]="staří";
    if (str_contains($comment, "mladí")) $list[]="mladí";
          
    return $list;
}

function GetArray($conn, $source, $pos, $len) : array {
    $arr = [];
    for ($i=0; $i<$len; $i++) $arr[$i]=mysqli_escape_string($conn, $source[$pos+$i]);
    $GLOBALS["index"]+=$len;
    return $arr;
}

function getCategoryAdjective($shapeA1) {
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

function getCitesIdsFromString($string, $listCites) : ?string {
    // parse
    if ($string==null) return null;
    $sources=explode(",", $string); // $sources=["nbdp", "sncj", ...]
    $citeIds=[]; // $citeIds=[0,3,7, ...]

    // check existing id string (shortcut) and convert into id list
    foreach ($sources as $source) {// "nbdp", "sncj", ...
        if (isset($listCites[$source])) $citeIds[]=$listCites[$source];
    }

    return implode("|", $citeIds); //"0,3,7,..."
}

/* @param array $listCites
 *
 */
function convertCites($rawCites, $listCites) {
    $sourceRaw=$rawCites; //$sourceRaw="nbdp|sncj|.."
    $sources=explode("|", $sourceRaw);// $sources=["nbdp", "sncj", ...]
    $citeIds=[]; // $citeIds=[0,3,7, ...]
    foreach ($sources as $source) {// "nbdp", "sncj", ...
        if (isset($listCites[$source])) $citeIds[]=$listCites[$source];
    }
    return join("|", $citeIds); //"0,3,7,..."
}