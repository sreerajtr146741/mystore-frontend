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
            'checkout.update_qty' => '/checkout/update-qty?id=',
            'orders.index' => '/orders',
            'orders.show' => '/orders/show?id=',
            'login' => '/login',
            'register' => '/register',
            'logout' => '/?logout=1',
            'orders.cancel' => '/orders/cancel?id=',
            'orders.return' => '/orders/return?id=',
            'orders.download' => '/orders/download?id=',
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
    $fetchedCats = $catRes->data ?? [];
    if (empty($fetchedCats)) {
        $fetchedCats = [
            'Mobile Phones', 'Laptops', 'Tablets', 'Smart Watches', 'Headphones', 
            'Cameras', 'Televisions', 'Gaming Consoles', 'Men\'s Fashion', 'Women\'s Fashion', 
            'Kids\' Fashion', 'Footwear', 'Watches', 'Bags & Luggage', 'Home Decor', 
            'Kitchenware', 'Furniture', 'Lighting', 'Beauty & Personal Care', 'Health & Wellness', 
            'Sports & Fitness', 'Toys & Games', 'Books', 'Stationery', 'Automotive', 
            'Groceries', 'Pet Supplies', 'Musical Instruments'
        ]; 
    }
    $_SESSION['categories'] = $fetchedCats;
}
$data['categories'] = $_SESSION['categories'];
$data['allCategories'] = []; // For admin select (transform if needed)

// --- ROUTE SWITCHING ---
$viewName = null; // Changed from default 'products.index' to null to detect 404
$data['products'] = collect([]); // Prevent undefined variable error

