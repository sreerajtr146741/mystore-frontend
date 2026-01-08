<?php
require __DIR__ . '/vendor/autoload.php';

use Jenssegers\Blade\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Carbon\Carbon;

// --------------------------------------------------------------------------
// 1. ROBUST MOCK HELPERS (Simulate Laravel)
// --------------------------------------------------------------------------

// --- Mock Paginator for $products->links() and $products->count() ---
if (!class_exists('MockPaginator')) {
    class MockPaginator implements Countable, IteratorAggregate, ArrayAccess {
        protected $items;
        protected $perPage;
        protected $currentPage;
        
        public function __construct($items, $perPage = 15, $currentPage = 1) {
            $this->items = collect($items);
            $this->perPage = $perPage;
            $this->currentPage = $currentPage;
        }
        // Countable
        public function count(): int { return $this->items->count(); }
        // IteratorAggregate
        public function getIterator(): Traversable { return $this->items->getIterator(); }
        // ArrayAccess
        public function offsetExists($offset): bool { return isset($this->items[$offset]); }
        public function offsetGet($offset): mixed { return $this->items[$offset]; }
        public function offsetSet($offset, $value): void { $this->items[$offset] = $value; }
        public function offsetUnset($offset): void { unset($this->items[$offset]); }
        
        // Laravel Paginator Methods
        public function hasMorePages() { return false; }
        public function nextPageUrl() { return '#'; }
        public function previousPageUrl() { return '#'; }
        public function firstItem() { return 1; }
        public function lastItem() { return $this->items->count(); }
        public function total() { return $this->items->count(); }
        public function links() { return ''; } // Return empty string for now
        public function appends($key) { return $this; }
    }
}

// --- Mock Auth ---
if (!function_exists('auth')) {
    class MockUser {
        public $id = 1;
        public $name = 'Demo User';
        public $role = 'admin'; // Change to 'buyer' or 'seller' to test matches
        public $email = 'admin@store.com';
        public $profile_photo_url = 'https://ui-avatars.com/api/?name=Demo+User&background=0D8ABC&color=fff';

        public function isAdmin() { return $this->role === 'admin'; }
        public function isSeller() { return $this->role === 'seller' || $this->role === 'admin'; }
        public function isBuyer() { return true; }
    }
    class MockAuth {
        public function user() { return new MockUser(); }
        public function check() { return true; } // Always logged in for demo
        public function id() { return 1; }
    }
    function auth() { return new MockAuth(); }
}

// --- Mock Request ---
if (!function_exists('request')) {
    class MockRequest {
        public $query;
        public function __construct() { $this->query = $_GET; }
        public function routeIs($pattern) {
            // Simple imitation: Matches if current view name contains pattern
            global $currentViewName; 
            return strpos($currentViewName, $pattern) !== false;
        }
        public function get($key, $default=null) { return $this->query[$key] ?? $default; }
        public function input($key, $default=null) { return $this->get($key, $default); }
        public function all() { return $this->query; }
        public function has($key) { return isset($this->query[$key]); }
        public function except($keys) {
            $keys = is_array($keys) ? $keys : [$keys];
            return array_diff_key($this->query, array_flip($keys));
        }
        public function only($keys) {
            $keys = is_array($keys) ? $keys : [$keys];
            return array_intersect_key($this->query, array_flip($keys));
        }
    }
    function request($key = null, $default = null) {
        static $req;
        if (!$req) $req = new MockRequest();
        if ($key) return $req->get($key, $default);
        return $req;
    }
}

// --- Mock Route ---
if (!function_exists('route')) {
    function route($name, $params = []) {
        $routes = [
            'home' => '/',
            'products.index' => '/products',
            'product.show' => '/products/show?id=' . ($params['id'] ?? 1),
            'cart.index' => '/cart',
            'checkout.index' => '/checkout',
            'checkout.single' => '/checkout?id=' . ($params[0] ?? 1),
            'orders.index' => '/orders',
            'orders.show' => '/orders/show?id=' . ($params[0] ?? 1),
            'login' => '/login',
            'register' => '/register',
            'logout' => '/?logout=1',
            'profile.edit' => '/profile/edit',
            'about' => '/about',
            'contact' => '/contact',
            // Admin
            'admin.dashboard' => '/dashboard',
            'admin.products.list' => '/admin/products/manage',
            'admin.products.create' => '/admin/products/create',
            'admin.products.edit' => '/admin/products/edit?id=' . ($params['id'] ?? 1),
            'admin.users' => '/admin/users',
            'admin.orders' => '/admin/orders',
            'admin.messages.index' => '/admin/messages',
            'admin.revenue' => '/admin/revenue',
            'admin.discounts.global.edit' => '/admin/discounts/global',
        ];
        
        $path = $routes[$name] ?? '/' . str_replace('.', '/', $name);
        if (!empty($params) && is_array($params)) {
            // Append params if not already in URL
            if (strpos($path, '?') === false) {
                $path .= '?' . http_build_query($params);
            }
        }
        return $path;
    }
}

