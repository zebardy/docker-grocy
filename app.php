<?php

#$fp = fopen('/config/data/sql.log', 'a');
#fwrite($fp, "!!!App starting up loading\n");
#$time_start = microtime(true);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Grocy\Helpers\UrlManager;
use \Grocy\Controllers\LoginController;

// Definitions for embedded mode
if (file_exists(__DIR__ . '/embedded.txt'))
{
	define('GROCY_IS_EMBEDDED_INSTALL', true);
	define('GROCY_DATAPATH', file_get_contents(__DIR__ . '/embedded.txt'));
	define('GROCY_USER_ID', 1);
}
else
{
	define('GROCY_IS_EMBEDDED_INSTALL', false);
	define('GROCY_DATAPATH', __DIR__ . '/data');
}

// Definitions for demo mode
if (file_exists(GROCY_DATAPATH . '/demo.txt'))
{
	define('GROCY_IS_DEMO_INSTALL', true);
	if (!defined('GROCY_USER_ID'))
	{
		define('GROCY_USER_ID', 1);
	}
}
else
{
	define('GROCY_IS_DEMO_INSTALL', false);
}

#$dep_load_time_start = microtime(true);
// Load composer dependencies
require_once __DIR__ . '/vendor/autoload.php';

// Load config files
require_once GROCY_DATAPATH . '/config.php';
require_once __DIR__ . '/config-dist.php'; // For not in own config defined values we use the default ones
#$dep_load_time = round((microtime(true) - $dep_load_time_start),6);

// Definitions for disabled authentication mode
if (GROCY_DISABLE_AUTH === true)
{
	if (!defined('GROCY_USER_ID'))
	{
		define('GROCY_USER_ID', 1);
	}
}

// Setup base application
$appContainer = new \Slim\Container([
	'settings' => [
		'displayErrorDetails' => true,
		'determineRouteBeforeAppMiddleware' => true
	],
	'view' => function($container)
	{
		return new \Slim\Views\Blade(__DIR__ . '/views', GROCY_DATAPATH . '/viewcache');
	},
	'LoginControllerInstance' => function($container)
	{
		return new LoginController($container, 'grocy_session');
	},
	'UrlManager' => function($container)
	{
		return new UrlManager(GROCY_BASE_URL);
	},
	'ApiKeyHeaderName' => function($container)
	{
		return 'GROCY-API-KEY';
	}
]);

#$creation_time_start = microtime(true);
$app = new \Slim\App($appContainer);
#$app_creation_time = round((microtime(true) - $creation_time_start),6);


// Load routes from separate file
require_once __DIR__ . '/routes.php';

#fwrite($fp, "!!!App starting run\n");
#$run_time_start = microtime(true);
$app->run();
#fwrite($fp, "!!!App - Total dependency load time in seconds: " . $dep_load_time . "\n");
#fwrite($fp, "!!!App - Total app creation time in seconds: " . $app_creation_time . "\n");
#fwrite($fp, "!!!App - Total run time in seconds: " . round((microtime(true) - $run_time_start),6) . "\n");
#fwrite($fp, "!!!App - Total execution time in seconds: " . round((microtime(true) - $time_start),6) . "\n");
#fwrite($fp, "!!!APP - ini: ".print_r(ini_get_all(),TRUE)."\n");
#fwrite($fp, "!!!APP - opcache status: ".print_r(opcache_get_status(),TRUE)."\n");
#fwrite($fp, "!!!APP - opcache config: ".print_r(opcache_get_configuration(),TRUE)."\n");
#fclose($fp);

#phpinfo();
