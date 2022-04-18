<?php

require '../config.php';
require '../vendor/autoload.php';

$hyp = new \Hyphenator\HyphenateTest();
$hyp->loadFromDb("mistranslate");