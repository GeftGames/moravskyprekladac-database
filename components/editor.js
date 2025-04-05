let filteredLists=[];

class filteredList{
    constructor(FilteredListName, type) {
        this.handlerItemSelectedChanged=null;
        this.handlerItemAddedChanged=null;
        this.FilteredList=document.getElementById("container_"+FilteredListName);//conteiner_regions
        this.ListContainer=document.getElementById("list_"+FilteredListName);
        this.FilterElement=document.getElementById("filter_"+FilteredListName);
        this.ContextMenu=document.getElementById("contextmenu_"+FilteredListName);
        filteredLists.push(this);
        this.TableName=FilteredListName;
        this.lastAddedId=-1;
        this.type=type;
    }

    EventItemSelectedChanged = function(func) {
        this.handlerItemSelectedChanged=func;
    }

    EventItemAddedChanged = function(func) {
        this.handlerItemAddedChanged=func;
    }

    SelectedItemChanged_dispatch = function() {
        setTimeout(this.handlerItemSelectedChanged, 0);
    }

    ItemAdded_dispatch = function() {
        setTimeout(this.handlerItemAddedChanged, 0);
    }

    filterText = function() {
        return this.FilterElement.value;
    }

    getSelectedItemsInList = function() {
        return this.ListContainer.querySelectorAll(".selectedSideItem");
    }

    getSelectedItemInList = function() {
        return this.ListContainer.querySelector(".selectedSideItem");
    }

    getSelectedIdInList = function() {
        let elementsSelected= this.ListContainer.querySelectorAll(".selectedSideItem");
        if (!elementsSelected) {
            return null;
        }
        return elementsSelected[0].dataset.id;
    }

    hasFocus() {
        return document.activeElement === this.FilteredList;
    }

    generateList = (list) => {
        this.ListContainer.innerHTML = "";
        this.filter=this.filterText().toLowerCase();
        if (!Array.isArray(list)) console.log("list is not array", list);
        list.forEach(item => {
            let id=item[0];
            let label=item[1];
            if (label==null) label="<Výchozí>";
            if (label.toLowerCase().includes(this.filter) || label==="Výchozí") {
                const div = document.createElement("div");
                div.classList.add("item");
                div.textContent = label;
                div.className="sideItem";
                div.dataset.id=id;
                this.ListContainer.appendChild(div);

                div.addEventListener("click", () => {
                    this.filteredListSelect(div);
                });

                div.addEventListener("contextmenu", (e) => {
                    e.preventDefault();
                    this.filteredListSelect(div);
                    HideContexMenu();
                    this.ContextMenu.style.display="flex";
                    this.ContextMenu.style.top=e.clientY+"px";
                    this.ContextMenu.style.left=e.clientX+"px";
                    contexMenuShown=this.ContextMenu;
                    return true;
                });
            }
        });

        if (list.length>0) {
            this.filteredListSelect(this.ListContainer.lastChild);
            this.lastAddedId=list[list.length-1];
        }
    }

    filteredListSelect = (element) => {
        let classNameSelected="selectedSideItem";

        // Deselect
        let elementsSelected = this.getSelectedItemsInList();
        if (elementsSelected!==undefined) {
            for (let e of elementsSelected) {
                e.classList.remove(classNameSelected);
            }
        }

        // Select
        element.classList.add(classNameSelected);

        // Set current id
        this.ListContainer.setAttribute("data-id", element.getAttribute("data-id"));
                
        // call event selection changed
      /*  document.dispatchEvent(new CustomEvent("selecteditemchanged", {
            object: this
        }));
        */
        this.SelectedItemChanged_dispatch();

        // Focus - ordinary is already focused
        //parentList.focus();
    }
       