// --- Other Helpers ---
if (!function_exists('csrf_token')) { function csrf_token() { return 'mock_token_123'; } }
if (!function_exists('csrf_field')) { function csrf_field() { return '<input type="hidden" name="_token" value="mock">'; } }
if (!function_exists('method_field')) { function method_field($m) { return '<input type="hidden" name="_method" value="'.$m.'">'; } }
if (!function_exists('asset')) { function asset($path) { return '/'.ltrim($path, '/'); } }
if (!function_exists('url')) { function url($path = '') { return '/'.ltrim($path, '/'); } }
if (!function_exists('old')) { function old($k, $d=null) { return $d; } }
if (!function_exists('session')) { 
    function session($key = null, $default = null) {
        // Mock Session Data
        $mockSession = [
            'cart' => [
                1 => ['name' => 'Wireless Headphones', 'price' => 2999, 'qty' => 1, 'image' => null, 'category' => 'Electronics'],
                2 => ['name' => 'Smart Watch', 'price' => 4500, 'qty' => 2, 'image' => null, 'category' => 'Wearables'],
            ],
            'success' => null, // 'Operation successful!',
            'error' => null,
        ];
        if ($key === null) return new class($mockSession) {
            private $data; 
            public function __construct($d){ $this->data = $d; }
            public function has($k){ return isset($this->data[$k]); }
            public function get($k, $def=null){ return $this->data[$k] ?? $def; }
            public function forget($k){}
        };
        return $mockSession[$key] ?? $default;
    } 
}
if (!function_exists('now')) { function now() { return Carbon::now(); } }


// --------------------------------------------------------------------------
// 2. DATA GENERATION
// --------------------------------------------------------------------------

function getGlobalData() {
    $products = collect([
        (object)['id'=>1, 'name'=>'Wireless Headphones', 'price'=>2999, 'stock'=>50, 'category'=>'Electronics', 'image'=>'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400', 'description'=>'Noise cancelling'],
        (object)['id'=>2, 'name'=>'Smart Watch', 'price'=>4500, 'stock'=>12, 'category'=>'Electronics', 'image'=>'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400', 'description'=>'Fitness tracker'],
        (object)['id'=>3, 'name'=>'Gaming Mouse', 'price'=>1200, 'stock'=>25, 'category'=>'Electronics', 'image'=>'https://images.unsplash.com/photo-1527814050087-3793815479db?w=400', 'description'=>'RGB lighting'],
        (object)['id'=>4, 'name'=>'Laptop Backpack', 'price'=>1899, 'stock'=>30, 'category'=>'Accessories', 'image'=>'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400', 'description'=>'Waterproof'],
        (object)['id'=>5, 'name'=>'Mechanical Keyboard', 'price'=>3499, 'stock'=>10, 'category'=>'Electronics', 'image'=>'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=400', 'description'=>'Blue switches'],
    ]);

    // Admin Stats
    $stats = [
        'total_users' => 1250,
        'total_products' => 84,
        'new_today' => 12,
        'total_revenue' => 154000,
        'suspended_users' => 5,
        'blocked_users' => 2,
    ];

    $userStats = [
        'buyers' => 1100,
        'sellers' => 50,
        'admins' => 5,
        'new_today' => 12,
        'active_30d' => 850
    ];

    $adminExtras = [
        'pending_orders' => 5,
        'pending_messages' => 3
    ];

    $monthlyRevenue = [
       ['month' => 'Jan', 'revenue' => 12000],
       ['month' => 'Feb', 'revenue' => 19000],
       ['month' => 'Mar', 'revenue' => 15000],
       ['month' => 'Apr', 'revenue' => 22000],
    ];

    $revenue = ['growth' => '+15%'];

    // Extended Categories for Admin
    $allCategories = [
        (object)[
            'id' => 1, 'name' => 'Electronics', 'children' => [
                (object)['id' => 11, 'name' => 'Phones'],
                (object)['id' => 12, 'name' => 'Laptops']
            ]
        ],
        (object)[
            'id' => 2, 'name' => 'Fashion', 'children' => [
                (object)['id' => 21, 'name' => 'Men'],
                (object)['id' => 22, 'name' => 'Women']
            ]
        ]
    ];

    return [
        'products' => new MockPaginator($products),
        'simpleProducts' => $products->map(fn($p) => (array)$p)->toArray(), // For JS
        'allCategories' => $allCategories,
        'categories' => ['Electronics', 'Fashion', 'Home', 'Beauty'], // Simple list for filters
        'cart' => session('cart', []),
        'stats' => $stats,
        'userStats' => $userStats,
        'adminExtras' => $adminExtras,
        'monthlyRevenue' => $monthlyRevenue,
        'revenue' => $revenue,
        'errors' => new ViewErrorBag(),
        'carouselSlides' => [
            ['image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200', 'title' => 'New Arrivals', 'link' => '#'],
            ['image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200', 'title' => 'Fashion Sale', 'link' => '#'],
        ],
    ];
}


