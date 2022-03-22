<?php

namespace Data;

class Part
{
    private string $part;
    private int $startIndex;
    private int $type;

    public const STARTS_WITH = 0;
    public const ENDS_WITH = 1;
    public const EVERYWHERE = 2;

    /**
     * @return string
     */
    public function getPart(): string
    {
        return $this->part;
    }

    /**
     * @param string $part
     */
    public function setPart(string $part): void
    {
        $this->part = $part;
    }

    /**
     * @return int
     */
    public function getStartIndex(): int
    {
        return $this->startIndex;
    }

    /**
     * @param int $startIndex
     */
    public function setStartIndex(int $startIndex): void
    {
        $this->startIndex = $startIndex;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public static function generateParts($word, $minLen, $maxLen) {
        $parts = [];

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

                $partObject = new Part();

                $partObject->setPart($part);
                $partObject->setStartIndex($x);

                if ($x == 0) {
                    $partObject->setType(Part::STARTS_WITH);
                    $parts[] = $partObject;
                }
                if ($len == $y && $x >= $endStart) {
                    $partObject->setType(Part::ENDS_WITH);
                    $parts[] = $partObject;
                }

                $partObject = new Part();
                $partObject->setPart($part);
                $partObject->setStartIndex($x);
                $partObject->setType(Part::EVERYWHERE);
                $parts[] = $partObject;
            }
        }

        return $parts;
    }
}