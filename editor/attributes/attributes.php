<?php
header('Content-Type: text/html; charset=utf8');
?>
<span>Základní atributy</span>
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
