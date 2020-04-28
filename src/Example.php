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

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->get('/', function () {
    /** @var ContainerProxy $this */
    $name = $this->request->getQueryParams()['name'] ?? 'nano';
    return $this->response->json([
        'message' => "hello {$name}",
        'method' => $this->request->getMethod()
    ]);
});
$app->run();
