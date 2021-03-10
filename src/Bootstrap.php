<?php

namespace App;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use ErrorException;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RedBeanPHP\R;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Bootstrap
{
    public static UrlGenerator $routeGenerator;

    public function run()
    {
        define('ROOT', realpath(__DIR__ . '/../'));

        $this->loadEnv();

        $this->registerErrorHandlers();

        ini_set('date.timezone', 'Europe/Riga');
        ini_set('post_max_size', '30M');
        ini_set('upload_max_filesize', '30M');

        $sessionPath = realpath(__DIR__ . '/../storage/sessions');

        if (!is_dir($sessionPath)) {
            die("Session directory missing");
        }

        ini_set('session.save_path', $sessionPath);
        ini_set('session.gc_maxlifetime', 30 * 24 * 3600);
        session_set_cookie_params(30 * 24 * 3600);
        session_start();

        require __DIR__ . '/functions.php';

        /** @var RouteCollection $routes */
        $routes = require __DIR__ . '/routes.php';

        $routes->addPrefix(ltrim(getenv('URL_PREFIX'), '/'));

        $routeContext = new RequestContext();

        self::$routeGenerator = new UrlGenerator($routes, $routeContext);

        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        try {
            $parameters = (new UrlMatcher($routes, $routeContext))->match($url);
        } catch (ResourceNotFoundException $e) {
            return 'Page not found';
        }

        R::setup('sqlite:' . __DIR__ . '/../storage/database.sqlite');

        return (new Controller())->call($parameters);
    }

    private function loadEnv()
    {
        try {
            Dotenv::create(__DIR__ . '/../', '.env')->load();
        } catch (InvalidPathException $e) {
            die('Environment file is not set.');
        }
    }

    private function registerErrorHandlers()
    {
        error_reporting(E_ALL | ~E_NOTICE);

        set_error_handler(
            function ($severity, $message, $file, $line) {
                throw new ErrorException($message, 0, $severity, $file, $line);
            }
        );

        if (getenv('DEBUG') === 'true') {
            ini_set('display_errors', 1);
            (new Run())->appendHandler(new PrettyPageHandler())->register();
        } else {
            ini_set('display_errors', 0);
        }

        $log = new Logger('app');

        /** @noinspection PhpUnhandledExceptionInspection */
        $log->pushHandler(new StreamHandler(__DIR__ . '/../storage/app.log', Logger::ERROR));

        ErrorHandler::register($log);
    }
}