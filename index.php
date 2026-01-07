<?php
require __DIR__ . '/vendor/autoload.php';

use Jenssegers\Blade\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Carbon\Carbon;

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
        // Common route mappings
        $routes = [
            'logout' => '#?action=logout',
            'profile.edit' => '/profile/edit',
            'login' => '/login',
            'register' => '/register',
            'admin.login' => '/admin-login',
            'cart.index' => '/cart',
            'checkout.index' => '/checkout',
            'orders.index' => '/orders',
            'products.index' => '/products',
            'home' => '/',
            'dashboard' => '/dashboard',
        ];
        
        // Return mapped route if exists
        if (isset($routes[$name])) {
            return $routes[$name];
        }
        
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

if (!function_exists('request')) {
    class MockRequest {
        public function __construct() {
            $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            $this->query = $_GET;
            $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        }
        
        public function routeIs($routeName) {
            // Simple route matching based on current URI
            $currentPath = trim($this->uri, '/');
            
            // Map route names to paths
            $routeMap = [
                'products.index' => 'products',
                'about' => 'about',
                'contact' => 'contact',
                'cart.index' => 'cart',
                'checkout.index' => 'checkout',
                'orders.index' => 'orders',
                'login' => 'login',
                'register' => 'register',
            ];
            
            $expectedPath = $routeMap[$routeName] ?? str_replace('.', '/', $routeName);
            return $currentPath === $expectedPath || $currentPath === 'auth/' . $expectedPath;
        }
        
        public function get($key, $default = null) {
            return $this->query[$key] ?? $default;
        }
        
        public function has($key) {
            return isset($this->query[$key]);
        }
        
        public function except($keys) {
            $keys = is_array($keys) ? $keys : [$keys];
            return array_diff_key($this->query, array_flip($keys));
        }
        
        public function only($keys) {
            $keys = is_array($keys) ? $keys : [$keys];
            return array_intersect_key($this->query, array_flip($keys));
        }
        
        public function all() {
            return $this->query;
        }
    }
    
    function request() {
        static $request = null;
        if ($request === null) {
            $request = new MockRequest();
        }
        return $request;
    }
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
$blade->addExtension('php', 'blade');

// --------------------------------------------------------------------------
// ROUTER
// --------------------------------------------------------------------------

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Route Aliases - Map friendly URLs to actual view paths
$routeAliases = [
    '/login' => 'auth.login',
    '/register' => 'auth.register',
    '/admin-login' => 'auth.admin-login',
    '/forgot-password' => 'auth.forgot-password',
    '/cart' => 'cart.index',
    '/checkout' => 'checkout.index',
    '/orders' => 'orders.index',
    '/dashboard' => 'layouts.index',
];

// Default Route
if ($uri === '/' || $uri === '/index.php') {
    $viewName = 'layouts.index';
} elseif (isset($routeAliases[$uri])) {
    // Use alias if defined
    $viewName = $routeAliases[$uri];
} else {
    // Convert URI to view path
    // e.g. /profile/edit -> profile.edit
    $path = ltrim($uri, '/');
    $path = str_replace('.php', '', $path);
    $viewName = str_replace('/', '.', $path);
    
    // Check if it's a directory with an index file
    $dirPath = $views . '/' . str_replace('.', '/', $viewName);
    $filePath = $views . '/' . str_replace('.', '/', $viewName) . '.php';
    
    if (is_dir($dirPath) && file_exists($dirPath . '/index.php')) {
        $viewName .= '.index';
    } elseif (!file_exists($filePath)) {
        // If direct file doesn't exist, try adding .index
        if (file_exists($dirPath . '.php')) {
            // File exists as-is, keep viewName
        } elseif (is_dir($dirPath) && file_exists($dirPath . '/index.php')) {
            $viewName .= '.index';
        }
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
        (object)['id'=>1, 'name'=>'Wireless Headphones', 'price'=>2999, 'stock'=>50, 'updated_at'=>Carbon::now()->subDay()],
        (object)['id'=>2, 'name'=>'Smart Watch', 'price'=>4500, 'stock'=>12, 'updated_at'=>Carbon::now()->subHours(2)],
        (object)['id'=>3, 'name'=>'Gaming Mouse', 'price'=>1200, 'stock'=>4, 'updated_at'=>Carbon::now()->subMinutes(5)],
    ],
    // Customer-facing product data
    'products' => [
        (object)[
            'id'=>1, 
            'name'=>'Wireless Headphones', 
            'description'=>'Premium noise-cancelling wireless headphones with 30-hour battery life',
            'price'=>2999, 
            'stock'=>50, 
            'category'=>'Electronics',
            'image'=>'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400',
            'created_at'=>Carbon::now()->subDays(10)
        ],
        (object)[
            'id'=>2, 
            'name'=>'Smart Watch', 
            'description'=>'Fitness tracking smartwatch with heart rate monitor and GPS',
            'price'=>4500, 
            'stock'=>12, 
            'category'=>'Electronics',
            'image'=>'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400',
            'created_at'=>Carbon::now()->subDays(8)
        ],
        (object)[
            'id'=>3, 
            'name'=>'Gaming Mouse', 
            'description'=>'High-precision gaming mouse with RGB lighting and programmable buttons',
            'price'=>1200, 
            'stock'=>25, 
            'category'=>'Electronics',
            'image'=>'https://images.unsplash.com/photo-1527814050087-3793815479db?w=400',
            'created_at'=>Carbon::now()->subDays(5)
        ],
        (object)[
            'id'=>4, 
            'name'=>'Laptop Backpack', 
            'description'=>'Durable laptop backpack with multiple compartments and USB charging port',
            'price'=>1899, 
            'stock'=>30, 
            'category'=>'Accessories',
            'image'=>'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400',
            'created_at'=>Carbon::now()->subDays(3)
        ],
        (object)[
            'id'=>5, 
            'name'=>'Mechanical Keyboard', 
            'description'=>'RGB mechanical keyboard with blue switches for gaming and typing',
            'price'=>3499, 
            'stock'=>18, 
            'category'=>'Electronics',
            'image'=>'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=400',
            'created_at'=>Carbon::now()->subDays(2)
        ],
        (object)[
            'id'=>6, 
            'name'=>'Portable Charger', 
            'description'=>'20000mAh portable power bank with fast charging support',
            'price'=>999, 
            'stock'=>45, 
            'category'=>'Accessories',
            'image'=>'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400',
            'created_at'=>Carbon::now()->subDay()
        ],
    ],
    'categories' => [
        (object)['id'=>1, 'name'=>'Electronics', 'slug'=>'electronics'],
        (object)['id'=>2, 'name'=>'Accessories', 'slug'=>'accessories'],
        (object)['id'=>3, 'name'=>'Fashion', 'slug'=>'fashion'],
        (object)['id'=>4, 'name'=>'Home & Living', 'slug'=>'home-living'],
    ],
    'cart' => [
        'items' => [],
        'total' => 0,
        'count' => 0
    ],
    'errors' => new ViewErrorBag(),
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
