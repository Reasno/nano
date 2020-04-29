<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Nano\Factory;

use Hyperf\Config\Config;
use Hyperf\Config\ProviderConfig;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Definition\ScanConfig;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Nano\App;
use Hyperf\Nano\BoundInterface;
use Hyperf\Nano\ContainerProxy;
use Hyperf\Utils\ApplicationContext;
use Psr\Log\LogLevel;

class AppFactory
{
    public static function create(string $host = '0.0.0.0', int $port = 9501, $hookFlags = SWOOLE_HOOK_ALL)
    {
        // Setting ini and flags
        ini_set('display_errors', 'on');
        ini_set('display_startup_errors', 'on');
        error_reporting(E_ALL);
        $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        $projectRootPath = dirname($reflection->getFileName(), 3);
        ! defined('BASE_PATH') && define('BASE_PATH', $projectRootPath);
        ! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', $hookFlags);

        // Prepare container
        $container = self::parepareContainer($host, $port);
        ApplicationContext::setContainer($container);

        return new App($container);
    }

    public static function parepareContainer(string $host, int $port): ContainerInterface
    {
        $config = new Config(ProviderConfig::load());
        $config->set('server', include __DIR__ . '/../Preset/Server.php');
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
            ],
        ]);
        $container = new Container(new DefinitionSource($config->get('dependencies'), new ScanConfig()));
        $container->set(ConfigInterface::class, $config);
        $container->define(DispatcherFactory::class, DispatcherFactory::class);
        $container->define(BoundInterface::class, ContainerProxy::class);
        return $container;
    }
}
