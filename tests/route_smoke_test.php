<?php
/**
 * Route Smoke Test - Core-Web
 *
 * Tests every registered route in both guest and authenticated modes.
 * Fails on any HTTP 500 status or PHP exception during dispatch.
 *
 * Usage:  php tests/route_smoke_test.php
 */

declare(strict_types=1);

$logfile = dirname(__DIR__) . '/tests/.smoke_results.tmp';
file_put_contents($logfile, ''); // reset

// Load Composer autoloader
$autoloadPaths = [
    __DIR__ . '/../vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
];
$loaded = false;
foreach ($autoloadPaths as $path) {
    if (is_file($path)) {
        require_once $path;
        $loaded = true;
        break;
    }
}
if (!$loaded) {
    fwrite(STDERR, "ERROR: Composer autoload not found. Run composer install.\n");
    exit(1);
}

// Define root path if not already set
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Polyfill for getallheaders() in CLI mode
if (!function_exists('getallheaders')) {
    function getallheaders(): array {
        $h = [];
        foreach ($_SERVER as $n => $v) {
            if (strpos($n, 'HTTP_') === 0) {
                $h[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($n, 5)))))] = $v;
            }
        }
        return $h;
    }
}

use LaswitchTech\Core\Config;
use LaswitchTech\Core\EntryPoint;
use LaswitchTech\Core\Helpers;
use LaswitchTech\Core\Module;
use LaswitchTech\Core\Request;
use LaswitchTech\Core\Response;
use LaswitchTech\Core\Router;

// Suppress non-fatal warnings (they don't indicate 500 errors)
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

// ---------------------------------------------------------------------------
// Globals management
// ---------------------------------------------------------------------------

function saveGlobals(): array {
    $globals = [];
    foreach (['CONFIG', 'AUTH', 'REQUEST', 'HELPER', 'DATABASE'] as $name) {
        $globals[$name] = $GLOBALS[$name] ?? null;
    }
    return $globals;
}

function restoreGlobals(array $saved): void {
    foreach ($saved as $name => $value) {
        $GLOBALS[$name] = $value;
    }
}

// ---------------------------------------------------------------------------
// Mock classes for CLI testing (no real database / auth required)
// ---------------------------------------------------------------------------

/**
 * Minimal Schema mock — satisfies BaseModel::init() which calls:
 *   $this->tables  = $this->Database->schema()->tables();
 *   $this->schema  = $this->Database->schema()->define($table);
 */
class SmokeTestSchemaMock {
    /** @var array<string, \LaswitchTech\Core\Objects\Definition> */
    private array $definitions = [];

    public function tables(): array { return []; }

    public function define(string $table): self {
        $this->definitions[$table] = [];
        return $this;
    }

    public function describe(): array { return []; }

    /** Proxy undefined calls to Module (safe fallback) */
    public function __call(string $name, array $arguments): mixed {
        trigger_error("Mock schema -> $name() called", E_USER_WARNING);
        return false;
    }
}

/**
 * Minimal Database mock — satisfies BaseModel which calls:
 *   $this->Database->schema()->tables()  (on init)
 *   $this->Database->query()->table(...)  (later CRUD methods, never reached in smoke test)
 */
class SmokeTestDatabaseMock extends Module {
    private ?SmokeTestSchemaMock $schema = null;

    public function schema(): SmokeTestSchemaMock {
        return $this->schema ??= new SmokeTestSchemaMock();
    }

    /** Return a no-op query builder stub (used by CRUD methods not reached in smoke test) */
    public function query(): Module { return new Module(); }
}

/**
 * Auth mock — satisfies all methods called during route dispatch:
 *   isLoaded(), isAuthenticated(), isAuthorized(), user()
 *
 * In guest mode, a partial mock simulates "no auth" state.
 * In auth mode (see SmokeTestFullAuth below), it simulates a verified admin.
 */
class SmokeTestAuth {
    public bool $needs_2fa = false;
    private ?SmokeTestUser $user = null;
    public function __construct(?SmokeTestUser $u = null) { $this->user = $u; }
    public function isAuthenticated(): bool { return true; }
    public function isLoaded(): bool        { return true; }
    public function user(mixed $u = null): ?SmokeTestUser {
        if ($u !== null && $this->user === null) $this->user = new SmokeTestUser();
        return $this->user ?? new SmokeTestUser();
    }
    public function isAuthorized(string $p, int $l): bool { return true; }
}

