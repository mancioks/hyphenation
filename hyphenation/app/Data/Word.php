<?php

namespace Data;

use Core\BaseController;
use Helper\Database;
use Helper\FileHelper;

class Word extends BaseController
{
    private int $id;
    private string $word;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function getAllWords()
    {
        $fileContents = FileHelper::getContents($this->file);

        $words = [];

        foreach ($fileContents as $element) {
            $word = new Word();
            $word->setWord($element);

            $words[] = $word;

            $this->logger->info("Word found: ".$element);
        }

        return $words;
    }

    public function getAllWordsFromDb()
    {
        $db = new Database();
        $db->query('SELECT * FROM words');

        $wordsFromDb = $db->getAll();

        $words = [];

        foreach ($wordsFromDb as $elementDb) {
            $element = $elementDb['value'];
            $word = new Word();
            $word->setId($elementDb['id']);
            $word->setWord($element);

            $words[] = $word;

            $this->logger->info("Word found: ".$element);
        }

        return $words;
    }


}