<?php


namespace Hyperf\Nano;


use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ExceptionHandlerFactory
{
    public static function create(\Closure $closure) {
        return new class($closure) extends ExceptionHandler {
            /**
             * @var \Closure
             */
            private $closure;

            public function __construct(\Closure $closure)
            {
                $this->closure = $closure;
            }

            public function handle(Throwable $throwable, ResponseInterface $response)
            {
                return call($this->closure, [$throwable, $response]);
            }

            public function isValid(Throwable $throwable): bool
            {
                return true;
            }
        };
    }
}