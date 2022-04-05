<?php

spl_autoload_register(function ($class_name) {
    $namespace = explode('\\', $class_name);

    if(file_exists(APP_ROOT_DIR.$namespace[0]) && is_dir(APP_ROOT_DIR.$namespace[0])) {
        $loadFrom = APP_ROOT_DIR;
    } else {
        $loadFrom = PROJECT_ROOT_DIR.'/vendor/';
    }

    $path = implode('/', $namespace);
    include $loadFrom . $path . '.php';
});