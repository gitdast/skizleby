<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require __DIR__ . '/.maintenance.php';
define('WWW_DIR', __DIR__);

// absolute filesystem path to the application root
define('APP_DIR', WWW_DIR . '/../app');

// absolute filesystem path to the libraries
define('LIBS_DIR', WWW_DIR . '/../libs');

// absolute filesystem path to the temporary files
define('TEMP_DIR', WWW_DIR . '/../temp');

// absolute filesystem path to the log files
define('LOG_DIR', WWW_DIR . '/../log/' . date('Y-m'));
// Create log subdir
if (!is_dir(LOG_DIR)) mkdir(LOG_DIR); 

$container = require __DIR__ . '/../app/bootstrap.php';

$container->getByType('Nette\Application\Application')->run();
