<?php

namespace Data;

use Helper\Database;
use Helper\FileHelper;

class Pattern
{
    private int $id;
    private string $pattern;
    private string $plainPattern;
    private string $type;
    private int $startIndex;

    public const STARTS_WITH = 0;
    public const ENDS_WITH = 1;
    public const EVERYWHERE = 2;

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

    public static function getAllPatterns($file)
    {
        $patterns = [];

        $fileContents = FileHelper::getContents($file);

        foreach ($fileContents as $element) {
            $pattern = new Pattern();
            $pattern->setPattern($element);

            $patterns[] = $pattern;
        }

        return $patterns;
    }

    public function setPattern($element, $id = null)
    {
        $this->pattern = $element;
        $this->plainPattern = Pattern::toText($element);

        if($id) {
            $this->id = $id;
        }

        if(str_starts_with($element, ".")) {
            $this->setType(Pattern::STARTS_WITH);
        } elseif (str_ends_with($element, ".")) {
            $this->setType(Pattern::ENDS_WITH);
        } else {
            $this->setType(Pattern::EVERYWHERE);
        }
    }

    public static function getAllPatternsFromDb()
    {
        $patterns = [];

        $db = new Database();
        $db->query('SELECT * FROM patterns');

        $patternsFromDb = $db->getAll();

        foreach ($patternsFromDb as $elementDb) {
            $element = $elementDb['value'];

            $pattern = new Pattern();
            $pattern->setPattern($element, $elementDb["id"]);

            $patterns[] = $pattern;
        }

        return $patterns;
    }

    public function loadById($id)
    {
        $db = new Database();
        $db->query("SELECT * FROM patterns WHERE id = ".$id);
        $data = $db->get();

        if($data) {
            $this->setPattern($data["value"], $id);
        }

        return $this;
    }

    private static function toText($pattern)
    {
        return preg_replace('/[^a-z]+/', '', $pattern);
    }
}