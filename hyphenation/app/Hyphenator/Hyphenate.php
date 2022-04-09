<?php

namespace Hyphenator;

use Core\BaseController;
use Data\Pattern;
use Data\Word;
use Helper\DBHelper;
use Helper\StringHelper;

class Hyphenate extends BaseController
{
    private $source;
    private $mode;

    public function __construct($word = false, $source = 'file')
    {
        parent::__construct();
        $this->source = $source;

        $patternObject = new Pattern();

        if($source == 'file') {
            $patternObject->setFile(PROJECT_ROOT_DIR."/var/pattern.txt");
            $pattern = $patternObject->getAllPatterns();
        }
        if($source == 'db') {
            $pattern = $patternObject->getAllPatternsFromDb();
        }

        $wordsObject = new Word();
        if($word) {
            $this->mode = 'cli';
            $wordsObject->setWord($word);
            $words = [$wordsObject];
        } else {
            $this->mode = 'web';
            if($source == 'file') {
                $wordsObject->setFile(PROJECT_ROOT_DIR."/var/words.txt");
                $words = $wordsObject->getAllWords();
            }
            if($source == 'db') {
                $words = $wordsObject->getAllWordsFromDb();
            }
        }

        $this->hyphenate($words, $pattern);

        //var_dump($this->hyphenate($words, $pattern));
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
            //echo implode("", $newPattern)." ".$startsFrom."<br>";
            $tempCount = 0;
            for($x = $startsFrom; $x < $startsFrom + count($newPattern); $x+=2) {
                if($newPattern[$tempCount] > $word[$x]) {
                    $word[$x] = $newPattern[$tempCount];
                }
                $tempCount+=2;
            }
        }

        //echo implode("", $word)."<br>";
        return str_replace("0", "", implode("", $word));
    }

    private function hyphenate($words, $pattern)
    {
        /**
         * @var Word $word
         */
        $hyphenated = [];
        foreach ($words as $word) {
            $found = $this->findPatternsInWord($word->getWord(), $pattern);


            $merged = $this->mergeWordWithPattern($word, $found);
            //echo $merged."<br>";
            $hyphenatedText = $this->hyphenateFromMerged($merged);
            $hyphenated[] = $hyphenatedText;

            echo $hyphenatedText."\n";

            if($this->mode == 'cli' && $this->source == 'db') {
                echo "patterns found:\n";
                foreach ($found as $element) {
                    echo $element->getPattern()."\n";
                }
            }
        }
        return $hyphenated;
    }

    private function hyphenateFromMerged($merged)
    {
        return trim(str_replace([1,3,5,7,9], "-", str_replace([2,4,6,8], "", $merged)), "-");
    }

    private function findPatternsInWord($word, $patterns)
    {
        $found = [];
        $wordLen = strlen($word);

        /**
         * @var \Data\Pattern $pattern
         */
        foreach ($patterns as $pattern) {
            if($pattern->getType() == Pattern::STARTS_WITH && str_starts_with($word, $pattern->getPlainPattern())) {
                $foundPattern = $pattern;
                $foundPattern->setStartIndex(0);
                $found[] = $foundPattern;
            } elseif ($pattern->getType() == Pattern::ENDS_WITH && str_ends_with($word, $pattern->getPlainPattern())) {
                $foundPattern = $pattern;
                $foundPattern->setStartIndex($wordLen - strlen($pattern->getPlainPattern()));
                $found[] = $foundPattern;
            } elseif ($pattern->getType() == Pattern::EVERYWHERE && str_contains($word, $pattern->getPlainPattern())) {
                $foundPositions = StringHelper::strposAll($word, $pattern->getPlainPattern());
                foreach ($foundPositions as $foundPosition) {
                    $foundPattern = clone $pattern;
                    $foundPattern->setStartIndex($foundPosition);
                    $found[] = $foundPattern;
                    //cia yra klaida, kad deda rastus patternus bet visu objektu start index pakeicia o ne vieno
                    //issisprende beda su clone, kopijuoja objekta ir nebera rysio su senu tada
                    //echo $foundPattern->getPattern()." - ".$foundPosition." ";
                }
            }
        }
        //echo '<pre>';
        //var_dump($found);
        return $found;
    }
}