<?php
session_start();
include 'config.php';
include 'api.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = [
        'email'    => $_POST['email'],
        'password' => $_POST['password']
    ];

    $response = callAPI("POST", "/login", $data);

    if (!empty($response['token'])) {
        $_SESSION['token'] = $response['token'];
        header("Location: products.php");
        exit;
    } else {
        $error = $response['message'] ?? "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyStore</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body{
            background: linear-gradient(135deg,#667eea 0%,#764ba2 100%);
            min-height: 100vh;
        }
        .glass {
            backdrop-filter: blur(14px);
            background: rgba(255,255,255,0.85);
            border: 1px solid rgba(255,255,255,.4);
        }
    </style>
</head>

<body class="flex items-center justify-center">

    <div class="w-full max-w-md p-1 mt-10">
        <div class="rounded-2xl p-[2px] bg-gradient-to-br from-white/40 to-white/10 shadow-xl">
            <div class="glass rounded-2xl p-8">

                <h2 class="text-3xl font-extrabold text-center mb-2 text-gray-900">Welcome Back!</h2>
                <p class="text-center text-gray-600 mb-6">Login to continue</p>

                <?php if(!empty($error)): ?>
                    <p class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 text-center font-semibold">
                        <?= $error ?>
                    </p>
                <?php endif; ?>

                <form action="login.php" method="POST" class="space-y-5">

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required
                            class="w-full p-3 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="you@example.com">
                    </div>

                    <div class="relative">
                        <label class="block mb-1 font-medium text-gray-700">Password</label>

                        <input type="password" id="password" name="password" required
                            class="w-full p-3 border rounded-lg pr-12 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="••••••••">

                        <span onclick="togglePassword()" 
                            class="absolute right-3 top-11 cursor-pointer text-gray-500">
                            👁
                        </span>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg shadow-md">
                        Login Now
                    </button>

                </form>

                <p class="text-center mt-6 text-gray-700">
                    Don't have an account?
                    <a href="register.php" class="text-indigo-600 font-semibold hover:underline">
                        Register here
                    </a>
                </p>

            </div>
        </div>
    </div>

<script>
function togglePassword(){
    const p = document.getElementById("password");
    p.type = p.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
