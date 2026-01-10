<?php
include '../../config.php';
include '../../api.php';

$email = $_GET['email'] ?? '';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize input
    $otp = trim($_POST['otp'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $data = [
        'email' => $email,
        'otp'   => $otp
    ];

    // Call API: verify-otp
    $response = callAPI("POST", "verify-otp", $data);

    if (isset($response['status']) && $response['status'] == true) {
        // Success -> Redirect to Login
        header("Location: login.php");
        exit;
    } else {
        $error = $response['message'] ?? 'Verification failed';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP - MyStore</title>
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
        .otp-input {
            letter-spacing: 0.5em;
        }
    </style>
</head>

<body class="min-h-screen bg-[url('https://images.unsplash.com/photo-1557683316-973673baf926?q=80&w=2029&auto=format&fit=crop')] bg-cover bg-center bg-no-repeat bg-fixed flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/80 to-purple-900/80 backdrop-blur-sm"></div>

    <div class="relative w-full max-w-md glass-card rounded-2xl shadow-2xl overflow-hidden anim-entry ring-1 ring-white/20">
        
        <div class="p-8 md:p-10 text-center">
            
            <div class="w-16 h-16 bg-white/50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner ring-1 ring-white/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>

            <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600 mb-2">Check Your Email</h2>
            <p class="text-gray-600 font-medium mb-6">
                We've sent a 6-digit verification code to<br/>
                <span class="text-indigo-700 font-bold"><?= htmlspecialchars($email) ?></span>
            </p>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg text-left" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <form action="verify-otp.php?email=<?= urlencode($email) ?>" method="POST" class="space-y-6">
                
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                <div>
                    <input type="text" name="otp" maxlength="6" required autofocus
                        class="otp-input w-full px-4 py-4 text-center text-3xl font-bold bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none text-gray-800 placeholder-gray-300"
                        placeholder="••••••" pattern="\d{6}">
                </div>

                <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 transform">
                    Verify Account
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200/50">
                <p class="text-sm text-gray-600">
                    Didn't receive the code? 
                    <a href="register.php" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline">Register again</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
