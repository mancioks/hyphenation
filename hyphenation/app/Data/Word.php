<?php

namespace Data;

use Helper\FileHelper;

class Word
{
    private string $word;
    private string $file;

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

            $words[] = $word;
        }

        return $words;
    }


}