<?php 

// [index, region, subregion, subsubregion, ...]
function GetArrayEnumNationss(){
    return [
        [0, "Neznámé"],

        [1, "Hanáci"],  
        [2, "Podhoráci"],
        [3, "Horáci"],
        [4, "Slováci"],
        [5, "Valaši"],
        [6, "Laši"],
        [7, "Gorali"],
        [8, "Prajzi"],
        [9, "Moravci"],
        [10, "Němci"],
        [11, "Šlonzok"],
        [12, "Češi"],
        [13, "Čuháci"],
        [14, "Charváti"],
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