<?php
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

error_reporting(E_ALL);

// Define path to application directory
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('IS_API') || define('IS_API', false);
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));

define('APPLICATION_PRIVATE', realpath(dirname(__FILE__) . '/../private'));
define('APPLICATION_TMP', realpath(dirname(__FILE__) . '/../temporary'));
define('APPLICATION_WEB', realpath(dirname(__FILE__)));
define('_SERVICE_ACCESS', true);
define('OPEN_ENV', !file_exists(APPLICATION_WEB . '/.offline'));

date_default_timezone_set('Europe/Warsaw');

set_include_path(implode(PATH_SEPARATOR, [
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/models'),
    ROOT_PATH,
    // get_include_path(),
]));

require __DIR__ . '/../vendor/autoload.php';

if (OPEN_ENV === false && @!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '193.105.35.130'])) {
    header('HTTP/1.1 503 Service Unavailable');
    echo file_get_contents(realpath(dirname(__FILE__)) . '/offline.html');

    return;
}

// --- CLI or WWW
$script_name = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_BASENAME);
if ($script_name === 'zf') {
    define('CLI', true);
} else {
    define('CLI', false);
}

// Define application environment
if ( isset($_COOKIE['Cec6hi3piu5Du5iezhoh3aithohrae7ziph2']) || CLI) {
    define('APPLICATION_ENV', 'development');
    define('DEBUG', true);

    $whoops = new Run;
    $whoops->pushHandler(new PrettyPageHandler);
    $whoops->register();
} else {
    define('APPLICATION_ENV', 'production');
    define('DEBUG', false);

    register_shutdown_function('shutdown');
}

if (DEBUG && isset($_COOKIE['Cec6hi3piu5Du5iezhoh3aithohrae7ziph2_nocache']) || CLI) {
    define('CACHE_USE', false);
} else {
    define('CACHE_USE', true);
}

if (DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

/** Zend_Application */
include_once APPLICATION_PATH . '/settings/config.php';

$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/settings/application.ini');
Zend_Registry::set('application', $application);

if (!isset($appBootstrap)) {
    $appBootstrap = null;
}

if (!defined('CLI') || CLI === false) {
    if (APPLICATION_ENV !== 'development') {
        try {
            $application->bootstrap($appBootstrap);
            !IS_API && $application->run();
        } catch (Exception $e) {
            header('HTTP/1.1 503 Service Unavailable');
            echo file_get_contents(APPLICATION_WEB . '/503.html');
            exit();
        }
    } else {
        $application->bootstrap($appBootstrap);
        !IS_API && $application->run();
    }
}

/**
 * Shutdown function
 * write error to file
 */
function shutdown()
{
    // get all errors
    $error = error_get_last();
    // if fatala error & parse error
    if ($error) {
        $error_file = APPLICATION_TMP . '/logs/log_' . $error['type'] . '.log';

        // set content of error file
        $content = 'Date: ' . date('Y-m-d H:i:s') . PHP_EOL;
        $content .= 'Message: ' . $error['message'] . PHP_EOL;
        $content .= 'File: ' . $error['file'] . PHP_EOL;
        $content .= 'Line: ' . $error['line'] . PHP_EOL;
        $content .= '-----------------------------------' . PHP_EOL;

        // write content to file
        $file = fopen($error_file, 'a+');
        fwrite($file, $content);

        if ($error['type'] == E_ERROR || $error['type'] == E_PARSE) {
            ob_clean();
            // show 503
            header('HTTP/1.1 503 Service Unavailable');
            echo file_get_contents(APPLICATION_WEB . '/503.html');
        }
    }
}
