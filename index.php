
<?php
session_start();

if(isset($_SESSION['user_id'])){
    header("Location: main/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Soben Cafe POS</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;

            background:
            linear-gradient(
                rgba(0,0,0,.6),
                rgba(0,0,0,.6)
            ),
            url('assets/images/coffee.jpg');

            background-size:cover;
            background-position:center;
        }

        .login-card{
            width:100%;
            max-width:420px;
            background:#fff;
            border-radius:20px;
            padding:40px;
            box-shadow:0 15px 35px rgba(0,0,0,.25);
        }

        .logo{
            font-size:60px;
            color:#6f4e37;
        }

        .brand{
            color:#6f4e37;
            font-weight:700;
        }

        .btn-login{
            background:#6f4e37;
            color:#fff;
            border:none;
        }

        .btn-login:hover{
            background:#5a3c2c;
            color:#fff;
        }

    </style>

</head>

<body>

<div class="login-card">

    <div class="text-center mb-4">

        <i class="fas fa-mug-hot logo"></i>

        <h2 class="brand mt-3">
            Soben Cafe POS
        </h2>

        <p class="text-muted">
            Login To Your Account
        </p>

    </div>

    <?php if(isset($_SESSION['error'])): ?>

        <div class="alert alert-danger">

            <?= $_SESSION['error']; ?>

        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>

    <form action="login.php" method="POST">

        <div class="mb-3">

            <label class="form-label">
                Username
            </label>

            <input type="text"
                   name="username"
                   class="form-control"
                   required>

        </div>

        <div class="mb-4">

            <label class="form-label">
                Password
            </label>

            <div class="input-group">

                <input type="password"
                       name="password"
                       id="password"
                       class="form-control"
                       required>

                <button type="button"
                        class="btn btn-outline-secondary"
                        onclick="togglePassword()">

                    <i class="fa fa-eye"></i>

                </button>

            </div>

        </div>

        <button type="submit"
                class="btn btn-login w-100">

            <i class="fa-solid fa-right-to-bracket"></i>
            Login

        </button>

    </form>

    <div class="text-center mt-4">

        <small class="text-muted">

            © 2026 Soben Cafe Shop

        </small>

    </div>

</div>

<script>

function togglePassword(){

    let password =
        document.getElementById('password');

    if(password.type === 'password'){

        password.type = 'text';

    }else{

        password.type = 'password';

    }
}

</script>

</body>
</html>

