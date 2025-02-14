<?php
session_start();
include "./data/config.php";

// Check if database exists
$conn_check = new mysqli($GLOBALS["serverNameDB"], $GLOBALS["usernameDB"], $GLOBALS["passwordDB"]);
$result = $conn_check->query("SELECT SCHEMA_NAME  FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$GLOBALS["databaseName"]."'");
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
if (isset($_POST["action"])) rest();

// Do dashboard stuff
include "editor/components.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database mp editor</title>
    <link rel="stylesheet" href="./data/style.css">
    <script src="./editor/editor.js"></script>
</head>
<body>
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
                        echo FilteredList($listTranslated, "lang"); 
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
                        <span>user</span> <switch>Povolit</switch><btn>Smazat X</btn>
                    </div>
                    <a class="button">Uložit</a>
                </div>
            </div>
        </div>
        <div id="popup_info" class="popupBackground" style="display:none">
            <div class="popup">
                <div class="popupHeader"><span onclick="popupClose('info')" class="popupClose">×</span></div>
                <div class="popupBody">
                    <h1>Schválit uživatele</h1>
                    <div style="overflow: scroll;    max-height: 50vh;">
                        <?php echo "Upload size:".ini_get("post_max_size") . " / " . ini_get("upload_max_filesize");?>
                        <?php 
                            ob_start();
                            phpinfo(); 
                            
                            // remove css
                            $php=ob_get_clean();
                            $phpE=substr($php, strpos($php, "<div"));
                            echo $phpE;  
                        ?>
                    </div>
                    <a class="button">Uložit</a>
                </div>
            </div>
        </div>
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
                    <span onclick="selectMainOption('translate')">Překlad</span>  
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
            <a class="navbarBtn" href="https://github.com/GeftGames/moravskyprekladac-database">Github</a>
            <a class="navbarBtn" onclick="popupShow('info')">Info</a>

            <div class="dropdown">
                <span class="dropdownTitle">[<?php echo $_SESSION['username']; ?>]</span>
                <div class="dropdown-content">
                    <?php if ($_SESSION["usertype"]==1):?><span class="dropDownListItem" onclick="popupShow('userPending')" class="captionlink">Schálit uživatele</span><?php endif; ?>
                    <?php if ($_SESSION["usertype"]==1):?><span class="dropDownListItem" onclick="popupShow('log')" class="captionlink">Výpis změn</span><?php endif; ?>
                    <?php if ($_SESSION["usertype"]==1):?><span class="dropDownListItem" onclick="popupShow('userSettings')" class="captionlink">Nastavení</span><?php endif; ?>
                    <a class="dropDownListItem" href="./actions/logout.php">Odhlásit</a>
                </div>
            </div>
        </div>

        <?php if (isset($GLOBALS["error"])) echo "<p class='error'>".$GLOBALS["error"]."</p>"; ?>                

        <!-- Header -->
        <div id="header">           
            <span id="mainOption_translate" class="choice selected" onclick="selectMainOption('translate')">Překlad</span> 
            <a class="choice" id="mainOption_source" onclick="selectMainOption('source')">Podklad CS</a>
        </div>

        <?php 
            $tabsCS=["Podstatná jména", "Přídavná jména", "Zájmena", "Číslovky", "Slovesa", "Předložky"];
            $tabs=[
                "Překlad", "Citace", "|", "Věty", "Část věty", "Fráze", "Slova", "|", "Vět. vzorec", "Fráz. vzorec", "|",
                "Podstatná jména", "Přídavná jména", "Zájmena", "Číslovky", "Slovesa", "Příslovce", "Předložky", "Spojky", "Částice", "Citoslovce",
            ];
            $html="<div id='tabsOption_translate'>";
            foreach ($tabs as $item) {
                if ($item!="|") $html.="<a class='tab'>$item</a>";  
                else $html.="|";  
            }
            $html.="</div>";
            $html.="<div id='tabsOption_source'>";
            foreach ($tabsCS as $item) {
                if ($item!="|") $html.="<a class='tab'>$item</a>";  
                else $html.="|";  
            }
            $html.="</div>";
            echo $html;
        ?>
        <div class="splitView">
            <div>
                <?php echo FilteredList(["dEN", "zIMA", "pES"], "noun"); ?>
                Smazat
                Přidat
                Přidat z internetu
                Duplikovat
                Setřídit ABC
            </div>
            <div class="editorView">
                <div id="noun" style="display:none">
                    <div class="row">
                        <label id="name">Název</label>
                        <input type="text" for="name">
                    </div>

                    <div>
                        <label id="name">Pád</label>
                        <table>
                            <tr>
                                <td class="tableHeader">Pád</td>
                                <td class="tableHeader">Jednotné</td>
                                <td class="tableHeader">Množné</td>
                            </tr>
                        <?php 
                        $html="";
                        for ($i=0; $i<7; $i++) {
                            $html.="<tr><td>".($i+1).".</td>";
                            for ($j=0; $j<2; $j++) $html.="<td><input type='text'></td>";
                            $html.="</tr>";
                        }
                        echo $html;
                        ?> 
                        </table>
                    </div>

                    <div>
                        <label id="name">Info</label>
                        <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                        <p>"?" Neznámý tvar</p>
                        <p>"-" Neexistuje tvar</p>
                    </div>
                </div>
                
                <div id="adjective" style="display:none">
                    <div class="row">
                        <label id="name">Název</label>
                        <input type="text" for="name">
                    </div>

                    <label id="shapesAdj">Skloňování</label>                  
                    <div style="flex-wrap: wrap; display: flex;">
                        <?php 
                        $html="";
                        for ($r=0; $r<4; $r++) {  
                            $tableName="";
                            if ($r==0) $tableName="Mužský životný";
                            else if ($r==1) $tableName="Mužský neživotný";
                            else if ($r==2) $tableName="Ženský";
                            else if ($r==3) $tableName="Střední";
                            $html.='<table class="tableShapesWrap">
                                <caption>'.$tableName.'</caption>
                                <tr>
                                    <td class="tableHeader">Pád</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                            </tr>';
                            for ($i=0; $i<7; $i++) {
                                $html.="<tr><td>".($i+1).".</td>";
                                for ($j=0; $j<2; $j++) $html.="<td><input type='text'></td>";
                                $html.="</tr>";
                            }
                            $html.='</table>';
                        }
                        echo $html;
                        ?> 
                    
                    </div>

                    <div>
                        <label id="name">Info</label>
                        <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                        <p>"?" Neznámý tvar</p>
                        <p>"-" Neexistuje tvar</p>
                    </div>
                </div>

                <div id="pronoun" style="display:none">
                    <div class="row">
                        <label id="name">Název</label>
                        <input type="text" for="name">
                    </div>

                    <label id="shapesAdj">Skloňování</label>                  
                    <div style="flex-wrap: wrap; display: flex;">
                        <?php 
                        $html="";
                        for ($r=0; $r<4; $r++) {  
                            $tableName="";
                            if ($r==0) $tableName="Mužský životný";
                            else if ($r==1) $tableName="Mužský neživotný";
                            else if ($r==2) $tableName="Ženský";
                            else if ($r==3) $tableName="Střední";
                            $html.='<table class="tableShapesWrap">
                                <caption>'.$tableName.'</caption>
                                <tr>
                                    <td class="tableHeader">Pád</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                            </tr>';
                            for ($i=0; $i<7; $i++) {
                                $html.="<tr><td>".($i+1).".</td>";
                                for ($j=0; $j<2; $j++) $html.="<td><input type='text'></td>";
                                $html.="</tr>";
                            }
                            $html.='</table>';
                        }
                        echo $html;
                        ?> 
                    
                    </div>

                    <div>
                        <label id="name">Info</label>
                        <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                        <p>"?" Neznámý tvar</p>
                        <p>"-" Neexistuje tvar</p>
                    </div>
                </div>

                <div id="number" style="display:none">
                    <div class="row">
                        <label id="name">Název</label>
                        <input type="text" for="name">
                    </div>

                    <label id="shapesAdj">Skloňování</label>                  
                    <div style="flex-wrap: wrap; display: flex;">
                        <?php 
                        $html="";
                        for ($r=0; $r<4; $r++) {  
                            $tableName="";
                            if ($r==0) $tableName="Mužský životný";
                            else if ($r==1) $tableName="Mužský neživotný";
                            else if ($r==2) $tableName="Ženský";
                            else if ($r==3) $tableName="Střední";
                            $html.='<table class="tableShapesWrap">
                                <caption>'.$tableName.'</caption>
                                <tr>
                                    <td class="tableHeader">Pád</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                            </tr>';
                            for ($i=0; $i<7; $i++) {
                                $html.="<tr><td>".($i+1).".</td>";
                                for ($j=0; $j<2; $j++) $html.="<td><input type='text'></td>";
                                $html.="</tr>";
                            }
                            $html.='</table>';
                        }
                        echo $html;
                        ?> 
                    
                    </div>

                    <div>
                        <label id="name">Info</label>
                        <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                        <p>"?" Neznámý tvar</p>
                        <p>"-" Neexistuje tvar</p>
                    </div>
                </div>

                <div id="verb" style="display:none">
                    <div class="row">
                        <label id="name">Název</label>
                        <input type="text" for="name">
                    </div>

                    <label id="shapesAdj">Skloňování</label>                  
                    <div style="flex-wrap: wrap; display: flex;">
                        <?php 
                        $html="";
                        for ($r=0; $r<4; $r++) {  
                            $tableName="";
                            if ($r==0) $tableName="Mužský životný";
                            else if ($r==1) $tableName="Mužský neživotný";
                            else if ($r==2) $tableName="Ženský";
                            else if ($r==3) $tableName="Střední";
                            $html.='<table class="tableShapesWrap">
                                <caption>'.$tableName.'</caption>
                                <tr>
                                    <td class="tableHeader">Pád</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                            </tr>';
                            for ($i=0; $i<7; $i++) {
                                $html.="<tr><td>".($i+1).".</td>";
                                for ($j=0; $j<2; $j++) $html.="<td><input type='text'></td>";
                                $html.="</tr>";
                            }
                            $html.='</table>';
                        }
                        echo $html;
                        ?> 
                    
                    </div>

                    <div>
                        <label id="name">Info</label>
                        <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                        <p>"?" Neznámý tvar</p>
                        <p>"-" Neexistuje tvar</p>
                    </div>
                </div>

                <div id="preposition" style="display:none">
                    <div class="row">
                        <label id="name">Název</label>
                        <input type="text" for="name">
                    </div>

                    <label id="shapesAdj">Skloňování</label>                  
                    <div style="flex-wrap: wrap; display: flex;">
                        <?php 
                        $html="";
                        for ($r=0; $r<4; $r++) {  
                            $tableName="";
                            if ($r==0) $tableName="Mužský životný";
                            else if ($r==1) $tableName="Mužský neživotný";
                            else if ($r==2) $tableName="Ženský";
                            else if ($r==3) $tableName="Střední";
                            $html.='<table class="tableShapesWrap">
                                <caption>'.$tableName.'</caption>
                                <tr>
                                    <td class="tableHeader">Pád</td>
                                    <td class="tableHeader">Jednotné</td>
                                    <td class="tableHeader">Množné</td>
                            </tr>';
                            for ($i=0; $i<7; $i++) {
                                $html.="<tr><td>".($i+1).".</td>";
                                for ($j=0; $j<2; $j++) $html.="<td><input type='text'></td>";
                                $html.="</tr>";
                            }
                            $html.='</table>';
                        }
                        echo $html;
                        ?> 
                    
                    </div>

                    <div>
                        <label id="name">Info</label>
                        <p>"dny,dny" čárkou bez mezery oddělit více možností, primární je první</p>
                        <p>"?" Neznámý tvar</p>
                        <p>"-" Neexistuje tvar</p>
                    </div>
                </div>
            </div>
        </div>
        <!--<span>Základní atributy</span>
        <div>
            Sídlo
            <div class="row">
                <label id="name">Název</label>
                <input type="text" for="name">
            </div>
            <div class="row">
                <label id="name">Spadá pod</label>
                <input type="text" for="name">
            </div>
            <div class="row">
                <label id="namemul">Varianty názvu</label>
                <input type="text" for="namemul">
            </div>
            <div class="row">
                <label for="region">Umístění</label>
                <select if="region">
                    <option>Neznámé</option>
                    <option>Vesnice</option>
                    <option>Část města</option>
                    <option>Město</option>
                    <option>Samota</option>
                    <option>Obecné nářečí</option>
                    <option>Jazyk</option>
                </select>
            </div>   
            Místo
            <div class="row">
                <label id="gps">Pozice</label><br>
                <label id="gpsX">X</label>
                <input type="number" for="gpsX">
                <label id="gpsY">Y</label>
                <input type="number" for="gpsY">
            </div>
            <div class="row">
                <label id="country">Země</label>
                <select>
                    <option>Neznámé</option>
                    <option>Morava</option>
                    <option>Slovensko</option>
                    <option>Slezsko v ČR</option>
                    <option>Slezsko v PL</option>
                    <option>Rakousko</option>
                    <option>Čechy</option>
                    <option>Jiné</option>
                </select>
            </div>
            <div class="row">
                <label for="region">region</label>
                <select if="region">
                    <option>Neznámé</option>
                    <option>Haná</option>
                    <option>Slovácko</option>
                    <option>Valašsko</option>
                    <option>Hřebečsko</option>
                    <option>Podhorácko</option>
                    <option>Horácko</option>
                    <option>Lašsko</option>
                    <option>Brněnsko</option>
                    <option>Záhoří</option>
                </select>
            </div>
            <div class="row">
                <label for="subregion">subregion</label>
                <select if="subregion">
                    <option>Neznámé</option>
                </select>
            </div>
            Dialekt
            <div class="row">
                <label for="dialect">dialekt</label>
                <select if="subregion">
                    <option>Neznámé</option>
                    <option>Hanácký</option>
                    <option>Hanácký horský</option>
                    <option>Valašský</option>
                    <option>Slovácký</option>
                    <option>Kelečský</option>
                    <option>Kopanický</option>
                    <option>Po prajsky</option>
                    <option>Charvátský</option>
                    <option>Čuhácký</option>
                    <option>Malohanácký</option>
                </select>
            </div>
            <div class="row">
                <label for="quality">Kvalita</label>
                <input type="number" id="quality">
            </div>
            <div class="row">
                <label for="quality">Zobrazit v mapě</label>
                <label class="switch">
                    <input id="quality" type="checkbox" onchange="ChangeStylizate()">
                    <span class="slider"></span>
                </label>
            </div>

            <div>
                <label for="devinfo">O překladu</label>
                <textarea id="devinfo"></textarea>
            </div>

            <div>
                <label for="devinfo">Popis nářečí</label>
                <textarea id="devinfo"></textarea>
            </div>
            <div>
                <label for="devinfo">Popis nářečí v okolí</label>
                <textarea id="devinfo"></textarea>
            </div>
            <div>
                <label for="devinfo">Poznámky k překladu</label>
                <textarea id="devinfo"></textarea>
            </div>
            <div>
                <label for="devinfo">JSON varianty</label>
                <textarea id="devinfo"></textarea>
            </div>

            <div >
                <label for="devinfo">Poznámky bokem</label>
                <textarea id="devinfo"></textarea>
            </div>
    -->
        </div>
    </div>
</body>
</html>