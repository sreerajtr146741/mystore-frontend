<?php
require __DIR__ . '/vendor/autoload.php';

use Jenssegers\Blade\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Carbon\Carbon;
use Illuminate\Support\Collection;

// --------------------------------------------------------------------------
// 1. ROBUST MOCK HELPERS (Simulate Laravel)
// --------------------------------------------------------------------------

// --- Mock Paginator for $products->links() and $products->count() ---
if (!class_exists('MockPaginator')) {
    class MockPaginator implements Countable, IteratorAggregate, ArrayAccess {
        protected $items;
        protected $perPage;
        protected $currentPage;
        protected $total;
        
        public function __construct($items, $perPage = 15, $currentPage = 1) {
            $this->items = collect($items);
            $this->perPage = $perPage;
            $this->currentPage = $currentPage;
            $this->total = $this->items->count();
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
        public function total() { return $this->total; }
        public function links() { return ''; } // Return empty string for now
        public function appends($key) { return $this; }
        public function hasPages() { return $this->total > $this->perPage; }
        public function isEmpty() { return $this->items->isEmpty(); }
    }
}

// --- Mock Auth ---
if (!function_exists('auth')) {
    class MockUser {
        public $id = 1;
        public $first_name = 'Demo';
        public $last_name = 'User';
        public $name = 'Demo User';
        public $role = 'admin'; // Change to 'buyer' or 'seller' to test matches
        public $email = 'admin@store.com';
        public $phone = '1234567890';
        public $address = '123 Admin St, Dashboard City';
        public $profile_photo_url = 'https://ui-avatars.com/api/?name=Demo+User&background=0D8ABC&color=fff';
        public $profile_photo = null;
        public $updated_at;

        public function __construct() {
            $this->updated_at = now();
        }

        public function isAdmin() { return $this->role === 'admin'; }
        public function isSeller() { return $this->role === 'seller' || $this->role === 'admin'; }
        public function isBuyer() { return true; }
    }
    class MockAuth {
        public function user() { return new MockUser(); }
        public function check() { return true; } // Always logged in for demo
        public function id() { return 1; }
        public function guard() { return $this; }
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
            return strpos((string)$currentViewName, $pattern) !== false;
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
            'products.show' => '/products/show?id=', // Dynamic append
            'product.show' => '/products/show?id=',
            'cart.index' => '/cart',
            'cart.add' => '/cart', // Mock POST
            'checkout.index' => '/checkout',
            'checkout.single' => '/checkout',
            'checkout.update_qty' => '/checkout/qty',
            'checkout.remove' => '/checkout/remove',
            'checkout.proceed' => '/checkout/proceed',
            'checkout.success' => '/checkout/success',
            'orders.index' => '/orders',
            'orders.show' => '/orders/show?id=',
            'login' => '/login',
            'register' => '/register',
            'logout' => '/?logout=1',
            'profile.edit' => '/profile/edit',
            'profile.update' => '/profile/edit',
            'about' => '/about',
            'contact' => '/contact',
            // Admin
            'admin.dashboard' => '/dashboard',
            'admin.products.list' => '/admin/products/manage',
            'admin.products.create' => '/admin/products/create',
            'admin.products.edit' => '/admin/products/edit',
            'admin.discount.category' => '/admin/discount',
            'admin.users' => '/admin/users',
            'admin.users.toggle' => '/admin/users', // Mock toggle
            'admin.users.destroy' => '/admin/users', // Mock destroy
            'admin.orders' => '/admin/orders',
            'admin.messages.index' => '/admin/messages',
            'admin.messages.reply' => '/admin/messages',
            'admin.messages.destroy' => '/admin/messages',
            'admin.revenue' => '/admin/revenue',
            'admin.discounts.global.edit' => '/admin/discounts/global',
        ];
        
        $path = $routes[$name] ?? '/' . str_replace('.', '/', $name);
        
        // Handle params definition differently based on if it's array or value
        if (!is_array($params)) $params = [$params];

        // Specific handling for ID appending logic used in route table above
        if (strpos($path, '?id=') !== false && !empty($params)) {
             $id = is_object($params[0] ?? null) ? $params[0]->id : ($params['id'] ?? $params[0] ?? 1);
             $path .= $id;
        } elseif (!empty($params)) {
             $path .= (strpos($path, '?') === false ? '?' : '&') . http_build_query($params);
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
                1 => ['id'=>1, 'name' => 'Wireless Headphones', 'price' => 2999, 'qty' => 1, 'image' => null, 'category' => 'Electronics'],
                2 => ['id'=>2, 'name' => 'Smart Watch', 'price' => 4500, 'qty' => 2, 'image' => null, 'category' => 'Wearables'],
            ],
            'success' => null, 
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
        (object)['id'=>1, 'name'=>'Wireless Headphones', 'price'=>2999, 'final_price'=>2500, 'discounted_price'=>2500, 'stock'=>50, 'category'=>'Electronics', 'image'=>'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400', 'description'=>'Noise cancelling', 'specifications'=>['General'=>[['key'=>'Brand','value'=>'Sony']]], 'status'=>'active', 'is_active'=>1, 'user'=>(object)['name'=>'Admin']],
        (object)['id'=>2, 'name'=>'Smart Watch', 'price'=>4500, 'final_price'=>4000, 'discounted_price'=>4000, 'stock'=>12, 'category'=>'Electronics', 'image'=>'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400', 'description'=>'Fitness tracker', 'specifications'=>[], 'status'=>'active', 'is_active'=>1, 'user'=>(object)['name'=>'Admin']],
        (object)['id'=>3, 'name'=>'Gaming Mouse', 'price'=>1200, 'final_price'=>1200, 'discounted_price'=>1200, 'stock'=>25, 'category'=>'Electronics', 'image'=>'https://images.unsplash.com/photo-1527814050087-3793815479db?w=400', 'description'=>'RGB lighting', 'specifications'=>[], 'status'=>'active', 'is_active'=>1, 'user'=>(object)['name'=>'Admin']],
        (object)['id'=>4, 'name'=>'Laptop Backpack', 'price'=>1899, 'final_price'=>1899, 'discounted_price'=>1899, 'stock'=>30, 'category'=>'Accessories', 'image'=>'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400', 'description'=>'Waterproof', 'specifications'=>[], 'status'=>'active', 'is_active'=>1, 'user'=>(object)['name'=>'Admin']],
        (object)['id'=>5, 'name'=>'Mechanical Keyboard', 'price'=>3499, 'final_price'=>3499, 'discounted_price'=>3499, 'stock'=>10, 'category'=>'Electronics', 'image'=>'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=400', 'description'=>'Blue switches', 'specifications'=>[], 'status'=>'active', 'is_active'=>1, 'user'=>(object)['name'=>'Admin']],
    ]);

    // Admin Stats
    $stats = ['total_users' => 1250, 'total_products' => 84, 'new_today' => 12, 'total_revenue' => 154000, 'suspended_users' => 5, 'blocked_users' => 2];
    $userStats = ['buyers' => 1100, 'sellers' => 50, 'admins' => 5, 'new_today' => 12, 'active_30d' => 850];
    $adminExtras = ['pending_orders' => 5, 'pending_messages' => 3];
    $monthlyRevenue = [['month' => 'Jan', 'revenue' => 12000], ['month' => 'Feb', 'revenue' => 19000], ['month' => 'Mar', 'revenue' => 15000], ['month' => 'Apr', 'revenue' => 22000]];
    $revenue = ['growth' => '+15%'];

    $allCategories = [
        (object)['id' => 1, 'name' => 'Electronics', 'children' => [(object)['id' => 11, 'name' => 'Phones'], (object)['id' => 12, 'name' => 'Laptops']]],
        (object)['id' => 2, 'name' => 'Fashion', 'children' => [(object)['id' => 21, 'name' => 'Men'], (object)['id' => 22, 'name' => 'Women']]]
    ];

    // Mock Users
    $mockUsers = new MockPaginator(collect([
        (object)['id'=>1, 'name'=>'John Doe', 'email'=>'john@example.com', 'role'=>'Buyer', 'status'=>'active', 'joined'=>'2024-01-01', 'created_at'=>now()->subMonths(3), 'phone'=>'1231231234', 'address'=>'123 Lane', 'orders_count'=>5, 'avatar'=>'https://ui-avatars.com/api/?name=John+Doe'],
        (object)['id'=>2, 'name'=>'Jane Smith', 'email'=>'jane@example.com', 'role'=>'Seller', 'status'=>'active', 'joined'=>'2024-02-01', 'created_at'=>now()->subMonths(2), 'phone'=>'9876543210', 'address'=>'456 Road', 'orders_count'=>12, 'avatar'=>'https://ui-avatars.com/api/?name=Jane+Smith'],
    ]));

    // Mock Orders
    $mockOrders = new MockPaginator(collect([
        (object)['id'=>101, 'user'=>(object)['name'=>'John Doe','email'=>'john@example.com'], 'created_at'=>now(), 'status'=>'placed', 'total'=>2999, 'items'=>collect([ (object)['product'=>(object)['name'=>'Product A','image'=>null], 'qty'=>1] ]) ],
        (object)['id'=>102, 'user'=>(object)['name'=>'Jane Smith','email'=>'jane@example.com'], 'created_at'=>now()->subDay(), 'status'=>'delivered', 'total'=>4500, 'items'=>collect([ (object)['product'=>(object)['name'=>'Product B','image'=>null], 'qty'=>1] ]) ],
    ]));

    // Mock Messages
    $mockMessages = new MockPaginator(collect([
        (object)[
            'id'=>1, 'first_name'=>'Alice', 'last_name'=>'Wonder', 'email'=>'alice@example.com', 'subject'=>'Help needed', 'message'=>'I need help with my order.', 'created_at'=>now(),
            'replies'=>collect([(object)['subject'=>'Re: Help', 'message'=>'Sure', 'created_at'=>now()]])
        ],
        (object)[
            'id'=>2, 'first_name'=>'Bob', 'last_name'=>'Builder', 'email'=>'bob@example.com', 'subject'=>'Product Question', 'message'=>'Is this item durable?', 'created_at'=>now()->subHours(2),
            'replies'=>collect([])
        ]
    ]));
    
    // Checkout Vars
    $cart = session('cart', []);
    $subtotal = 0; foreach($cart as $i) $subtotal += ($i['price']*$i['qty']);
    $shipping = 0; 
    $total = $subtotal;

    return [
        'products' => new MockPaginator($products),
        'simpleProducts' => $products->map(fn($p) => (array)$p)->toArray(), 
        'allCategories' => $allCategories,
        'categories' => ['Electronics', 'Fashion', 'Home', 'Beauty'],
        'cart' => $cart,
        'items' => $cart, // For checkout view which uses $items
        'stats' => $stats,
        'userStats' => $userStats,
        'adminExtras' => $adminExtras,
        'monthlyRevenue' => $monthlyRevenue,
        'revenue' => $revenue,
        'errors' => new ViewErrorBag(),
        'users' => $mockUsers,
        'orders' => $mockOrders,
        'messages' => $mockMessages,
        'status' => 'all', // For orders page
        'counts' => ['all'=>2, 'placed'=>1, 'processing'=>0, 'shipped'=>0, 'delivered'=>1, 'cancelled'=>0, 'return_requested'=>0, 'returned'=>0],
        'product' => $products->first(), // Default for show pages if not specified
        'similarProducts' => $products->take(2),
        'randomProducts' => $products->take(2),
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'discount' => 0,
        'platform_fee' => 0,
        'total' => $total,
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
$uri = rtrim($uri, '/') ?: '/'; 

// Route Map
$map = [
    '/' => 'products.index',
    '/index.php' => 'products.index',
    '/products' => 'products.index',
    '/products/show' => 'products.show',
    '/cart' => 'cart.index',
    '/checkout' => 'checkout.index',
    '/checkout/process' => 'checkout.success', // Mock process -> success
    '/checkout/success' => 'checkout.success',
    '/checkout/proceed' => 'checkout.success',
    '/orders' => 'orders.index',
    '/login' => 'auth.login',
    '/register' => 'auth.register',
    '/about' => 'about',
    '/contact' => 'contact',
    '/profile/edit' => 'profile.edit',
    
    // Admin
    '/dashboard' => 'admin.dashboard',
    '/admin/users' => 'admin.users.index',
    '/admin/products/manage' => 'admin.products.manage',
    '/admin/products/create' => 'admin.products.create',
    '/admin/orders' => 'admin.orders',
    '/admin/messages' => 'admin.messages.index',
    '/admin/revenue' => 'admin.revenue',
];

$viewName = $map[$uri] ?? null;

if (!$viewName) {
    $cleanPath = ltrim($uri, '/');
    $potentialView = str_replace('/', '.', $cleanPath);
    
    if ($blade->exists($potentialView)) {
        $viewName = $potentialView;
    } elseif ($blade->exists($potentialView . '.index')) {
        $viewName = $potentialView . '.index';
    } else {
        $viewName = 'errors.404'; 
    }
}

$currentViewName = $viewName;

if (!$blade->exists($viewName)) {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>View <code>$viewName</code> not found.</p>";
    exit;
}

// --------------------------------------------------------------------------
// 4. RENDER WITH SAFE DEFAULTS
// --------------------------------------------------------------------------

try {
    $data = getGlobalData();
    // Pass everything
    echo $blade->make($viewName, $data)->render();
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
