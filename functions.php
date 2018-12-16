<?php
function makeTextFileUniqueAndReturnIt($file)
{
    $fileArray = file($file, FILE_IGNORE_NEW_LINES);
    $fileArray = array_unique($fileArray);
    file_put_contents($file, "");
    foreach ($fileArray as $key => $value) {
        file_put_contents($file, $value."\n", FILE_APPEND);
    }
    return file($file, FILE_IGNORE_NEW_LINES);
}
function printArray($array, $title)
{
    echo("<pre>");
    echo $title . ":\n";
    print_r($array);
    echo("</pre>");
}

function prettyPrint($text)
{
    echo("<pre>");
    echo "\n" . $text . "\n";
    echo("</pre>");
}
function appendToTxtFile($file, $text)
{
    file_put_contents($file, $text."\n", FILE_APPEND);
}
function insertArrayAtStartOfTxtFile($file, $arrayToInsert)
{
    $fileArray = file($file, FILE_IGNORE_NEW_LINES);
    $outputArray = array_merge($arrayToInsert, $fileArray);
    printArray($arrayToInsert, "array to insert array");
    replaceTextFileWithArray($file, $outputArray);
}

function replaceTextFileWithArray($file, $array)
{
    file_put_contents($file, "");
    foreach ($array as $key => $value) {
        appendToTxtFile($file, $value);
    }
}

function addArray2ToArray1(&$array1, $array2)
{
    foreach ($array2 as $key => $value) {
        array_push($array1, $value);
    }
}

function addArrayToJsonFile($file, $array)
{
    echo 'add array to json called';
    //make the id the key

    //printArray($arrayToAdd, "array to add");
    //get current array saved as json
    $json = file_get_contents($file);
    $currentArrayInJson = json_decode($json, true);
    //echo 'json:';
    //print_r($json);
    //printArray($currentArrayInJson, "currentArrayInJson");
    prettyPrint("current array in json size". count($currentArrayInJson));
    if (!isset($currentArrayInJson)) {
        $currentArrayInJson = array();
    }
    $arrayToSave = $currentArrayInJson + $array;
    //printArray($arrayToSave, "array to save");
    prettyPrint("array to save count". count($arrayToSave));

    file_put_contents("$file", json_encode($arrayToSave, JSON_PRETTY_PRINT));//pretty print doesn't acc tak up mor espace
}

function getArrayFromJsonFile($file)
{
    return json_decode(file_get_contents("$file"), true);
}

function scandir_only_wanted_files($path)
{
    $dirArray = scandir($path);
    foreach ($dirArray as $key => $value) {
        if ($value == '.'|| $value == '..'|| $value == '.DS_Store') {
            unset($dirArray[$key]);
        }
    }
    $dirArray = array_values($dirArray);
    // echo ("<pre>");
    // echo " processed dirArray  \n";
    // print_r($dirArray);
    // echo ("</pre>");
    return $dirArray;
}


function distributeJustUsedMemesRandomlyInVideoMemesToPost($idsUsedInThisCompilation)
{
    echo 'distribue jsut used memes called';
    //in fact, it is fine if they are distributed randomly and end up among the bnad new memes near the bottom of the text file because it will skip over recently used memes anyway
    $videoMemesToPost = makeTextFileUniqueAndReturnIt("videomemestopost.txt");

    foreach ($videoMemesToPost as $key => $value) {
        if (in_array($value, $idsUsedInThisCompilation)) {
            echo "\nid used in compilation: ".$value;
            unset($videoMemesToPost[$key]);
        }
    }
    $videoMemesToPost = array_values($videoMemesToPost);
    $randomKeys = array_rand($videoMemesToPost, count($idsUsedInThisCompilation));
    //  printArray($randomKeys, "random keys");
    shuffle($randomKeys);
    foreach ($randomKeys as $key => $valueRandomKey) {
        array_splice($videoMemesToPost, $valueRandomKey, 0, $idsUsedInThisCompilation[$key]);
    }
    //printArray($videoMemesToPost, "new video memes to post after randomly insterted used memes into array");
    replaceTextFileWithArray("videomemestopost.txt", $videoMemesToPost);
}

function isBetweenTimeLimits($from, $till, $input)
{
    $f = DateTime::createFromFormat('!H:i', $from);
    $t = DateTime::createFromFormat('!H:i', $till);
    $i = DateTime::createFromFormat('!H:i', $input);
    if ($f > $t) {
        $t->modify('+1 day');
    }
    return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
}