// --------------------------------------------------------------------------
// 3. BLADE SETUP & ROUTING
// --------------------------------------------------------------------------

$views = __DIR__ . '/mystorefrontend';
$cache = __DIR__ . '/cache';
if (!is_dir($cache)) @mkdir($cache, 0777, true);

$blade = new Blade($views, $cache);
$blade->addExtension('php', 'blade');

// Router Logic
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$uri = rtrim($uri, '/') ?: '/'; // Normalize trailing slash

// Simple Route Map
$map = [
    '/' => 'products.index',
    '/index.php' => 'products.index',
    '/products' => 'products.index',
    '/cart' => 'cart.index',
    '/checkout' => 'checkout.index',
    '/orders' => 'orders.index',
    '/login' => 'auth.login',
    '/register' => 'auth.register',
    '/about' => 'about',
    '/contact' => 'contact',
    '/profile/edit' => 'profile.edit',
    
    // Admin
    '/dashboard' => 'admin.dashboard',
    '/admin/users' => 'admin.users.index', // You might need to check file existence
    '/admin/products/manage' => 'admin.products.manage',
    '/admin/products/create' => 'admin.products.create',
    '/admin/messages' => 'admin.messages.index',
    '/admin/revenue' => 'admin.revenue',
];

// Determine View
$viewName = $map[$uri] ?? null;

if (!$viewName) {
    // Dynamic Fallback: /foo/bar -> foo.bar
    $cleanPath = ltrim($uri, '/');
    $potentialView = str_replace('/', '.', $cleanPath);
    
    if ($blade->exists($potentialView)) {
        $viewName = $potentialView;
    } elseif ($blade->exists($potentialView . '.index')) {
        $viewName = $potentialView . '.index';
    } else {
        $viewName = 'errors.404'; // Make sure this exists or handle below
    }
}

// Global View Name for RouteIs() mock
$currentViewName = $viewName;

// Prevent 404 infinite loop if error page missing
if (!$blade->exists($viewName)) {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>View <code>$viewName</code> not found.</p>";
    exit;
}

// --------------------------------------------------------------------------
// 4. RENDER
// --------------------------------------------------------------------------

try {
    echo $blade->make($viewName, getGlobalData())->render();
} catch (Exception $e) {
    echo "<div style='font-family:sans-serif; padding:20px; border-left:5px solid red; background:#fff0f0;'>";
    echo "<h3>Blade Rendering Error</h3>";
    echo "<b>View:</b> $viewName<br>";
    echo "<b>Message:</b> " . $e->getMessage() . "<br>";
    echo "<b>File:</b> " . $e->getFile() . " on line " . $e->getLine();
    echo "<pre style='background:#333; color:#fff; padding:10px; overflow:auto;'>";
    echo $e->getTraceAsString();
    echo "</pre>";
    echo "</div>";
}
