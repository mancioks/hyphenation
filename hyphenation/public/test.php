<?php

require '../config.php';
require '../vendor/autoload.php';

$hyp = new \Hyphenator\Hyphenate();
$hyp->loadFromDb("mistranslate");