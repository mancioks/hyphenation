<?php

namespace Data;

use Helper\FileHelper;

class Word
{
    private string $word;
    private array $parts;
    private int $partMinLen;
    private int $partMaxLen;
    private string $file;

    /**
     * @return int
     */
    public function getPartMinLen(): int
    {
        return $this->minLen;
    }

    /**
     * @param int $minLen
     */
    public function setPartMinLen(int $minLen): void
    {
        $this->minLen = $minLen;
    }

    /**
     * @return int
     */
    public function getPartMaxLen(): int
    {
        return $this->maxLen;
    }

    /**
     * @param int $maxLen
     */
    public function setPartMaxLen(int $maxLen): void
    {
        $this->maxLen = $maxLen;
    }

    /**
     * @return string
     */
    public function getWord(): string
    {
        return $this->word;
    }

    /**
     * @param string $word
     */
    public function setWord(string $word): void
    {
        $this->word = $word;
    }

    /**
     * @return array
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @param array $parts
     */
    public function setParts(array $parts): void
    {
        $this->parts = $parts;
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

    public function getAllWords()
    {
        $fileContents = FileHelper::getContents($this->file);

        $words = [];

        foreach ($fileContents as $element) {
            $word = new Word();
            $word->setWord($element);
            $word->setParts(Part::generateParts($element, $this->getPartMinLen(), $this->getPartMaxLen()));

            $words[] = $word;
        }

        return $words;
    }


}