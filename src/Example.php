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
namespace Hyperf\Nano;

use Hyperf\Nano\Factory\AppFactory;
use Hyperf\DB\DB;

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
//$app->get('/', function () {
//    /** @var ContainerProxy $this */
//    $user = $this->request->input('user', 'nano');
//    $method = $this->request->getMethod();
//
//    return [
//        'message' => "hello {$user}",
//        'method' => $method,
//    ];
//});

$app->config([
    'db.default' => [
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ]
]);
$app->addRoute(['GET', 'POST'], '/', function(){
    return DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);
});
$app->run();
