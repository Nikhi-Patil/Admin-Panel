<?php
session_start();
include "inc/db_cfg.php";

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$error = "";
$user_id = (int)$_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password === '' || $confirm_password === '') {
        $error = "Please enter both password fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = $new_password ;

        $stmt = $conn->prepare(
            "UPDATE login
            SET password = ?, first_login = 0
            WHERE user_id = ?"
        );
        $stmt->bind_param("si", $new_password, $user_id);

        if ($stmt->execute()) {

            if ($stmt->affected_rows > 0) {
                header("Location: admin_master/index.php");
                exit;
            } else {
                $error = "Password was not updated. User not found.";
            }

        } else {
            $error = $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Reset Password</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f4f6f9;
    }

    .page-wrap {
        min-height: 100vh;
        display: flex;
        align-items: center;
    }

    .card-shell {
        max-width: 460px;
        width: 100%;
        margin: 0 auto;
    }

    .password-wrapper {
        position: relative;
        width: 100%;
    }

    .password-wrapper input {
        padding-right: 45px;
    }

    .toggle-password {
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
        font-size: 18px;
    }
    </style>
</head>

<body>
    <div class="container page-wrap">
        <div class="card card-shell shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="assets/img/jayshreemain.png" alt="Logo" style="width:180px;">
                    <h3 class="mt-3 mb-0">Reset Password</h3>
                    <p class="text-muted mb-0"><?= htmlspecialchars($username) ?></p>
                </div>

                <?php if ($error !== "") { ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php } ?>

                <form method="POST">
                    <div class="form-group">
                        <label>New Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <i class="fa-solid fa-eye toggle-password" data-target="new_password"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                required>
                            <i class="fa-solid fa-eye toggle-password" data-target="confirm_password"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark btn-block">Update Password</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.toggle-password').forEach(icon => {
        icon.addEventListener('click', function() {
            const target = document.getElementById(this.dataset.target);
            if (target.type === 'password') {
                target.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                target.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });
    </script>
</body>

</html>