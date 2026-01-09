<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';
require __DIR__ . '/api_helper.php';

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Collection;
use Carbon\Carbon;

// --------------------------------------------------------------------------
// 1. SETUP
// --------------------------------------------------------------------------
$views = __DIR__ . '/mystorefrontend';
// No Blade setup needed.

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
        if ($key === null) return new class { 
            public function all(){ return $_GET; } 
            public function has($k){ return isset($_GET[$k]); }
            public function ajax() { return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'; }
            public function routeIs($pattern) {
                // Simple partial check on URI for now
                global $uri;
                // pattern keys
                $map = [
                    'products.index' => ['/', '/products'],
                    'about' => ['/about'],
                    'contact' => ['/contact']
                ];
                if(isset($map[$pattern])) {
                    return in_array($uri, $map[$pattern]);
                }
                return false;
            }
        };
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
    $_SESSION['categories'] = $catRes->data ?? [
        'Mobile Phones', 'Laptops', 'Tablets', 'Smart Watches', 'Headphones', 
        'Cameras', 'Televisions', 'Gaming Consoles', 'Men\'s Fashion', 'Women\'s Fashion', 
        'Kids\' Fashion', 'Footwear', 'Watches', 'Bags & Luggage', 'Home Decor', 
        'Kitchenware', 'Furniture', 'Lighting', 'Beauty & Personal Care', 'Health & Wellness', 
        'Sports & Fitness', 'Toys & Games', 'Books', 'Stationery', 'Automotive', 
        'Groceries', 'Pet Supplies', 'Musical Instruments'
    ]; 
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

} elseif ($uri === '/about') {
    $viewName = 'pages.about';
} elseif ($uri === '/contact') {
    $viewName = 'pages.contact';

} elseif ($uri === '/checkout') {
    $viewName = 'checkout.index';
    $data['items'] = $_SESSION['cart'] ?? [];
    
} elseif ($uri === '/checkout/success') {
    $viewName = 'checkout.success';
    $_SESSION['cart'] = []; 

} elseif ($uri === '/checkout/proceed') {
    // Post Order logic
    if (!empty($_SESSION['cart'])) {
        $payload = [
            'items' => array_values($_SESSION['cart']),
            'total' => $data['total'] ?? 0,
        ];
        
        // Attempt to place order via API
        $res = api_client('orders', 'POST', $payload);
        
        if ($res && ($res->status ?? false)) {
            $_SESSION['last_order'] = $res->order ?? null;
        }
    }
    header("Location: /checkout/success"); exit;

} elseif ($uri === '/orders') {
    $viewName = 'orders.index';
    $apiRes = api_client('orders'); 
    $data['orders'] = collect($apiRes->data ?? []);
    
} elseif ($uri === '/verify-otp') {
    $viewName = 'auth.verify-register-otp';
    $data['email'] = $_SESSION['register_email'] ?? 'your email';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $otp = $_POST['otp'] ?? '';
        $email = $_SESSION['register_email'] ?? '';
        
        $res = api_client('verify-otp', 'POST', ['email' => $email, 'otp' => $otp]);
        
        if ($res && isset($res->token)) {
             $_SESSION['api_token'] = $res->token;
             $_SESSION['user'] = $res->user;
             unset($_SESSION['register_email']); 
             header("Location: /"); exit;
        } else {
             $data['errors']->add('otp', $res->message ?? 'Invalid OTP (Mock: 123456)');
             // Fallback for mock environment
             if ($otp === '123456') {
                 $_SESSION['user'] = (object)['id'=>1, 'name'=>'User', 'email'=>$email, 'role'=>'buyer']; 
                 header("Location: /"); exit;
             }
        }
    }

} elseif ($uri === '/resend-otp') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_SESSION['register_email'] ?? '';
        api_client('resend-otp', 'POST', ['email' => $email]);
        $_SESSION['status'] = 'OTP Resent!';
        header("Location: /verify-otp"); exit;
    }

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
        
        if ($res && (isset($res->status) && $res->status == true)) {
             $_SESSION['register_email'] = $_POST['email'];
             if (isset($res->token)) {
                 $_SESSION['api_token'] = $res->token;
                 $_SESSION['user'] = $res->user;
                 header("Location: /"); exit;
             } else {
                 header("Location: /verify-otp"); exit;
             }
        } elseif ($res && isset($res->token)) {
             $_SESSION['api_token'] = $res->token;
             $_SESSION['user'] = $res->user;
             header("Location: /"); exit;
        } else {
            // Mock fallback
            $_SESSION['register_email'] = $_POST['email'];
            header("Location: /verify-otp"); exit;
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
        
        // Fetch fresh data for stats
        $usersRes = api_client('admin/users');
        $prodsRes = api_client('products');
        $ordersRes = api_client('admin/orders'); // Requires Auth
        $msgsRes = api_client('admin/messages');
        
        $users = collect($usersRes->data ?? []);
        $products = collect($prodsRes->data ?? []);
        $orders = collect($ordersRes->data ?? []);
        $messages = collect($msgsRes->data ?? []);
        
        // Calculate Stats
        $totalRevenue = $orders->where('status', '!=', 'cancelled')->sum('total');
        $pendingOrders = $orders->where('status', 'pending')->count();
        $pendingMessages = $messages->count(); // Assuming all fetched are relevant or add filtered check
        
        $data['stats'] = [
            'total_users' => $users->count(),
            'total_products' => $products->count(),
            'new_today' => $products->where('created_at', '>=', date('Y-m-d'))->count(),
            'total_revenue' => $totalRevenue,
            'suspended_users' => $users->where('status', 'suspended')->count(),
            'blocked_users' => $users->where('status', 'blocked')->count()
        ];
        
        $data['userStats'] = [
            'buyers' => $users->where('role', 'buyer')->count(),
            'sellers' => $users->where('role', 'seller')->count(),
            'admins' => $users->where('role', 'admin')->count(),
            'new_today' => $users->where('created_at', '>=', date('Y-m-d'))->count(),
            'active_30d' => $users->count() // Mock active metric for now
        ];
        
        $data['adminExtras'] = [
            'pending_orders' => $pendingOrders,
            'pending_messages' => $pendingMessages
        ];
        
        // Revenue Chart Mock Data (or calculate if orders have dates)
        $data['revenue'] = ['growth' => '+10%'];
        $data['monthlyRevenue'] = [
            ['month'=>'Jan', 'revenue'=>5000],
            ['month'=>'Feb', 'revenue'=>7000], 
            // In a real app, group $orders by month
        ];
    }
}

// --------------------------------------------------------------------------
// 4. RENDER (Plain PHP)
// --------------------------------------------------------------------------

try {
    extract($data);
    $viewFile = $views . '/' . str_replace('.', '/', $viewName) . '.php';
    
    // Check if view exists
    if (!file_exists($viewFile)) {
        // Try fallback logic (e.g., folder/index.php)
        $fallback = str_replace('/', '.', ltrim($uri, '/'));
        if (strpos($fallback, '.') === false) $fallback .= '.index'; // e.g. products -> products.index
        $fallbackFile = $views . '/' . str_replace('.', '/', $fallback) . '.php';
        
        if (file_exists($fallbackFile)) {
             $viewFile = $fallbackFile;
        } else {
             // 404
             http_response_code(404);
             echo "<h1>404 Not Found</h1> View not found: $viewName"; 
             exit;
        }
    }
    
    // Include View
    include $viewFile;

} catch (Exception $e) {
    echo "<div style='font-family:sans-serif; padding:20px; border-left:5px solid red; background:#fff0f0;'>";
    echo "<h3>Rendering Error</h3>";
    echo "<b>Message:</b> " . $e->getMessage() . "<br>";
    echo "<b>File:</b> " . $e->getFile() . " on line " . $e->getLine();
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
