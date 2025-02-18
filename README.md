# moravskyprekladac-database
# log in
http://localhost/moravskyprekladac/database/login.php
user: 
password: 

# register
http://localhost/moravskyprekladac/database/register.php

# new database if empty
http://localhost/moravskyprekladac/database/system_actions/inicialize_new_database.php


Database structure
- global list of nouns, adjectives, pronoun, numbers, verbs in standart czech (from)
- local list (group) of nouns, adjectives, pronou, numbers, verbs in own translations (to)
- local list of relations for noun, pronoun, number, verb; relations have also comment (text) and source (id) and uppercase type (lowercase, name-start with, shortcut-all big), relation means connect multiple from (id) with with base text and ending (multiple falls) 
- adverbs, prepositions, conjuctions, particles, interjections dont have relation, lists they have direct connect texts from to texts to with comments and source

