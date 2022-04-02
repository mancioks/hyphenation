<?php

require '../config.php';
require '../app/code/Helper/DBHelper.php';

use Helper\DBHelper;

//var_dump($argv);

//echo SERVERNAME;

$db = new DBHelper();
$data = $db->select()->from("words")->get();
var_dump($data);