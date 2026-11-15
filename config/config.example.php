<?php
// Copy this file to config.php and adjust for your local MAMP/XAMPP setup.
define('DB_DRIVER', 'mysql');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '8889');        // MAMP default MySQL port
define('DB_NAME', 'taylors_webinar_hub');
define('DB_USER', 'root');
define('DB_PASS', 'root');        // MAMP default password; XAMPP is usually ''
define('DB_CHARSET', 'utf8mb4');
define('SQLITE_PATH', __DIR__ . '/../database/taylors_webinar_hub.sqlite');
define('APP_ROOT', dirname(__DIR__));
ini_set('display_errors', 1);
error_reporting(E_ALL);
