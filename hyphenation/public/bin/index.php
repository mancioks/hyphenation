<?php

const DEBUG = false;

$fileName = 'words.txt';
if(DEBUG) {
    $fileName = 'word.txt';
    echo "<pre>";
}

$maxLen = 0;
$minLen = 500;

$patterns = new SplFileObject('patterns.txt');
$patterns->setFlags(
    SplFileObject::SKIP_EMPTY |
    SplFileObject::DROP_NEW_LINE
);

$pattern["starts_with"] = [];
$pattern["everywhere"] = [];
$pattern["ends_with"] = [];

function splitWord($word, $maxLen, $minLen)
{
    $parts["starts_with"] = [];
    $parts["everywhere"] = [];
    $parts["ends_with"] = [];

    $wordLen = strlen($word);
    $endStart = $wordLen - $maxLen;

    for ($x = 0; $x <= ($wordLen-$minLen); $x++) {
        $len = $maxLen;
        $thisMaxLen = $wordLen - $x;

        if ($len > $thisMaxLen) {
            $len = $thisMaxLen;
        }
        for ($y = $minLen; $y <= $len; $y++) {
            $part = substr($word, $x, $y);

            if ($x == 0) {
                $parts["starts_with"][] = ["start_index" => $x, "part" => $part];
            }
            if ($len == $y && $x >= $endStart) {
                $parts["ends_with"][] = ["start_index" => $x, "part" => $part];
            }
            $parts["everywhere"][] = ["start_index" => $x, "part" => $part];

            /*if ($x == 0) {
                $parts["starts_with"][] = ["start_index" => $x, "part" => $part];
                //zodzio pradzios negalima manau det prie tikrinimu su everywhere (is patterno)
            } elseif ($len == $y && $x >= $endStart) {
                $parts["ends_with"][] = ["start_index" => $x, "part" => $part];
                $parts["everywhere"][] = ["start_index" => $x, "part" => $part];
            } else {
                $parts["everywhere"][] = ["start_index" => $x, "part" => $part];
            }*/
        }
    }

    return $parts;
}

function findPartsInPattern($splitParts, $pattern)
{
    $found = [];
    foreach ($splitParts as $type => $parts) {
        foreach ($parts as $part) {
            if(isset($pattern[$type][$part["part"]])) {
                if(DEBUG) {
                    echo $pattern[$type][$part["part"]]."<br>";
                }
                $found[] = ["start_index" => $part["start_index"], "pattern" => trim($pattern[$type][$part["part"]], ".")];
            }
        }
    }

    return $found;
}

function mergeWordWithPattern($word, $foundPattern, $debugPattern = null)
{
    $word = str_split($word);
    $length = count($word);
    for($x = $length; $x >= 0; $x--) {
        array_splice( $word, $x, 0, 0 );
    }

    if(DEBUG) {
        echo "<br>".str_replace("0", " ", implode("", $word))."<br>";
    }

    foreach ($foundPattern as $pattern) {
        $startsFrom = $pattern["start_index"] * 2;

        $refactoredPattern = str_split($pattern["pattern"]);
        $newPattern = [];

        $beforeWas = null;

        foreach ($refactoredPattern as $symbol) {
            if($beforeWas) {
                if($beforeWas == "char") {
                    if(is_numeric($symbol)) {
                        $newPattern[] = $symbol;
                        $beforeWas = "num";
                    } else {
                        $newPattern[] = 0;
                        $newPattern[] = $symbol;
                        $beforeWas = "char";
                    }
                } else {
                    $newPattern[] = $symbol;
                    $beforeWas = "char";
                }
            } else {
                $newPattern[] = $symbol;
                is_numeric($symbol) ? $beforeWas = "num" : $beforeWas = "char";
            }
        }

        if(!is_numeric($newPattern[0])) {
            array_splice( $newPattern, 0, 0, 0 );
        }
        if(!is_numeric(end($newPattern))) {
            $newPattern[] = 0;
        }

        if(DEBUG) {
            $k = 0;
            for($l = 0; $l < count($word); $l++) {
                if($l >= $startsFrom && $l < ($startsFrom + count($newPattern))) {
                    if($newPattern[$k] != 0 && is_numeric($newPattern[$k])) {
                        echo $newPattern[$k];
                    } else {
                        echo " ";
                    }
                    $k++;
                } else {
                    echo " ";
                }
            }

            //gali reiket perdaryt pattern arrays kartu su skaiciais
            echo " ".$pattern["pattern"]."<br>";
        }

        $tempCount = 0;
        for($x = $startsFrom; $x < $startsFrom + count($newPattern); $x+=2) {
            if($newPattern[$tempCount] > $word[$x]) {
                $word[$x] = $newPattern[$tempCount];
            }
            $tempCount+=2;
        }
    }

    if(DEBUG) {
        echo str_replace("0", " ", implode("", $word))."<br>";
        echo str_replace([1,3,5,7,9], "-", str_replace([0,2,4,6,8], " ", implode("", $word)))."<br>";
        echo "<br>".implode("", $word)."<br>";
        echo str_replace("0", "", implode("", $word))."<br>";
    }
    return str_replace("0", "", implode("", $word));
}

function patternToText($pattern)
{
    return preg_replace('/[^a-z]+/', '', $pattern);
}

foreach ($patterns as $line) {
    $text = patternToText($line);

    strlen($text) > $maxLen ? $maxLen = strlen($text):null;
    strlen($text) < $minLen ? $minLen = strlen($text):null;

    if(str_starts_with($line, ".")) {
        $pattern["starts_with"][$text] = $line;
    } elseif (str_ends_with($line, ".")) {
        $pattern["ends_with"][$text] = $line;
    } else {
        $pattern["everywhere"][$text] = $line;
    }
}

function hyphenateFromMerged($merged)
{
    //return $merged;
    return trim(str_replace([1,3,5,7,9], "-", str_replace([2,4,6,8], "", $merged)), "-");
}

$words = new SplFileObject($fileName);
$words->setFlags(
    SplFileObject::SKIP_EMPTY |
    SplFileObject::DROP_NEW_LINE
);

foreach ($words as $word) {
    $parts = splitWord($word,$maxLen, $minLen);
    $found = findPartsInPattern($parts, $pattern);

    if(!DEBUG) {
        $merged = mergeWordWithPattern($word, $found);
    } else {
        $merged = mergeWordWithPattern($word, $found, $pattern);
    }

    $hyphenated = hyphenateFromMerged($merged);

    echo $hyphenated."<br>";
}