<?php


namespace Hyperf\Nano;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Exception\Exception;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__.'/../vendor/autoload.php';

$app = AppFactory::create();
$app->get('/', function (){
    $request = Context::get(ServerRequestInterface::class);
    return $request->getMethod();
});
$app->addMiddleware(function($request, $handler) {
    throw new Exception('bug');
    return $res->json(['message'=>'ret']);
});
$app->addExceptionHandler(function(){
    $res = $this->get(ResponseInterface::class);
    return $res->json(['message'=>'ex']);
});
$app->addListener(AfterWorkerStart::class, function(){
   $this->get(StdoutLoggerInterface::class)->info('starting');
});
$app->run();
