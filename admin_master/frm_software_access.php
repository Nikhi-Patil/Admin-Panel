<?php
session_start();

include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";
$page = $_GET['page'] ?? "software";
?>

<?php if($page=="software"){ ?>
<div class="app-content-header" style="padding:6px .5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Software Access</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end ">
                        <button class="btn btn-secondary me-2" id="syncMaster">
                            <i class="fa-solid fa-rotate"></i>
                            Sync Masters
                        </button>

                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#moduleModal">
                            <i class="fa-solid fa-layer-group"></i>
                            Add Software Module
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#softwareModal">
                            <i class="fa-solid fa-user-shield"></i>
                            Manage Permissions
                        </button>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <button id="export-csv" class="btn btn-warning btn-sm">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </button>
                <button id="print-table" class="btn btn-info btn-sm">
                    <i class="fa-solid fa-print"></i>
                    Print
                </button>
                <div class="card-tools m-0 d-flex align-items-center ms-auto">
                    <div class="input-group input-group-sm" style="width:16rem;">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="table-filter" type="search" class="form-control" placeholder="Search...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="permission_table"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="moduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Software Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="moduleForm">
                    <input type="hidden" id="module_master_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Module Name</label>
                        <input type="text" class="form-control" id="module_name" name="module_name"
                            placeholder="Enter Module Name" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="moduleSaveBtn">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Module
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="softwareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="softwareModalLabel">Add Software Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <form id="softwareForm">
                    <input type="hidden" id="permission_id" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Software Module</label>
                            <select id="module_id" name="module_id" class="form-select" required>
                                <option value="">Select Module</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Assign Masters</h5>
                        <div>
                            <button type="button" id="selectAll" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-check-double"></i>
                                Select All
                            </button>
                            <button type="button" id="clearAll" class="btn btn-danger btn-sm">
                                <i class="fa-solid fa-xmark"></i>
                                Clear All
                            </button>
                        </div>
                    </div>
                    <div id="masterList" class="row">
                        <!-- Loaded by AJAX -->
                    </div>
                    <hr>
                    <button type="submit" id="saveBtn" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Permission
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById("syncMaster").addEventListener("click", function() {
    fetch("qur_software_access.php?action=sync_master")
        .then(res => res.json())
        .then(data => {
            Swal.fire({
                icon: data.status,
                title: data.message
            });
        });
});
</script>
<script>
const table = new Tabulator("#permission_table", {
    ajaxURL: "qur_software_access.php?action=list",
    ajaxConfig: "GET",
    layout: "fitColumns",
    pagination: true,
    paginationSize: 10,
    columns: [{
            title: "ID",
            field: "id",
            width: 70,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Software Module",
            field: "module_name",
            width: 180,
            headerHozAlign: "center"
        },
        {
            title: "Assigned Masters",
            field: "masters",
            widthGrow: 3,
            headerHozAlign: "center"
        },
        {
            title: "Created By",
            field: "created_by",
            width: 120,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Updated By",
            field: "updated_by",
            width: 120,
            hozAlign: "center",
            headerHozAlign: "center"
        },
    ]
});
document.getElementById("table-filter")
    .addEventListener("input", function() {
        let value = this.value.toLowerCase();
        if (value) {
            table.setFilter(function(data) {
                return [
                        data.module_name,
                        data.masters,
                        data.created_by,
                        data.updated_by
                    ]
                    .some(field =>
                        String(field ?? "")
                        .toLowerCase()
                        .includes(value)
                    );
            });
        } else {
            table.clearFilter();
        }
    });
</script>
<script>
// Reset Modal
document.querySelector('[data-bs-target="#softwareModal"]')
    .addEventListener("click", function() {
        document.getElementById("softwareForm").reset();
        document.getElementById("module_id").selectedIndex = 0;
        document.getElementById("masterList").innerHTML = "";
        document.getElementById("softwareModalLabel").innerHTML = "Manage Permissions";
        document.getElementById("saveBtn").innerHTML = "Save Permissions";
    });

document.getElementById("selectAll")
    .addEventListener("click", function() {
        document.querySelectorAll(".master-checkbox")
            .forEach(cb => cb.checked = true);
    });

document.getElementById("clearAll")
    .addEventListener("click", function() {
        document.querySelectorAll(".master-checkbox")
            .forEach(cb => cb.checked = false);
    });

// SAVE SOFTWARE MODULE
document.getElementById("moduleForm").addEventListener("submit", function(e) {
    e.preventDefault();
    let fd = new FormData(this);
    fetch("qur_software_access.php?action=save_module", {
            method: "POST",
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            Swal.fire({
                icon: data.status,
                title: data.message
            });
            if (data.status == "success") {
                bootstrap.Modal
                    .getInstance(document.getElementById("moduleModal"))
                    .hide();
                document.getElementById("moduleForm").reset();
                document.getElementById("masterList").innerHTML = "";
                loadModules();
                table.replaceData();
            }
        });
});
// LOAD MODULES
function loadModules() {
    fetch("qur_software_access.php?action=module")
        .then(res => res.json())
        .then(data => {
            let ddl = document.getElementById("module_id");
            ddl.innerHTML = "<option value=''>Select Module</option>";
            data.forEach(function(row) {
                ddl.innerHTML += `
                <option value="${row.id}">
                    ${row.module_name}
                </option>
            `;
            });
        });
}

document.addEventListener("DOMContentLoaded", function() {
    loadModules();
});

document.querySelector('[data-bs-target="#moduleModal"]')
    .addEventListener("click", function() {
        document.getElementById("moduleForm").reset();
        document.getElementById("module_master_id").value = "";
        document.getElementById("moduleSaveBtn").innerHTML = "Save Module";
    });

// LOAD MASTERS
document.getElementById("module_id").addEventListener("change", function() {
    let module = this.value;
    if (module == "") {
        document.getElementById("masterList").innerHTML = "";
        return;
    }
    document.getElementById("softwareModalLabel").innerHTML = "Manage Permissions";
    document.getElementById("saveBtn").innerHTML = "Save Permissions";
    fetch("qur_software_access.php?action=load_master&module_id=" + module)
        .then(res => res.text())
        .then(html => {
            document.getElementById("masterList").innerHTML = html;
        });
});

// SAVE PERMISSION
document.getElementById("softwareForm")
    .addEventListener("submit", function(e) {
        e.preventDefault();
        let fd = new FormData(this);
        fetch("qur_software_access.php?action=save_permission", {
                method: "POST",
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                Swal.fire({
                    icon: data.status,
                    title: data.message
                });
                if (data.status == "success") {
                    bootstrap.Modal
                        .getInstance(
                            document.getElementById("softwareModal")
                        )
                        .hide();
                    document.getElementById("softwareForm").reset();
                    document.getElementById("masterList").innerHTML = "";
                    if (typeof table !== "undefined") {
                        table.replaceData();
                    }
                }
            });
    });

document.getElementById("export-csv")
    .addEventListener("click", function() {
        table.download("csv", "software_permission.csv");
    });
document.getElementById("print-table")
    .addEventListener("click", function() {
        table.print(false, true);
    });
</script>
</main>
<?php } ?>

<?php
include "../include/footer.php";
include "../include/footer_base.php";
?>