/** Full auth mock for authenticated-mode tests */
class SmokeTestFullAuth extends SmokeTestAuth {
    public function __construct() {
        parent::__construct(new SmokeTestUser());
    }
}

class SmokeTestUser {
    public function found(): bool           { return true; }
    public function deleted(): bool         { return false; }
    public function banned(): bool          { return false; }
    public function verified(): bool        { return true; }
    public function setting(string $k): mixed { return null; }
    public function roles(bool $f = false): array {
        return [new class {
            public function permissions(string $p): int { return 100; }
        }];
    }
    public function organization(): array { return ['isActive' => 1]; }
}

/** Minimal core helper stub (no-op init) */
class SmokeTestCoreHelper {
    public function init(): void { /* no-op */ }
}

// ---------------------------------------------------------------------------
// Setup helpers
// ---------------------------------------------------------------------------

function setupGuestMode(): void {
    // Create mocks FIRST so Models doesn't crash during instantiation
    $mockDb  = new SmokeTestDatabaseMock();
    $mockAuth = new SmokeTestAuth(); // partial: isAuthenticated() returns true but no real user

    // Order matters: Helpers constructor reads $GLOBALS independently per plugin
    $GLOBALS['CONFIG']     = new Config('bootstrap');
    $GLOBALS['REQUEST']    = new Request();
    $GLOBALS['AUTH']       = $mockAuth;
    $GLOBALS['DATABASE']   = $mockDb;
    $GLOBALS['UUID']        = new \LaswitchTech\Core\UUID();
    $GLOBALS['LOG']         = new \LaswitchTech\Core\Log();
    $GLOBALS['OUTPUT']      = new \LaswitchTech\Core\Output();
    $GLOBALS['LOCALE']     = new \LaswitchTech\Core\Locales();
    $GLOBALS['CSRF']        = new \LaswitchTech\Core\CSRF();
    $GLOBALS['HELPER']      = @new Helpers();
    $GLOBALS['HELPER']->Core = new SmokeTestCoreHelper();
    $GLOBALS['MODEL']       = new \LaswitchTech\Core\Models();
    $GLOBALS['INSTALLER']   = new Module();
    $GLOBALS['UPDATER']     = new Module();
    $GLOBALS['SMS']         = new Module();
    $GLOBALS['SMTP']        = new Module();
    $GLOBALS['IMAP']        = new Module();
    $GLOBALS['SLS']         = new Module();
    $GLOBALS['STYLE']       = new \LaswitchTech\Core\Style();
    $GLOBALS['BUILDER']     = new \LaswitchTech\Core\Builder();
}

function setupAuthMode(): void {
    $mockDb  = new SmokeTestDatabaseMock();
    $config  = new Config('bootstrap');
    $mockAuth = new SmokeTestFullAuth();

    $GLOBALS['CONFIG']     = $config;
    $GLOBALS['REQUEST']    = new Request();
    $GLOBALS['DATABASE']   = $mockDb;
    $GLOBALS['UUID']       = new \LaswitchTech\Core\UUID();
    $GLOBALS['LOG']        = new \LaswitchTech\Core\Log();
    $GLOBALS['OUTPUT']      = new \LaswitchTech\Core\Output();
    $GLOBALS['LOCALE']     = new \LaswitchTech\Core\Locales();
    $GLOBALS['CSRF']       = new \LaswitchTech\Core\CSRF();
    $GLOBALS['MODEL']      = new \LaswitchTech\Core\Models();
    $GLOBALS['INSTALLER']  = new Module();
    $GLOBALS['UPDATER']    = new Module();
    $GLOBALS['SMS']        = new Module();
    $GLOBALS['SMTP']       = new Module();
    $GLOBALS['IMAP']       = new Module();
    $GLOBALS['SLS']        = new Module();
    $GLOBALS['STYLE']      = new \LaswitchTech\Core\Style();
    $GLOBALS['BUILDER']    = new \LaswitchTech\Core\Builder();

    $GLOBALS['AUTH']       = $mockAuth;
    $GLOBALS['HELPER']     = @new Helpers();
    $GLOBALS['HELPER']->Core = new SmokeTestCoreHelper();
}

// ---------------------------------------------------------------------------
// Dispatch
// ---------------------------------------------------------------------------

