<?php
include 'config.php';
include 'api.php';

$email = $_GET['email'] ?? ($_POST['email'] ?? '');
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = [
        'email' => $email,
        'otp'   => $_POST['otp']
    ];

    // Call backend API
    $response = callAPI("POST", "/verify-otp", $data);

    if (!empty($response['status']) && $response['status'] == true) {
        header("Location: login.php");
        exit;
    } else {
        $error = $response['message'] ?? "Invalid OTP. Try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP • MyStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body{background:linear-gradient(135deg,#f3f4f6 0%,#e5e7eb 100%);min-height:100vh;}
        .glass{background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.2);}
    </style>
</head>

<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-md glass rounded-2xl shadow-xl p-8 text-center">

        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 mb-2">OTP Verification</h2>

        <p class="text-gray-500 mb-4">
            Enter the 6-digit OTP sent to:<br>
            <span class="font-semibold text-black"><?= htmlspecialchars($email) ?></span>
        </p>

        <?php if (!empty($error)): ?>
            <p class="text-red-600 font-semibold mb-3"><?= $error ?></p>
        <?php endif; ?>

        <form action="verify-otp.php?email=<?= urlencode($email) ?>" method="POST" class="space-y-5">

            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

            <input type="text" name="otp" maxlength="6" required
                class="w-full text-center text-3xl tracking-[0.4em] font-bold py-4 rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500"
                placeholder="000000" pattern="\d{6}">

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg">
                Verify OTP
            </button>
        </form>

        <div class="mt-5 text-sm text-gray-500">
            <a href="login.php" class="hover:text-black">← Back to Login</a>
        </div>
    </div>
</body>
</html>
