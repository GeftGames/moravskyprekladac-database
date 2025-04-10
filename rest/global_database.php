<?php 
// Rest api - database global tools 
namespace REST;

use function Safe\mysql_real_escape_string;

function database_init() :void{
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
        "CREATE TABLE preposition_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shape VARCHAR(255),
            falls VARCHAR(255),
            tags VARCHAR(255)
        );",
        "CREATE TABLE conjunction_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shape VARCHAR(255),
            type TINYINT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE particle_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shape VARCHAR(255),
            tags VARCHAR(255)
        );",
        "CREATE TABLE interjection_cs (
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
        "CREATE TABLE prepositions_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shapes varchar(255),
            translate INT,
            falls VARCHAR(255)
        );",

        "CREATE TABLE adverbs_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT
        );",
        "CREATE TABLE conjunctions_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT
        );",
        "CREATE TABLE particles_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT
        );",
        "CREATE TABLE interjections_to (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `relation` INT,
            `shape` TEXT,
            `cite` VARCHAR(255),
            `comment` VARCHAR(255),
            `tags` VARCHAR(255),
            `priority` TiNYINT
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
            `tags` VARCHAR(255),
            `priority` TiNYINT,
            `tmp_pattern_from_body` VARCHAR(255),
            `tmp_imp_from_pattern` VARCHAR(255)
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
            pattern_from INT
        );",
        "CREATE TABLE pronoun_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            pattern_from INT
        );",
        "CREATE TABLE number_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            pattern_from INT
        );",
        "CREATE TABLE verb_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            pattern_from INT
        );",
        "CREATE TABLE adverb_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `from` VARCHAR(255)
        );",
        "CREATE TABLE preposition_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shape_from INT
        );",
        "CREATE TABLE conjuction_relations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shape_from INT
        );",
        "CREATE TABLE particle_relations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shape_from INT
        );",
        "CREATE TABLE interjection_relations (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shape_from VARCHAR(255)
        );",


        // translate
        "CREATE TABLE translate (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translateName VARCHAR(255),
            administrativeTown VARCHAR(255),
            gpsX float,
            gpsY float,
            region INT,
            subregion INT,
            country TINYINT,
            langtype TINYINT,
            quality TINYINT,
            dialect TINYINT,
            editors VARCHAR(255),
            devinfo TEXT,
            options TEXT
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

        // simpleword_relations
        "CREATE TABLE simpleword_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate int,
            shape_from varchar(255),
            display tinyint,
            tags varchar(255),
            uppercase tinyint
        );",

        // simpleword_to
        "CREATE TABLE simpleword_to (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            parent int,
            shapes varchar(255),
            tags varchar(255),
            comment varchar(255),
            source int
        );",

        // phrase_relations
        "CREATE TABLE phrase_relations (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate int,
            shape_from varchar(255),
            display tinyint,
            pos tinyint,
            tags varchar(255)
        );",

        // phrase_to
        "CREATE TABLE phrase_to (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            parent int,
            shape varchar(255),
            tags varchar(255),
            comment varchar(255),
            source int
        );",

        // regions
        "CREATE TABLE regions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255) NOT NULL DEFAULT '$namedef',
            type TINYINT,
            parent INT,
            translates JSON
        );",

        // regions ploace
        "CREATE TABLE place_regions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            region_id INT,
            zone_type TINYINT,
            confinence TINYINT,
            comment VARCHAR(255)
        );",

        // replaces
        "CREATE TABLE replaces_start (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` INT
        );",
        "CREATE TABLE replaces_inside (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` INT
        );",
        "CREATE TABLE replaces_end (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT '$namedef',
            `source` VARCHAR(255),
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
        $devinfo_saveFormat = $conn->real_escape_string($devinfo);
        $options_saveFormat = $conn->real_escape_string($options);

        // insert translate
        $sql="INSERT INTO translate (`translateName`, `administrativeTown`, `gpsX`, `gpsY`, `country`, `langtype`, `quality`, `dialect`, `editors`, `devinfo`, `options`) SELECT 
        '$name', '$administrativeTown', $gpsX, $gpsY, $country, $langtype, $quality, $dialect, '$editors', '$devinfo_saveFormat', '$options_saveFormat'
        WHERE NOT EXISTS (SELECT 1 FROM translate WHERE translateName = '$name')";

        if ($conn->query($sql) === TRUE) {
            //ok
            if (mysqli_affected_rows($conn) > 0) {
            } else {
                throwError("Překlad s tímto názvem už v databázi existuje!");
            }
        }else{
            sqlError($sql, $conn);
        }
        $langId=$conn->insert_id;

        // cites

        $citesRawLines=explode('\\n', $cites);

        foreach ($citesRawLines as $citeLineRaw) {
            if (str_starts_with($citeLineRaw, "kniha|")
             || str_starts_with($citeLineRaw, "periodikum|")
             || str_starts_with($citeLineRaw, "sncj|")
             || str_starts_with($citeLineRaw, "web|")
             || str_starts_with($citeLineRaw, "cja|")) {
                $citeVars=[];
                $vars=explode("|", $citeLineRaw); //["smt=d", "shgj=df", ...]

                foreach ($vars as $varr) {
                    $var=explode("=", $varr);//["smt","d"]
                    if (count($var)==2){
                        $varCode=$var[0];
                        $varValue=$var[1];
                        $citeVars[$varCode]=$varValue;
                    } elseif (count($var)==1) {
                        $citeVars[$var[0]]=true;
                    }
                }
                $citeData=[];
                // more info ./globa/cites.php
                $citetypeRaw=$vars[0];
                $citeType=0;

                if ($citetypeRaw=="kniha") $citeType=1;
                elseif ($citetypeRaw=="web") $citeType=2;
                elseif ($citetypeRaw=="sncj") $citeType=3;
                elseif ($citetypeRaw=="periodikum") $citeType=4;

                $shortcut="";
                if (isset($citeVars["shortcut"]))$shortcut=$citeVars["shortcut"];
                else if ($citetypeRaw=="sncj") $shortcut="sncj";
                else throwError("Missing shortcut: ".$citetypeRaw);

                // only defined cite types
                $listSame=["shortcut", "nazev", "podnazev", "odkaz", "issn", "ibsn", "autor", "jmeno", "prijmeni", "odkaz", "vydavatel", "spolecnost"];
                $sql = "INSERT INTO cites (label, data, type)
                    SELECT ?, ?, ?
                    WHERE NOT EXISTS (SELECT 1 FROM cites WHERE label = ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssis", $label, $data, $citeType, $label);

                $data=json_encode($citeData,JSON_UNESCAPED_UNICODE);

                $label="";
                if (isset($citeVars["nazev"])) $label=$citeVars["nazev"];
                else if (isset($citeVars["typ"]) && ($citeVars["typ"]=="sncj" || $citeVars["typ"]=="cja" || $citeVars["typ"]=="inf")) $label=$citeVars["typ"];
                else if (isset($citeVars["shortcut"])) $label=$citeVars["shortcut"];
                else if (isset($citeVars["odkaz"])) $label=$citeVars["odkaz"];

                $sql="INSERT INTO cites (label, data, type) SELECT '$label', '$data', $citeType
                    WHERE NOT EXISTS (SELECT 1 FROM cites WHERE label = '$label')";

                $idcite=-1;
                if ($stmt->execute()) {
                    $idcite=$conn->insert_id;
                }else{
                    sqlError($sql, $conn);
                }


                // Pieces of cite
                $dataPiece=[];
                $listSameP=["strany", "cislo", "rocnik", "odkaz", "kapitola", "shortcut", "zpracovano"];
                foreach ($listSameP as $key) {
                    if (isset($citeVars[$key])) {
                        if ($citeVars[$key]!="")$dataPiece[$key]=$citeVars[$key];
                    }
                }

                $dataPieceSave=json_encode($dataPiece, JSON_UNESCAPED_UNICODE);

                $sqlPiece="INSERT INTO piecesofcite (label, parent, translate, data) VALUES ('$label', '$idcite', '$langId', '$dataPieceSave')";

                if ($conn->query($sqlPiece) === TRUE) {
                    //ok
                }else{
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

        // SentencePattern
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;
           // itemsSentencePatterns.Add(ItemSentencePattern.Load(line));
        }

        // SentencePatternPart
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;
           // itemsSentencePatternParts.Add(ItemSentencePatternPart.Load(line));
        }

        // Sentences
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
            //itemsSentences.Add(ItemSentence.Load(line));
        }

        // SentencePart
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;
         //   itemsSentenceParts.Add(ItemSentencePart.Load(line));
        }

        // Phrase
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from=$parts[0];
            $display=$parts[1];
            $pos=$parts[2];
            $tags="";

            $sql="INSERT INTO phrase_relations (translate, shape_from, display, pos, tags) VALUES ($langId, '$from', $display, $pos, '$tags');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }

            $idPhrase=$conn->insert_id;

            $sql_to=[];
            $tos=loadListTranslatingToData($parts, 3);
            foreach ($tos as $to) {
                $shape=$to["Text"];
                $comment=$to["Comment"];
                $source=$to["Source"];
                $tags=join(",", tryToGetTags($comment));
                $sourceId=0;

                $sql_to[]="($idPhrase, '$shape', '$tags', '$comment', $sourceId)";
            }

            $sqlTo="INSERT INTO phrase_to (parent, shape, tags, comment, source) VALUES ".implode(", ", $sql_to).";";
            if ($conn->query($sqlTo) === TRUE) {
                //ok
            }else{
                sqlError($sqlTo,$conn);
            }
        }

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

            $sql="INSERT INTO simpleword_relations (translate, shape_from, display, tags, uppercase) VALUES ($langId, '$from', $display, '$tags', $uppercase);";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }

            $idSimpleWord=$conn->insert_id;

            $tos=loadListTranslatingToData($parts,2);
            $sql_to=[];
            foreach ($tos as $to) {
                $shape=$to["Text"];
                $comment=$to["Comment"];
                $source=$to["Source"];
                $tags=join(",", tryToGetTags($comment));
                $sourceId=0;

                $sql_to[]="($idSimpleWord, '$shape', '$tags', '$comment', $sourceId)";
            }

            $sql="INSERT INTO simpleword_to (parent, shapes, tags, comment, source) VALUES".implode(", ", $sql_to).";";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }

        }

        // ReplaceS
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from="";
            $to="";
            if (count($parts)==1) $from=$to=$parts[0];
            else {
                $from=$parts[0];
                $to=$parts[1];
            }

            $sql="INSERT INTO replaces_start (`translate`, `source`, `to`) VALUES ($langId, '$from', '$to');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }

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
                $from=$parts[0];
                $to=$parts[1];
            }

            $sql="INSERT INTO replaces_inside (`translate`, `source`, `to`) VALUES ($langId, '$from', '$to');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }

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
                $from=$parts[0];
                $to=$parts[1];
            }

            $sql="INSERT INTO replaces_end (`translate`, `source`, `to`) VALUES ($langId, '$from', '$to');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }

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

        // Adjectives
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                //itemsAdjectives.Add(ItemAdjective.Load(line));
        }

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

        // PatternPronounsTo
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $label=$parts[0];
            $base=extractBase($label);
            $shapes=array_slice($parts, 1);

            $sql="INSERT INTO pronoun_patterns_to (translate, label, base, shapes) ".
                "SELECT $langId, '$label', '$base', '".implode("|",$shapes)."';";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }

        // Pronouns
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
          //      itemsPronouns.Add(ItemPronoun.Load(line));
        }

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

        // Numbers
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
            //    itemsNumbers.Add(ItemNumber.Load(line));
        }

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

            if ($SContinous)        $Continous         = implode("|", GetArray($shapes, $GLOBALS["index"], 6));
            if ($SFuture)           $Future            = implode("|", GetArray($shapes, $GLOBALS["index"], 6));
            if ($SImperative)       $Imperative        = implode("|", GetArray($shapes, $GLOBALS["index"], 3));
            if ($SPastActive)       $PastActive        = implode("|", GetArray($shapes, $GLOBALS["index"], 8));
            if ($SPastPassive)      $PastPassive       = implode("|", GetArray($shapes, $GLOBALS["index"], 8));
            if ($STransgressiveCont)$TransgressiveCont = implode("|", GetArray($shapes, $GLOBALS["index"], 3));
            if ($STransgressivePast)$TransgressivePast = implode("|", GetArray($shapes, $GLOBALS["index"], 3));
            if ($SAuxiliary)        $Auxiliary         = implode("|", GetArray($shapes, $GLOBALS["index"], 6));

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

        // PatternVerbsTo
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

            if ($SContinous)        $Continous         = implode("|", GetArray($shapes, $GLOBALS["index"], 6));
            if ($SFuture)           $Future            = implode("|", GetArray($shapes, $GLOBALS["index"], 6));
            if ($SImperative)       $Imperative        = implode("|", GetArray($shapes, $GLOBALS["index"], 3));
            if ($SPastActive)       $PastActive        = implode("|", GetArray($shapes, $GLOBALS["index"], 8));
            if ($SPastPassive)      $PastPassive       = implode("|", GetArray($shapes, $GLOBALS["index"], 8));
            if ($STransgressiveCont)$TransgressiveCont = implode("|", GetArray($shapes, $GLOBALS["index"], 3));
            if ($STransgressivePast)$TransgressivePast = implode("|", GetArray($shapes, $GLOBALS["index"], 3));
            if ($SAuxiliary)        $Auxiliary         = implode("|", GetArray($shapes, $GLOBALS["index"], 6));

            $category=$parts[2];

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
                "SELECT $langId, '$label', '$base', '$category', 
                    '$Infinitive', 
                    '$Continous', 
                    '$Future', 
                    '$Imperative', 
                    '$PastActive', 
                    '$PastPassive', 
                    '$TransgressiveCont',
                    '$TransgressivePast',
                    '$Auxiliary';";

            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }

        // Verb
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
            //    itemsVerbs.Add(ItemVerb.Load(line));
        }

        // Adverb
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-") break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from=$parts[0];

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
            echo "adverbFromId: $adverbFromId<br>";

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
                    die();
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
            $tos = loadListTranslatingToData($parts, 1);

            // Prepare the SQL statement with placeholders for multiple rows
            $sql = "INSERT INTO adverbs_to (shape, relation, cite, comment, tags) VALUES ";
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
                $sql .= implode(", ", $values).";";

                // Execute the single SQL statement
                if ($conn->query($sql) === TRUE) {
                    // ok
                } else {
                    sqlError($sql, $conn);
                }
            }
        }

        // Preposition
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from=$parts[0];
            $falls=$parts[1];

            // insert from cs
            $sql="INSERT INTO preposition_cs (shape, falls) ".
                  "SELECT '$from', '$falls' ".
                  "WHERE NOT EXISTS (SELECT 1 FROM preposition_cs WHERE shape = '$from');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }
        }

        // Conjunction
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|',$line);
            $from=$parts[0];

            // insert from cs
            $sql="INSERT INTO conjunction_cs (shape) ".
                  "SELECT ('$from') ".
                  "WHERE NOT EXISTS (SELECT 1 FROM conjunction_cs WHERE shape = '$from');";
            if ($conn->query($sql) === TRUE) {
                //ok
            }else{
                sqlError($sql,$conn);
            }

            // TODO: insert to, relationship
        }

        // Particle
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|', $line);
            $from  = $parts[0];

            // insert from cs
            $sql="INSERT INTO particle_cs (shape) ".
                  "SELECT ('$from') ".
                  "WHERE NOT EXISTS (SELECT 1 FROM particle_cs WHERE shape = '$from');";
            if ($conn->query($sql) === TRUE) {
                //ok
            } else {
                sqlError($sql,$conn);
            }
        }

        // Interjection
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;

            $parts = explode('|', $line);
            $from  = $parts[0];

            // insert from cs
            $sql="INSERT INTO interjection_cs (shape) ".
                  "SELECT ('$from') ".
                  "WHERE NOT EXISTS (SELECT 1 FROM interjection_cs WHERE shape = '$from');";
            if ($conn->query($sql) === TRUE) {
                //ok
            } else {
                sqlError($sql,$conn);
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

function throwError($string) :void{
    if (!isset($_SESSION["error"])) $_SESSION["error"]="";
    $_SESSION["error"].=$string;
}
function throwInfo($string):void {
    if (!isset($_SESSION["error"])) $_SESSION["error"]="";
    $_SESSION["info"].="<p>".$string."<p>";
}

function sqlError($sql, $conn) :void{
    $maxLen=50;
    $len=strlen($sql);
    if ($len>$maxLen)$sql=substr($sql,0,$maxLen)."...";

    if (!isset($_SESSION["error"])) $_SESSION["error"]="";
    $_SESSION["error"].="<p class='error'>Problém s SQL: ".$sql."; ".$conn->error."</p>";
}

function loadListTranslatingToData($rawData, $start) : array{
    $list=[];
         
    $len=count($rawData);
    for ($i=$start; $i<$len ; $i+=3) {
        if ($i<$len-1) $list[]=["Text"=>$rawData[$i], "Comment"=>$rawData[$i+1], "Source"=>$rawData[$i+2]];
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
function GetArray($source, $pos, $len) : array {
    $arr = [];
    for ($i=0; $i<$len; $i++) $arr[$i]=$source[$pos+$i];
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
    $sources=explode("|", $string); // $sources=["nbdp", "sncj", ...]
    $citeIds=[]; // $citeIds=[0,3,7, ...]
    foreach ($sources as $source) {// "nbdp", "sncj", ...
        if (isset($listCites[$source])) $citeIds[]=$listCites[$source];
    }

    return join("|", $citeIds); //"0,3,7,..."
}
