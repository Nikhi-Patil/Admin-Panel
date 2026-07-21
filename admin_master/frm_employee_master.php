<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";

$page = $_GET['page'] ?? 'employee';
?>

<style>
body {
    font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.small-popup {
    padding: 0.75rem !important;
    border-radius: 0.9rem !important;
}

.small-title {
    font-size: 1rem !important;
    line-height: 1.2 !important;
    margin: 0 !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($page == "employee") { ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Employee Master</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#employeeModal">
                            <i class="fas fa-plus"></i>
                            Add Employee
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
                <button id="export-csv" type="button" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </button>

                <button id="print-table" type="button" class="btn btn-sm btn-info">
                    <i class="fa-solid fa-print"></i>
                    Print
                </button>

                <div class="card-tools m-0 d-flex align-items-center ms-auto">
                    <div class="input-group input-group-sm" style="width: 16rem">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="table-filter" type="search" class="form-control" placeholder="Filter rows&hellip;"
                            aria-label="Filter rows" />
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div id="employee_table"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="employeeForm">
                    <input type="hidden" id="employee_id" name="id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Employee Name</label>
                            <input type="text" id="employee_name" name="employee_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Location</label>
                            <input type="text" id="location" name="location" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Contact No</label>
                            <input type="text" id="contact_no" name="contact_no" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Designation</label>
                            <select class="form-select" id="designation_id" name="designation_id" required>
                                <option value="">Select Designation</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Department</label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Level</label>
                            <select class="form-select" id="level_select" required>
                                <option value="">Select Level</option>
                                <option value="Level 1">Level 1</option>
                                <option value="Level 2">Level 2</option>
                                <option value="Level 3">Level 3</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3 d-none" id="level_other_wrap">
                            <label>Other Level</label>
                            <input type="text" id="level_other" class="form-control" placeholder="Enter level">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success" id="saveBtn">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const presetLevels = ["Level 1", "Level 2", "Level 3"];

const levelSelect = document.getElementById("level_select");
const levelOtherWrap = document.getElementById("level_other_wrap");
const levelOther = document.getElementById("level_other");

function toggleLevelOther() {
    if (!levelOtherWrap || !levelOther) {
        return;
    }

    if (levelSelect.value === "Other") {
        levelOtherWrap.classList.remove("d-none");
        levelOther.required = true;
    } else {
        levelOtherWrap.classList.add("d-none");
        levelOther.required = false;
        levelOther.value = "";
    }
}

levelSelect.addEventListener("change", toggleLevelOther);

const table = new Tabulator("#employee_table", {
    ajaxURL: "qur_employee_master.php?action=list",
    ajaxConfig: "GET",
    layout: "fitColumns",
    pagination: true,
    paginationSize: 10,
    hozAlign: "center",
    columns: [{
            title: "ID",
            field: "id",
            width: 55,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Employee Name",
            field: "employee_name",
            width: 160,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Email",
            field: "email",
            width: 180,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Location",
            field: "location",
            width: 105,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Contact No",
            field: "contact_no",
            width: 140,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Designation",
            field: "designation_name",
            width: 130,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Department",
            field: "department_name",
            width: 130,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Level",
            field: "level",
            width: 80,
            hozAlign: "center",
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
            width: 130,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Action",
            width: 90,
            hozAlign: "center",
            headerHozAlign: "center",
            formatter: function(cell) {
                let row = cell.getRow().getData();
                return `
                    <button class="btn btn-primary action-btn edit-btn" data-id="${row.id}">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button class="btn btn-danger action-btn delete-btn" data-id="${row.id}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;
            }
        }
    ]
});

function loadOptions(url, elementId, labelField) {
    fetch(url)
        .then(res => res.json())
        .then(data => {
            const el = document.getElementById(elementId);
            el.innerHTML = `<option value="">Select ${labelField}</option>`;
            data.forEach(item => {
                el.innerHTML += `<option value="${item.id}">${item[labelField]}</option>`;
            });
        });
}

loadOptions("qur_employee_master.php?action=designations", "designation_id", "designation");
loadOptions("qur_employee_master.php?action=departments", "department_id", "department_name");

document.addEventListener("click", function(e) {
    const deleteBtn = e.target.closest(".delete-btn");
    const editBtn = e.target.closest(".edit-btn");

    if (deleteBtn) {
        const id = deleteBtn.dataset.id;
        if (confirm("Delete this employee?")) {
            fetch("qur_employee_master.php?action=delete", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "id=" + encodeURIComponent(id)
                })
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        table.replaceData();
                        alert("Deleted Successfully");
                    } else {
                        alert(res.message || "Delete failed.");
                    }
                });
        }
    }

    if (editBtn) {
        const id = editBtn.dataset.id;
        fetch("qur_employee_master.php?action=get&id=" + encodeURIComponent(id))
            .then(response => response.json())
            .then(data => {
                document.getElementById("employee_id").value = data.id || "";
                document.getElementById("employee_name").value = data.employee_name || "";
                document.getElementById("email").value = data.email || "";
                document.getElementById("location").value = data.location || "";
                document.getElementById("contact_no").value = data.contact_no || "";
                document.getElementById("designation_id").value = data.designation_id || "";
                document.getElementById("department_id").value = data.department_id || "";

                if (presetLevels.includes(data.level)) {
                    levelSelect.value = data.level;
                    toggleLevelOther();
                } else {
                    levelSelect.value = "Other";
                    toggleLevelOther();
                    levelOther.value = data.level || "";
                }

                document.getElementById("employeeModalLabel").innerHTML = "Edit Employee";
                document.getElementById("saveBtn").innerHTML = "Update";
                new bootstrap.Modal(document.getElementById("employeeModal")).show();
            });
    }
});

document.querySelector('[data-bs-target="#employeeModal"]').addEventListener("click", function() {
    document.getElementById("employeeForm").reset();
    document.getElementById("employee_id").value = "";
    document.getElementById("employeeModalLabel").innerHTML = "Add Employee";
    document.getElementById("saveBtn").innerHTML = "Save";
    toggleLevelOther();
});

document.getElementById("employeeForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const levelValue = levelSelect.value === "Other" ? levelOther.value.trim() : levelSelect.value;
    formData.set("level", levelValue);

    fetch("qur_employee_master.php?action=save", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                try {
                    const modalEl = document.getElementById("employeeModal");
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal
                        .getOrCreateInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }

                    document.getElementById("employeeForm").reset();
                    document.getElementById("employee_id").value = "";
                    document.getElementById("employeeModalLabel").innerHTML = "Add Employee";
                    document.getElementById("saveBtn").innerHTML = "Save";
                    toggleLevelOther();
                    table.replaceData();

                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Saved Successfully",
                        html: data.temp_password ?
                            "Username: <b>" + (data.username || "") + "</b><br>" +
                            "Temporary Password: <b>" + data.temp_password + "</b><br><br>" +
                            "The employee will be asked to reset it on first login." : "",
                        showConfirmButton: false,
                        timer: data.temp_password ? 2500 : 1500,
                        width: data.temp_password ? "320px" : "260px",
                        customClass: {
                            popup: "small-popup",
                            title: "small-title"
                        }
                    });
                } catch (uiError) {
                    console.error("Employee save UI error:", uiError);
                    table.replaceData();
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Saved Successfully",
                        showConfirmButton: false,
                        timer: 1500,
                        width: "260px",
                        customClass: {
                            popup: "small-popup",
                            title: "small-title"
                        }
                    });
                }
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: data.message || "Save failed.",
                    showConfirmButton: true,
                    width: "300px",
                    customClass: {
                        popup: "small-popup",
                        title: "small-title"
                    }
                });
            }
        })
        .catch(() => {
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Save failed.",
                showConfirmButton: true,
                width: "300px",
                customClass: {
                    popup: "small-popup",
                    title: "small-title"
                }
            });
        });
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('table-filter').addEventListener('input', (e) => {
        const value = e.target.value;
        if (value) {
            const term = value.toLowerCase();
            table.setFilter(function(data) {
                return [
                    data.id,
                    data.employee_name,
                    data.email,
                    data.location,
                    data.contact_no,
                    data.designation_name,
                    data.department_name,
                    data.level
                ].some(field => String(field ?? "").toLowerCase().includes(term));
            });
        } else {
            table.clearFilter();
        }
    });

    document.getElementById('print-table').addEventListener('click', () => table.print(false, true));
    document.getElementById('export-csv').addEventListener('click', () => table.download('csv',
        'employee_master.csv'));
});
</script>
</main>
<?php } ?>

<?php if ($page == "recycle") { ?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Employee Recycle Bin</h1>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <button id="export-csv" type="button" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </button>
            </div>
            <div class="card-body">
                <div id="employee_recycle_table"></div>
            </div>
        </div>
    </div>
</div>

<script>
const recycleTable = new Tabulator("#employee_recycle_table", {
    ajaxURL: "qur_employee_master.php?action=list1",
    ajaxConfig: "GET",
    layout: "fitColumns",
    pagination: true,
    paginationSize: 10,
    columns: [{
            title: "ID",
            field: "id",
            width: 55,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Employee Name",
            field: "employee_name",
            width: 160,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Email",
            field: "email",
            width: 180,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Location",
            field: "location",
            width: 105,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Contact No",
            field: "contact_no",
            width: 140,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Designation",
            field: "designation_name",
            width: 130,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Department",
            field: "department_name",
            width: 130,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Level",
            field: "level",
            width: 80,
            hozAlign: "center",
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
            width: 130,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Action",
            width: 90,
            hozAlign: "center",
            headerHozAlign: "center",
            formatter: function(cell) {
                let row = cell.getRow().getData();
                return `
                    <button class="btn btn-primary action-btn recycle-btn" data-id="${row.id}">
                        <i class="fa-solid fa-trash-arrow-up"></i>
                    </button>
                `;
            }
        }
    ]
});

document.addEventListener("click", function(e) {
    const btn = e.target.closest(".recycle-btn");
    if (!btn) return;

    const id = btn.dataset.id;
    if (confirm("Restore this employee?")) {
        fetch("qur_employee_master.php?action=restore", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "id=" + encodeURIComponent(id)
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    recycleTable.replaceData();
                    alert("Employee restored successfully.");
                } else {
                    alert(res.message || "Restore failed.");
                }
            });
    }
});

document.getElementById('export-csv').addEventListener('click', () => recycleTable.download('csv',
    'employee_recycle_bin.csv'));
</script>
</main>
<?php } ?>

<?php
include "../include/footer.php";
include "../include/footer_base.php";
?>