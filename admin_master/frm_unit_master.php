<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";
?>
<?php
$page = $_GET['page'] ?? 'plant';
?>
<?php if($page=="plant"){ ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Unit List</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#plantModal">
                            <i class="fas fa-plus"></i>
                            Add Unit
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

                    <div id="unit_table"></div>
                    <!-- disply table   -->
                    <script>
                    const table = new Tabulator("#unit_table", {
                        ajaxURL: "qur_unit_master.php?action=list",
                        ajaxConfig: "GET",
                        layout: "fitColumns",

                        pagination: true,
                        paginationSize: 10,
                        hozAlign: "center",

                        columns: [{
                                title: "ID",
                                field: "id",
                                width: 120,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Unit",
                                field: "unit",
                                width: 150,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Location",
                                field: "location",
                                width: 150,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Address",
                                field: "address",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Created By",
                                field: "created_by",
                                width: 160,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Updated By",
                                field: "updated_by",
                                width: 160,
                                hozAlign: "center",
                                headerHozAlign: "center"

                            },
                            {
                                title: "Action",
                                width: 100,
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

                                fetch("qur_unit_master.php?action=delete", {
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

                            fetch("qur_unit_master.php?action=get&id=" + id)
                                .then(response => response.json())
                                .then(data => {

                                    document.getElementById("plant_id").value = data.id;
                                    document.getElementById("unit").value = data.unit;
                                    document.getElementById("location").value = data.location
                                    document.getElementById("address").value = data.address;

                                    document.getElementById("plantModalLabel").innerHTML = "Edit Unit";
                                    document.getElementById("saveBtn").innerHTML = "Update";

                                    new bootstrap.Modal(document.getElementById("plantModal")).show();
                                });
                        }

                    });

                    // Add Plant Button
                    document.querySelector('[data-bs-target="#plantModal"]').addEventListener("click", function() {

                        document.getElementById("plantForm").reset();

                        document.getElementById("plant_id").value = "";

                        document.getElementById("plantModalLabel").innerHTML = "Add Unit";

                        document.getElementById("saveBtn").innerHTML = "Save";

                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- form for  Unit  -->
    <div class="modal fade" id="plantModal" tabindex="-1" aria-labelledby="plantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="plantModalLabel">Add Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form id="plantForm">

                        <input type="hidden" id="plant_id" name="id">

                        <div class="mb-3">
                            <label>Unit</label>
                            <input type="text" id="unit" name="unit" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Location</label>
                            <input type="text" id="location" name="location" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Address</label>

                            <textarea id="address" name="address" class="form-control" required></textarea>
                        </div>
                        <button type="submit" id="saveBtn" class="btn btn-success">
                            Save
                        </button>

                    </form>

                </div>

                <script>
                document.getElementById("plantForm").addEventListener("submit", function(e) {

                    e.preventDefault();

                    let formData = new FormData(this);

                    fetch("qur_unit_master.php?action=save", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {

                            if (data.trim() == "success") {

                                bootstrap.Modal.getInstance(
                                    document.getElementById("plantModal")
                                ).hide();

                                document.getElementById("plantForm").reset();

                                document.getElementById("plant_id").value = "";

                                document.getElementById("plantModalLabel").innerHTML = "Add Unit";

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
                            field: 'unit',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'address',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'id',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'location',
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
            .addEventListener('click', () => table.download('csv', 'unit_master.csv'));
    });
    </script>

    <?php } ?>


    <?php if($page=="recycle"){ ?>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Unit List</h1>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Unit</h3>

                    </div>
                    <div class="card-body">
                        <div id="unit_table"></div>
                        <!-- disply table   -->
                        <script>
                        const table = new Tabulator("#unit_table", {
                            ajaxURL: "qur_unit_master.php?action=list1",
                            ajaxConfig: "GET",
                            layout: "fitColumns",

                            pagination: true,
                            paginationSize: 10,
                            hozAlign: "center",

                            columns: [{
                                    title: "ID",
                                    field: "id",
                                    width: 120,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Unit",
                                    field: "unit",
                                    width: 150,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Location",
                                    field: "location",
                                    width: 150,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Address",
                                    field: "address",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Created By",
                                    field: "created_by",
                                    width: 160,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Deleted By",
                                    field: "updated_by",
                                    width: 160,
                                    hozAlign: "center",
                                    headerHozAlign: "center"

                                },
                                {
                                    title: "Action",
                                    width: 100,
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

                            if (confirm("Restore this Unit?")) {

                                fetch("qur_unit_master.php?action=restore", {
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
                                            alert("Unit restored successfully.");
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
