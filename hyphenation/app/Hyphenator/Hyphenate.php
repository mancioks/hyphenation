<?php

namespace Hyphenator;

use Core\BaseController;
use Data\Pattern;
use Helper\Database;
use Helper\StringHelper;

class Hyphenate extends BaseController
{
    private $hyphenated;
    private $foundPatterns;
    private $word;

    public function getHyphenated()
    {
        return $this->hyphenated;
    }

    public function getFoundPatterns()
    {
        return $this->foundPatterns;
    }

    public function getWord()
    {
        return $this->word;
    }

    public function hyphenate($word, $pattern)
    {
        $this->word = $word;
        $found = $this->findPatternsInWord($word, $pattern);
        $merged = $this->mergeWordWithPattern($word, $found);
        $hyphenatedText = $this->hyphenateFromMerged($merged);

        $this->foundPatterns = $found;
        $this->hyphenated = $hyphenatedText;
    }

    private function mergeWordWithPattern($word, $foundPattern)
    {
        $word = str_split($word);

        $length = count($word);
        for ($x = $length; $x >= 0; $x--) {
            array_splice($word, $x, 0, 0);
        }

        foreach ($foundPattern as $pattern) {
            $startsFrom = $pattern->getStartIndex() * 2;

            $refactoredPattern = str_split(trim($pattern->getPattern(), "."));
            $newPattern = [];

            $beforeWas = null;

            foreach ($refactoredPattern as $symbol) {
                if ($beforeWas) {
                    if ($beforeWas == "char") {
                        if (is_numeric($symbol)) {
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

            if (!is_numeric($newPattern[0])) {
                array_splice($newPattern, 0, 0, 0);
            }
            if (!is_numeric(end($newPattern))) {
                $newPattern[] = 0;
            }
            //echo implode("", $newPattern)." ".$startsFrom."<br>";
            $tempCount = 0;
            for ($x = $startsFrom; $x < $startsFrom + count($newPattern); $x += 2) {
                if ($newPattern[$tempCount] > $word[$x]) {
                    $word[$x] = $newPattern[$tempCount];
                }
                $tempCount += 2;
            }
        }

        //echo implode("", $word)."<br>";
        return str_replace("0", "", implode("", $word));
    }

    private function hyphenateFromMerged($merged)
    {
        return trim(str_replace([1, 3, 5, 7, 9], "-", str_replace([2, 4, 6, 8], "", $merged)), "-");
    }

    private function findPatternsInWord($word, $patterns)
    {
        $found = [];
        $wordLen = strlen($word);

        /**
         * @var Pattern $pattern
         */
        foreach ($patterns as $pattern) {
            if ($pattern->getType() == Pattern::STARTS_WITH && str_starts_with($word, $pattern->getPlainPattern())) {
                $foundPattern = clone $pattern;
                $foundPattern->setStartIndex(0);
                $found[] = $foundPattern;
            } elseif ($pattern->getType() == Pattern::ENDS_WITH && str_ends_with($word, $pattern->getPlainPattern())) {
                $foundPattern = clone $pattern;
                $foundPattern->setStartIndex($wordLen - strlen($pattern->getPlainPattern()));
                $found[] = $foundPattern;
            } elseif ($pattern->getType() == Pattern::EVERYWHERE && str_contains($word, $pattern->getPlainPattern())) {
                $foundPositions = StringHelper::strposAll($word, $pattern->getPlainPattern());
                foreach ($foundPositions as $foundPosition) {
                    $foundPattern = clone $pattern;
                    $foundPattern->setStartIndex($foundPosition);
                    $found[] = $foundPattern;
                }
            }
        }

        return $found;
    }

    public function loadFromDb($word)
    {
        $db = new Database();
        $db->query("SELECT * FROM hyphenated WHERE word = '".$word."'");
        $hyphenated = $db->get();
        if($hyphenated) {
            $this->word = $word;
            $this->hyphenated = $hyphenated["hyphenated"];

            $db = new Database();
            $db->query("SELECT * FROM hyphenated_patterns WHERE hyphenated_id = '".$hyphenated["id"]."'");
            $patterns = $db->getAll();

            $foundPatterns = [];

            foreach ($patterns as $element) {
                $pattern = new Pattern();
                $pattern->loadById($element["pattern_id"]);

                $foundPatterns[] = $pattern;
            }

            $this->foundPatterns = $foundPatterns;
        } else {
            return false;
        }

        return $this;
    }

    public function insertIntoDb()
    {
        if($this->hyphenated) {
            $db = new Database();
            $db->begin();
            $db->query('INSERT INTO hyphenated (word, hyphenated) VALUES (?,?)');
            $db->exec([$this->word, $this->hyphenated]);
            $wordId = $db->lastId();

            $db->query('INSERT INTO hyphenated_patterns (hyphenated_id, pattern_id) VALUES (?,?)');

            foreach ($this->foundPatterns as $element) {
                $db->exec([$wordId, $element->getId()]);
            }

            $db->commit();
        }

        return $this;
    }

    public static function existsInDb($word)
    {
        $db = new Database();
        $db->query("SELECT * FROM hyphenated WHERE word = '".$word."'");
        $hyphenated = $db->get();
        if($hyphenated) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteFromDb($word)
    {
        $db = new Database();
        $db->query("SELECT * FROM hyphenated WHERE word = '".$word."'");
        $data = $db->get();

        if($data) {
            $db->query("DELETE FROM hyphenated_patterns WHERE hyphenated_id = ".$data["id"]);
            $db->exec();
            $db->query("DELETE FROM hyphenated WHERE id = ".$data["id"]);
            $db->exec();
        }
    }
}