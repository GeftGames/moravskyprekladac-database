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
            label VARCHAR(255) DEFAULT $namedef,
            base VARCHAR(255),
            shapes TEXT,
            gender TINYINT DEFAULT 0,
            tags VARCHAR(255)
        );",
        "CREATE TABLE adjective_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT $namedef,
            base VARCHAR(255),
            shapes TEXT,
            category TINYINT DEFAULT 0,
            tags VARCHAR(255)
        );",
        "CREATE TABLE pronoun_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT $namedef,
            base VARCHAR(255),
            shapes TEXT,
            category TINYINT DEFAULT 0,
            tags VARCHAR(255)
        );",
        "CREATE TABLE number_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT $namedef,
            base VARCHAR(255),
            shapes TEXT,
            pattern_type TINYINT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE verb_patterns_cs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label VARCHAR(255) DEFAULT $namedef,
            base VARCHAR(255),
            shapetype INT,
            shapes TEXT,
            category TINYINT,
            tags VARCHAR(255)
        );",
        "CREATE TABLE adverb_cs (
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
        "CREATE TABLE noun_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255) DEFAULT $namedef,
            gender TINYINT,
            shapes JSON
        );",
        "CREATE TABLE adjective_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255) DEFAULT $namedef,
            pattern_type TINYINT,
            shapes JSON
        );",
        "CREATE TABLE pronoun_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255) DEFAULT $namedef,
            pattern_type TINYINT,
            shapes JSON
        );",
        "CREATE TABLE number_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            label varchar(255) DEFAULT $namedef,
            pattern_type TINYINT,
            shapes JSON
        );",
        "CREATE TABLE verb_pattern_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            pattern_type_show TINYINT,
            reversible TINYINT,
            label varchar(255) DEFAULT $namedef,
            shapes JSON
        );",
        "CREATE TABLE preposition_to (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shapes varchar(255),
            falls VARCHAR(255)
        );",

        // relations
        "CREATE TABLE noun_relations (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `from` INT,
            `tmp_imp_from_pattern` VARCHAR(255),
            `pattern_from_body` VARCHAR(255)
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
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            translate INT,
            shape_from VARCHAR(255)
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

        // kniha
        "CREATE TABLE cites (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,            
            label VARCHAR(255) DEFAULT $namedef,
            data JSON
        );",
        // Ukázky
        "CREATE TABLE piecesofcite (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,  
            label VARCHAR(255) DEFAULT $namedef,
            `parent` INT,
            translate INT,
            people JSON,
            cite JSON,
            `text` TEXT
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
            label varchar(255) NOT NULL DEFAULT $namedef,
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
            `label` VARCHAR(255) DEFAULT $namedef,
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` INT
        );",
        "CREATE TABLE replaces_inside (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT $namedef,
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` INT
        );",
        "CREATE TABLE replaces_end (
            `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `translate` INT,
            `label` VARCHAR(255) DEFAULT $namedef,
            `source` VARCHAR(255),
            `to` VARCHAR(255),
            `cite` INT
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
            sqlError($sql, $conn);
        }
        $langId=$conn->insert_id;

        $citeList=[];
        $citesRawArray=explode("\n", $cites);
        foreach ($citesRawArray as $rawCite) {
            if ($rawCite!="") {
                $cite=[];
                $vars=explode("\n", $rawCite);
                
                foreach ($vars as $varr) {
                    $var=explode("\n", $varr);
                    $varCode=$var[0];
                    $varValue=$var[1];
                    $cite[$varCode]=$varValue;
                }
                $citeList[]=$cite;

                 if ($conn->query($sql) === TRUE) {            
                    //ok
                    $sql="INSERT INTO cites (label, data) SELECT 
                    '$name', '$administrativeTown', $gpsX, $gpsY, $region, $country, $langtype, $quality, $dialect, '".$editors."', '".$devinfo."', '".$options."'".
                    "WHERE NOT EXISTS (SELECT 1 FROM translate WHERE translateName = '".$name."')";

                  
                }else{
                    sqlError($sql, $conn);
                }
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
          
            $sql="INSERT INTO noun_patterns_cs (label, base, gender, shapes) ".
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
            if ($line == "-")  break;
            if ($line == "")  continue;
              //  itemsPatternNounTo.Add(ItemPatternNoun.Load(line));
        }

            // Noun
       for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
            //    itemsNouns.Add(ItemNoun.Load(line));
        }

        // PatternAdjectives
        for ($i++; $i<$linesLen; $i++) {
            $line = $lines[$i];
            if ($line == "-")  break;
            if ($line == "")  continue;
                            
            $parts = explode('|',$line);            
            $label=$parts[0];
            $base=extractBase($label);
            $category=$parts[1];
            $shapesN=array_slice($parts, 2, 14);
            $shapesF=array_slice($parts, 2+14,14);
            $shapesA=array_slice($parts, 2+14*2,14);
            $shapesI=array_slice($parts, 2+14*3,14);
          
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
            //itemsPatternAdjectiveTo.Add(ItemPatternAdjective.Load(line));
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
            if ($line == "-")  break;
            if ($line == "")  continue;
            //    itemsPatternPronounTo.Add(ItemPatternPronoun.Load(line));
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
            //    var item=ItemPatternNumber.Load(line);
              //  if (item!=null) itemsPatternNumberTo.Add(item);
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
            $category=$parts[2];
            $shapes=array_slice($parts, 3);   
                        
            $sql="INSERT INTO verb_patterns_cs (label, base, shapetype, category, shapes) ".
                  "SELECT '$label', '$base', '$shapetype', '$category', '".implode("|", $shapes)."' ".
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
            if ($line == "-")  break;
            if ($line == "")  continue;
            //    itemsPatternVerbTo.Add(ItemPatternVerb.Load(line));
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
            if ($line == "-")  break;
            if ($line == "")  continue;
            //    itemsAdverbs.Add(ItemAdverb.Load(line));

            $parts = explode('|',$line);            
            $from=$parts[0];

            // insert from cs
            $sql="INSERT INTO adverb_cs (shape) ".
                  "SELECT ('$from') ".
                  "WHERE NOT EXISTS (SELECT 1 FROM adverb_cs WHERE shape = '$from');";
            if ($conn->query($sql) === TRUE) {            
                //ok
            }else{
                sqlError($sql,$conn);
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
    if (!isset($_SESSION["error"])) $_SESSION["error"]="";
    $_SESSION["error"].=$string;
}

function sqlError($sql, $conn) {
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

function tryToGetTags($comment) : array{
    $list=[];
         
    if (str_contains($comment, "expr.")) $list[]="expr.";
    if (str_contains($comment, "val.")) $list[]="val.";
    if (str_contains($comment, "han.")) $list[]="han.";
    if (str_contains($comment, "staří")) $list[]="staří";
    if (str_contains($comment, "mladí")) $list[]="mladí";
          
    return $list;
}
