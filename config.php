<?php

// Database connection information
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'commentwall');
define('DB_PASSWORD', 'commentwall');
define('DB_NAME', 'commentwall');
define('DB_PORT', null);

// Set the default timezone
if(ini_get('date.timezone') == null) {
    date_default_timezone_set('America/Denver');
}

require_once('index.php');
