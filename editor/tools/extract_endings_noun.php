<?php
$translate=$_SESSION["translate"];

$GLOBALS["script"].= /** @lang JavaScript */"
    window.search=function(){
        let fall=document.getElementById('fall').value;
        let gender=document.getElementById('gender').value;
        let pattern=document.getElementById('pattern').value;
        let number=document.getElementById('number').value;
    
        let formData = new URLSearchParams();
        formData.append('action', 'search_endings_noun');
        formData.append('fall', fall);
        formData.append('number', number);
        formData.append('gender', gender);
        formData.append('pattern', pattern);
        formData.append('translate', ".$translate.");

        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        }).then(response => response.json())
        .then(json => {
            if (json.status==='OK') {
                let out=document.getElementById('output_noun_endings');
                out.innerHTML='';
                console.log(json.data.length);
                for (let d of json.data) {
                    if (d.to.length == 0) continue;
                    let wrap=document.createElement('tr'); 
                    wrap.className='nounEndingsRow';
                     // btn add
                    let wrapBtn=document.createElement('td');
                    wrap.appendChild(wrapBtn);
                    
                    let btnAdd=document.createElement('a');
                    btnAdd.className='button';
                    btnAdd.innerText='Přidat';
                    btnAdd.style.backgroundColor='var(--ColorOrig)';
                    btnAdd.addEventListener('click', function(e) {        
                        addEndingNoun(d.source_ending, d.to[0].ending, fall, number, pattern, gender);
                    });    
                    wrapBtn.appendChild(btnAdd);
                    
                    // ending
                    let wrapLabel=document.createElement('td');
                    wrap.appendChild(wrapLabel);
                    
                    let label=document.createElement('span');                   
                    label.innerText=d.source_ending+'>'+d.to[0].ending;
                    wrapLabel.appendChild(label); 
                    
                    // percent
                    let wrapLabelp=document.createElement('td');
                    wrap.appendChild(wrapLabelp);
                    
                    let labelp=document.createElement('span');                   
                    labelp.innerText=(Math.round(d.to[0].percent*100))+'%';
                    wrapLabelp.appendChild(labelp);  
                    
                   
                    // examples
                    let wrapEx=document.createElement('td');
                    wrap.appendChild(wrapEx);
                    
                    let ex=document.createElement('span');
                    let examples=[];
                    for (let tr of d.to[0].tr){
                        examples.push(tr.source+'>'+tr.to);
                    }
                    ex.innerText=examples.join(', ');
                    wrapEx.appendChild(ex);  
                    
                    out.appendChild(wrap);
                }
                if (json.data.length == 0) {
                    let note=document.createElement('span');
                    note.innerText='Nenalezen zádný záznam.';
                    output.appendChild(note);
                }
                resizeAttrPage();
            } else {
                let out=document.getElementById('output_noun_endings');
                out.innerHTML='';
                let note=document.createElement('span');
                note.innerText='Nenalezen zádný záznam.';
                out.appendChild(note);
                
                console.warn('error search_endings_noun', json);
            }
        });
    }
    
    function addEndingNoun(source, to, fall, number, pattern, gender) {
        let formData = new URLSearchParams();
        formData.append('action', 'add_ending_noun');
        formData.append('fall', fall);
        formData.append('number', number);
        formData.append('gender', gender);
        formData.append('pattern', pattern);
        formData.append('source', source);
        formData.append('to', to);
        formData.append('translate', ".$translate.");

        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        }).then(response => response.json())
        .then(json => { 
            if (json.status==='OK') {
               alert('Přidáné!');
            } else {
                alert('Neco se pokazilo!');
                console.log('error currentRegionSave',json);
            }
        });
    }    
    function resizeAttrPage() {
        const attrpage = document.getElementById('attrpage');
        const rect = attrpage.getBoundingClientRect();
        const offsetTop = rect.top;
        const maxHeight = window.innerHeight - offsetTop;
        attrpage.style.maxHeight = maxHeight + 'px';
    }
";

// output_noun_endings tablble max height to fill page
$GLOBALS["onload"].= /** @lang JavaScript */"
    window.addEventListener('resize', resizeAttrPage);
    window.addEventListener('DOMContentLoaded', resizeAttrPage);
    resizeAttrPage();


   // window.resizeAttrPage=resizeAttrPage();
";
?>
<table>
    <tr>
        <td><label for="gender">Rod</label></td>
        <td><select id="gender" style="width: 100%;">
            <option value="0">Neznámý</option>
            <option value="1">Střední</option>
            <option value="2">Ženský</option>
            <option value="3">Mužský životný</option>
            <option value="4">Mužský neživotný</option>
        </select></td>
    </tr>

    <tr>
      <td><label for="pattern">Vzor</label></td>
        <td><select id="pattern" name="type" style="width: 100%;">
            <option value="0">Neznámý</option>
            <optgroup label="Střední">
                <option value="1">Město</option>
                <option value="2">Moře</option>
                <option value="3">Kuře</option>
                <option value="4">Stavení</option>
            </optgroup>
            <optgroup label="Ženský">
                <option value="5">Žena</option>
                <option value="6">Růže</option>
                <option value="7">Píseň</option>
                <option value="8">Kost</option>
            </optgroup>
            <optgroup label="Mužský">
                <option value="9">Pán</option>
                <option value="10">Hrad</option>
                <option value="11">Les</option>
                <option value="12">Muž</option>
                <option value="13">Stroj</option>
                <option value="14">Předseda</option>
                <option value="15">Soudce</option>
            </optgroup>
            <optgroup label="Přídavné">
                <option value="16">Mladý</option>
                <option value="17">Jarní</option>
            </optgroup>
        </select></td> <!-- -->
    </tr>

    <tr>
        <td><label for="number">Číslo</label></td>
        <td><select id="number" style="width: 100%;">
            <option value="0">Neznámý</option>
            <option value="1">Jednotný</option>
            <option value="2">Množný</option>
        </select></td>
    </tr>

    <tr>
        <td><label for="fall">Pád</label></td>
        <td><input type="number" id="fall" style="width: 100%;"></td>
    </tr>

    <tr>
        <td colspan="2"><a class="button" onclick="window.search()">Hledat</a></td>
    </tr>
</table>
<div id="attrpage" style="overflow-y: scroll;">
<table id="output_noun_endings" style="max-width: 18cm"></table>
</div>