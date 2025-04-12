<?php
require __DIR__.'/../vendor/autoload.php';

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

// Verify .env is working (optional debug check)
if (!getenv('DB_CONNECTION')) {
    die('Error: .env file not loaded or DB_CONNECTION missing!');
}

// =============================================
// 1. Database Setup (Eloquent)
// =============================================
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection(require __DIR__.'/../config/database.php');
$capsule->setAsGlobal();
$capsule->bootEloquent();

// =============================================
// 2. View Setup (Blade Templating)
// =============================================
use Illuminate\View\Factory;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\FileViewFinder;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;

// Create necessary instances
$filesystem = new Filesystem();
$dispatcher = new Dispatcher();

// Configure template engines
$engineResolver = new EngineResolver();

// Register PHP engine (for plain PHP templates)
$engineResolver->register('php', function() use ($filesystem) {
    return new PhpEngine($filesystem); // Filesystem is required
});

// Register Blade engine
$bladeCompiler = new BladeCompiler(
    $filesystem,
    __DIR__.'/../storage/views/cache'  // Cache directory for compiled views
);

$engineResolver->register('blade', function() use ($bladeCompiler) {
    return new CompilerEngine($bladeCompiler);
});

// Set up the view finder
$viewFinder = new FileViewFinder($filesystem, [__DIR__.'/../app/views']);

// Create the view factory
$viewFactory = new Factory($engineResolver, $viewFinder, $dispatcher);

// Share the view factory instance for dependency injection
$container = new Illuminate\Container\Container();
$container->instance(Factory::class, $viewFactory);

// =============================================
// 3. Routing Setup
// =============================================
use Illuminate\Routing\Router;

$router = new Router(new Dispatcher(), $container);
require __DIR__.'/../routes/web.php';

// =============================================
// 4. Run the Application
// =============================================
$request = Illuminate\Http\Request::capture();
$response = $router->dispatch($request);
$response->send();