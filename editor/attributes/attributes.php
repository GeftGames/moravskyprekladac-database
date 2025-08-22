<?php
include 'components/selectmultipleandsort.php';

$filter=$_SESSION['translate'];;
$sql = "SELECT `name`, `nameVariants`, `gpsX`, `gpsY`, `country`, langtype, administrativeTown, showInMaps, quality, devinfo, options FROM `translate` WHERE `id`=$filter;";
$result = $conn->query($sql);

$gpsX=0;
$gpsY=0;
$country=0;
$langtype=0;
$translateName='';
$administrativeTown='';
$showInMaps=false;
$quality=0;
$nameVariants='';
$devinfo='';
$options='';
$hidden=false;/*no selected translate*/
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $translateName = $row["name"];
        $gpsX = $row["gpsX"];
        $gpsY = $row["gpsY"];
        $country = $row["country"];
        $administrativeTown = $row["administrativeTown"];
        $langtype = $row["langtype"];
        $showInMaps=$row["showInMaps"];
        $quality=$row["quality"];
        $nameVariants=$row["nameVariants"];
        $devinfo=str_replace("\\n", "\n", $row["devinfo"]);
        $options=str_replace("\\n", "\n", $row["options"]);
    }
}else{
    $hidden=true;
}

$GLOBALS["onload"].= /** @lang JavaScript */"
    window.addEventListener('resize', resizeAttrPage);
    window.addEventListener('DOMContentLoaded', resizeAttrPage); // ensure it runs on page load
    resizeAttrPage();

    function resizeAttrPage() {
        const attrpage = document.getElementById('attrpage');
        const rect = attrpage.getBoundingClientRect();
        const offsetTop = rect.top;
        const maxHeight = window.innerHeight - offsetTop;
        attrpage.style.maxHeight = maxHeight + 'px';
    }
    
    {
        let variantsRaw='".$nameVariants."'.split(',');
        let variants={};
        for (let v of variantsRaw) {
            let kv=v.split('=');
            variants[kv[0]]=kv[1];
        }
        loadTableDataFromJson('variants', JSON.stringify(variants));
    }
";
// save this attributes page
$GLOBALS["script"].= /** @lang JavaScript */"
    var flist_piecesofcite; 
    var saveAttrs = function() {
        let formData = new URLSearchParams();
        formData.append('action', 'attrs');        
        
        // id
        formData.append('id', $filter);
        
        // label
        let name=document.getElementById('name').value;
        formData.append('name', name);
        
        //administrative town
        let nameAdministrativeTown=document.getElementById('nameAdministrativeTown').value;
        formData.append('administrativeTown', nameAdministrativeTown);
              
        // variants of name
        let variants=getTableData('variants');
        let json=JSON.parse(variants);
        let arr=[];
        for (let j in json) {
            arr.push(j,json[j]);
        }
        formData.append('variants', arr.join(','));
        
        // country
        let country=document.getElementById('country').value;
        formData.append('country', country);
        
        // Category of this translate
        let category=document.getElementById('category').value;
        formData.append('category', category);
        
        // GPS
        let gpsX=document.getElementById('gpsX').value;
        formData.append('gpsX', gpsX);
        
        let gpsY=document.getElementById('gpsY').value;
        formData.append('gpsY', gpsY);
        
        let pieceofciteParent=document.getElementById('pieceofciteParent').value;
        formData.append('people', paramsPeople);
  
        

        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        }).then(response => response.json())
        .then(json => {
            if (json.status==='OK'){
               flist_piecesofcite.getSelectedItemInList().innerText=label;
            }else console.log('error currentPieceOfCiteSave: ', json);
        });
    };";
