<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";
?>
<?php
$page = $_GET['page'] ?? 'supplier';
?>
<?php if($page=="supplier"){ ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Supplier List</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#supplierModal">
                            <i class="fas fa-plus"></i>
                            Add Supplier
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

                    <div id="supplier_table"></div>
                    <!-- disply table   -->
                    <script>
                    const table = new Tabulator("#supplier_table", {
                        ajaxURL: "qur_supplier_master.php?action=list",
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
                                title: "Supplier Name",
                                field: "supplier_name",
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Location",
                                field: "location",
                                width: 140,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Email",
                                field: "email",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Contact",
                                field: "contact_no",
                                width: 150,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Created By",
                                field: "created_by",
                                width: 130,
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
                    </script>
                    <!-- script for the delete and edit button  -->
                    <script>
                    document.addEventListener("click", function(e) {

                        if (e.target.closest(".delete-btn")) {

                            let id = e.target.closest(".delete-btn").dataset.id;

                            if (confirm("Delete this record?")) {

                                fetch("qur_supplier_master.php?action=delete", {
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

                    // Edit Button
                    document.addEventListener("click", function(e) {

                        if (e.target.closest(".edit-btn")) {

                            let id = e.target.closest(".edit-btn").dataset.id;

                            fetch("qur_supplier_master.php?action=get&id=" + id)
                                .then(response => response.json())
                                .then(data => {

                                    document.getElementById("supplier_id").value = data.id;
                                    document.getElementById("supplier_name").value = data.supplier_name;
                                    document.getElementById("location").value = data.location;
                                    document.getElementById("email").value = data.email;
                                    document.getElementById("contact_no").value = data.contact_no;

                                    document.getElementById("supplierModalLabel").innerHTML =
                                        "Edit supplier";
                                    document.getElementById("saveBtn").innerHTML = "Update";

                                    new bootstrap.Modal(document.getElementById("supplierModal")).show();
                                });
                        }

                    });

                    // Add supplier Button
                    document.querySelector('[data-bs-target="#supplierModal"]').addEventListener("click", function() {

                        document.getElementById("supplierForm").reset();

                        document.getElementById("supplier_id").value = "";

                        document.getElementById("supplierModalLabel").innerHTML = "Add Supplier";

                        document.getElementById("saveBtn").innerHTML = "Save";

                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- form for  supplier  -->
    <div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="supplierModalLabel">Add Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form id="supplierForm">

                        <input type="hidden" id="supplier_id" name="id">

                        <div class="mb-3">
                            <label>Supplier Name</label>
                            <input type="text" id="supplier_name" name="supplier_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Location</label>
                            <input type="text" id="location" name="location" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Email Id</label>

                            <input id="email" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Contact</label>

                            <input id="contact_no" name="contact_no" class="form-control">
                        </div>
                        <button type="submit" id="saveBtn" class="btn btn-success">
                            Save
                        </button>

                    </form>

                </div>

                <script>
                document.getElementById("supplierForm").addEventListener("submit", function(e) {

                    e.preventDefault();

                    let formData = new FormData(this);

                    fetch("qur_supplier_master.php?action=save", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {

                            if (data.trim() == "success") {

                                bootstrap.Modal.getInstance(
                                    document.getElementById("supplierModal")
                                ).hide();

                                document.getElementById("supplierForm").reset();

                                document.getElementById("supplier_id").value = "";

                                document.getElementById("supplierModalLabel").innerHTML = "Add supplier";

                                document.getElementById("saveBtn").innerHTML = "Save";

                                table.replaceData();

                            } else {
                                alert(data);
                            }

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
                table.setFilter([
                    [{
                            field: 'supplier_name',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'location',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'id',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'email',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'contact_no',
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
            .addEventListener('click', () => table.download('csv', 'supplier_master.csv'));
    });
    </script>
    <?php } ?>


    <?php if($page=="recycle"){ ?>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Supplier Recycle Bin</h1>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <div class="card">

                    <div class="card-body">
                        <div id="supplier_table"></div>
                        <!-- disply table   -->
                        <script>
                        const table = new Tabulator("#supplier_table", {
                            ajaxURL: "qur_supplier_master.php?action=list1",
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
                                    title: "Supplier Name",
                                    field: "supplier_name",
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Location",
                                    field: "location",
                                    width: 140,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Email",
                                    field: "email",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Contact",
                                    field: "contact_no",
                                    width: 150,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Created By",
                                    field: "created_by",
                                    width: 130,
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
                        </script>
                        <script>
                        document.addEventListener("click", function(e) {

                            let btn = e.target.closest(".recycle-btn");

                            if (!btn) return;

                            let id = btn.dataset.id;

                            if (confirm("Restore this Supplier?")) {

                                fetch("qur_supplier_master.php?action=restore", {
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
                                            alert("Supplier restored successfully.");
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