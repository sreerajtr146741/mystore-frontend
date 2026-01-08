<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';
require __DIR__ . '/api_helper.php';

use Jenssegers\Blade\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Collection;
use Carbon\Carbon;

// --------------------------------------------------------------------------
// 1. SETUP BLADE
// --------------------------------------------------------------------------
$views = __DIR__ . '/mystorefrontend';
$cache = __DIR__ . '/cache';
if (!is_dir($cache)) @mkdir($cache, 0777, true);

$blade = new Blade($views, $cache);

// --------------------------------------------------------------------------
// 2. HELPER MOCKS (Laravel Style Helpers for Views)
// --------------------------------------------------------------------------
if (!function_exists('csrf_token')) { function csrf_token() { return $_SESSION['_token'] ?? 'token_'.bin2hex(random_bytes(16)); } }
if (!function_exists('csrf_field')) { function csrf_field() { return '<input type="hidden" name="_token" value="'.csrf_token().'">'; } }
if (!function_exists('method_field')) { function method_field($m) { return '<input type="hidden" name="_method" value="'.$m.'">'; } }
if (!function_exists('asset')) { function asset($path) { return '/'.ltrim($path, '/'); } }
if (!function_exists('url')) { function url($path = '') { return '/'.ltrim($path, '/'); } }
if (!function_exists('old')) { function old($k, $d=null) { return $d; } }
if (!function_exists('session')) { 
    function session($key = null, $default = null) {
        if ($key === null) return new class { public function get($k, $d=null){ return $_SESSION[$k] ?? $d; } };
        return $_SESSION[$key] ?? $default;
    } 
}
if (!function_exists('auth')) {
    function auth() {
        return new class {
            public function user() { return $_SESSION['user'] ?? null; }
            public function check() { return isset($_SESSION['user']); }
            public function id() { return $_SESSION['user']->id ?? null; }
        };
    }
}
if (!function_exists('request')) {
    function request($key = null, $default = null) {
        if ($key === null) return new class { public function all(){ return $_GET; } public function has($k){ return isset($_GET[$k]); } };
        return $_GET[$key] ?? $default;
    }
}
if (!function_exists('route')) {
    function route($name, $params = []) {
        $routes = [
            'home' => '/',
            'products.index' => '/products',
            'product.show' => '/products/show?id=',
            'products.show' => '/products/show?id=',
            'cart.index' => '/cart',
            'cart.add' => '/cart',
            'checkout.index' => '/checkout',
            'checkout.process' => '/checkout/success',
            'checkout.proceed' => '/checkout/success',
            'checkout.success' => '/checkout/success', 
            'orders.index' => '/orders',
            'orders.show' => '/orders/show?id=',
            'login' => '/login',
            'register' => '/register',
            'logout' => '/?logout=1',
            'admin.users' => '/admin/users',
            'admin.users.update' => '/admin/users',
            'admin.users.destroy' => '/admin/users',
            'admin.users.toggle' => '/admin/users',
            'admin.orders' => '/admin/orders',
            'admin.products.list' => '/admin/products/manage',
            'admin.products.create' => '/admin/products/create',
            'admin.products.edit' => '/admin/products/edit?id=',
            'admin.products.destroy' => '/admin/products/manage',
            'admin.messages.index' => '/admin/messages',
        ];
        $path = $routes[$name] ?? '/';
        
        // Parameter Handling
        if(is_array($params)) {
             if(isset($params['id'])) $path .= $params['id'];
             else if(!empty($params)) {
                  // If route has query param ?id=, append value
                  if(strpos($path, '=') !== false) $path .= reset($params);
                  else $path .= '?' . http_build_query($params);
             }
        } elseif($params) {
             if(strpos($path, '=') !== false) $path .= $params;
        }
        return $path;
    }
}

// --------------------------------------------------------------------------
// 3. ROUTER & CONTROLLER LOGIC
// --------------------------------------------------------------------------

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$uri = rtrim($uri, '/') ?: '/';

// Simple Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: /");
    exit;
}

// Data Container
$data = [];
$data['errors'] = new ViewErrorBag();
$data['cart'] = $_SESSION['cart'] ?? [];

// Calculate Cart Totals locally for now alongside API products
$subtotal = 0;
foreach($data['cart'] as $item) $subtotal += ($item['price'] * $item['qty']);
$data['subtotal'] = $subtotal;
$data['total'] = $subtotal; 
$data['shipping'] = 0;
$data['discount'] = 0;
$data['platform_fee'] = 0;

// Fetch Global Categories (Cached in session)
if (!isset($_SESSION['categories'])) {
    $catRes = api_client('categories'); 
    $_SESSION['categories'] = $catRes->data ?? ['Electronics', 'Fashion', 'Home', 'Beauty', 'Books']; 
}
$data['categories'] = $_SESSION['categories'];
$data['allCategories'] = []; // For admin select (transform if needed)

// --- ROUTE SWITCHING ---
$viewName = 'products.index'; 

