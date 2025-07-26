<?php
function splitIntoWords($sentence): array {
    // Define a regular expression pattern to match spaces, commas, semicolons, and hard spaces
    return preg_split('/[\s,;\u{00A0}]+/u', $sentence, -1, PREG_SPLIT_NO_EMPTY);
}

// display option for add into database
function add_option($action, $source, $text_to) : string{
    return '<a class="button" onclick="$action">+</a> <div style="display: flex"><span>Source: $source</span> <span>$text_to</span></div>';
}

$sentenceparts=loadFromSQL(["id", "from", "translate"], "sentenceparts","");
$sentenceparts_to=loadFromSQL(["parent", "shape", "cite"], "sentenceparts_to", "");
/*
$prepositions_cs=loadFromSQL(["id", "shape"], "prepositions_cs","");
$prepositions_relation=loadFromSQL(["id", "shape_from", "translate"], "prepositions_relation", "");
$prepositions_to=loadFromSQL(["relation", "shape", "cite", "falls"], "prepositions_to", "");

$conjunctions_cs=loadFromSQL(["id", "shape"], "conjunctions_cs","");
$conjunctions_relation=loadFromSQL(["id", "shape_from", "translate"], "conjunctions_relation", "");
$conjunctions_to=loadFromSQL(["relation", "shape", "cite", "comment"], "conjunctions_to", "");
*/
// load texts
$html='';
$listOfNonExists=[];
foreach ($sentenceparts_to as $sentencepart_to) {

    // get from string
    foreach ($sentenceparts as $sentencepart) {
        if ($sentencepart_to["parent"]==$sentencepart["id"]) {
            // get from
            $words_cs=splitIntoWords($sentencepart["text"]);

            // words for get solve
            $words_to=splitIntoWords($sentencepart_to["text"]);

            // check if it's not in the database
            foreach ($words_to as $word) {

                /*      // prepositions
                   foreach ($prepositions_to as $preposition_to) {
                       if ($prepositions_to["translate"]==$sentencepart["translate"]) { // search in same translate as source sentence
                           if ($word==$preposition_to["text"]) {
                               break;
                           }
                       }
                   }

                   // conjunction
                   foreach ($conjunctions_to as $conjunction_to) {
                       if ($conjunctions_to["translate"]==$sentencepart["translate"]) { // search in same translate as source sentence
                           if ($word==$conjunction_to["text"]) {
                               break;
                           }
                       }
                   }
                   // todo: nouns, adjectives,...

                   // word is not in database...

                   /// - Same as source - ///
                   // prepositions
                /*   foreach ($prepositions_cs as $preposition_cs) {
                       if ($word==$preposition_cs["text"]) {
                           $html.=add_option("addPreposition(".$word.",".$sentencepart_to["cite"].")", $sentencepart_to["text"], $word);
                           break;
                       }
                   }

                   // conjunction
                   foreach ($conjunctions_cs as $conjunction_cs) {
                       if ($word==$conjunction_cs["text"]) {
                           $html.=add_option("addConjunction(".$word.",".$sentencepart_to["cite"].")", $sentencepart_to["text"], $word);
                           break;
                       }
                   }

                   /// - Different as source, check similar translates - near - ///


                   /// - Different as source, check similar translates - far - ///
   */
                // unknown word
            }
            break;
        }
    }
}

echo $html;