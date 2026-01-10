<?php
// reset-password.php
// Logic is handled in index.php (POST /reset-password)
// This view just displays the form.
$email = $_GET['email'] ?? '';
$otp = $_GET['otp'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set New Password - MyStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <div class="relative w-full max-w-md glass-card rounded-2xl shadow-2xl overflow-hidden anim-entry ring-1 ring-white/20">
        
        <div class="p-8 md:p-10 text-center">
            
            <div class="w-16 h-16 bg-white/50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner ring-1 ring-white/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>

            <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-indigo-600 mb-2">New Password</h2>
            <p class="text-gray-600 font-medium mb-6">Create a strong password for<br><strong><?= htmlspecialchars($email) ?></strong></p>

            <?php if (isset($errors) && $errors->any()): ?>
                 <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg text-left" role="alert">
                    <p class="font-bold">Error</p>
                    <ul class="list-disc list-inside">
                        <?php foreach($errors->all() as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/reset-password" method="POST" class="space-y-5">
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <input type="hidden" name="otp" value="<?= htmlspecialchars($otp) ?>">

                <div class="space-y-1 text-left">
                    <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide ml-1">New Password</label>
                    <input type="password" name="password" required placeholder="••••••••" minlength="6"
                    class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none placeholder-gray-400 text-gray-800">
                </div>

                <div class="space-y-1 text-left">
                    <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide ml-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" minlength="6"
                    class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none placeholder-gray-400 text-gray-800">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 transform">
                        <i class="bi bi-check-circle-fill me-2"></i> Reset Password
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200/50">
                <a href="/" class="inline-flex items-center text-sm font-bold text-gray-600 hover:text-purple-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
