<?php
require __DIR__ . '/vendor/autoload.php';

use Jenssegers\Blade\Blade;
use Illuminate\Support\MessageBag;

// --------------------------------------------------------------------------
// MOCK LARAVEL HELPERS
// --------------------------------------------------------------------------

if (!function_exists('auth')) {
    class MockUser {
        public $id = 1;
        public $name = 'Demo User';
        public $role = 'admin';
        public $email = 'admin@store.com';
        public $profile_photo_url = 'https://ui-avatars.com/api/?name=Demo+User&background=0D8ABC&color=fff';

        public function isAdmin() { return true; }
        public function isSeller() { return true; }
        public function isBuyer() { return true; }
    }
    class MockAuth {
        public function user() { return new MockUser(); }
        public function check() { return true; }
        public function id() { return 1; }
    }
    function auth() { return new MockAuth(); }
}

if (!function_exists('route')) {
    function route($name, $params = []) {
        // Return a dummy URL mapping. 
        // For a perfectly working app, we'd need a full route map.
        // For this demo, we can assume routes map to view paths.
        if ($name == 'logout') return '#?action=logout';
        if ($name == 'profile.edit') return '/profile/edit';
        
        // Convert k.b to k/b
        return '/' . str_replace('.', '/', $name);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() { return 'mock_token_123'; }
}

if (!function_exists('csrf_field')) {
    function csrf_field() { return '<input type="hidden" name="_token" value="mock_token_123">'; }
}

if (!function_exists('method_field')) {
    function method_field($method) { return '<input type="hidden" name="_method" value="'.$method.'">'; }
}

if (!function_exists('session')) {
    class MockSession {
        public function has($key) { return false; }
        public function get($key, $default=null) { return $default; }
    }
    function session($key = null) {
        if ($key) return null;
        return new MockSession();
    }
}

if (!function_exists('now')) {
    function now() { return new DateTime(); }
}

if (!function_exists('url')) {
    function url($path = null) { return $path ? '/'.ltrim($path, '/') : '/'; }
}

if (!function_exists('asset')) {
    function asset($path) { return '/'.ltrim($path, '/'); }
}

if (!function_exists('old')) {
    function old($key, $default = null) { return $default; }
}

// --------------------------------------------------------------------------
// BLADE SETUP
// --------------------------------------------------------------------------

$views = __DIR__ . '/mystorefrontend';
$cache = __DIR__ . '/cache';

if (!is_dir($cache)) {
    @mkdir($cache, 0777, true);
}

// Initialize Blade
$blade = new Blade($views, $cache);

// REGISTER .php AS BLADE TEMPLATE
// This is critical because the existing files are .php but contain Blade syntax
$blade->view()->addExtension('php', 'blade');

// --------------------------------------------------------------------------
// ROUTER
// --------------------------------------------------------------------------

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Default Route
if ($uri === '/' || $uri === '/index.php') {
    $viewName = 'layouts.index';
} else {
    // Convert URI to view path
    // e.g. /profile/edit -> profile.edit
    $path = ltrim($uri, '/');
    $path = str_replace('.php', '', $path);
    $viewName = str_replace('/', '.', $path);
    
    // Add 'index' if directory
    if (is_dir($views . '/' . $path)) {
        $viewName .= '.index';
    }
}

// Data to share with views (Simulating Controller Data)
$data = [
    'stats' => [
        'total_users' => 125,
        'total_products' => 84,
        'sellers' => 12,
        'today_revenue' => 12500,
        'new_today' => 4
    ],
    'sellerStats' => [
        'count' => 15,
        'total_value' => 45000,
        'low_stock' => 2
    ],
    'sellerProducts' => [
        (object)['id'=>1, 'name'=>'Wireless Headphones', 'price'=>2999, 'stock'=>50, 'updated_at'=>new DateTime('-1 day')],
        (object)['id'=>2, 'name'=>'Smart Watch', 'price'=>4500, 'stock'=>12, 'updated_at'=>new DateTime('-2 hours')],
        (object)['id'=>3, 'name'=>'Gaming Mouse', 'price'=>1200, 'stock'=>4, 'updated_at'=>new DateTime('-5 mins')],
    ],
    'errors' => new MessageBag(),
];

// Check if view exists and render
if ($blade->exists($viewName)) {
    try {
        echo $blade->make($viewName, $data)->render();
    } catch (Exception $e) {
        // Fallback for debugging
        echo "<div style='padding:20px;background:#fee;border:1px solid red;color:red;'>";
        echo "<h3>View Rendering Error</h3>";
        echo "<p>Could not render view: <strong>$viewName</strong></p>";
        echo "<pre>" . $e->getMessage() . "</pre>";
        echo "</div>";
    }
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>The view '{$viewName}' could not be found via mapping.</p>";
}
