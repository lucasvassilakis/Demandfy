<?php

define('APP', dirname(__FILE__));
define('URL', 'http://localhost/Demandfy');
define('APP_NAME', 'Demandfy');
define('DB_HOST', getenv('DEMANDFY_DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DEMANDFY_DB_USER') ?: 'root');
define('DB_PASS', getenv('DEMANDFY_DB_PASS') ?: '');
define('DB_NAME', getenv('DEMANDFY_DB_NAME') ?: 'framework');
define('DB_PORT', getenv('DEMANDFY_DB_PORT') ?: '3306');

const APP_VERSAO = '1.0.0';