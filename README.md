nano

Scaling Hyperf down to a single file.

## Example

That's all you need.

```php
<?php
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->get('/{name}', function ($name) {
    return $this->response->json([
        'hello' => $name,
        'method' => $this->request->getMethod()
    ]);
});
$app->run();
```

## Feature
* No skeleton.
* Fast startup.
* Zero config.
* Closure style.
* Support all Hyperf features except annotations.
* Compatible with all Hyperf components.

