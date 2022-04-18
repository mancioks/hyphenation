<?php

namespace Controller;

use Core\BaseController;
use Data\Pattern;
use Helper\Database;
use Hyphenator\Hyphenate;
use Hyphenator\HyphenateTest;

class Api extends BaseController
{
    private $word;
    private $header;
    private $data;

    public function words($word = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $paths = explode("/", trim($_SERVER['REQUEST_URI'], "/"));
        array_shift($paths);

        if(method_exists($this, $method)) {
            $this->word = $word;
            $this->data = [];
            $this->{strtolower($method)}();
            header('HTTP/1.1 '.$this->header);
            echo json_encode($this->data);
        } else {
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET, PUT, POST, DELETE');
        }
    }

    private function get()
    {
        if($this->word) {
            $hyphenated = new HyphenateTest();
            if($hyphenated->loadFromDb($this->word)) {
                $patterns = [];
                foreach ($hyphenated->getFoundPatterns() as $pattern) {
                    $patterns[] = $pattern->getPattern();
                }
                $this->data[] = ["hyphenated" => $hyphenated->getHyphenated()];
                $this->data[] = ["patterns" => $patterns];
                $this->header = "200 OK";
            } else {
                $this->header = "404 Not Found";
            }
        } else {
            $db = new Database();
            $db->query("SELECT * FROM hyphenated");
            $data = $db->getAll();

            $words = [];

            foreach ($data as $hyphenatedDb) {
                $hyphenated = new HyphenateTest();
                $hyphenated->loadFromDb($hyphenatedDb["word"]);

                $word = [];
                $word["word"] = $hyphenated->getWord();
                $word["hyphenated"] = $hyphenated->getHyphenated();

                $patterns = [];
                foreach ($hyphenated->getFoundPatterns() as $pattern) {
                    $patterns[] = $pattern->getPattern();
                }
                $word["patterns"] = $patterns;

                $words[] = $word;
            }

            $this->data[] = $words;

            $this->header = "200 OK";
        }
    }

    private function post()
    {
        if($this->word) {
            if(!HyphenateTest::existsInDb($this->word)) {
                $hyphenate = new HyphenateTest();
                $hyphenate->hyphenate($this->word, Pattern::getAllPatternsFromDb());
                $hyphenate->insertIntoDb();

                $patterns = [];
                foreach ($hyphenate->getFoundPatterns() as $pattern) {
                    $patterns[] = $pattern->getPattern();
                }
                $this->data[] = ["hyphenated" => $hyphenate->getHyphenated()];
                $this->data[] = ["patterns" => $patterns];
            } else {
                $this->header = "409 Conflict";
            }
        } else {
            // error
            $this->header = "400 Bad Request";
        }
    }

    private function put()
    {
        if($this->word) {
            if(HyphenateTest::existsInDb($this->word)) {
                HyphenateTest::deleteFromDb($this->word);

                $hyphenate = new HyphenateTest();
                $hyphenate->hyphenate($this->word, Pattern::getAllPatternsFromDb());
                $hyphenate->insertIntoDb();

                $patterns = [];
                foreach ($hyphenate->getFoundPatterns() as $pattern) {
                    $patterns[] = $pattern->getPattern();
                }
                $this->data[] = ["hyphenated" => $hyphenate->getHyphenated()];
                $this->data[] = ["patterns" => $patterns];

                $this->header = "200 OK";
            } else {
                $this->header = "404 Not Found";
            }
        } else {
            // error
            $this->header = "400 Bad Request";
        }
    }
    private function delete()
    {
        if($this->word) {
            if(HyphenateTest::existsInDb($this->word)) {
                HyphenateTest::deleteFromDb($this->word);
                $this->header = "200 OK";
                $this->data[] = ["status" => "ok"];
            } else {
                $this->header = "404 Not Found";
            }
            // delete word
        } else {
            // error
            $this->header = "400 Bad Request";
        }
    }
}