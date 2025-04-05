<?php


//function ($id) : void {
    $GLOBALS["script"].= /** @lang JavaScript */'
    function editor_to(parent) {
        console.log(parent);
        let editorWrap = document.createElement("details");
        let summary = document.createElement("summary");
        summary.textContent = "Pattern";
        summary.className = "filterSelect";
        summary.style="    width: fit-content;    display: list-item;cursor: pointer;user-select: none;";
        editorWrap.appendChild(summary);
         
        let rowSection1 = document.createElement("div");
        rowSection1.className = "row section";
        
        let label1 = document.createElement("label");
        label1.id = "name";
        label1.textContent = "Popis";
        rowSection1.appendChild(label1);
        rowSection1.appendChild(document.createElement("br"));
        
        let input1 = document.createElement("input");
        input1.type = "text";
        input1.id = "nounLabel";
        input1.setAttribute("for", "name");
        input1.value = "";
        input1.placeholder = "pohádKA";
        rowSection1.appendChild(input1);
        
        let button1 = document.createElement("a");
        button1.className = "button";
        button1.onclick = function() {};
        button1.textContent = "Sestavit";
        rowSection1.appendChild(button1);
        editorWrap.appendChild(rowSection1);
        
        let rowSection2 = document.createElement("div");
        rowSection2.className = "row section";
        
        let label2 = document.createElement("label");
        label2.id = "base";
        label2.textContent = "Základ";
        rowSection2.appendChild(label2);
        rowSection2.appendChild(document.createElement("br"));
        
        let input2 = document.createElement("input");
        input2.type = "text";
        input2.id = "nounBase";
        input2.setAttribute("for", "name");
        input2.value = "";
        input2.placeholder = "pohád";
        rowSection2.appendChild(input2);
        editorWrap.appendChild(rowSection2);
        
        let rowSection3 = document.createElement("div");
        rowSection3.className = "row section";
        
        let label3 = document.createElement("label");
        label3.textContent = "Rod";
        rowSection3.appendChild(label3);
        
        let select = document.createElement("select");
        select.id = "nounGender";
        select.name = "type";
        
        let options = [
            {value: "0", text: "Neznámý"},
            {value: "4", text: "Střední"},
            {value: "3", text: "Ženský"},
            {value: "2", text: "Mužský neživotný"},
            {value: "1", text: "Mužský životný"}
        ];
        
        options.forEach(opt => {
            let option = document.createElement("option");
            option.value = opt.value;
            option.textContent = opt.text;
            select.appendChild(option);
        });
        
        rowSection3.appendChild(select);
        rowSection3.appendChild(document.createElement("br"));
        editorWrap.appendChild(rowSection3);
        
        let section4 = document.createElement("div");
        section4.className = "section";
        
        let label4 = document.createElement("label");
        label4.id = "name";
        label4.textContent = "Pád";
        section4.appendChild(label4);
        
        let table = document.createElement("table");
        let headerRow = document.createElement("tr");
        
        ["Pád", "Jednotné", "Množné"].forEach(text => {
            let th = document.createElement("td");
            th.className = "tableHeader";
            th.textContent = text;
            headerRow.appendChild(th);
        });
        
        table.appendChild(headerRow);
        
        for (let i = 0; i < 7; i++) {
            let row = document.createElement("tr");
            let cell1 = document.createElement("td");
            cell1.textContent = (i + 1) + ".";
            row.appendChild(cell1);
            
            for (let j = 0; j < 2; j++) {
                let cell = document.createElement("td");
                let input = document.createElement("input");
                input.id = "noun" + (j == 0 ? i : 7 + i);
                input.type = "text";
                cell.appendChild(input);
                row.appendChild(cell);
            }
            
            table.appendChild(row);
               // $html.="</tr>";
        }
        section4.appendChild(table);
        editorWrap.appendChild(section4);      
        parent.appendChild(editorWrap);
    }';
//}