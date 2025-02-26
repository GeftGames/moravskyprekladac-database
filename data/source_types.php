<?php
$source_types=[
    // [code, text, type]

    // hlavn�
    ["recorded_place", "M�sto po��zen�", "string"],    
    ["recorded_date", "Datum po��zen�", "date"],    
    ["writed_type", "Zp�sob z�pisu", "string"], // typ z�pisu - z nahr�vky, z pam�ti, rychlopis

    // zapisovatel
    ["writer", "Jm�no a p��jmen� zapisovatele", "string"],
    ["local_writer", "Lok�ln� zapisovatel", "boolean"], // z m�sta pobl� nebo v m�st� uk�z
    ["writer_age", "V�k zapisovatele", "int"],
    ["writer_bornplace", "M�sto narozen� zapisovatele", "string"],
    ["writer_borntime", "Datum narozen� zapisovatele", "date"],
    
    // osoby
    [
        ["recorded_person", "Jm�no a p��jmen� mluv��ho", "string"],    
        ["recorded_person_bornplace", "M�sto narozen� mluv��ho", "string"],    
        ["recorded_person_borndate", "Datum narozen� mluv��ho", "date"],
        ["recorded_person_liveplaces", "M�sta pobytu mluv��ho", "string"],
        ["recorded_person_age", "V�k mluv��ho", "int"],
    ],

    // bli�s�� �daje o zdroji
    ["type", "Typ", "string"], // vypr�v�n�, p�se�, rozhovor, kniha
    ["stylistics", "Um�leck�", "enum"], // re�ln� (z�znam), neum�leck� (kniha, vypr�v�n�), um�leck� (b�se�, p�se�)
    ["language", "jazykov� prost�edky", "enum"], //n��e�n�, polon��e�n�, nen��e�n�
]