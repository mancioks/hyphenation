<?php

$start_time = microtime(true);

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
                $found[] = ["start_index" => $part["start_index"], "pattern" => trim($pattern[$type][$part["part"]], ".")];
            }
        }
    }

    return $found;
}

function mergeWordWithPattern($word, $foundPattern)
{
    $word = str_split($word);
    $length = count($word);
    for($x = $length; $x >= 0; $x--) {
        array_splice( $word, $x, 0, 0 );
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

        $tempCount = 0;
        for($x = $startsFrom; $x < $startsFrom + count($newPattern); $x+=2) {
            if($newPattern[$tempCount] > $word[$x]) {
                $word[$x] = $newPattern[$tempCount];
            }
            $tempCount+=2;
        }
    }

    //return implode("", $word);
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

$words = new SplFileObject('words.txt');
$words->setFlags(
    SplFileObject::SKIP_EMPTY |
    SplFileObject::DROP_NEW_LINE
);

foreach ($words as $word) {
    $parts = splitWord($word,$maxLen, $minLen);
    $found = findPartsInPattern($parts, $pattern);
    $merged = mergeWordWithPattern($word, $found);
    $hyphenated = hyphenateFromMerged($merged);

    echo $hyphenated."<br>";
}



$end_time = microtime(true);
$execution_time = ($end_time - $start_time);
echo "\n<br>Execution time of script = ".$execution_time." sec<br>\n";