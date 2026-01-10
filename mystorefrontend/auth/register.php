<?php
include '../../config.php';
include '../../api.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize input
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phoneno'] ?? '');
    $password = $_POST['password'] ?? '';

    $data = [
        'name' => $firstname . " " . $lastname,
        'email' => $email,
        'phone' => $phone,
        'password' => $password
    ];

    // Call API: Removed leading slash to prevent double slash issue
    $response = callAPI("POST", "register", $data);

    if (!empty($response['status']) && $response['status'] == true) {
        header("Location: verify-otp.php?email=" . urlencode($email));
        exit;
    } else {
        $error = $response['message'] ?? "Registration failed. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account - MyStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .anim-entry {
            animation: fadeIn 0.6s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="min-h-screen bg-[url('https://images.unsplash.com/photo-1557683316-973673baf926?q=80&w=2029&auto=format&fit=crop')] bg-cover bg-center bg-no-repeat bg-fixed flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/80 to-purple-900/80 backdrop-blur-sm"></div>

    <div class="relative w-full max-w-lg glass-card rounded-2xl shadow-2xl overflow-hidden anim-entry ring-1 ring-white/20">
        
        <div class="p-8 md:p-10">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600 mb-2">Create Account</h2>
                <p class="text-gray-500 font-medium">Join us to start shopping</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-5">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide ml-1">First Name</label>
                        <input name="firstname" type="text" placeholder="John" required 
                        class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none placeholder-gray-400 text-gray-800">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide ml-1">Last Name</label>
                        <input name="lastname" type="text" placeholder="Doe" required 
                        class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none placeholder-gray-400 text-gray-800">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide ml-1">Email Address</label>
                    <input name="email" type="email" placeholder="john@example.com" required 
                    class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none placeholder-gray-400 text-gray-800">
                </div>

                <div class="space-y-1">
                   <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide ml-1">Phone Number</label>
                    <input name="phoneno" type="tel" placeholder="+1 234 567 8900" required 
                    class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none placeholder-gray-400 text-gray-800">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide ml-1">Password</label>
                    <div class="relative">
                        <input name="password" id="password" type="password" placeholder="••••••••" required 
                        class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none placeholder-gray-400 text-gray-800 pr-12">
                        
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-indigo-600 transition-colors focus:outline-none">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 transform">
                        Create Account
                    </button>
                </div>

                <p class="text-center text-sm text-gray-600 mt-6">
                    Already have an account? <a href="login.php" class="font-semibold text-indigo-600 hover:text-indigo-800 hover:underline">Sign in</a>
                </p>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeOffIcon = document.getElementById('eye-off-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