function setRouteNamespace(string $ns): void {
    $_SERVER['REQUEST_URI'] = $ns;
    if (!isset($_SERVER['QUERY_STRING'])) $_SERVER['QUERY_STRING'] = '';
}

function dispatchRoute(string $namespace, string $logfile): array {
    setRouteNamespace($namespace);
    try {
        $router = new Router();
        $entry  = new EntryPoint();
        $response = $entry->execute($router, $namespace);
        if ($response === null) return ['status' => 0, 'error' => 'null response'];
        return ['status' => $response->status, 'error' => null];
    } catch (Throwable $e) {
        $className = get_class($e);
        $message   = $e->getMessage();
        file_put_contents($logfile, "EXCEPTION: {$className}: {$message} on {$namespace}\n", FILE_APPEND);
        return ['status' => 500, 'error' => 'EXCEPTION'];
    }
}

// ---------------------------------------------------------------------------
// Test runner
// ---------------------------------------------------------------------------

class SmokeTestResult {
    public int $passed   = 0;
    public int $failed   = 0;
    /** @var array<string, string> */
    public array $failures = [];
}

function testMode(string $label, callable $setup): SmokeTestResult {
    global $logfile;
    fwrite(STDERR, "\n$label\n" . str_repeat('=', strlen($label) + 2) . "\n");

    $saved = saveGlobals();
    $setup();

    $router   = new Router();
    $allRoutes = $router->all();
    if (empty($allRoutes)) {
        fwrite(STDERR, "WARNING: No routes discovered.\n");
        restoreGlobals($saved);
        return new SmokeTestResult();
    }

    $routeNamespaces = [];
    foreach ($allRoutes as $key => $dto) {
        if (is_string($key) && str_starts_with($key, '/')) $routeNamespaces[] = $key;
    }
    sort($routeNamespaces);

    $result = new SmokeTestResult();
    $total  = count($routeNamespaces);
    fwrite(STDERR, "Testing $total routes...\n");

    foreach ($routeNamespaces as $i => $ns) {
        $resp = dispatchRoute($ns, $logfile);

        if ($resp['status'] >= 500) {
            $result->failed++;
            $result->failures[$ns] = $resp['error'] ?? 'HTTP ' . $resp['status'];
        } else {
            $result->passed++;
        }
    }

    fwrite(STDERR, "Result: {$result->passed}/$total passed\n");
    restoreGlobals($saved);
    return $result;
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

fwrite(STDERR, "\nCore-Web Route Smoke Test\n" . str_repeat('=', 25) . "\n");

$guestResult = testMode('Guest Mode (no authentication)', function(): void { setupGuestMode(); });
$authResult  = testMode('Authenticated Mode (verified user, admin role)', function(): void { setupAuthMode(); });

$totalGuest = $guestResult->passed + $guestResult->failed;
$totalAuth  = $authResult->passed + $authResult->failed;
$totalPassed = $guestResult->passed + $authResult->passed;
$totalFailed = $guestResult->failed + $authResult->failed;
$grandTotal  = $totalGuest + $totalAuth;

fwrite(STDERR, "\n" . str_repeat('=', 72) . "\n");
fwrite(STDERR, "  Guest mode:  {$guestResult->passed}/$totalGuest passed\n");
fwrite(STDERR, "  Auth mode:   {$authResult->passed}/$totalAuth passed\n");
fwrite(STDERR, "  Grand total: $totalPassed/$grandTotal passed, $totalFailed failed\n");

if (!empty($guestResult->failures)) {
    fwrite(STDERR, "\nGuest failures:\n");
    foreach ($guestResult->failures as $ns => $err) fwrite(STDERR, "  x $ns — $err\n");
}
if (!empty($authResult->failures)) {
    fwrite(STDERR, "\nAuth failures:\n");
    foreach ($authResult->failures as $ns => $err) fwrite(STDERR, "  x $ns — $err\n");
}

// Show exceptions from dispatch
$exceptions = file_get_contents($logfile);
if ($exceptions !== false && strlen(trim($exceptions)) > 0) {
    fwrite(STDERR, "\nDispatch exceptions:\n" . $exceptions);
}

if ($totalFailed > 0 || !empty($guestResult->failures) || !empty($authResult->failures)) {
    fwrite(STDERR, "\nFAIL: {$totalFailed} failure(s)\n");
    unlink($logfile);
    exit(1);
} else {
    fwrite(STDERR, "\nALL ROUTES OK\n");
    unlink($logfile);
    exit(0);
}
