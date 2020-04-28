# nano

Scaling Hyperf down to a single file.

## Example

That's all you need.

```php
<?php
// index.php
use Hyperf\Nano\ContainerProxy;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->get('/', function () {
    /** @var ContainerProxy $this */
    $user = $this->request->input('user', 'nano');
    $method = $this->request->getMethod();

    return [
        'message' => "hello {$user}",
        'method' => $method,
    ];
});
$app->run();
```

Run
```bash
php index.php start
```

## Feature
* No skeleton.
* Fast startup.
* Zero config.
* Closure style.
* Support all Hyperf features except annotations.
* Compatible with all Hyperf components.

## More Examples

### DI container 
```php
<?php
use Hyperf\Nano\ContainerProxy;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->getContainer()->set(Foo::class, new Foo());
$app->get('/', function () {
    /** @var ContainerProxy $this */
    $foo = $this->get(Foo::class);
    return $foo->bar();
});
$app->run();
```
> $this are bind to ContainerProxy in all closures provided by nano, including middleware, exception handler and more.
### Middleware
```php
<?php
use Hyperf\Nano\ContainerProxy;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->getContainer()->set(Foo::class, new Foo());
$app->get('/', function () {
    /** @var ContainerProxy $this */
    return $this->request->getAttribute('key');
});
$app->addMiddleware(function ($request, $handler) {
    $request = $request->withAttribute('key', 'value');
    return $handler->handle($request);
});
$app->run();
```

### ExceptionHandler

```php
<?php
use Hyperf\Nano\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->getContainer()->set(Foo::class, new Foo());
$app->get('/', function () {
    throw new \Exception();
});
$app->addExceptionHandler(function ($throwable, ResponseInterface $response) {
    return $response->withStatus('403', 'not allowed');
});
$app->run();
```

### Custom Command

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addCommand('echo', function(){
    $this->get(StdoutLoggerInterface::class)->info('A new command called echo!');
});
$app->run();
```

To run this command, execute
```bash
php index.php echo
```

### Event Listener
```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addListener(BootApplication::class, function($event){
    $this->get(StdoutLoggerInterface::class)->info('App started');
});
$app->run();
```

### Custom Process
```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addProcess(function(){
    while (true) {
        sleep(1);
        $this->get(StdoutLoggerInterface::class)->info('Processing...');
    }
});
$app->run();
```

### Crontab

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nano\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->addCrontab('* * * * * *', function(){
    $this->get(StdoutLoggerInterface::class)->info('execute every second!');
});
$app->run();
```