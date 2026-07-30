<?php
session_start();
include "inc/db_cfg.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
   

    $password = $_POST['password'];
///$
    $stmt = $conn->prepare("
            SELECT
                user_id,
                username,
                user_name,
                password,
                first_login
            FROM login
            WHERE username=?
    

    ");
    

    $stmt->bind_param("s", $username);
  
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $row = $result->fetch_assoc();

        // Verify hashed password
        if ($password == $row['password']) {

            $_SESSION['user_id']   = $row['user_id'];
            $_SESSION['username']  = $row['username'];   // Email
            $_SESSION['user_name'] = $row['user_name'];  // Display Name
            $_SESSION['level']     = 'employee';

            if ((int)($row['first_login'] ?? 0) === 1) {
                header("Location: reset_password.php");
            } else {
                header("Location: admin_master/frm_employee_master.php");
            }

            exit;

        } else {
            $error = "Invalid email or password.";
        }

    } else {
        $error = "Invalid email or password.";
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
    <title>Login</title>
</head>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
}

.container-fluid,
.row {
    height: 100vh;
}

/* LEFT SIDE */

.bg-section {
    background: url("assets/img/bg.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    height: 100vh;
}

.overlay {
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, .5);

    display: flex;
    justify-content: center;
    align-items: center;
}

.content {
    color: #fff;
    text-align: center;
    padding: 40px;
}

.content h1 {
    font-size: 48px;
    font-weight: bold;
}

.content p {
    font-size: 20px;
}

/* RIGHT SIDE */

.form-section {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: #fff;
}

.login-box {
    width: 70%;
    max-width: 400px;
}

.login-box h2 {
    text-align: center;
    margin-bottom: 30px;
}

.form-control {
    height: 45px;
}

.btn {
    height: 45px;
}

/* Mobile */

@media(max-width:768px) {

    .bg-section {
        height: 250px;
    }

    .form-section {
        height: auto;
        padding: 40px 20px;
    }

}

/* Password Field Wrapper */
.password-wrapper {
    position: relative;
    width: 100%;
}

/* Password Input */
.password-wrapper input {
    width: 100%;
    padding-right: 45px;
}

/* Eye Icon */
.toggle-password {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6c757d;
    font-size: 18px;
    transition: color 0.3s ease;
}

/* Hover Effect */
.toggle-password:hover {
    color: #000;
}
</style>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- Left Side -->
            <div class="col-md-6 bg-section">
                <div class="overlay">
                    <div class="content">
                        <h1>Welcome to Jayashree Polymers</h1>
                    </div>
                </div>
            </div>

            <!-- Right Side -->
            <div class="col-md-6 form-section">

                <div class="login-box">

                    <div class="text-center mb-4">

                        <img src="assets/img/jayshreemain.png" alt="Company Logo" class="login-logo"
                            style="width: 200px; higth: 200px;">

                        <h2 class="mt-3"><b>Login</b></h2>

                    </div>

                    <form method="POST" action="">

                        <div class="form-group">
                            <label>Email ID</label>
                            <input type="email" name="username" class="form-control" placeholder="Enter Email" required>
                        </div>

                        <div class="form-group">
                            <label>Password</label>

                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" class="form-control"
                                    placeholder="Enter Password" required>

                                <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark btn-block">
                            Login
                        </button>

                    </form>

                </div>

            </div>

        </div>
    </div>
    <?php if (!empty($error)) { ?>
    <script>
    alert("<?= htmlspecialchars($error) ?>");
    </script>
    <?php } ?>
    <script>
    document.addEventListener("DOMContentLoaded", function() {

        const togglePassword = document.getElementById("togglePassword");
        const password = document.getElementById("password");

        togglePassword.addEventListener("click", function() {

            if (password.type === "password") {
                password.type = "text";
                this.classList.remove("fa-eye");
                this.classList.add("fa-eye-slash");
            } else {
                password.type = "password";
                this.classList.remove("fa-eye-slash");
                this.classList.add("fa-eye");
            }

        });

    });
    </script>
</body>

</html>