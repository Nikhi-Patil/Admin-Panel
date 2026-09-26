<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";?>

<?php
$page = $_GET['page'] ?? 'sub_department';
?>
<?php if($page=="sub_department"){ ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Sub Department List</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#sub_departmentModal">
                            <i class="fas fa-plus"></i>
                            Add Sub Department
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
                        <div class="input-group input-group-sm" style="width: 16rem">
                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input id="table-filter" type="search" class="form-control"
                                placeholder="Filter rows&hellip;" aria-label="Filter rows" />
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <div id="sub_department_table"></div>
                    <!-- disply table   -->
                    <script>
                    const table = new Tabulator("#sub_department_table", {
                        ajaxURL: "qur_sub_department_master.php?action=list",
                        ajaxConfig: "GET",
                        layout: "fitColumns",

                        pagination: true,
                        paginationSize: 10,
                        hozAlign: "center",
                        paginationSizeSelector: [10, 25, 50, 100],

                        initialSort: [{
                            column: "id",
                            dir: "asc"
                        }],

                        columns: [{
                                title: "ID",
                                field: "id",
                                width: 120,
                                sorter: "number",
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Unit",
                                field: "unit",
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Department",
                                field: "department_name",
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Sub Department",
                                field: "sub_department_name",
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Created By",
                                field: "created_by",
                                width: 150,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Updated By",
                                field: "updated_by",
                                width: 150,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"

                            },
                            {
                                title: "Action",
                                width: 100,
                                hozAlign: "center",
                                resizable: false,
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
                    </script>
                    <!-- script for the delete and edit button  -->
                    <script>
                    document.addEventListener("click", function(e) {

                        if (e.target.closest(".delete-btn")) {

                            let id = e.target.closest(".delete-btn").dataset.id;

                            if (confirm("Delete this record?")) {

                                fetch("qur_sub_department_master.php?action=delete", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded"
                                        },
                                        body: "id=" + id
                                    })
                                    .then(res => res.json())
                                    .then(res => {
                                        if (res.status == "success") {
                                            table.deleteRow(id);
                                            alert("Deleted Successfully");
                                        }
                                    });

                            }

                        }

                    });

                    // Edit Button
                    document.addEventListener("click", function(e) {

                        if (e.target.closest(".edit-btn")) {

                            let id = e.target.closest(".edit-btn").dataset.id;

                            fetch("qur_sub_department_master.php?action=get&id=" + id)
                                .then(response => response.json())
                                .then(data => {

                                    document.getElementById("sub_department_id").value = data.id;
                                    document.getElementById("unit_id").value = data.unit_id;
                                    document.getElementById("department_id").value = data.department_id;
                                    document.getElementById("sub_department_name").value = data
                                        .sub_department_name;

                                    document.getElementById("sub_departmentModalLabel").innerHTML =
                                        "Edit Sub Department";
                                    document.getElementById("saveBtn").innerHTML = "Update";

                                    new bootstrap.Modal(document.getElementById("sub_departmentModal"))
                                        .show();
                                });
                        }

                    });

                    // Add Department Button
                    document.querySelector('[data-bs-target="#sub_departmentModal"]').addEventListener("click",
                        function() {

                            document.getElementById("sub_departmentForm").reset();

                            document.getElementById("sub_department_id").value = "";

                            document.getElementById("sub_departmentModalLabel").innerHTML = "Add Sub Department";

                            document.getElementById("saveBtn").innerHTML = "Save";

                        });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- form for  Department  -->
    <div class="modal fade" id="sub_departmentModal" tabindex="-1" aria-labelledby="sub_departmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="sub_departmentModalLabel">Add Sub Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form id="sub_departmentForm">

                        <input type="hidden" name="id" id="sub_department_id">

                        <div class="mb-3">
                            <label>Unit</label>
                            <select class="form-select" id="unit_id" name="unit_id" required>
                                <option value="">Select Unit</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Department</label>
                            <select class="form-select" id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Sub Department</label>
                            <input type="text" class="form-control" id="sub_department_name" name="sub_department_name"
                                required>
                        </div>

                        <button type="submit" class="btn btn-success" id="saveBtn">
                            Save
                        </button>

                    </form>

                </div>

                <script>
                document.getElementById("sub_departmentForm").addEventListener("submit", function(e) {

                    e.preventDefault();

                    let formData = new FormData(this);

                    fetch("qur_sub_department_master.php?action=save", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {

                            if (data.status === "success") {

                                bootstrap.Modal.getInstance(
                                    document.getElementById("sub_departmentModal")
                                ).hide();

                                document.getElementById("sub_departmentForm").reset();

                                document.getElementById("sub_department_id").value = "";

                                document.getElementById("sub_departmentModalLabel").innerHTML =
                                    "Add Sub Department";

                                document.getElementById("saveBtn").innerHTML = "Save";

                                table.setData("qur_sub_department_master.php?action=list");
                            } else {
                                alert(data.message || "Save failed.");
                            }

                        });

                });

                fetch("qur_sub_department_master.php?action=units")
                    .then(res => res.json())
                    .then(data => {

                        let unit = document.getElementById("unit_id");

                        unit.innerHTML = '<option value="">Select Unit</option>';

                        data.forEach(item => {
                            unit.innerHTML += `
                <option value="${item.id}">
                    ${item.unit}
                </option>
            `;
                        });

                    });

                fetch("qur_sub_department_master.php?action=departments")
                    .then(res => res.json())
                    .then(data => {

                        let dept = document.getElementById("department_id");

                        data.forEach(item => {

                            dept.innerHTML += `
            <option value="${item.id}">
                ${item.department_name}
            </option>
        `;

                        });

                    });
                </script>


            </div>
        </div>
    </div>
    </main>
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
                table.setFilter([{
                        field: 'sub_department_name',
                        type: 'like',
                        value: value
                    },
                    {
                        field: 'id',
                        type: 'like',
                        value: value
                    }
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
            .addEventListener('click', () => {
                table.download("csv", "sub_department.csv");
            });
    });
    </script>




    <?php } ?>
    <?php if($page=="recycle"){ ?>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Sub Department Recycle Bin</h1>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <div class="card">

                    <div class="card-body">
                        <div id="sub_department_table"></div>
                        <!-- disply tableb   -->
                        <script>
                        const table = new Tabulator("#sub_department_table", {
                            ajaxURL: "qur_sub_department_master.php?action=list1",
                            ajaxConfig: "GET",
                            layout: "fitColumns",

                            pagination: true,
                            paginationSize: 10,
                            hozAlign: "center",
                            paginationSizeSelector: [10, 25, 50, 100],

                            initialSort: [{
                                column: "id",
                                dir: "asc"
                            }],

                            columns: [{
                                    title: "ID",
                                    field: "id",
                                    width: 120,
                                    sorter: "number",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Unit",
                                    field: "unit",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Department",
                                    field: "department_name",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Sub Department",
                                    field: "sub_department_name",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Created By",
                                    field: "created_by",
                                    width: 120,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Deleted By",
                                    field: "updated_by",
                                    width: 120,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"

                                },
                                {
                                    title: "Action",
                                    width: 100,
                                    hozAlign: "center",
                                    resizable: false,
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
                        </script>
                        <script>
                        document.addEventListener("click", function(e) {

                            let btn = e.target.closest(".recycle-btn");

                            if (!btn) return;

                            let id = btn.dataset.id;

                            if (confirm("Restore this Sub Department?")) {

                                fetch("qur_sub_department_master.php?action=restore", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded"
                                        },
                                        body: "id=" + id
                                    })
                                    .then(res => res.json())
                                    .then(res => {

                                        if (res.status == "success") {
                                            table.deleteRow(id);
                                            alert("Sub Department restored successfully.");
                                        } else {
                                            alert("Restore failed.");
                                        }

                                    });

                            }

                        });
                        </script>
                    </div>
                </div>
            </div>
        </div>
        </main>


        <?php } ?>



        <?php
include "../include/footer.php";
include "../include/footer_base.php";

?>