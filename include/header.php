<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm"
    style="position:sticky; top:0; z-index:1050; padding: 0px;" id="navigation" tabindex="-1 ">

    <div class="container-fluid" style="background-color: #15243a;">

        <!-- Sidebar Toggle -->
        <button class="btn btn-outline-secondary me-3 p-1" id="sidebarToggle" style=" background-color: #ffffff ;">
            <img src="<?= BASE_URL ?>assets/img/jayshreemain.png" alt="Logo" width="40" height="40">
        </button>

        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="frm_employee_master.php" style="color: rgb(255 255 255)">
            Admin Panel
        </a>
        <!-- Right Side -->
        <ul class=" navbar-nav ms-auto align-items-center">



            <!-- User -->
            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown"
                    style="color: rgb(255 255 255)">

                    <img src="<?= BASE_URL ?>assets/img/profile.jpg" width="35" height="35" class="rounded-circle me-2">

                    <span style="color: rgb(255 255 255) ">
                        <b><?php echo $_SESSION['user_id']; ?></b>
                    </span>

                </a>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="fa-solid fa-user"></i>
                            Profile
                        </a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item text-danger" href="../login.php">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            Logout
                        </a>
                    </li>

                </ul>

            </li>

        </ul>

    </div>

</nav>