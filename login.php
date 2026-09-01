<?php
require_once 'connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // ទាញយកទិន្នន័យ User តាម Username
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND status = 'active' LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            // ផ្ទៀងផ្ទាត់ Password (គាំទ្រទាំង 123, MD5 និង password_hash)
            $is_valid = false;
            
            if ($password === $user['password']) {
                $is_valid = true; // Plaintext (ឧទាហរណ៍: 123)
            } elseif (md5($password) === $user['password']) {
                $is_valid = true; // MD5 Hash
            } elseif (password_verify($password, $user['password'])) {
                $is_valid = true; // Bcrypt Hash
            }

            if ($is_valid) {
                // រក្សាទុក Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                // បញ្ជូនទៅ Dashboard
                header("Location: main/dashboard.php");
                exit();
            } else {
                $error = "ពាក្យសម្ងាត់មិនត្រឹមត្រូវទេ!";
            }
        } else {
            $error = "រកមិនឃើញគណនីនេះ ឬគណនីត្រូវបានផ្អាក!";
        }
    } else {
        $error = "សូមបញ្ចូល Username និង Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soben Cafe POS - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background-color: #6c757d; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: sans-serif; }
        .login-card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); width: 350px; text-align: center; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn-login { width: 100%; padding: 10px; background: #6f4e37; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn-login:hover { background: #5a3e2b; }
        .alert-danger { color: red; background: #f8d7da; padding: 8px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>

<div class="login-card">
    <h2>☕ Soben Cafe POS</h2>
    <p>Login To Your Account</p>

    <?php if (!empty($error)): ?>
        <div class="alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required placeholder="Enter username">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter password">
        </div>
        <button type="submit" class="btn-login">🔑 Login</button>
    </form>
</div>

</body>
</html>