?>
<div id='attrpage' style="overflow-y: auto;<?php if ($hidden) echo "display: none";?>">
        <table>
            <tr><td><h3>Sídlo</h3></td></tr>
            <tr>
                <td><label for="name">Název</label></td>
                <td><input type="text" id="name" value='<?php echo $translateName; ?>'></td>
            </tr>
            <tr>
                <td><label for="nameAdministrativeTown">Spadá pod</label></td>
                <td><input type="text" id="nameAdministrativeTown" value='<?php echo $administrativeTown; ?>'></td>
            </tr>
            <tr class="rowsection">
                <td><label for="namemul">Varianty názvu</label></td>
                <td colspan="2"><?php
                    include "components/table_editor.php";
                    echo basicTableEditor("variants", [], ["lang"=>"nář", "text"=>"Holomóc"]);
                ?></td>
            </tr>
            <tr>
                <td><label for="category">Typ překladu</label></td>
                <td><select id="category">
                    <option<?php if ($langtype==0) echo ' selected'; ?>>Neznámé</option>
                    <option<?php if ($langtype==1) echo ' selected'; ?>>Vesnice</option>
                    <option<?php if ($langtype==2) echo ' selected'; ?>>Část města</option>
                    <option<?php if ($langtype==3) echo ' selected'; ?>>Město</option>
                    <option<?php if ($langtype==4) echo ' selected'; ?>>Samota</option>
                    <option<?php if ($langtype==5) echo ' selected'; ?>>Obecné nářečí</option>
                    <option<?php if ($langtype==6) echo ' selected'; ?>>Jazyk</option>
                </select></td>
            </tr>
            <tr><td><h3>Místo</h3></td></tr>
            <tr>
                <td><label id="gps">Pozice</label></td>
                <td>
                    <label for="gpsX">X</label>
                    <input type="number" id="gpsX" value='<?php echo $gpsX; ?>'>
                </td>
                <td>
                    <label for="gpsY">Y</label>
                    <input type="number" id="gpsY" value='<?php echo $gpsY; ?>'>
                </td>
            </tr>
            <tr>
                <td><label for="country">Země</label></td>
                <td><select id="country" name="country">
                    <option<?php if ($country==0) echo ' selected'; ?>>Neznámé</option>
                    <option<?php if ($country==1) echo ' selected'; ?>>Morava</option>
                    <option<?php if ($country==2) echo ' selected'; ?>>Slovensko</option>
                    <option<?php if ($country==3) echo ' selected'; ?>>Slezsko v ČR</option>
                    <option<?php if ($country==4) echo ' selected'; ?>>Slezsko v PL</option>
                    <option<?php if ($country==5) echo ' selected'; ?>>Rakousko</option>
                    <option<?php if ($country==6) echo ' selected'; ?>>Čechy</option>
                    <option<?php if ($country==7) echo ' selected'; ?>>Jiné</option>
                </select></td>
            </tr>
            <tr>
                <td><label for="region">region</label></td>
                <td colspan=2>
                    <?php
                        $sql="SELECT `id`, `label` FROM `regions`;";
                        $result = $conn->query($sql);
                        $list_regions=[];
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $list_regions[]=[$row["id"], $row["label"]];
                            }
                        }

                        echo generateSelectMultipleWithSort($conn,"region", $filter, ", zone_type");
                    ?>
                </select></td>
            </tr>

            <tr class="rowsection"><td><h3>Dialekt</h3></td><tr>
            <tr>
                <td><label for="dialect">dialekt dle lingvistiků (Bartoše)</label></td>
                <td><select id="dialect">
                    <option value="0">Neznámý</option>

                    <optgroup label="<slovenský>">
                        <option value="1.0">Slovenský</option>

                        <option value="1.1.0">Moravskoslovenská</option>
                        <option value="1.1.1">Slovácký</option>
                        <option value="1.1.2">Valašský</option>
                        <option value="1.1.2">Kelečský</option>
                        <option value="1.1.2">Starojický</option>

                        <option value="1.2">Dolský</option>

                        <option value="1.2">Uherskoslovenská</option>
                        <option value="1.1">Kopanický</option>
                    </optgroup>

                    <optgroup label="<hanácké>">
                        <option value="2.0">Hanácký</option>
                        <option value="2.1">Čuhácký</option>
                        <option value="2.1">Hanácký horský</option>
                        <option value="2.1">kunštátský</option>
                    </optgroup>

                    <optgroup label="<lašské>">
                        <option value="3.0">Lašské</option>
                        <option value="3.1">Hlučínské (po prajzky)</option>
                    </optgroup>

                    <optgroup label="<české>">
                        <option value="3.0">Český</option>
                    </optgroup>

                    <optgroup label="<jiné>">
                        <option value="4.0">Chorvatský</option>
                        <option value="5.0">Německý</option>
                    </optgroup>
                </select></td>
            </tr>

            <tr class="rowsection">
                <td><label for="region">jazyk dle obyvatel</label></td>
                <td colspan=2>
                    <?php
                    $sql="SELECT `id`, `label` FROM `langs`;";
                    $result = $conn->query($sql);
                    $list_nations=[];
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $list_nations[]=[$row["id"], $row["label"]];
                        }
                    }

                    echo generateSelectMultipleWithSort($conn, "lang", $filter,"");
                    ?>
                </td>
            </tr>
            <tr class="rowsection">
                <td><label for="region">národnost dle obyvatel</label></td>
                <td colspan=2>
                    <?php
                    $sql="SELECT `id`, `label` FROM `nations`;";
                    $result = $conn->query($sql);
                    $list_nations=[];
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $list_nations[]=[$row["id"], $row["label"]];
                        }
                    }

                    echo generateSelectMultipleWithSort($conn, "nation", $filter,"");
                    ?>
                </td>
            </tr>

            <tr class="rowsection">
                <td><label for="quality">Kvalita</label></td>
                <td><input type="number" id="quality"<?php echo $quality;?>></td>
            </tr>
            <tr class="rowsection">
                <td><label for="quality">Zobrazit v mapě</label></td>
                <td><label class="switch">
                    <input id="quality" type="checkbox"<?php if ($showInMaps) echo '  checked';?>>
                    <span class="slider"></span>
                </label></td>
            </tr>
            </table>

            <div>
                <div class="section" style="display: grid;width: -webkit-fill-available;">
                    <label for="devinfo">O překladu</label>
                    <textarea id="devinfo" style="max-width: 15cm;width: -webkit-fill-available;;height: 5cm"><?php echo $devinfo; ?></textarea>
                </div>

                <div class="section" style="display: grid;width: -webkit-fill-available;">
                    <label for="devinfo">Popis nářečí</label>
                    <textarea id="devinfo" style="max-width: 15cm;width: -webkit-fill-available;;height: 5cm"><?php echo $devinfo; ?></textarea>
                </div>
                <div class="section" style="display: grid;width: -webkit-fill-available;">
                    <label for="devinfo">Popis nářečí v okolí</label>
                    <textarea id="devinfo" style="max-width: 15cm;width: -webkit-fill-available;;height: 5cm"><?php echo $devinfo; ?></textarea>
                </div>
                <div class="section" style="display: grid;width: -webkit-fill-available;">
                    <label for="devinfo">Poznámky k překladu</label>
                    <textarea id="devinfo" style="max-width: 15cm;width: -webkit-fill-available;;height: 5cm"><?php echo $devinfo; ?></textarea>
                </div>
                <div class="section" style="display: grid;width: -webkit-fill-available;">
                    <label for="devinfo">JSON varianty</label>
                    <div>
                        <a class="button" onclick="addVariantsReplace()">+ Replace</a> <a class="button" onclick="addVariantsChoose()">+ Choose</a>
                    </div>
                    <textarea id="devinfo" style="max-width: 15cm;width: -webkit-fill-available;;height: 5cm"><?php echo $options; ?></textarea>
                </div>

                <div class="section" style="display: grid;width: -webkit-fill-available;">
                    <label for="devinfo">Poznámky bokem</label>
                    <textarea id="devinfo" style="max-width: 15cm;width: -webkit-fill-available;height: 5cm"><?php echo $devinfo; ?></textarea>
                </div>

                <div class="section" style="display: grid;">
                    <a class="button" onclick="saveAttrs()">Uložit</a>
                </div>
            </div>
    </div>