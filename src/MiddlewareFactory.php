<?php


namespace Hyperf\Nano;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareFactory
{
    public static function create(\Closure $closure) {
        return new class($closure) implements MiddlewareInterface {
            /**
             * @var \Closure
             */
            private $closure;

            public function __construct(\Closure $closure)
            {
                $this->closure = $closure;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return call($this->closure, [$request, $handler]);
            }
        };
    }
}