if ($uri === '/' || $uri === '/products' || $uri === '/index.php') {
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
    $viewName = 'contact'; // Changed from 'pages.contact' to correct path 'contact.php' in root/mystorefrontend
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = [
            'name' => trim(($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? '')),
            'email' => $_POST['email'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'message' => $_POST['message'] ?? ''
        ];
        
        // Call Backend API
        $res = api_client('contact', 'POST', $payload);
        
        if ($res && (isset($res->status) && $res->status == true)) {
            $_SESSION['success'] = $res->message ?? 'Message sent successfully!';
            header("Location: /contact"); // PRG pattern
            exit;
        } else {
             $data['errors']->add('contact', $res->message ?? 'Failed to send message. Please try again.');
        }
    }

} elseif ($uri === '/checkout') {
    $viewName = 'checkout.index';
    $data['items'] = $_SESSION['cart'] ?? [];
    
} elseif ($uri === '/checkout/success') {
    $viewName = 'checkout.success';
    $_SESSION['cart'] = []; 

} elseif ($uri === '/checkout/update-qty') {
    // AJAX Update Qty
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
         $input = json_decode(file_get_contents('php://input'), true);
         $id = $_GET['id'] ?? 0; // passed in URL
         $qty = $input['qty'] ?? 1;
         
         if ($id && isset($_SESSION['cart'][$id])) {
             if ($qty < 1) unset($_SESSION['cart'][$id]);
             else $_SESSION['cart'][$id]['qty'] = $qty;
             
             // Recalculate Totals
             $cart = $_SESSION['cart'];
             $subtotal = 0;
             foreach($cart as $item) $subtotal += ($item['price'] * $item['qty']);
             
             header('Content-Type: application/json');
             echo json_encode([
                 'success' => true,
                 'totals' => [
                     'subtotal' => $subtotal,
                     'shipping' => 0,
                     'platform_fee' => 0,
                     'total' => $subtotal,
                     'discount' => 0
                 ]
             ]);
             exit;
         }
    }
    exit;

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
    $queryParams = http_build_query($_GET);
    $apiRes = api_client("orders?$queryParams"); 
    $data['orders'] = collect($apiRes->data ?? []); // Ensure using standard key
    
    // Manual Pagination Meta if API provides
    if (!method_exists($data['orders'], 'hasMorePages')) {
         $hasNext = isset($apiRes->next_page_url);
         $nextUrl = $apiRes->next_page_url ?? null;
         $total = $apiRes->total ?? count($data['orders']);
         
         $data['orders']->macro('hasMorePages', function() use ($hasNext){ return $hasNext; });
         $data['orders']->macro('nextPageUrl', function() use ($nextUrl){ return $nextUrl; });
         $data['orders']->macro('total', function() use ($total){ return $total; });
    }

    if (request()->ajax()) {
        $orders = $data['orders']; // pass to view
        ob_start();
        include $views . '/mystorefrontend/orders/partials/card.php';
        $html = ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'html' => $html,
            'next_url' => $data['orders']->nextPageUrl()
        ]);
        exit;
    }
    
} elseif ($uri === '/verify-otp') {
    $viewName = 'auth.verify-register-otp'; // Use generic OTP view
    $data['email'] = $_SESSION['register_email'] ?? 'your email';
    $type = $_GET['type'] ?? $_SESSION['otp_type'] ?? 'register';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $otp = $_POST['otp'] ?? '';
        $email = $_SESSION['register_email'] ?? '';
        
        if ($type === 'password') {
             // Verify Password OTP
             $res = api_client('auth/verify-password-otp', 'POST', ['email' => $email, 'otp' => $otp]);
             $token = $res->data->reset_token ?? null;
             
             if ($token) {
                 $_SESSION['reset_token'] = $token;
                 header("Location: /reset-password"); exit;
             } else {
                 $data['errors']->add('otp', $res->message ?? 'Invalid OTP');
             }

        } else {
             // Verify Register OTP
             $res = api_client('auth/verify-register', 'POST', ['email' => $email, 'otp' => $otp]);
             $token = $res->token ?? $res->data->token ?? null;
             $user = $res->user ?? $res->data->user ?? null;
             
             if ($token) {
                 $_SESSION['api_token'] = $token;
                 $_SESSION['user'] = $user;
                 unset($_SESSION['register_email']); 
                 header("Location: /"); exit;
             } else {
                 $data['errors']->add('otp', $res->message ?? 'Invalid OTP');
             }
        }
    }

} elseif ($uri === '/resend-otp') {
    $type = $_GET['type'] ?? $_SESSION['otp_type'] ?? 'register';
    $email = $_SESSION['register_email'] ?? '';
    
    if ($type === 'password') {
         api_client('auth/forgot-password', 'POST', ['email' => $email]); // Re-trigger forgot pw email
    } else {
         api_client('auth/resend-register-otp', 'POST', ['email' => $email]);
    }
    $_SESSION['status'] = 'OTP Resent!';
    header("Location: /verify-otp?type=$type"); exit;

} elseif ($uri === '/forgot-password') {
    $viewName = 'auth.forgot-password';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $res = api_client('auth/forgot-password', 'POST', ['email' => $email]);
        
        if ($res && isset($res->status) && $res->status == true) {
             $_SESSION['register_email'] = $email; // Reuse this key or create 'otp_email'
             $_SESSION['otp_type'] = 'password';
             $_SESSION['status'] = $res->message ?? 'OTP sent to email.';
             header("Location: /verify-otp?type=password"); exit;
        } else {
             $data['errors']->add('email', $res->message ?? 'Unable to send reset link.');
        }
    }

} elseif ($uri === '/reset-password') {
    $viewName = 'auth.reset-password';
    $data['email'] = $_SESSION['register_email'] ?? '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = [
            'password' => $_POST['password'],
            'password_confirmation' => $_POST['password_confirmation']
        ];
        
        // MANUALLY inject the RESET TOKEN for this request
        // api_client helper uses $_SESSION['api_token'] by default. 
        // We will temporarily swap it or overload the headers if possible.
        // For now, simpler to swap:
        $originalToken = $_SESSION['api_token'] ?? null;
        $_SESSION['api_token'] = $_SESSION['reset_token'] ?? null;
        
        $res = api_client('auth/reset-password', 'POST', $payload);
        
        // Restore original logic (though user likely isn't logged in)
        if ($originalToken) $_SESSION['api_token'] = $originalToken;
        else unset($_SESSION['api_token']);

        if ($res && (isset($res->status) && $res->status == true)) {
            $_SESSION['success'] = 'Password reset successfully! Please login.';
            unset($_SESSION['reset_token']);
            header("Location: /login"); exit;
        } else {
             $data['errors']->add('reset', $res->message ?? 'Password mismatch or invalid token.');
        }
    }


} elseif ($uri === '/login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $res = api_client('auth/login', 'POST', $_POST);
        
        $token = $res->token ?? $res->access_token ?? $res->data->token ?? null;
        $user  = $res->user ?? $res->data->user ?? null;
       
        if ($token) {
            $_SESSION['api_token'] = $token;
            $_SESSION['user'] = $user ?? (object)['name'=>'User', 'email'=>$_POST['email']];
            
            // Redirect based on Role
            $role = $_SESSION['user']->role ?? 'buyer';
            $redirect = ($role === 'admin' || $role === 'seller') ? '/admin/dashboard' : '/';
            
            if (is_ajax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => $redirect]);
                exit;
            } else {
                header("Location: $redirect"); exit;
            }
        } else {
             $msg = $res->message ?? 'Invalid credentials';
             
             if (is_ajax()) {
                 header('Content-Type: application/json');
                 echo json_encode(['success' => false, 'message' => $msg]);
                 exit;
             } else {
                 $data['errors']->add('login', $msg);
             }
        }
    }
    $viewName = 'auth.login';

} elseif ($uri === '/register') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $res = api_client('auth/register', 'POST', $_POST);
        // Expecting: "Registration successful. OTP sent..."
        
        if ($res && (isset($res->status) && $res->status == true)) {
             $_SESSION['register_email'] = $_POST['email'];
             $_SESSION['otp_type'] = 'register';
             
             // Check if backend auto-logins
             if (isset($res->data->token)) {
                 $_SESSION['api_token'] = $res->data->token;
                 $_SESSION['user'] = $res->data->user;
                 $redirect = '/';
             } else {
                 $redirect = '/verify-otp';
             }
             
             if (is_ajax()) {
                 header('Content-Type: application/json');
                 echo json_encode(['success' => true, 'redirect' => $redirect]);
                 exit;
             } else {
                 header("Location: $redirect"); exit;
             }
             
        } else {
            $msg = $res->message ?? 'Registration failed.';
            if (is_ajax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            } else {
                $data['errors']->add('register', $msg);
                $_SESSION['register_email'] = $_POST['email']; 
            }
        }
    }
    $viewName = 'auth.register';

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
        $queryParams = http_build_query($_GET);
        $res = api_client("products?$queryParams"); // Pass filters to API
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
        
        // Use dedicated Admin Dashboard Endpoint
        $dashRes = api_client('admin/dashboard');
        
        if ($dashRes && isset($dashRes->data)) {
            $d = $dashRes->data;
            // Map API response to View variables
            $data['stats'] = [
                'total_users' => $d->total_users ?? 0,
                'total_products' => $d->total_products ?? 0,
                'total_revenue' => $d->total_revenue ?? 0,
                'new_today' => $d->new_products_count ?? 0, // Adjust key based on likely response or generic
                'suspended_users' => 0 // Fallback if not in dash API
            ];
            
            // Pass entire data object for flexibility in view
            $data['dashboard'] = $d;
        } else {
            // Fallback Manual Fetch if Dashboard API fails/doesn't exist yet
            $usersRes = api_client('admin/users');
            $prodsRes = api_client('products');
            $ordersRes = api_client('admin/orders');
            
            $users = collect($usersRes->data ?? []);
            $products = collect($prodsRes->data ?? []);
            $orders = collect($ordersRes->data ?? []);
            
            $data['stats'] = [
                'total_users' => $users->count(),
                'total_products' => $products->count(),
                'total_revenue' => $orders->where('status', '!=', 'cancelled')->sum('total'),
                'new_today' => 0,
            ];
        }
        
        $data['userStats'] = ['buyers' => 0, 'active_30d' => 0]; // Placeholders
        $data['adminExtras'] = ['pending_orders' => 0, 'pending_messages' => 0];
    }
}

// Fallback if no route matched
if ($viewName === null) {
    http_response_code(404);
    echo "<div style='text-align:center; padding:50px; font-family: sans-serif;'><h1>404 Not Found</h1><p>The page <b>" . htmlspecialchars($uri) . "</b> could not be found.</p><a href='/'>Go Home</a></div>";
    exit;
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
