<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
?>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <style>
    :root {
        --main-option-color: #ffffff;
    }

    .sidebar-menu>.nav-item>.nav-link.main-option {
        color: var(--main-option-color) !important;
        font-weight: 600;
    }

    .sidebar-menu>.nav-item>.nav-link.main-option:hover,
    .sidebar-menu>.nav-item>.nav-link.main-option.active {
        color: #ffffff !important;
        background-color: rgba(0, 186, 242, 0.38) !important;
    }
    </style>

    <div class="app-wrapper">

        <!-- Sidebar -->
        <aside class="app-sidebar " data-bs-theme="dark"
            style="position:fixed; top:70px; left:0; height:calc(100vh - 70px); top: 50px; background-color: #15243a;">

            <div class="sidebar-wrapper">

                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">

                        <!-- Master -->
                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link main-option">
                                <i class="fa-solid fa-folder-tree"></i>
                                <p>
                                    Master
                                    <i class="nav-arrow fa-solid fa-chevron-right"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_employee_master.php" class="nav-link">
                                        <i class="fa-solid fa-user-tie"></i>
                                        <p>Employee Master</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_unit_master.php?page=plant"
                                        class="nav-link">
                                        <i class="fa-solid fa-warehouse"></i>
                                        <p>Unit Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_plant_master.php?page=plant"
                                        class="nav-link">
                                        <i class="fa-solid fa-industry"></i>
                                        <p>Plant Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_department_master.php?page=department"
                                        class="nav-link">
                                        <i class="fa-solid fa-building"></i>
                                        <p>Department Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_sub_department_master.php?page=sub_department"
                                        class="nav-link">
                                        <i class="fa-solid fa-diagram-project"></i>
                                        <p>Sub Department Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_designation_master.php?page=designation"
                                        class="nav-link">
                                        <i class="fa-solid fa-id-badge"></i>
                                        <p>Designation Master</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_incoterms_master.php?page=incoterms"
                                        class="nav-link">
                                        <i class="fa-solid fa-shipping-fast"></i>
                                        <p>Incoterms Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_currency_master.php?page=currency"
                                        class="nav-link">
                                        <i class="fa-solid fa-coins"></i>
                                        <p>Currency Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_supplier_master.php?page=supplier"
                                        class="nav-link">
                                        <i class="fa-solid fa-truck"></i>
                                        <p>Supplier Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_category_master.php?page=category"
                                        class="nav-link">
                                        <i class="fa-solid fa-tags"></i>
                                        <p>Category Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_sub_category_master.php?page=sub_category"
                                        class="nav-link">
                                        <i class="fa-solid fa-sitemap"></i>
                                        <p>Sub Category Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_customer_master.php?page=customer"
                                        class="nav-link">
                                        <i class="fa-solid fa-users"></i>
                                        <p>Customer Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_part_master.php?page=part"
                                        class="nav-link">
                                        <i class="fa-solid fa-puzzle-piece"></i>
                                        <p>Part Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_compound_master.php?page=compound"
                                        class="nav-link">
                                        <i class="fa-solid fa-vials"></i>
                                        <p>Compound Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_molding_machine_master.php?page=molding_machine"
                                        class="nav-link">
                                        <i class="fa-solid fa-cube"></i>
                                        <p>Molding Machine Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_bop_master.php?page=bop" class="nav-link">
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                        <p>BOP Master</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Recycle Bin -->
                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link main-option">
                                <i class="fa-solid fa-trash-can"></i>
                                <p>Recycle Bin</p>
                                <i class="nav-arrow fa-solid fa-chevron-right"></i>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_employee_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-user-tie"></i>
                                        <p>Employee Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_unit_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-warehouse"></i>
                                        <p>Unit Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_plant_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-industry"></i>
                                        <p>Plant Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_department_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-building"></i>
                                        <p>Department Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_designation_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-id-badge"></i>
                                        <p>Designation Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_incoterms_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-shipping-fast"></i>
                                        <p>Incoterms Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_currency_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-coins"></i>
                                        <p>Currency Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_sub_department_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-diagram-project"></i>
                                        <p>Sub Department Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_Supplier_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-truck"></i>
                                        <p>Supplier Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_Category_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-tags"></i>
                                        <p>Category Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_sub_category_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-sitemap"></i>
                                        <p>Sub Category Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_customer_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-users"></i>
                                        <p>Customer Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_part_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-puzzle-piece"></i>
                                        <p>Part Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_compound_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-vials"></i>
                                        <p>Compound Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_molding_machine_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-cube"></i>
                                        <p>Molding Machine Master</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_bop_master.php?page=recycle"
                                        class="nav-link">
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                        <p>BOP Master</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Settings -->
                        <li class="nav-item">
                            <a href="javascript:void(0)" class="nav-link main-option">
                                <i class="fa-solid fa-gear"></i>
                                <p>
                                    Settings
                                    <i class="nav-arrow fa-solid fa-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>reset_password.php" class="nav-link">
                                        <i class="fa-solid fa-key"></i>
                                        <p>Reset Password</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/index.php" class="nav-link">
                                        <i class="fa-solid fa-user-plus"></i>
                                        <p>Pass on User</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/index.php" class="nav-link">
                                        <i class="fas fa-user-cog"></i>
                                        <p>Active/Inactive User</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?= BASE_URL ?>admin_master/frm_software_access.php?page=software"
                                        class="nav-link">
                                        <i class="fa-solid fa-laptop-code"></i>
                                        <p>Software Access</p>
                                    </a>
                                </li>
                            </ul>
                        </li> <!-- Settings -->

                    </ul>
                </nav>
            </div> <!-- sidebar-wrapper -->
        </aside>

        <!-- Main Content -->
        <main class="app-main" style="margin-left:250px; margin-top:80px; padding:20px;">