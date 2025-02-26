<?php
$wikidirectonary="https://cs.wiktionary.org/wiki/";
$ssc="https://prirucka.ujc.cas.cz/?slovo=";

function GetNounFromWeb($name) {
    // Create a new DOMDocument instance
    $dom = new DOMDocument;

    // Suppress warnings due to malformed HTML
    libxml_use_internal_errors(true);

    // Load the HTML content from a URL
    $dom->loadHTMLFile('http://www.example.com');

    // Restore error handling
    libxml_clear_errors();

    // Get all tables in the document
    $tables = $dom->getElementsByTagName('table');

    // Check if tables exist and iterate over them
    if ($tables->length > 0) {
        foreach ($tables as $index => $table) {
            echo "Table " . ($index + 1) . ":\n";
            echo $dom->saveHTML($table) . "\n";
        }
    } else {
        echo "No tables found.\n";
    }
}