if ($uri === '/' || $uri === '/products' || $uri === '/index.php') {
    // Products Index
    $viewName = 'products.index';
    $queryParams = http_build_query($_GET);
    $apiRes = api_client("products?$queryParams");
    $data['products'] = collect($apiRes->data ?? []);
    
    // Fallback Mock Paginator methods if API returns simple array
    if (!$data['products'] instanceof \Illuminate\Support\Collection) {
         $data['products'] = collect($data['products']);
    }

    // Mock Pagination helpers on the collection if missing
    if (!method_exists($data['products'], 'hasMorePages')) {
        $data['products']->macro('hasMorePages', function() { return false; });
        $data['products']->macro('nextPageUrl', function() { return '#'; });
        $data['products']->macro('links', function() { return ''; });
    }

    // Banners
    $data['carouselSlides'] = [
        ['image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200&q=80', 'title' => 'Mega Sale', 'link' => '#'],
        ['image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200&q=80', 'title' => 'Fashion', 'link' => '#']
    ];

} elseif ($uri === '/products/show') {
    // Single Product
    $viewName = 'products.show';
    $id = $_GET['id'] ?? 0;
    $apiRes = api_client("products/$id");
    // If API returns array inside data
    $prod = $apiRes->data ?? $apiRes ?? null;
    $data['product'] = $prod;
    $data['relatedProducts'] = collect([]);

} elseif ($uri === '/cart') {
    // Actions - Add/Remove handled by simple session logic for speed, but fetching fresh data from API
    if (isset($_GET['action']) && $_GET['action'] == 'add') {
        $id = $_GET['id'] ?? 0;
        if (!isset($_SESSION['cart'][$id])) {
            $p = api_client("products/$id");
            $p = $p->data ?? $p ?? null;
            if ($p) {
                $_SESSION['cart'][$id] = [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $p->price,
                    'image' => $p->image, // Will resolve in view
                    'qty' => 1
                ];
            }
        }
        header("Location: /cart"); exit;
    }
    // Remove Action
    if (isset($_GET['action']) && $_GET['action'] == 'remove') {
         $id = $_GET['id'] ?? 0;
         unset($_SESSION['cart'][$id]);
         header("Location: /cart"); exit;
    }
    
    $viewName = 'cart.index';
    $data['cartItems'] = $_SESSION['cart'] ?? [];

} elseif ($uri === '/checkout') {
    $viewName = 'checkout.index';
    $data['items'] = $_SESSION['cart'] ?? [];
    
} elseif ($uri === '/checkout/success') {
    $viewName = 'checkout.success';
    $_SESSION['cart'] = []; 

} elseif ($uri === '/orders') {
    $viewName = 'orders.index';
    // User must be logged in in real scenario, here we try fetch
    $apiRes = api_client('orders'); 
    $data['orders'] = collect($apiRes->data ?? []);
    
} elseif ($uri === '/login') {
    $viewName = 'auth.login';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $res = api_client('login', 'POST', $_POST);
        if ($res && (isset($res->token) || isset($res->access_token))) {
            $_SESSION['api_token'] = $res->token ?? $res->access_token;
            $_SESSION['user'] = $res->user ?? (object)['name'=>'User', 'email'=>$_POST['email']];
            header("Location: /"); exit;
        } else {
             $data['errors']->add('login', 'Invalid credentials');
        }
    }

} elseif ($uri === '/register') {
    $viewName = 'auth.register';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $res = api_client('register', 'POST', $_POST);
        if ($res && (isset($res->token) || isset($res->access_token))) {
             $_SESSION['api_token'] = $res->token ?? $res->access_token;
             $_SESSION['user'] = $res->user;
             header("Location: /"); exit;
        }
    }

} elseif (strpos($uri, '/admin') === 0) {
    // Admin Routes
    if ($uri === '/admin/users') {
        $viewName = 'admin.users.index';
        $res = api_client('admin/users');
        $data['users'] = collect($res->data ?? []);
        
    } elseif ($uri === '/admin/orders') {
        // Fallback or explicit check
        $viewName = 'admin.orders.index';
        if (!$blade->exists($viewName)) $viewName = 'admin.orders'; // If file is admin/orders.php

        $res = api_client('admin/orders');
        $data['orders'] = collect($res->data ?? []);

    } elseif ($uri === '/admin/products/manage') {
        $viewName = 'admin.products.manage';
        $res = api_client('products'); // Or admin/products
        $data['products'] = collect($res->data ?? []);
        // Setup simple pagination on this collection if needed
        if (!method_exists($data['products'], 'hasMorePages')) {
             $data['products']->macro('hasMorePages', function(){ return false;});
             $data['products']->macro('nextPageUrl', function(){ return '#';});
        }
        
    } elseif ($uri === '/admin/messages') {
         $viewName = 'admin.messages.index';
         $res = api_client('admin/messages');
         $data['messages'] = collect($res->data ?? []);
    } else {
        $viewName = 'admin.dashboard';
        // Dashboard Stats
        $data['stats'] = ['total_users'=>0, 'total_products'=>0, 'total_revenue'=>0]; // Defaults
    }
}

// --------------------------------------------------------------------------
// 4. RENDER
// --------------------------------------------------------------------------

try {
    if (!$blade->exists($viewName)) {
        // Try fallback with dot notation for cleaner URLs like /admin/users -> admin.users
        $fallback = str_replace('/', '.', ltrim($uri, '/'));
        if ($blade->exists($fallback)) {
            $viewName = $fallback;
        } elseif ($blade->exists($fallback . '.index')) {
             $viewName = $fallback . '.index';
        } else {
             echo "<h1>404 Not Found</h1> View not found for uri: $uri"; exit;
        }
    }
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
