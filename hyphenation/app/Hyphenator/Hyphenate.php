<?php

namespace Hyphenator;

use Core\BaseController;
use Data\Database\Hyphenated;
use Data\Database\HyphenatedPatterns;
use Data\Pattern;
use Data\Word;
use Helper\Database;
use Helper\DBHelper;
use Helper\StringHelper;

class Hyphenate extends BaseController
{
    private $source;
    private $mode;
    private $type;
    private $hyphenatedWords;
    private $foundPatterns;

    private $wordsFile;
    private $patternFile;

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * @param mixed $wordsFile
     */
    public function setWordsFile($wordsFile): void
    {
        $this->wordsFile = $wordsFile;
    }

    /**
     * @param mixed $patternFile
     */
    public function setPatternFile($patternFile): void
    {
        $this->patternFile = $patternFile;
    }

    /**
     * @return mixed
     */
    public function getFoundPatterns()
    {
        return $this->foundPatterns;
    }

    /**
     * @param string $mode
     */
    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return mixed
     */
    public function getHyphenatedWords()
    {
        return $this->hyphenatedWords;
    }


    public function __construct()
    {
        parent::__construct();
        $this->source = 'file';
        $this->mode = 'web';
        $this->type = 'all';
    }

    public function hyphenate($word = false)
    {
        $patternObject = new Pattern();

        if($this->source == 'file') {
            $patternObject->setFile($this->patternFile);
            $pattern = $patternObject->getAllPatterns();
        }
        if($this->source == 'db') {
            $pattern = $patternObject->getAllPatternsFromDb();
        }

        $wordsObject = new Word();

        if($word) {
            $this->foundPatterns = [];
            $this->type = 'single';
            $wordsObject->setWord($word);
            $words = [$wordsObject];
        } else {
            $this->type = 'all';
            if($this->source == 'file') {
                $wordsObject->setFile($this->wordsFile);
                $words = $wordsObject->getAllWords();
            }
            if($this->source == 'db') {
                $words = $wordsObject->getAllWordsFromDb();
            }
        }

        $hyphenated = [];

        foreach ($words as $word) {
            if($this->source == "db" && Hyphenated::exists($word->getWord())) {
                $hyphenatedObj = new Hyphenated();
                $hyphenatedObj->loadByWord($word->getWord());
                $hyphenated[] = $hyphenatedObj->getHyphenated();

                $found = [];

                if($this->type == 'single') {
                    foreach (HyphenatedPatterns::getPatternsByWordId($hyphenatedObj->getId()) as $element) {
                        $patternObj = new Pattern();
                        $patternObj->setPattern($element->getValue());

                        $found[] = $patternObj;
                    }
                }
            } else {
                $found = $this->findPatternsInWord($word->getWord(), $pattern);

                $merged = $this->mergeWordWithPattern($word, $found);
                //echo $merged."<br>";
                $hyphenatedText = $this->hyphenateFromMerged($merged);
                $hyphenated[] = $hyphenatedText;

                if($this->source == 'db') {
                    $db = new Database();
                    $db->begin();
                    $db->query('INSERT INTO hyphenated (word, hyphenated) VALUES (?,?)');
                    $db->exec([$word->getWord(), $hyphenatedText]);
                    $wordId = $db->lastId();

                    $db->query('INSERT INTO hyphenated_patterns (hyphenated_id, pattern_id) VALUES (?,?)');

                    foreach ($found as $element) {
                        $db->exec([$wordId, $element->getId()]);
                    }

                    $db->commit();
                }
            }
            if($this->mode == 'cli' && $this->source == 'db' && $this->type == 'single') {
                foreach ($found as $element) {
                    $this->foundPatterns[] = $element;
                }
            }
        }
        $this->hyphenatedWords = $hyphenated;

        return $this;
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