<?php
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '3000');
set_time_limit(3000);

define('PROJECT_ROOT_DIR', __DIR__);
define('APP_ROOT_DIR', PROJECT_ROOT_DIR.'/app/');

define('SERVERNAME', 'mariadb');
define('DB_NAME', 'hyphenation');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('SERVER_PORT', 3306);

define('SERVERNAME_CLI', "localhost");
define('SERVERPORT_CLI', '3306');