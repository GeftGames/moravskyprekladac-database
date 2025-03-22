<?php
function createSelectList($list, $id, $defId) {
    $encodedList=json_encode($list);
    // Basic form
    $GLOBALS['onload'].= /** @lang JavaScript */"
        createSelectFilter('$id');
        filteredSearchList_".$id." = new filteredSearchList($encodedList, '$id');
        filteredSearchList_".$id.".selectId($defId);
    ";

    $GLOBALS['script'].= /** @lang JavaScript */"
    var filteredSearchList_$id;
    ";
}

function selectListScripts() :void{
    // init
    $GLOBALS['script'].= /** @lang JavaScript */'
    var createSelectFilter = (id) => {
        let e=document.getElementById("select_"+id);
  
        // Create main div
        const container = document.createElement("div");
        container.style.display = "flex";
        container.className = "filterSelect";
        container.addEventListener("click", ()=>{switchSearch(id)});
     
        // Create span
        const span = document.createElement("span");
        span.id = `selectedLabel_${id}`;
        span.style.minWidth = "5cm";
        span.style.display = "block";
        container.appendChild(span);
     
        // Create dropdown indicator
        const dropdown = document.createElement("div");
        dropdown.className = "filterbtnpop";
        dropdown.textContent = "▼";  
        container.appendChild(dropdown);
    
        // Create hidden input
        const input = document.createElement("input");
        input.type = "hidden";
        input.id = `listreturnholder_${id}`;
        container.appendChild(input);
        e.appendChild(container);
        
        const containerPopup = document.createElement("div");
        containerPopup.id = `searchList_${id}`;
        containerPopup.className = "popupChoose";
        containerPopup.style.display = "none";
    
        const inputContainerPopup = document.createElement("div");
        inputContainerPopup.style.display = "flex";
        containerPopup.appendChild(inputContainerPopup);
    
        const inputPopup = document.createElement("input");
        inputPopup.id = `filter_${id}`;
        inputPopup.type = "text";
        inputContainerPopup.appendChild(inputPopup);
    
        const listContainerPopup = document.createElement("div");
        listContainerPopup.id = `listtoselect_${id}`;
        listContainerPopup.className = "listSearchSelect";
        containerPopup.appendChild(listContainerPopup);
      
        e.appendChild(containerPopup);
    };';

    // Hide popup
    $GLOBALS['script'].= /** @lang JavaScript */'
    var switchSearch=(id)=>{
        let e=document.getElementById("searchList_"+id);
        if (e.style.display === "block"){
            e.style.display = "none";
        }else{
            e.style.display = "block";
            document.getElementById("filter_" +id).focus();
        }
    }

   /* var appply= (id) =>{
        document.getElementById("filter_" +id).apply();
    };*/
   ';

    // Filter class
    $GLOBALS['script'].= /** @lang JavaScript */
        '
    class filteredSearchList{
        constructor(list, FilteredListName) {
            this.handlerItemSelectedChanged=null;
            this.classNameSelected="selectedItem";
            
            // elements
            this.SelectedLabel=document.getElementById("selectedLabel_"+FilteredListName);
            this.FilterElement=document.getElementById("filter_"+FilteredListName);
            this.ListContainer=document.getElementById("listtoselect_"+FilteredListName);
            this.ReturnHolder =document.getElementById("listreturnholder_"+FilteredListName);
            this.Popup        =document.getElementById("searchList_"+FilteredListName);
            
            this.FilterElement.addEventListener("input", () => {
                this.generateList();
            });
            
            this.list=list;
            this.generateList();
        }
    
        getSelectedItemInList = () => {
            return this.ListContainer.querySelectorAll("."+this.classNameSelected);
        } 
        
        filterText () {
            return this.FilterElement.value.toLowerCase();
        }
            
        getSelectedIdInList() {
            let elementsSelected= this.ListContainer.querySelectorAll("."+this.classNameSelected);
            if (!elementsSelected) {
                return null;
            }
            return elementsSelected[0].dataset.id;
        }
    
        hasFocus() {
            return document.activeElement === this.Popup;
        }
        
        // new item
        reload = () => {
            this.FilterElement.value="";
            this.generateList(this.list);       
        }
    
        generateList = () => {
            // Clean           
            this.ListContainer.innerHTML = "";
            
            // get filter text
            let filter = this.filterText();
     
            this.list.forEach(item => {
                let id=item[0];
                let label=item[1];
                
                if (label.toLowerCase().includes(filter) || label==="Výchozí") {
                    const div = document.createElement("div");
                    div.classList.add("item");
                    div.textContent = label;
                    div.className="sideItem";
                    div.dataset.id=id;
                    this.ListContainer.appendChild(div);
    
                    div.addEventListener("click", () => {
                        this.filteredListSelect(div);
                        this.Popup.style.display = "none";
                    });
                }
            });
    
           // if (this.list.length>0) {
                //this.filteredListSelect(this.ListContainer.lastChild);
              //  this.lastAddedId=this.list[this.list.length-1];
           // }
        }
    
        filteredListSelect(element) {    
            // Deselect
            let elementsSelected = this.getSelectedItemInList();
            if (elementsSelected!==undefined) {
                for (let e of elementsSelected) {
                    e.classList.remove(this.classNameSelected);
                }
            }
    
            // Select
            element.classList.add(this.classNameSelected);
            
            this.selectedId=element.getAttribute("data-id");
            this.SelectedLabel.innerText=element.innerText;  
            this.ReturnHolder.value= element.getAttribute("data-id");
        }
        
        selectId(id) {
            
            // Deselect
            let elementsSelected = this.getSelectedItemInList();
            if (elementsSelected!==undefined) {
                for (let e of elementsSelected) {
                    e.classList.remove(this.classNameSelected);
                }
            }
            if (id!=null){
                let selectedE;
                if (id===-1) selectedE=this.ListContainer.lastChild;
                else selectedE=this.ListContainer.querySelector(`div[data-id="`+id+`"]`);
                selectedE.classList.add(this.classNameSelected);
                this.SelectedLabel.innerText=selectedE.innerText;  
                this.ReturnHolder.value= selectedE.getAttribute("data-id");
            }
        }
    }';
}