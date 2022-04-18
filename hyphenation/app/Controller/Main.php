<?php

namespace Controller;

use Core\BaseController;
use Data\Pattern;
use Hyphenator\HyphenateTest;

class Main extends BaseController
{
    public function index()
    {
        $this->render("main/index");
    }
    public function hyphenate($word = 'empty')
    {
        if(isset($_POST["word"])) $word = $_POST["word"];

        $source = $_POST['source'] ?? 'file';

        if($source == 'db') {
            $pattern = Pattern::getAllPatternsFromDb();
        } else {
            $pattern = Pattern::getAllPatterns('../var/pattern.txt');
        }

        $hyphenate = new HyphenateTest();

        if($source == 'db') {
            if(!$hyphenate->loadFromDb($word)) {
                $hyphenate->hyphenate($word, $pattern);
                $hyphenate->insertIntoDb();
            }
        } else {
            $hyphenate->hyphenate($word, $pattern);
        }

        $this->render("main/hyphenate", ["hyphenated" => $hyphenate->getHyphenated()]);

//        echo '<pre>';
//        var_dump($hyphenate->getFoundPatterns());
    }
}