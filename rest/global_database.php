<?php 
// Rest api - database global tools 
namespace REST;

function database_init() {
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

        // From cs tags=[nonstandart, expr. , mor., val., ...]
        "CREATE TABLE noun_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255),
            gender TINYINT,
            base VARCHAR(255),
            shapes TEXT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE adjective_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255),
            base varchar(255),
            category TINYINT,
            shapes JSON
        );",
        "CREATE TABLE pronoun_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255),
            base VARCHAR(255),
            category TINYINT,
            shapes JSON
        );",
        "CREATE TABLE number_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            base varchar(255),
            pattern_type TINYINT,
            shapes TEXT
        );",
        "CREATE TABLE verb_pattern_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255),
            verbtype TINYINT,
            base varchar(255),
            shapes JSON
        );",
        "CREATE TABLE preposition_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            fall TINYINT,
            shape varchar(255)
        );",
        "CREATE TABLE conjunction_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            type TINYINT,
            shape varchar(255)
        );",//type=přací, ano/ne, ...
        "CREATE TABLE particle_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            type TINYINT,
            shape varchar(255)
        );",

        // Translate to
        "CREATE TABLE noun_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255),
            gender TINYINT,
            shapes JSON
        );",
        "CREATE TABLE adjective_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255),
            pattern_type TINYINT,
            shapes JSON
        );",
        "CREATE TABLE pronoun_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255),
            pattern_type TINYINT,
            shapes JSON
        );",
        "CREATE TABLE number_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255),
            pattern_type TINYINT,
            shapes JSON
        );",
        "CREATE TABLE verb_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            pattern_type_show TINYINT,
            reversible TINYINT,
            label varchar(255),
            shapes JSON
        );",
        "CREATE TABLE preposition_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shapes varchar(255),
            fall TINYINT
        );",

        // relations
        "CREATE TABLE noun_relation (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            uppercase TINYINT,
            pattern_from INT,
            tmp_imp_from_pattern VARCHAR(255),
            pattern_from_body VARCHAR(255)
        );",
        "CREATE TABLE adjective_relation (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            pattern_from INT
        );",
        "CREATE TABLE pronoun_relation (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            pattern_from INT
        );",
        "CREATE TABLE number_relation (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            pattern_from INT
        );",
        "CREATE TABLE verb_relation (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            pattern_from INT
        );",
        "CREATE TABLE adverb_relation (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shape_from VARCHAR(255)
        );",
        "CREATE TABLE preposition_relation (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shape_from INT
        );",
        "CREATE TABLE conjuction_relation (
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
            translateName varchar(255),
            administrativeTown varchar(255),
            gpsX float,
            gpsY float,
            region INT,
            subregion INT,
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
            data JSON
        );",
        // Ukázky
        "CREATE TABLE pieceofsource (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,  
            belongs INT,
            translate INT,
            datatext TEXT,
            info JSON
        );",

        // log
        "CREATE TABLE logs (
            created DATE,  
            user INT,
            logtext TEXT
        );",
        
        // edits
        "CREATE TABLE translate_edits (
            id NOT NULL AUTO_INCREMENT PRIMARY KEY,  
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
            tags varchar(255)
        );",

        // phrase_to
        "CREATE TABLE phrase_to (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            parent int,
            shapes varchar(255),
            tags varchar(255),
            comment varchar(255),
            source int
        );",

        // phrase_to
        "CREATE TABLE phrase_to (
            id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            parent int,
            shapes varchar(255),
            tags varchar(255),
            comment varchar(255),
            source int
        );",

        // regions
        "CREATE TABLE regions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255) NOT NULL,
            type TINYINT,
            parent INT,
            translates JSON,
        );",

        // regions ploace
        "CREATE TABLE place_regions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            region_id INT,
            zone_type TINYINT,
            confinence TINYINT,
            comment VARCHAR(255),
        );",
    ];

    $conn = new \mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
    foreach ($sqls as $sql) {
        if ($conn->query($sql) === TRUE) {
            // Table created successfully
         //   if ($dev) echo "<p>".$sql."</p>";
        } else {
            sqlError($sql,$conn);
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

        // insert translate
        $sql="INSERT INTO translate (translateName, administrativeTown, gpsX, gpsY, region, country, langtype, quality, dialect, editors, devinfo, options) SELECT ".
        "'".$name."', '".$administrativeTown."', ".$gpsX.", ".$gpsY.", ".$region.", ".$country.", ".$langtype.", ".$quality.", ".$dialect.", '".$editors."', '".$devinfo."', '".$options."'".
        "WHERE NOT EXISTS (SELECT 1 FROM translate WHERE translateName = '".$name."')";

        if ($conn->query($sql) === TRUE) {            
            //ok
            if (mysqli_affected_rows($conn) > 0) {
            } else {
                throwError("Překlad s tímto názvem už v databázi existuje!");
            }
        }else{
            sqlError($sql,$conn);
        }
        $langId=$conn->insert_id;

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
            if ($line == "-")  break;
            if ($line == "")  continue;
         //   itemsSentenceParts.Add(ItemSentencePart.Load(line));
        }

            // Phrase
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
          //  itemsPhrases.Add(ItemPhrase.Load(line));
        }

        // SimpleWords
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
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
/*
            // ReplaceS
          for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsReplaceS.Add(ItemReplaceS.Load(line));
            }

            // ReplaceG
           for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsReplaceG.Add(ItemReplaceG.Load(line));
            }

            // ReplaceE
          for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsReplaceE.Add(ItemReplaceE.Load(line));
            }

            // PatternNounFrom
           for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPatternNounFrom.Add(ItemPatternNoun.Load(line));
            }

            // PatternNounTo
         for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPatternNounTo.Add(ItemPatternNoun.Load(line));
            }

            // Noun
       for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsNouns.Add(ItemNoun.Load(line));
            }

            // PatternAdjectives
           for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPatternAdjectiveFrom.Add(ItemPatternAdjective.Load(line));
            }

            // PatternAdjectivesTo
           for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPatternAdjectiveTo.Add(ItemPatternAdjective.Load(line));
            }

            // Adjectives
           for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsAdjectives.Add(ItemAdjective.Load(line));
            }

            // PatternPronounsFrom
          for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPatternPronounFrom.Add(ItemPatternPronoun.Load(line));
            }

            // PatternPronounsTo
          for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPatternPronounTo.Add(ItemPatternPronoun.Load(line));
            }

            // Pronouns
         for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPronouns.Add(ItemPronoun.Load(line));
            }

            // PatternNumbersFrom
          for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                var item=ItemPatternNumber.Load(line);
                if (item!=null) itemsPatternNumberFrom.Add(item);
            }

            // PatternNumbersTo
          for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                var item=ItemPatternNumber.Load(line);
                if (item!=null) itemsPatternNumberTo.Add(item);
            }

            // Numbers
           for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsNumbers.Add(ItemNumber.Load(line));
            }

            // PatternVerbsFrom
           for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPatternVerbFrom.Add(ItemPatternVerb.Load(line));
            }

            // PatternVerbsTo
         for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPatternVerbTo.Add(ItemPatternVerb.Load(line));
            }

            // Verb
           for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsVerbs.Add(ItemVerb.Load(line));
            }

            // Adverb
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsAdverbs.Add(ItemAdverb.Load(line));
            }

            // Preposition
         for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPrepositions.Add(ItemPreposition.Load(line));
            }

            // Conjunction
          for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsConjunctions.Add(ItemConjunction.Load(line));
            }

            // Particle
           for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsParticles.Add(ItemParticle.Load(line));
            }

            // Interjection
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsInterjections.Add(ItemInterjection.Load(line));
            }

            // PhrasePattern
         for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                itemsPhrasePattern.Add(ItemPhrasePattern.Load(line));
            }*/
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

function throwError($string) {
    $GLOBALS["error"]=$string;
}
function sqlError($sql, $conn) {
    $maxLen=50;
    $len=strlen($sql);
    if ($len>$maxLen)$sql=substr($sql,0,$maxLen)."...";
    $GLOBALS["error"].="Problém s SQL: ".$sql."; ".$conn->error."<br>";
}

function loadListTranslatingToData($rawData, $start) : array{
    $list=[];
         
    $len=count($rawData);
    for ($i=$start; $i<$len ; $i+=3) {
        if ($i<$len-1) $list[]=["Text"=>$rawData[$i], "Comment"=>$rawData[$i+1], "Source"=>$rawData[$i+2]];
        else if (($i-$start)%2==0 && i==$len-1) 
            $list[]=["Text"=>$rawData[$i], "Comment"=>$rawData[$i+1], "Source"=>$rawData[$i+2]];
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
