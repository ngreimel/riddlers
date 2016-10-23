<?php

// Load wordlist and properly format words (no spaces/newlines)
$words = file($argv[1]);
$words = array_map('trim', $words);

// Sort words into multi-dimensional array by length
// e.g. $array = [ 'one' => [ 'a', 'i' ], 'two' => [ 'an', 'by' ] ]
$master = [];
foreach ($words as $word) {
    $length = strlen($word);
    if (!isset($master[$length])) {
        $master[$length] = [];
    }
    $master[$length][] = $word;
}

// Start with longest words
krsort($master);

// Save wordlist
// ex. $wordlist[word] = [ wor, ord ]
// $wordlist[word] only exists if wor or ord is a word
// and the array will contain only valid words
// if word.length == 2, $wordlist[word] === true
$wordlist = [];
foreach ($master as $length => $words) {

    // Shortest possible words are 2 letters long
    if ($length == 2) {
        foreach ($words as $word) {
            $wordlist[$word] = true;
        }
        break;
    }

    // Set next array to search for valid wor/ord's
    $next = [];
    if (isset($master[$length - 1])) {
        $next = $master[$length - 1];
    }

    // All the words
    foreach ($words as $word) {
        $wordlist[$word] = [];

        // Remove first and last letters, respectively
        $wor = substr($word, 0, $length - 1);
        $ord = substr($word, 1);
        
        // Test for valid words
        if (in_array($wor, $next)) {
            $wordlist[$word][] = $wor;
        }
        if (in_array($ord, $next)) {
            $wordlist[$word][] = $ord;
        }
        
        // Were there any matches?
        if (!count($wordlist[$word])) {
            unset($wordlist[$word]);
        }
    }
}

// These are the possibilities
$allthewords = array_keys($wordlist);

// Iterate
foreach ($allthewords as $index => $word) {
    $path = follow($word, $wordlist);
    
    // Could we find a path to a 2-letter word?
    if ($path) {
        echo implode($path, ' < '), PHP_EOL;
        exit;
    }
}

/**
 * Search $nodelist for $word.
 *   If $nodelist[$word] exists and is true, return $path
 *   If $nodelist[$word] exists and is an array, follow each child and add to $path
 *
 * @param $word     string
 * @param $nodelist array
 * @param $path     array
 */
function follow($word, $nodelist, $path = []) {
    if (is_array($nodelist)) {
        // Great success!
        if (isset($nodelist[$word]) && true === $nodelist[$word]) {
            return array_merge($path, [$word]);
        }

        // List contains word, follow paths
        if (isset($nodelist[$word]) && is_array($nodelist[$word])) {
            foreach ($nodelist[$word] as $words) {
                $path_plus_word = array_merge($path, [$word]);
                $new_path = follow($words, $nodelist, $path_plus_word);
                if ($new_path) {
                    return $new_path;
                }
            }
        }
    }

    return false;
}