    loadItems = (listContainer) => {
        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=regions_items&table='+this.TableName
        }).then(response => response.text()) 
        .then(html => {
            listContainer.innerHTML = html;
        });
    }

    list_add = () =>{
        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=list'+this.type+'_add&table='+this.TableName
        }).then(response => response.json())
        .then(json => {
            if (json.status==="ERROR"){
                console.log(json); 
                return; 
            }
            this.generateList(json.list);
            this.ListContainer.lastChild.classList.add("selectedSideItem");
            this.ItemAdded_dispatch();
        });
    }

    list_remove = () => {
     
        // selected container
        let elementsSelected = this.getSelectedItemsInList();
    
        // no selected
        if (!elementsSelected) {
            return;
        }

        //no multiple
       // if (elementsSelected.length>1) return;
        
        if (!confirm("smazat "+elementsSelected.innerText+"?")) return;

        let id=elementsSelected[0].dataset.id;
        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=list_'+this.type+'remove&table='+this.TableName+'&id='+id
        }).then(response => response.json())
        .then(json => {
            this.generateList(json);
        });
    }

    list_duplicate = ()=> {
        // selected container
        let elementsSelected = this.getSelectedItemsInList();
    
        // no selected
        if (!elementsSelected) {
            return;
        }

        //no multiple
       // if (Array.isArray(elementsSelected)) return;

        let id=elementsSelected[0].dataset.id;
        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=list_'+this.type+'duplicate&table='+this.TableName+'&id='+id
        }).then(response => response.json())
        .then(json => {
            this.generateList(json);
        });
    }
}

document.addEventListener("click", (e) => {
    HideContexMenu();
});

window.addEventListener('resize', () => {
    refreshFilteredLists();
});

function refreshFilteredLists(){
    for (let e of filteredLists) {
        const rect = e.FilteredList.getBoundingClientRect();
        e.FilteredList.style.height="calc(100vh - "+rect.top+"px)";
    }

    // Editor right side
    let editorView=document.getElementsByClassName('editorView');
    for (let e of editorView){
        const rect = e.getBoundingClientRect();
        e.style.height="calc(100vh - "+rect.top+"px)";
    }
}

document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("keydown", (e)=>{

        let selectedContainer=null;
        for (let i of filteredLists) {
            if (i.hasFocus()){
                selectedContainer=i;
                break;
            }
        }
        if (selectedContainer==null) return;

        // active element
        let listContainers = document.getElementsByClassName("filterList");
        let activeElement = [...listContainers].find(list => list === document.activeElement);
        if (!activeElement) return;
        
        // selected item in list
        // all selected items in multiple lists
        let elementsSelected = activeElement.querySelector(".selectedSideItem");
        if (!Array.isArray(elementsSelected))elementsSelected=[elementsSelected];
        let selectedE = [...elementsSelected].find(el => el.parentNode.parentNode === activeElement);
       
        let dir=0;
        switch (e.key) {
            case "ArrowUp":
                dir=-1;
                break;
                
            case "ArrowDown":
                dir = 1;
                break;

            case "PageUp":
                dir=-10;
                break;
                
            case "PageDown":
                dir = 10;
                break;

            default:
                return;
        }

        // block default action
        e.preventDefault();

        if (dir<0) {
            for (let i=0; i<10; i++) {
                let prev=selectedE;
                for (let skip=0; skip<Math.abs(dir); skip++) {
                    if (prev.previousElementSibling!==undefined) prev=prev.previousElementSibling;
                }
                if (prev===undefined) return;
                if (prev.nodeName!=="DIV") continue;
                selectedContainer.filteredListSelect(prev);
            }
        } else if (dir>0) {
            for (let i=0; i>-10; i--) {
                let next=selectedE;
                for (let skip=0; skip<Math.abs(dir); skip++) {
                    if (next.nextElementSibling!==undefined) next=next.nextElementSibling;
                }
                if (next===undefined) return;
                if (next.nodeName!=="DIV") continue;
                selectedContainer.filteredListSelect(next);
            }
        }
    });
});

// Hide contex menu if action
let contexMenuShown=null;
function HideContexMenu() {
    if (contexMenuShown!==undefined && contexMenuShown!==null) {
        contexMenuShown.style.display="none";
    }
}

function getFilteredListById(id) {
    let e=document.getElementById("list_"+id);
    for (let i of filteredLists) {
        if (i.ListContainer === e) return i;
    }
}

function popupClose(name){
    document.getElementById("popup_"+name).style.display="none";
}

function popupShow(name){
    document.getElementById("popup_"+name).style.display="flex";
}

function selectMainOption(elToSelect) {
    let tabs=["source", "global", "attributes", "tools", "patterns", "replaces", "simple", "relations", "translate"];
    for (let tab of tabs) {
        let eMainOption = document.getElementById("mainOption_" + tab);
        eMainOption.classList.remove("selected");
        document.getElementById("tabsOption_"+tab).style.display="none";
    }
  
    document.getElementById("mainOption_"+elToSelect).classList.add("selected");
    document.getElementById("tabsOption_"+elToSelect).style.display="block";
}