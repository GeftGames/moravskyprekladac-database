<?php 

// [index, region, subregion, subsubregion, ...]
function GetArrayEnumRegions(){
    return [
        [0, "Neznámé"],

        [2, "Mohelnicko"],  
        [3, "Zábřežsko"],

        [11, "Haná", "Uničovsko"],
        [12, "Haná", "Litovelsko"],
        [13, "Haná", "Šternbersko"],
        [14, "Haná", "Konicko"],
        [15, "Haná", "Bouzovsko"],
        [16, "Haná", "Blaťácko"],
        [17, "Haná", "Blaťácko", "Kosířsko"],
        [18, "Haná", "Blaťácko", "Prostějovsko"],
        [19, "Haná", "Blaťácko", "Olomoucko"],
        [20, "Haná", "Čuhácko"],
        [21, "Haná", "Tršicko"],
        [22, "Haná", "Střední Haná"],
        [23, "Haná", "Moravjácko"],
        [24, "Haná", "Zábečví"],
        [25, "Haná", "Vyškovsko"],
        [26, "Haná", "Slavkovsko-Bučovicko"],
        [27, "Haná", "Podchřibsko"],
        [28, "Haná", "Zámoraví"],
        [29, "Haná", "Hulínsko"],
        [30, "Haná", "Otrokovicko"],
    
        // Slovácko
        [26, "Slovácko", "Dolňácko"],
        [27, "Slovácko", "Dolňácko", "Kyjovsko"],
        [28, "Slovácko", "Dolňácko", "Uherskohradišštko"],
        [29, "Slovácko", "Dolňácko", "Uherskohradišštko", "Bojkovicko"],
        [30, "Slovácko", "Dolňácko", "Uherskobrodsko"],
        [31, "Slovácko", "Dolňácko", "Bzenecko"],
        [32, "Slovácko", "Dolňácko", "Hlucko"],
        [33, "Slovácko", "Dolňácko", "Hodonínsko"],
        [34, "Slovácko", "Dolňácko", "Strážnicko"],
        [35, "Slovácko", "Horňácko"],
        [36, "Slovácko", "Podluží"],
        [37, "Slovácko", "Podluží", "Jižní"],
        [38, "Slovácko", "Podluží", "Charvátské"],
        [39, "Slovácko", "Podluží", "Severní"],
        [40, "Slovácko", "Kopanice"],
        [41, "Slovácko", "Hanácké Slovácko"],
        [42, "Slovácko", "Hanácké Slovácko", "Ždánicko"],
        [43, "Slovácko", "Hanácké Slovácko", "Velkopavlovicko"],
        [44, "Slovácko", "Hanácké Slovácko", "Kloboukovsko"],
        [45, "Slovácko", "Luhačovické Zálesí"],
        
        // Valašsko
        [47, "Valašsko", "Rožnovsko"],
        [48, "Valašsko", "Meziříčsko"],
        [49, "Valašsko", "Vsacko"],
        [50, "Valašsko", "Vsacko", "Dolní"],
        [51, "Valašsko", "Vsacko", "Horní"],
        [52, "Valašsko", "Podřevnicko"],
        [53, "Valašsko", "Podřevnicko", "Zlínsko"],
        [54, "Valašsko", "Podřevnicko", "Vizovicko"],
        [55, "Valašsko", "Podřevnicko", "Lukovsko"],
        [56, "Valašsko", "Podřevnicko", "Fryštatsko"],
        [57, "Valašsko", "Kloboukovsko"],
        [58, "Valašsko", "Kloboukovsko", "Závrší"],
        [59, "Valašsko", "Kloboukovsko", "Vizovické Záhoří"],
        [60, "Valašsko", "Kloboukovsko", "Hornolidečsko"],

        // Záhoří/Pobečví/Hranicko
        [62, "Záhoří", "Lipeňské"],
        [63, "Záhoří", "Hostýnské"],
        [66, "Pobečví", "Hranické"],
        [67, "Pobečví", "Hustopečské"],
        [70, "Kravařsko", "Starojicko"],
        [71, "Kravařsko", "Novojicko"],

        // Brněnsko
        [73, "Brněnsko", "Brno"],
        [74, "Brněnsko", "Židlochovicko"],
        [75, "Brněnsko", "Rajhradsko"],

        // Drahansko
        [77, "Drahansko", "Protivansko"],
        [78, "Drahansko", "Jedovnicko"],
        [79, "Drahansko", "Brodecko"],
        
        // Horácko
        [81, "Malá Haná"],
        [82, "Horácko"],
        [83, "Horácko", "Jižní"],
        [84, "Horácko", "Jižní", "Telčsko"],
        [85, "Horácko", "Jižní", "Dačicko"],
        [86, "Horácko", "Jižní", "Slavonicko"],
        [87, "Horácko", "Jižní", "Jemnicko"],
        [88, "Horácko", "Střední"],
        [89, "Horácko", "Střední", "Jihlavsko"],
        [90, "Horácko", "Severní"],
        [91, "Horácko", "Severní", "Žďársko"],
        [92, "Horácko", "Severní", "Novomětsko"],
        [93, "Horácko", "Severní", "Bystřičko"],
        [94, "Horácko", "Severní", "Kunštátsko"],
        [95, "Podhorácko"],
        [96, "Podhorácko", "Severní"],
        [97, "Podhorácko", "Severní", "Letovicko"],
        [98, "Podhorácko", "Severní", "Nedvědicko"],
        [99, "Podhorácko", "Severní", "Tišnovsko"],
        [100, "Podhorácko", "Střední"],
        [101, "Podhorácko", "Střední", "Třebíčsko"],
        [102, "Podhorácko", "Střední", "Velkobítěšsko"],
        [103, "Podhorácko", "Jižní"],
        [104, "Podhorácko", "Jižní", "Náměšťsko"],
        [105, "Podhorácko", "Jižní", "Hrotovicko"],
        [106, "Podhorácko", "Jižní", "Jaroměřičsko"],
        [107, "Podhorácko", "Jižní", "Moravskobudějovicko"],
        [108, "Dolsko"],
        [109, "Dolsko", "Ivančicko"],
        [110, "Dolsko", "Oslovansko"],
        [111, "Dolsko", "Krumlovsko"],
        [112, "Dolsko", "Znojemsko"],
        [113, "Dolsko", "Miroslavsko"],

        // Němci
        [114, "Jesenicko"],
        [115, "Jesenicko", "Spálovsko"],
        [116, "Jesenicko", "Vítkovsko"],
        [117, "Šumpersko"],
        [118, "Hřebečsko"],
        [119, "Podyjí"],
        [120, "Podyjí", "Pálava"],
        [121, "Podyjí", "Pohořelicko"],
        [122, "Podyjí", "Hrušovansko"],
        [123, "Podyjí", "Znojemsko"],
        [124, "Podyjí", "Vranovsko"],
        [125, "Podyjí", "Novomlýnsko"],

        // Lašsko
        [126, "Lašsko"],
        [127, "Lašsko", "Frenštátsko"],
        [128, "Lašsko", "Frýdlansko"],
        [129, "Lašsko", "Kopřivnicko"],
        [130, "Lašsko", "Místecko"],
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
        [144, "Slovensko", "Považie", "Dolné"],
        [145, "Slovensko", "Považie", "Středné"],
        [146, "Slovensko", "Považie", "Horné"],

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