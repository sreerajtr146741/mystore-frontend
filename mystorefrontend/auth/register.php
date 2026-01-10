<?php
include 'config.php';
include 'api.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = [
        'name' => $_POST['firstname'] . " " . $_POST['lastname'],
        'email' => $_POST['email'],
        'phone' => $_POST['phoneno'],
        'password' => $_POST['password']
    ];

    $response = callAPI("POST", "/register", $data);

    if (!empty($response['status']) && $response['status'] == true) {
        header("Location: verify-otp.php?email=" . $_POST['email']);
        exit;
    } else {
        $error = $response['message'] ?? "Registration failed";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - MyStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg p-8 bg-white rounded-xl shadow-xl">

        <h2 class="text-3xl font-bold mb-4 text-center">Create Account</h2>

        <?php if (!empty($error)): ?>
            <p class="text-red-600 text-center mb-3 font-semibold"><?= $error ?></p>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">

            <div class="grid grid-cols-2 gap-4">
                <input name="firstname" type="text" placeholder="First Name"
                required class="border p-3 rounded-lg w-full">

                <input name="lastname" type="text" placeholder="Last Name"
                required class="border p-3 rounded-lg w-full">
            </div>

            <input name="email" type="email" placeholder="Email Address"
            required class="border p-3 rounded-lg w-full">

            <input name="phoneno" type="text" placeholder="Phone Number"
            required class="border p-3 rounded-lg w-full">

            <div class="relative">
                <input name="password" id="password" type="password" placeholder="Password"
                required class="border p-3 rounded-lg w-full pr-12">
                
                <span onclick="toggle('password', this)" 
                    class="absolute right-4 top-3 cursor-pointer opacity-60">👁</span>
            </div>

            <button class="bg-indigo-600 hover:bg-indigo-700 text-white w-full py-3 rounded-lg font-semibold">
                Register
            </button>
        </form>
    </div>

<script>
function toggle(id, btn){
    let el = document.getElementById(id);
    el.type = el.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
