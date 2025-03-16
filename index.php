<?php
session_start();
include "./data/config.php";

// Check if database exists
$conn_check = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"]);
$result = $conn_check->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$GLOBALS["databaseName"]."'");
if ($result->num_rows == 0) {
    header("Location: ./actions/inicialize_new_database.php");
    exit();
}
// Check if login
if (!isset($_SESSION['username'])) {
    header("Location: ./actions/login.php");
    exit();
}

$conn = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"], $GLOBALS["databaseName"]);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// rest api
include "./rest/handler.php";
if (isset($_POST["action"])) {
    rest();
    exit;
}

// >> Load page
$GLOBALS["onload"]="";
$GLOBALS["script"]="";

// set select page
$selectPage="0";
if (isset($_GET["page"])) $selectPage=$_GET["page"];

$selectEditor="0";
if (isset($_GET["editor"])) $selectEditor=$_GET["editor"];

include "./components/filter_list.php";

// cotent
$content="";
$currenteditor=getcwd()."/editor/".$selectPage."/".$selectEditor.".php";
if (file_exists($currenteditor)) {
    ob_start();
    include($currenteditor);
    $content=ob_get_contents();
    ob_get_clean();
} else $_SESSION["error"].="ERROR: Editor not found! '".$currenteditor.'"';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database mp editor</title>
    <link rel="stylesheet" href="./data/style.css">
    <script src="./components/editor.js"></script>
    <script>
        <?php echo $GLOBALS["script"]; ?>
        function onload() {
            <?php echo $GLOBALS["onload"]; ?>
        }
    </script>
</head>
<body onload="onload()">
<div id="popups" style="position: fixed;">
    <div id="popup_databaseUpload" class="popupBackground" style="display:none">
        <div class="popup">
            <div class="popupHeader"><span onclick="popupClose('databaseUpload')" class="popupClose">×</span></div>
            <div class="popupBody">
                <h1>Načíst databázi?</h1>
                <p>Smaže se aktuální databáze!</p>
                <form method="POST" style="display: flex; flex-direction: column;">
                    <input name="input_database" type="file" accept="*">
                    <div style="margin-top: 10px;">
                        <button type="submit" style="float: right;">Načíst</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="popup_databaseImportOld" class="popupBackground" style="display:none">
        <div class="popup">
            <div class="popupHeader"><span onclick="popupClose('databaseImportOld')" class="popupClose">×</span></div>
            <div class="popupBody">
                <h1>Načíst starou databázi *.trw?</h1>
                <p>Přidá se do aktuální databáze!</p>
                <form method="POST" style="display: flex; flex-direction: column;" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="database_importold">
                    <input type="file" name="database_files[]" accept="*.trw" multiple>
                    <div style="margin-top: 10px;">
                        <button type="submit" style="float: right;">Načíst</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="popup_selectLang" class="popupBackground" style="display:none">
        <div class="popup">
            <div class="popupHeader"><span onclick="popupClose('selectLang')" class="popupClose">×</span></div>
            <div class="popupBody">
                <h1>Vybrat překlad</h1>
                <?php
                $sql ="SELECT translateName, administrativeTown FROM translate";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $listTranslated=[];
                    while ($row = $result->fetch_assoc()) {
                        $listTranslated[]= $row["translateName"]." (".$row["administrativeTown"].")";
                    }
                    echo FilteredList($listTranslated, "lang", "");
                } else {
                    echo "<p>Prázné! Vytvořte nebo importujte překlady</p>";
                }
                ?>
            </div>
        </div>
    </div>
    <div id="popup_newLang" class="popupBackground" style="display:none">
        <div class="popup">
            <div class="popupHeader"><span onclick="popupClose('newLang')" class="popupClose">×</span></div>
            <div class="popupBody">
                <h1>Vybrat překlad</h1>
                <form>
                    <input type="text" name="new-translate">
                    <a class="button">Vytvořit</a>
                </form>
            </div>
        </div>
    </div>
    <div id="popup_userSettings" class="popupBackground" style="display:none">
        <div class="popup">
            <div class="popupHeader"><span onclick="popupClose('userSettings')" class="popupClose">×</span></div>
            <div class="popupBody">
                <h1>nastavení</h1>
                <form>
                    <label>Motiv</label>
                    <select>
                        <option>Výchozí</option>
                        <option>Světlý</option>
                        <option>Tmavý</option>
                    </select>
                    <br>
                    <label>Nové heslo</label>
                    <a class="button">Změnit</a>
                    <br>

                    <label>Nový email</label>
                    <a class="button">Změnit</a>
                    <br>

                    <a class="button">Uložit</a>
                </form>
            </div>
        </div>
    </div>
    <div id="popup_log" class="popupBackground" style="display:none">
        <div class="popup">
            <div class="popupHeader"><span onclick="popupClose('log')" class="popupClose">×</span></div>
            <div class="popupBody">
                <h1>Výpis z logu</h1>
                <div>
                </div>
                <a class="button">Zavřít</a>
            </div>
        </div>
    </div>
    <div id="popup_userPending" class="popupBackground" style="display:none">
        <div class="popup">
            <div class="popupHeader"><span onclick="popupClose('userPending')" class="popupClose">×</span></div>
            <div class="popupBody">
                <h1>Schválit uživatele</h1>
                <div>
                    <span>user</span> <switch>Povolit</switch><a class="button">Smazat X</a>
                </div>
                <a class="button">Uložit</a>
            </div>
        </div>
    </div>
    <!--<div id="popup_info" class="popupBackground" style="display:none">
            <div class="popup">
                <div class="popupHeader"><span onclick="popupClose('info')" class="popupClose">×</span></div>
                <div class="popupBody">
                    <h1>Schválit uživatele</h1>
                    <div style="overflow: scroll;    max-height: 50vh;">
                        <?php // echo "Upload size:".ini_get("post_max_size") . " / " . ini_get("upload_max_filesize");?>
                        <?php
    //   ob_start();
    //  phpinfo();

    // remove css
    // $php=ob_get_clean();
    // $phpE=substr($php, strpos($php, "<div"));
    // echo $phpE;
    ?>
                    </div>
                    <a class="button">Uložit</a>
                </div>
            </div>
        </div>-->
