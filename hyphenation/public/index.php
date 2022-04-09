<?php


require '../config.php';
require PROJECT_ROOT_DIR.'/vendor/autoload.php';

use \Helper\Database;

$start_time = microtime(true);

//echo "<pre>";

new \Helper\Database();
new \Hyphenator\Hyphenate(false, 'db');

$end_time = microtime(true);

$execution_time = ($end_time - $start_time);
echo "<br><br>Execution time of script = ".$execution_time." sec<br>\n";