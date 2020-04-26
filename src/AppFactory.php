<?php


namespace Hyperf\Nano;


use Composer\Factory;
use Hyperf\Config\Config;
use Hyperf\Config\ProviderConfig;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Definition\ScanConfig;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Log\LogLevel;

class AppFactory
{
    public static function create($host = '0.0.0.0', $port = 9501, $hookFlags = SWOOLE_HOOK_ALL){

        // Setting ini and flags
        ini_set('display_errors', 'on');
        ini_set('display_startup_errors', 'on');
        error_reporting(E_ALL);
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        $projectRootPath = dirname($reflection->getFileName(), 3);
        ! defined('BASE_PATH') && define('BASE_PATH', $projectRootPath);
        ! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', $hookFlags);

        // Prepare container
        $container = new Container(new DefinitionSource([], new ScanConfig()));
        $config = new Config(ProviderConfig::load());
        $config->set('server', include __DIR__.'/Server.php');
        $config->set('server.servers.0.host', $host);
        $config->set('server.servers.0.port', $port);
        $config->set(StdoutLoggerInterface::class, [
            'log_level' => [
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::DEBUG,
                LogLevel::EMERGENCY,
                LogLevel::ERROR,
                LogLevel::INFO,
                LogLevel::NOTICE,
                LogLevel::WARNING,
            ]
        ]);
        $container->set(ConfigInterface::class, $config);
        foreach ($config->get('dependencies') as $key => $value) {
            $container->define($key, $value);
        }
        $container->define(DispatcherFactory::class, DispatcherFactory::class);

        ApplicationContext::setContainer($container);
        return new App($container);
    }

}