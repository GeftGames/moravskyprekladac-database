<?php
function splitIntoSentences($text) : array {
    return preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
}

function splitIntoWords($sentence): array {
    // Define a regular expression pattern to match spaces, commas, semicolons, and hard spaces
    return preg_split('/[\s,;\u{00A0}]+/u', $sentence, -1, PREG_SPLIT_NO_EMPTY);
}

// exists this sentence in the database
function findSentenceInDatabase($sentence): ?bool {
    $sql = "SELECT id, `from`, `to` FROM sentences_to WHERE to='$sentence' LIMIT 1;";
    $result = $conn->query($sql);
    $list = [];
    if (!$result) {
        throwError("SQL error: ".$sql);
        return null;
    }
    return $result->num_rows > 0;
}

// load texts
$sql="SELECT id, label, text, translated FROM piecesofcite;";
$result = $conn->query($sql);
$list=[];
if (!$result) throwError("SQL error: ".$sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row["text"]!=null) {
            $list[]=[$row["id"], $row["label"], $row["text"], $row["translated"]];
        }
    }
} else {
    // TODO: echo "0 results ";
}

$listOfNonExists=[];
foreach ($list as $item) {
    $text=$item[2];
    $translated=$item[3];

    // get sentences
    $sentences=splitIntoSentences($text);
    $sentencesT=splitIntoSentences($translated);
    for ($i=0; $i<count($sentences); $i++) {
        $sentence = $sentences[$i];
        $sentenceT = $sentencesT[$i];

        // search words in database
        $exists=findSentenceInDatabase($sentence);
        if ($exists) $listOfNonExists[]=[$sentence, $sentenceT, $item];
    }
}

$html='';
foreach ($listOfNonExists as $item) {
    $sentence=$item[0];
    $sentenceT=$item[1];
    $action="addSentence('$item[0]', '$sentence', '$sentenceT')";
    $html.='<a class="button" onclick="$action">+</a> <div style="display: flex"><span>$sentence</span> <span>$sentenceT</span></div>';
}
echo $html;