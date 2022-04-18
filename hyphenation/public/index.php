<?php

require '../config.php';
require PROJECT_ROOT_DIR . '/vendor/autoload.php';

$request = $_SERVER["PATH_INFO"] ?? "/";
$request = trim($request, "/");

$request = explode("/", $request);

if (!empty($request[0])) {
    if(class_exists("\Controller\\" . $request[0])) {
        $class = ucfirst($request[0]);
        if(isset($request[1])) {
            if(method_exists("\Controller\\" . $class, $request[1])) {
                $method = $request[1];
                if(isset($request[2])) {
                    $param = $request[2];
                }
            } else {
                $class = "Error";
                $method = "Index";
            }
        } else {
            $method = "Index";
        }
    } else {
        $class = "Error";
        $method = "Index";
    }
} else {
    $class = "Main";
    $method = "Index";
}

$class = "\Controller\\" . $class;
$classObj = new $class;

if(isset($param)) {
    $classObj->$method($param);
} else {
    $classObj->$method();
}