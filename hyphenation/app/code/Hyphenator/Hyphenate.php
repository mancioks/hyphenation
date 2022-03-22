<?php

namespace Hyphenator;

use Data\Pattern;
use Data\Word;

class Hyphenate
{
    public function __construct()
    {
        $patternObject = new Pattern();
        $patternObject->setFile(PROJECT_ROOT_DIR."/var/pattern.txt");
        $pattern = $patternObject->getAllPatterns();

        $wordsObject = new Word();
        $wordsObject->setFile(PROJECT_ROOT_DIR."/var/words.txt");
        $wordsObject->setPartMaxLen($patternObject->getMaxLen());
        $wordsObject->setPartMinLen($patternObject->getMinLen());
        $words = $wordsObject->getAllWords();

        var_dump($this->hyphenate($words, $pattern));
    }

    private function mergeWordWithPattern($wordObject, $foundPattern)
    {
        $word = str_split($wordObject->getWord());

        $length = count($word);
        for($x = $length; $x >= 0; $x--) {
            array_splice( $word, $x, 0, 0 );
        }

        foreach ($foundPattern as $pattern) {
            $startsFrom = $pattern->getStartIndex() * 2;

            $refactoredPattern = str_split(trim($pattern->getPattern(), "."));
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

        return str_replace("0", "", implode("", $word));
    }

    private function hyphenate($words, $pattern)
    {
        /**
         * @var \Data\Word $word
         */
        $hyphenated = [];
        foreach ($words as $word) {
            $found = $this->findPartsInPattern($word->getParts(), $pattern);
            $merged = $this->mergeWordWithPattern($word, $found);
            $hyphenated[] = $this->hyphenateFromMerged($merged);
        }

        return $hyphenated;
    }

    private function hyphenateFromMerged($merged)
    {
        return trim(str_replace([1,3,5,7,9], "-", str_replace([2,4,6,8], "", $merged)), "-");
    }

    private function findPartsInPattern($splitParts, $patternObject)
    {
        $found = [];

        /**
         * @var \Data\Part $part
         */
        foreach ($splitParts as $part) {

            /**
             * @var \Data\Pattern $pattern
             */
            foreach ($patternObject as $pattern) {
                if($part->getPart() == $pattern->getPlainPattern() && $part->getType() == $pattern->getType()) {
                    $foundPattern = $pattern;
                    $foundPattern->setStartIndex($part->getStartIndex());

                    $found[] = $foundPattern;
                }
            }
        }

        return $found;
    }
}