<?php
$source_types=[
    // [code, text, type]

    // hlavnн
    ["recorded_place", "Mнsto poшнzenн", "string"],    
    ["recorded_date", "Datum poшнzenн", "date"],    
    ["writed_type", "Zpщsob zбpisu", "string"], // typ zбpisu - z nahrбvky, z pamмti, rychlopis

    // zapisovatel
    ["writer", "Jmйno a pшнjmenн zapisovatele", "string"],
    ["local_writer", "Lokбlnн zapisovatel", "boolean"], // z mнsta poblнћ nebo v mнstм ukбz
    ["writer_age", "Vмk zapisovatele", "int"],
    ["writer_bornplace", "Mнsto narozenн zapisovatele", "string"],
    ["writer_borntime", "Datum narozenн zapisovatele", "date"],
    
    // osoby
    [
        ["recorded_person", "Jmйno a pшнjmenн mluvинho", "string"],    
        ["recorded_person_bornplace", "Mнsto narozenн mluvинho", "string"],    
        ["recorded_person_borndate", "Datum narozenн mluvинho", "date"],
        ["recorded_person_liveplaces", "Mнsta pobytu mluvинho", "string"],
        ["recorded_person_age", "Vмk mluvинho", "int"],
    ],

    // bliћsљн ъdaje o zdroji
    ["type", "Typ", "string"], // vyprбvмnн, pнseт, rozhovor, kniha
    ["stylistics", "Umмleckй", "enum"], // reбlnй (zбznam), neumмleckй (kniha, vyprбvмnн), umмleckй (bбseт, pнseт)
    ["language", "jazykovй prostшedky", "enum"], //nбшeиnн, polonбшeиnн, nenбшeиnн
]