</div>
<div id="content">
    <!-- Navbar -->
    <div id="navbar">
        <div class="dropdown">
            <span class="dropdownTitle">Databáze</span>
            <div class="dropdown-content">
                <span class="dropDownListItem" >Zabalíčkovat</span>
                <span class="dropDownListItem" onclick="popupShow('databaseImportOld')">Importovat *.trw</span>
                <hr>
                <span class="dropDownListItem" onclick="popupShow('databaseUpload')">Načíst databázi</span>
                <span class="dropDownListItem" onclick="popupShow('databaseExport')">Exportovat databázi</span>
            </div>
        </div>
        <div class="dropdown">
                <span class="dropdownTitle">
                    <span>Překlad</span>
                    <!--  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: text-bottom;">
                          <path d="M5 7L10 12L15 7" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>-->
                </span>
            <div class="dropdown-content">
                <span class="dropDownListItem" onclick="popupShow('selectLang')">Vybrat</span>
                <span class="dropDownListItem" onclick="popupShow('newLang')">Nový</span>
                <span class="dropDownListItem" onclick="popupShow('removeLang')">Smazat</span>
            </div>
        </div>
        <div class="dropdown">
                <span class="dropdownTitle">
                    Odkazy
                </span>
            <div class="dropdown-content">
                <a class="dropDownListItem" target="_blank" href="https://github.com/GeftGames/moravskyprekladac-database">Github</a>
                <a class="dropDownListItem" target="_blank" href="phpMyAdmin/">phpMyAdmin</a>
            </div>
        </div>


        <a class="navbarBtn" onclick="popupShow('info')">Info</a>

        <div class="dropdown">
            <span class="dropdownTitle">[<?php echo $_SESSION['username']; ?>]</span>
            <div class="dropdown-content">
                <?php if ($_SESSION["usertype"]==1):?><span class="dropDownListItem captionlink" onclick="popupShow('userPending')" >Schálit uživatele</span><?php endif; ?>
                <?php if ($_SESSION["usertype"]==1):?><span class="dropDownListItem captionlink" onclick="popupShow('log')" >Výpis změn</span><?php endif; ?>
                <?php if ($_SESSION["usertype"]==1):?><span class="dropDownListItem captionlink" onclick="popupShow('userSettings')" >Nastavení</span><?php endif; ?>
                <a class="dropDownListItem" href="./actions/logout.php">Odhlásit</a>
            </div>
        </div>
    </div>

    <div style="max-width: 5cm">
        <?php if (isset($_SESSION["error"])) echo $_SESSION["error"]; ?>
    </div>

    <?php
    $tabs=[
        [
            ["source", "Podklad CS"],
            [["noun", "Podstatná jména"], ["adjective", "Přídavná jména"],  ["pronoun", "Zájmena"],  ["number", "Číslovky"],  ["verb", "Slovesa"], ["adverb", "Příslovce"], ["preposition", "Předložky"], ["conjunction", "Spojky"], ["particle", "Částice"], ["interjection", "Citoslovce"]]
        ],
        [
            ["global", "Globální"],
            [["cites", "Citace"], ["regions", "Regiony"], ["nations", "Národnosti"]]
        ],
        "|",
        [
            ["tools", "nástroje"],
            [["analyzesentences", "Analýza textu"], ["analyzeSentance", "Analýza vět"], ["searchdup", "Hledat duplikáty"]]
        ],
        [
            ["attributes", "Atributy"],
            [["attributes", "Atributy"], ["piecesofcite", "Citace"]]
        ],
        "|",
        [
            ["simple", "Základní"],
            [["sentence", "Věty"], ["sentencepart", "Část věty"], ["phrase", "Fráze"], ["simplewords", "Slova"]]
        ],
        [
            ["patterns", "Vzorce"],
            [["sentencepattern", "Větní"], ["phrasepattern", "Fráze"]]
        ],
        [
            ["replaces", "Náhrady"],
            [["replace_general_start", "Obecně na začátku"], ["replace_general_inside", "Obecně uprostřed"], ["replace_general_end", "Obecně nakonci"],"|",
                ["replace_defined", "Definované"], ["replace_noun_defined", "Zakončení pods."], ["replace_adjective_defined", "Zakončení příd."]]
        ],
        [
            ["translate", "Překlad"],
            [["noun", "Podstatná jména"], ["adjective", "Přídavná jména"], ["pronoun", "Zájmena"], ["number", "Číslovky"], ["verb", "Slovesa"], ["adverb", "Příslovce"], ["preposition", "Předložky"], ["conjunction", "Spojky"], ["particle", "Částice"], ["", "Citoslovce"]]
        ]
    ];

    // createbuttons
    $tabsHTML="<div>";
    $headerHTML='<div id="header">';
    $headerEditor="";
    foreach ($tabs as $item) {
        if (!is_array($item)){
            $headerHTML.=" | ";
            continue;
        }
        $header=$item[0];
        $headerCode=$header[0];
        $headerName=$header[1];

        $tabsBtns=$item[1];
        $style="";
        $currentTab=$headerCode==$selectPage;
        if (!$currentTab) $style=" style='display: none'";
        else $headerEditor="<span>".$headerName."<span>";
        $tabsHTML.="<div id='tabsOption_$headerCode'$style>";

        $headerHTML.='<span id="mainOption_'.$headerCode.'" class="choice '.($currentTab ? "selected" :"").'" onclick="selectMainOption('."'$headerCode'".')">'.$headerName.'</span>';

        foreach ($tabsBtns as $tabsBtn) {
            if ($tabsBtn=="|") $tabsHTML.="|";
            else {
                $btnText=$tabsBtn[1];
                $btnName=$tabsBtn[0];
                $selectedSubTab=$btnName==$selectEditor;
                $currentPage=$selectedSubTab ? " selected" : "";

                if ($btnName==$selectEditor && $headerCode==$selectPage) $headerEditor.="&gt;<span>".$btnText."</span>";
                $tabsHTML.="<a class='choice$currentPage' href='./?page=$headerCode&editor=$btnName'>$btnText</a>";
            }
        }
        $tabsHTML.="</div>";
    }
    $headerHTML.="</div>";
    $tabsHTML.="</div>";

    // tabs
    echo $headerHTML;
    echo $tabsHTML;

    // header
    echo "<h1>".$headerEditor."</h1>";

    // content
    echo "<div>".$content."</div>";
    ?>
</div>
</body>
</html>