<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";
?>

<div class="app-content-header" style="padding:6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">
                    User Access Master
                </h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userAccessModal">
                            <i class="fas fa-plus"></i>
                            Add User Access
                        </button>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">

                    <button id="export-csv" type="button" class="btn btn-sm btn btn-warning">
                        <i class="fa-solid fa-file-csv"></i>
                        Export CSV
                    </button>
                    <button id="print-table" type="button" class="btn btn-sm btn btn-info">
                        <i class="fa-solid fa-print"></i>
                        Print
                    </button>
                    <div class="card-tools m-0 d-flex align-items-center ms-auto">
                        <div class="input-group input-group-sm" style="width:16rem">
                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input id="table-filter" type="search" class="form-control"
                                placeholder="Filter rows&hellip;" aria-label="Filter rows" />
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="user_access_table"></div>
                    <script>
                    const table = new Tabulator("#user_access_table", {
                        ajaxURL: "qur_user_access.php?action=list",
                        ajaxConfig: "GET",
                        layout: "fitColumns",
                        pagination: true,
                        paginationSize: 10,
                        paginationSizeSelector: [10, 25, 50, 100],

                        initialSort: [{
                            column: "id",
                            dir: "asc"
                        }],


                        columns: [{
                                title: "Employee",
                                field: "employee_name",
                                width: 150,
                                sorter: "number",
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Sub Departments",
                                field: "sub_departments",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Modules",
                                field: "modules",
                                width: 200,
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Created By",
                                field: "created_by",
                                width: 110,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Updated By",
                                field: "updated_by",
                                width: 110,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Action",
                                width: 90,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center",
                                formatter: function(cell) {
                                    let row = cell.getRow().getData();
                                    return `
                                        <button class="btn btn-primary action-btn edit-btn" data-id="${row.id}">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                        <button class="btn btn-danger action-btn delete-btn" data-id="${row.id}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    `;
                                }
                            }
                        ]
                    });
                    </script>

                    <script>
                    //add user access

                    document.querySelector('[data-bs-target="#userAccessModal"]').addEventListener("click", function() {
                        document.getElementById("userAccessForm").reset();
                        document.getElementById("user_access_id").value = "";
                        document.getElementById("userAccessModalLabel").innerHTML = "Add User Access";
                        document.getElementById("saveBtn").innerHTML = "Save";

                    });
                    // EDIT
                    document.addEventListener("click", function(e) {

                        if (e.target.closest(".edit-btn")) {

                            let id = e.target.closest(".edit-btn").dataset.id;

                            fetch("qur_user_access.php?action=get&id=" + id)
                                .then(response => response.json())
                                .then(data => {

                                    const row = data.data || data;
                                    document.getElementById("user_access_id").value = row.id || "";
                                    document.getElementById("employee_id").value = row.employee_id || "";
                                    loadSubDepartments(row.sub_department_ids ?
                                        row.sub_department_ids.split(",") : []);
                                    loadModules(row.module_ids ?
                                        row.module_ids.split(",") : []);

                                    document.getElementById("userAccessModalLabel").innerHTML =
                                        "Edit User Access";
                                    document.getElementById("saveBtn").innerHTML = "Update";

                                    bootstrap.Modal
                                        .getOrCreateInstance(document.getElementById("userAccessModal"))
                                        .show();
                                });
                        }

                    });

                    // DELETE

                    document.addEventListener("click", function(e) {
                        if (e.target.closest(".delete-btn")) {
                            let id = e.target.closest(".delete-btn").dataset.id;
                            if (confirm("Delete this record?")) {

                                fetch("qur_user_access.php?action=delete", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded"
                                        },
                                        body: "id=" + id
                                    })
                                    .then(res => res.json())
                                    .then(res => {
                                        if (res.status == "success") {
                                            table.replaceData();
                                            alert("Deleted Successfully");
                                        } else {
                                            alert(res.message || "Delete failed.");
                                        }
                                    });

                            }

                        }

                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- USER ACCESS MODAL -->
    <div class="modal fade" id="userAccessModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">

                    <h5 class="modal-title" id="userAccessModalLabel"> Add User Access</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="userAccessForm">
                        <input type="hidden" id="user_access_id" name="id">
                        <!-- Employee -->
                        <div class="mb-3">
                            <label class="form-label">Employee</label>
                            <select id="employee_id" name="employee_id" class="form-select" required>
                                <option value="">Select Employee</option>
                            </select>
                        </div>
                        <!-- ================================= -->
                        <div class="row">
                            <!-- Sub Departments -->
                            <div class="col-md-7">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        Sub Departments
                                    </div>
                                    <div class="card-body">
                                        <div id="subDepartmentList" class="row">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modules -->
                            <div class="col-md-5">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        Modules
                                    </div>
                                    <div class="card-body">
                                        <div id="moduleList" class="row">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <button type="submit" id="saveBtn" class="btn btn-success">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    let allEmployees = [];
    let allSubDepartments = [];
    let allModules = [];

    // LOAD EMPLOYEES
    function loadEmployees() {
        fetch("qur_user_access.php?action=employees")
            .then(res => res.json())
            .then(data => {
                allEmployees = data;
                let html = '<option value="">Select Employee</option>';
                data.forEach(emp => {
                    html += `
                <option value="${emp.id}">
                    ${emp.employee_name}
                </option>
            `;
                });
                document.getElementById("employee_id").innerHTML = html;
            });
    }

    // LOAD SUB DEPARTMENTS
    function loadSubDepartments(selected = []) {
        fetch("qur_user_access.php?action=sub_departments")
            .then(res => res.json())
            .then(data => {
                allSubDepartments = data;
                let html = '';
                data.forEach(item => {
                    let checked =
                        selected.includes(String(item.id)) ?
                        "checked" : "";
                    html += `
            <div class="col-md-6 mb-2">
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="sub_department_ids[]"
                        value="${item.id}"
                        ${checked}>
                    <label class="form-check-label">
                    ${item.unit}|${item.department_name}|${item.sub_department_name}
                    </label>
                </div>
            </div>
            `;
                });
                document.getElementById("subDepartmentList").innerHTML = html;
            });
    }

    // LOAD MODULES
    function loadModules(selected = []) {
        fetch("qur_user_access.php?action=modules")
            .then(res => res.json())
            .then(data => {
                allModules = data;
                let html = '';
                data.forEach(item => {
                    let checked =
                        selected.includes(String(item.id)) ?
                        "checked" : "";
                    html += `
            <div class="col-md-6 mb-2">
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="module_ids[]"
                        value="${item.id}"
                        ${checked}>
                    <label class="form-check-label">
                        ${item.module_name}
                    </label>
                </div>
            </div>
            `;
                });
                document.getElementById("moduleList").innerHTML = html;
            });
    }

    // TABULATOR
    </script>
    <script>
    const statusBadge = (cell) => {
        const value = cell.getValue();
        const map = {
            Active: 'success',
            Invited: 'info',
            Suspended: 'secondary'
        };
        const color = map[value] || 'secondary';
        return `<span class="badge text-bg-${color}">${value}</span>`;
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('table-filter').addEventListener('input', (e) => {
            const value = e.target.value;
            if (value) {
                table.setFilter([
                    [{
                            field: 'employee_name',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'sub_departments',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'modules',
                            type: 'like',
                            value: value
                        },
                    ],
                ]);
            } else {
                table.clearFilter();
            }
        });

        document
            .getElementById('print-table')
            .addEventListener('click', () => table.print(false, true));

        document
            .getElementById('export-csv')
            .addEventListener('click', () => table.download('csv', 'user_access.csv'));
    });
    </script>
    <script>
    // LOAD LOOKUPS
    document.addEventListener("DOMContentLoaded", function() {
        loadEmployees();
        loadSubDepartments();
        loadModules();
    });

    // RESET FORM
    function resetForm() {
        document.getElementById("user_access_id").value = "";
        document.getElementById("employee_id").value = "";
        loadSubDepartments();
        loadModules();
    }

    // ADD BUTTON
    document
        .querySelector('[data-bs-target="#userAccessModal"]')
        .addEventListener("click", function() {
            resetForm();
            document.getElementById("userAccessModalLabel").textContent =
                "Add User Access";
            document.getElementById("saveBtn").textContent = "Save";
        });
    // EMPLOYEE CHANGE
    document
        .getElementById("employee_id")
        .addEventListener("change", function() {
            const employee = this.value;

            if (!employee) {
                loadSubDepartments();
                loadModules();
                return;
            }

            fetch("qur_user_access.php?action=get_by_employee&employee_id=" + employee)
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        loadSubDepartments(
                            res.data.sub_department_ids ?
                            res.data.sub_department_ids.split(",") : []
                        );
                        loadModules(
                            res.data.module_ids ?
                            res.data.module_ids.split(",") : []
                        );
                    } else {
                        loadSubDepartments();
                        loadModules();
                    }
                });
        });
    // SAVE
    document.getElementById("userAccessForm").addEventListener("submit", function(e) {

        e.preventDefault();

        let formData = new FormData(this);

        fetch("qur_user_access.php?action=save", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {

                if (data.status === "success") {

                    bootstrap.Modal.getOrCreateInstance(
                        document.getElementById("userAccessModal")
                    ).hide();

                    document.getElementById("userAccessForm").reset();

                    document.getElementById("user_access_id").value = "";

                    document.getElementById("userAccessModalLabel").innerHTML = "Add User Access";

                    document.getElementById("saveBtn").innerHTML = "Save";

                    table.replaceData();

                } else {
                    alert(data);
                }

            });

    });
    </script>
    </main>

    <?php
include "../include/footer.php";
include "../include/footer_base.php";
?>