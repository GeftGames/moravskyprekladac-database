<?php 

// [index, region, subregion, subsubregion, ...]
function GetArrayEnumRegions(){
    return [
        [0, "Neznámé"],

        // Haná
        [1, "Horní Haná"],  

        [10, "Haná"],
    
        // Slovácko
        [25, "Slovácko"],
        
        // Valašsko
        [46, "Valašsko"],

        // Záhoří/Pobečví/Hranicko
        [61, "Záhoří"],

        // Brněnsko
        [72, "Brněnsko"],

        // Drahansko
        [76, "Drahansko"],
        [80, "Blansko"],
        
        // Horácko
        [81, "Malá Haná"],
        [82, "Horácko"],
        [95, "Podhorácko"],
        [108, "Dolsko"],

        // Němci
        [114, "Jesenicko"],
        [117, "Šumpersko"],
        [118, "Hřebečsko"],
        [119, "Podyjí"],

        // Lašsko
        [126, "Lašsko"],
        [131, "Těšínsko", "Zaolší", "Lašsko", "Frýdecko"],

        // Slezsko
        [132, "Těšínsko", "Zaolší"],
        [133, "Těšínsko", "Zaolší", "Goralsko"],
        [134, "Těšínsko", "Zaolší", "Karvinsko"],
        [135, "Těšínsko", "Zaolší", "Morávka"],
        [136, "Těšínsko", "Zaolší", "Třinecko"],
        [137, "Opavsko"],
        [138, "Hlučínsko"],
        [139, "Ostravsko"],

        // Slovensko
        [140, "Slovensko"],
        [141, "Slovensko", "Záhorie"],
        [142, "Slovensko", "Kysuce"],
        [143, "Slovensko", "Považie"],

        [147, "Morava"],
        [148, "Nezařaditelné"],
    ]; 
}

function GetRegionCode($names) : int{
    $regions=GetArrayEnumRegions();

   // global $regions;
    $len_regions=count($names); 
    $arr_len=count($regions);

    // Všecke, kery obsahojó
    $all_includes=[];
    for ($rs=0; $rs<$len_regions; $rs++) {
        $region=$regions[$rs];
              
        foreach ($names as $name) {
            for ($r=1; $r<$arr_len; $r++){
                if ($region[$r]==$name) {
                    $all_includes[]=$rs;
                    break;
                }
            }
        }
    }

    // Vybrat nélepši
    $bestLen=0;
    $bestIds=[];
    foreach ($all_includes as $regionListId) { // $regionListId is not id in [id, "", ""],
        $row=$all_includes[$regionListId];
        $same=0;       
        foreach ($names as $name) {
            for ($r=1; $r<$arr_len; $r++) {
                if ($row[$r]==$name) {
                    $same++;
                    break;
                }
            }
        }
     
        if ($same>$bestLen) {
            $bestIds=[$regionListId];
            $bestLen=$same;
        }
        if ($same==$bestLen) {
            $bestIds[]=$regionListId;
        }
    }

    // nélepši = némiň
    $minId=-1;
    $minLen=-1;
    foreach ($bestIds as $bestId) {
        $row=$regions[$bestId];
        $row_len=count($row);
        if ($row_len<$minLen) {
            $minLen=$row_len;
            $minId=$bestId;
        }
    }
    if ($minId!=-1) return $regions[$minId][0];

    if (count($bestIds)>0) return $regions[$bestIds[0]][0];

    return $regions[0][0];
}