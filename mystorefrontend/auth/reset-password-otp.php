<?php
include '../../config.php';
include '../../api.php';

$email = $_GET['email'] ?? '';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Check if this is a Resend Request
    if (isset($_POST['resend'])) {
        $response = callAPI("POST", "forgot-password", ['email' => $email]);
        if(isset($response['status']) && $response['status'] == true) {
            $success = "OTP Resent successfully.";
        } else {
            $error = $response['message'] ?? "Failed to resend OTP";
        }
    } 
    // Verify OTP Request
    else {
        $otp = $_POST['otp'] ?? '';
        
        // In the backend flow, we usually verify OTP first. 
        // If the backend expects verification before password reset, we do this.
        // Assuming "verify-otp" endpoint handles this check.
        
         $data = [
            'email' => $email,
            'otp'   => $otp
        ];

        // We use the same verify-otp endpoint, or a specific one for password reset if backend distinguishes.
        // Usually it's the same or "verify-reset-token". Let's assume verify-otp returns a token or success.
        $response = callAPI("POST", "verify-otp", $data);

        if (isset($response['status']) && $response['status'] == true) {
            // Success -> Redirect to Reset Password Page with email (and ideally a token)
            header("Location: reset-password.php?email=" . urlencode($email) . "&otp=" . urlencode($otp));
            exit;
        } else {
            $error = $response['message'] ?? 'Invalid OTP';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Reset OTP - MyStore</title>
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
        .otp-letter-spacing { letter-spacing: 0.5em; }
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-green-600 to-teal-600 mb-2">Verify OTP</h2>
            <p class="text-gray-600 font-medium mb-6">Enter the 6-digit code sent to<br><strong><?= htmlspecialchars($email) ?></strong></p>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg text-left" role="alert">
                    <p class="font-bold">Error</p>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg text-left" role="alert">
                    <p class="font-bold">Success</p>
                    <p><?= htmlspecialchars($success) ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                
                <div>
                     <input type="text" name="otp" maxlength="6" required autofocus
                        class="w-full text-center text-3xl font-bold py-4 bg-white/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none otp-letter-spacing text-gray-800"
                        placeholder="••••••" pattern="\d{6}" inputmode="numeric">
                </div>

                <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 transform">
                    Verify Code
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200/50">
                <form action="" method="POST" class="inline">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    <input type="hidden" name="resend" value="1">
                    <p class="text-sm text-gray-600">
                        Didn't receive the code? 
                        <button type="submit" class="font-bold text-green-600 hover:text-green-800 hover:underline bg-transparent border-0 cursor-pointer p-0">Resend</button>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
