<?php

namespace Data;

use Helper\FileHelper;

class Pattern
{
    private string $pattern;
    private string $plainPattern;
    private string $type;
    private string $file;
    private int $startIndex;

    public const STARTS_WITH = 0;
    public const ENDS_WITH = 1;
    public const EVERYWHERE = 2;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getPlainPattern(): string
    {
        return $this->plainPattern;
    }

    /**
     * @param string $plainPattern
     */
    public function setPlainPattern(string $plainPattern): void
    {
        $this->plainPattern = $plainPattern;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     */
    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
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

    public function getAllPatterns()
    {
        $patterns = [];

        $fileContents = FileHelper::getContents($this->file);

        foreach ($fileContents as $element) {
            $plainText = Pattern::toText($element);

            $pattern = new Pattern();

            $pattern->setPattern($element);
            $pattern->setPlainPattern($plainText);

            if(str_starts_with($element, ".")) {
                $pattern->setType(Pattern::STARTS_WITH);
            } elseif (str_ends_with($element, ".")) {
                $pattern->setType(Pattern::ENDS_WITH);
            } else {
                $pattern->setType(Pattern::EVERYWHERE);
            }

            $patterns[] = $pattern;
        }

        return $patterns;
    }

    private static function toText($pattern)
    {
        return preg_replace('/[^a-z]+/', '', $pattern);
    }

    public function getMinLen(): int
    {
        $minLen = 500;

        $fileContents = FileHelper::getContents($this->file);

        foreach ($fileContents as $element) {
            $text = Pattern::toText($element);
            if(strlen($text) < $minLen) $minLen = strlen($text);
        }

        return $minLen;
    }

    public function getMaxLen(): int
    {
        $maxLen = 0;

        $fileContents = FileHelper::getContents($this->file);

        foreach ($fileContents as $element) {
            $text = Pattern::toText($element);
            if(strlen($text) > $maxLen) $maxLen = strlen($text);
        }

        return $maxLen;
    }
}