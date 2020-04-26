<?php


namespace Hyperf\Nano;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @method addRoute($httpMethod, string $route, $handler, array $options = [])
 * @method addGroup($prefix, callable $callback, array $options = [])
 * @method get($route, $handler, array $options = [])
 * @method post($route, $handler, array $options = [])
 * @method put($route, $handler, array $options = [])
 * @method delete($route, $handler, array $options = [])
 * @method patch($route, $handler, array $options = [])
 * @method head($route, $handler, array $options = [])
 */
class App
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var DispatcherFactory
     */
    protected $dispatcherFactory;

    private $serverName = 'http';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $this->container->get(ConfigInterface::class);
        $this->dispatcherFactory = $this->container->get(DispatcherFactory::class);
    }

    /**
     * Run the application
     */
    public function run()
    {
        $application = $this->container->get(\Hyperf\Contract\ApplicationInterface::class);
        $application->run();
    }

    /**
     * Config the application using arrays
     * @param array $configs
     */
    public function config(array $configs)
    {
        foreach ($configs as $key => $value) {
            $this->config->set($key, $value);
        }
    }

    /**
     * Set a value in the DI container
     * @param string $identifier
     * @param mixed $instance
     */
    public function set(string $identifier, $instance)
    {
        if ($this->container instanceof \Hyperf\Contract\ContainerInterface){
            $this->container->set($identifier, $instance);
        }
        throw new \InvalidArgumentException("set method is supported when container implements \\Hyperf\\Contract\\ContainerInterface");
    }

    /**
     * Add a middleware globally
     * @param MiddlewareInterface|string|\Closure $middleware
     */
    public function addMiddleware($middleware)
    {
        if ($middleware instanceof MiddlewareInterface|| is_string($middleware)){
            $this->appendConfig('middlewares.'.$this->serverName, $middleware);
            return;
        }

        if ($middleware instanceof \Closure){
            $this->appendConfig(
                'middlewares.'.$this->serverName,
                MiddlewareFactory::create($middleware->bindTo($this->container))
            );
            return;
        }

        throw new \InvalidArgumentException("not a valid middleware");
    }

    /**
     * Add an exception handler globally
     * @param string|\Closure $middleware
     */
    public function addExceptionHandler($exceptionHandler)
    {
        if (is_string($exceptionHandler)){
            $this->appendConfig('exceptions.handler'.$this->serverName, $exceptionHandler);
            return;
        }

        if ($exceptionHandler instanceof \Closure){
            $handler = ExceptionHandlerFactory::create($exceptionHandler->bindTo($this->container));
            $handlerId = spl_object_hash($handler);
            $this->container->set($handlerId, $handler);
            $this->appendConfig(
                'exceptions.handler.'.$this->serverName,
                $handlerId
            );
            return;
        }

        throw new \InvalidArgumentException("not a valid exception handler");
    }

    /**
     * Add an exception handler globally
     * @param string|\Closure|null $listener
     */
    public function addListener(string $event, $listener = null, int $priority = 1)
    {
        if ($listener === null){
            $listener = $event;
        }

        if (is_string($listener)){
            $this->appendConfig('listeners', $listener);
            return;
        }

        if ($listener instanceof \Closure){
            $listener = $listener->bindTo($this->container);
            $provider = $this->container->get(ListenerProviderInterface::class);
            $provider->on($event, $listener, $priority);
            return;
        }

        throw new \InvalidArgumentException("not a valid exception handler");
    }

    private function appendConfig(string $key, $configValues)
    {
        $configs = $this->config->get($key, []);
        array_push($configs, $configValues);
        $this->config->set($key, $configs);
    }

    /**
     * Define a value in the DI container
     * @param string $identifier
     * @param string $className
     */
    public function define(string $identifier, string $className)
    {
        if ($this->container instanceof \Hyperf\Contract\ContainerInterface) {
            $this->container->define($identifier, $className);
        }
        throw new \InvalidArgumentException("define method is supported when container implements \\Hyperf\\Contract\\ContainerInterface");
    }

    public function __call($name, $arguments)
    {
        $router = $this->dispatcherFactory->getRouter($this->serverName);
        foreach ($arguments as &$argument){
            if ($argument instanceof \Closure){
                $argument->bindTo($this->container);
            }
        }
        return $router->{$name}(...$arguments);
    }

    public function addServer(string $serverName, callable $callback)
    {
        $this->serverName = $serverName;
        call($callback, [$this]);
        $this->serverName = 'http';
    }
}