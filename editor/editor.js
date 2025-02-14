function generateList(list, parentId, filter) {
    const listContainer = document.getElementById(parentId);
    listContainer.innerHTML = "";

    list.forEach(item => {
        if (item.toLowerCase().includes(filter.toLowerCase())) {
            const div = document.createElement("div");
            div.classList.add("item");
            div.textContent = item;
            div.className="selectItem";
            listContainer.appendChild(div);
        }      
    });
}

function filteredListSelect(thisE) {
    thisE.classList.add("selected");
}

function popupClose(name){
    document.getElementById("popup_"+name).style.display="none";
}
function popupShow(name){
    document.getElementById("popup_"+name).style.display="flex";
}
/*
function databaseImportOld(){
    let files=document.getElementById("database_file").files;

    // Clear previous content
    document.getElementById("input_database").innerText="";

    for (let file of files){
        const reader = new FileReader();
        reader.onload = () => {            
            document.getElementById("input_database").innerText+=reader.result+"\n---NEWFILE---\n";
        };        
        reader.readAsText(file);
    }
}*/
function selectMainOption(elToSelect) {
    let tabs=["source", "translate"];
    for (let tab of tabs){
          document.getElementById("mainOption_"+tab).classList.remove("selected");
          document.getElementById("tabsOption_"+tab).style.display="none";
    }
  
    document.getElementById("mainOption_"+elToSelect).classList.add("selected");
    document.getElementById("tabsOption_"+elToSelect).style.display="block";
}