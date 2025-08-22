<?php
$translate=$_SESSION["translate"];

$GLOBALS["script"].= /** @lang JavaScript */"
    window.search=function(){
        let person=document.getElementById('person').value;
        let type=document.getElementById('replaceTypeVerb').value;
        let gender=document.getElementById('gender').value;
        let vclass=document.getElementById('class').value;
        let number=document.getElementById('number').value;
        let trans=document.getElementById('trans').value;
    
        let formData = new URLSearchParams();
        formData.append('action', 'search_endings_verb');
        formData.append('translate', ".$translate.");
        formData.append('class', vclass);
        formData.append('type', type);
        formData.append('person', person);
        formData.append('number', number);
        formData.append('gender', gender);
        formData.append('trans', trans);

        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        }).then(response => response.json())
        .then(json => {
            if (json.status==='OK') {
                let out=document.getElementById('output_verb_endings');
                out.innerHTML='';
                console.log(json.data.length);
                for (let d of json.data) {
                    if (d.to.length == 0) continue;
                    let wrap=document.createElement('tr'); 
                    wrap.className='verbEndingsRow';
                     // btn add
                    let wrapBtn=document.createElement('td');
                    wrap.appendChild(wrapBtn);
                    
                    let btnAdd=document.createElement('a');
                    btnAdd.className='button';
                    btnAdd.innerText='Přidat';
                    btnAdd.style.backgroundColor='var(--ColorOrig)';
                    btnAdd.addEventListener('click', function(e) {        
                        addEndingVerb(d.source_ending, d.to[0].ending, fall, number, vclass, gender);
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
                let out=document.getElementById('output_verb_endings');
                out.innerHTML='';
                let note=document.createElement('span');
                note.innerText='Nenalezen zádný záznam.';
                out.appendChild(note);
                
                console.warn('error search_endings_verb', json);
            }
        });
    }
    
    function addEndingVerb(source, to, fall, number, vclass, gender) {
        let formData = new URLSearchParams();
        formData.append('action', 'add_ending_verb');
        formData.append('fall', fall);
        formData.append('number', number);
        formData.append('class', vclass);
        formData.append('gender', gender);
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
      
    var changeVerbType = function() {
            let type=document.getElementById('replaceTypeVerb');
            
            let number=document.getElementById('rowReplaceTypeVerbNumber');
            let person=document.getElementById('rowReplaceTypeVerbPerson');
            let gender=document.getElementById('rowReplaceTypeVerbGender');
            let trans =document.getElementById('rowReplaceTypeVerbTrans');
            
            let valType=type.value;
            // infinive
            if (valType==='1') {
                number.style.display='none';
                person.style.display='none';
                gender.style.display='none';
                trans.style.display='none';
            // přit + bud
            }else if (valType==='2' || valType==='3') {
                number.style.display='table-row';
                person.style.display='table-row';
                gender.style.display='none';
                trans.style.display='none';
            // rozkazovaci
            }else if (valType==='4') {
                number.style.display='table-row';
                person.style.display='table-row';
                gender.style.display='none';
                trans.style.display='none';
            // min
            }else if (valType==='5' || valType==='6') {
                number.style.display='table-row';
                person.style.display='none';
                gender.style.display='table-row';
                trans.style.display='none';
            // přech
            }else if (valType==='7' || valType==='8') {
                number.style.display='none';
                person.style.display='none';
                gender.style.display='none';
                trans.style.display='table-row';
            // podmi
            }else if (valType==='9' || valType==='10') {
                number.style.display='table-row';
                person.style.display='none';
                gender.style.display='table-row'; 
                trans.style.display='none';
            }else {
                number.style.display='none';
                person.style.display='none';
                gender.style.display='none';
                trans.style.display='none';
               // console.warn('Unknown valType: '+valType);                
            }
        }
";

// output_verb_endings tablble max height to fill page
$GLOBALS["onload"].= /** @lang JavaScript */"
    window.addEventListener('resize', resizeAttrPage);
    window.addEventListener('DOMContentLoaded', resizeAttrPage);
    resizeAttrPage();
    changeVerbType();
";
?>
<table>
    <tr>
        <td><label for="class">Třída</label></td>
        <td><select id="class" name="type" style="width: 100%;">
            <option value="0">{Neznámá}</option>
            <option value="1">1. -E</option>
            <option value="2">2. -NE</option>
            <option value="3">3. -JE</option>
            <option value="4">4. -Í</option>
            <option value="5">5. -Á</option>
        </select></td>
    </tr>

    <tr>
        <td><label for="replaceTypeVerb">Typ</label></td>
        <td><select id="replaceTypeVerb" style="width: 100%;" onchange="changeVerbType()">
            <option value="0">{Neznámý}</option>
            <option value="1">Infinitiv</option>
            <option value="2">Přítomný</option>
            <option value="3">Budoucí</option>
            <option value="4">Rozkazovací</option>
            <option value="5">Minulý činný</option>
            <option value="6">Minulý trpný</option>
            <option value="7">Přechodník přít</option>
            <option value="8">Přechodník min</option>
            <option value="10">Podmiňovací</option>
        </select></td>
    </tr>

    <tr id="rowReplaceTypeVerbNumber">
        <td><label for="number">Číslo</label></td>
        <td><select id="number" style="width: 100%;">
            <option value="0">{Neznámý}</option>
            <option value="1">Jednotný</option>
            <option value="2">Množný</option>
        </select></td>
    </tr>

    <tr id="rowReplaceTypeVerbGender">
        <td><label for="gender">Rod</label></td>
        <td><select id="gender" style="width: 100%;">
            <option value="0">{Neznámý}</option>
            <option value="1">Mužský živ.</option>
            <option value="2">Mužský než.</option>
            <option value="3">Ženský</option>
            <option value="4">Střední</option>
        </select></td>
    </tr>

    <tr id="rowReplaceTypeVerbPerson">
        <td><label for="person">Osoba</label></td>
        <td><select id="person" style="width: 100%;">
            <option value="0">{Neznámý}</option>
            <option value="1">1.</option>
            <option value="2">2.</option>
            <option value="3">3.</option>
        </select></td>
    </tr>

    <tr id="rowReplaceTypeVerbTrans">
        <td><label for="trans">Podmiňovací tvar</label></td>
        <td><select id="trans" style="width: 100%;">
                <option value="0">{Neznámý}</option>
                <option value="1">Mužský</option>
                <option value="3">Ženský+střední</option>
                <option value="4">Množné</option>
            </select></td>
    </tr>

    <tr>
        <td colspan="2"><a class="button" onclick="window.search()">Hledat</a></td>
    </tr>
</table>
<div id="attrpage" style="overflow-y: scroll;">
<table id="output_verb_endings" style="max-width: 18cm"></table>
</div>