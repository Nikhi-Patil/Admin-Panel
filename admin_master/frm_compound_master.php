<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";
?>
<?php
$page = $_GET['page'] ?? 'compound';
?>
<?php if($page=="compound"){ ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Compound List</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#compoundModal">
                            <i class="fas fa-plus"></i>
                            Add Compound
                        </button>
                        <button class="btn btn-primary ms-2" data-bs-toggle="modal"
                            data-bs-target="#compoundImportModal">
                            <i class="fa-solid fa-file-excel"></i>
                            Excel Bulk Upload
                        </button>

                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="modal fade" id="compoundImportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header bg-primary text-white">

                    <h5 class="modal-title">
                        <i class="fa-solid fa-file-excel"></i>
                        Bulk Upload Compound Master
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <!-- STEP 1 -->
                    <div class="card border-success mb-4">

                        <div class="card-header bg-success text-white">
                            <strong>Step 1 : Download Template</strong>
                        </div>

                        <div class="card-body">

                            <p class="mb-3">
                                Download the latest Compound Master Excel template.
                                Fill all the required columns without changing
                                the header names.
                            </p>

                            <a href="qur_compound_master.php?action=download_template" class="btn btn-success">

                                <i class="fa-solid fa-download"></i>
                                Download Template

                            </a>

                        </div>

                    </div>

                    <!-- STEP 2 -->
                    <div class="card border-primary">

                        <div class="card-header bg-primary text-white">
                            <strong>Step 2 : Import Excel File</strong>
                        </div>

                        <div class="card-body">

                            <form id="compoundImportForm" enctype="multipart/form-data">

                                <label class="form-label">
                                    Select Excel File
                                </label>

                                <input type="file" id="compound_excel_file" name="excel_file" class="form-control mb-3"
                                    accept=".xlsx,.csv" required>

                                <button type="submit" class="btn btn-primary">

                                    <i class="fa-solid fa-file-import"></i>
                                    Import Excel

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

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

                    <div id="compound_table"></div>
                    <!-- disply table   -->
                    <script>
                    const table = new Tabulator("#compound_table", {
                        ajaxURL: "qur_compound_master.php?action=list",
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


                        resizableColumns: false,
                        movableColumns: false,


                        columns: [{
                                title: "ID",
                                field: "id",
                                width: 100,
                                sorter: "number",
                                resizable: false,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Polymer Name",
                                field: "polymer",
                                resizable: false,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Compound Code",
                                field: "compound_code",
                                width: 180,
                                resizable: false,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "IM Code",
                                field: "im_code",
                                width: 150,
                                resizable: false,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Created By",
                                field: "created_by",
                                width: 160,
                                resizable: false,
                                hozAlign: "center",
                                headerHozAlign: "center"
                            },
                            {
                                title: "Updated By",
                                field: "updated_by",
                                width: 160,
                                resizable: false,
                                hozAlign: "center",
                                headerHozAlign: "center"

                            },
                            {
                                title: "Action",
                                width: 100,
                                resizable: false,
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
                    <!-- script for the Import and download the xecel file -->
                    <script>
                    document.getElementById("compoundImportForm").addEventListener("submit", function(e) {
                        e.preventDefault();

                        const fileInput = document.getElementById("compound_excel_file");

                        if (!fileInput.files.length) {
                            alert("Please select an Excel file.");
                            return;
                        }

                        fetch("qur_customer_master.php?action=bulk_upload", {
                                method: "POST",
                                body: new FormData(this)
                            })
                            .then(async response => {
                                const text = await response.text();

                                console.log("Response:");
                                console.log(text);

                                alert(text); // Temporary

                                try {
                                    const result = JSON.parse(text);
                                    alert(result.message);
                                } catch (e) {
                                    console.error(e);
                                }
                            });
                    });
                    </script>
                    <!-- script for the delete and edit button  -->
                    <script>
                    document.addEventListener("click", function(e) {

                        if (e.target.closest(".delete-btn")) {

                            let id = e.target.closest(".delete-btn").dataset.id;

                            if (confirm("Delete this record?")) {

                                fetch("qur_compound_master.php?action=delete", {
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

                            fetch("qur_compound_master.php?action=get&id=" + id)
                                .then(response => response.json())
                                .then(data => {

                                    document.getElementById("compound_id").value = data.id;
                                    document.getElementById("compound_code").value = data.compound_code;
                                    document.getElementById("polymer").value = data.polymer
                                    document.getElementById("im_code").value = data.im_code;

                                    document.getElementById("compoundModalLabel").innerHTML =
                                        "Edit Compound";
                                    document.getElementById("saveBtn").innerHTML = "Update";

                                    new bootstrap.Modal(document.getElementById("compoundModal")).show();
                                });
                        }

                    });

                    // Add Plant Button
                    document.querySelector('[data-bs-target="#compoundModal"]').addEventListener("click", function() {

                        document.getElementById("compoundForm").reset();

                        document.getElementById("compound_id").value = "";

                        document.getElementById("compoundModalLabel").innerHTML = "Add Compound";

                        document.getElementById("saveBtn").innerHTML = "Save";

                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- form for  Compound  -->
    <div class="modal fade" id="compoundModal" tabindex="-1" aria-labelledby="compoundModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="compoundModalLabel">Add Compound</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form id="compoundForm">

                        <input type="hidden" id="compound_id" name="id">

                        <div class="mb-3">
                            <label>Compound Code</label>
                            <input type="text" id="compound_code" name="compound_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Polymer Name</label>
                            <input type="text" id="polymer" name="polymer" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>IM Code</label>
                            <input type="text" id="im_code" name="im_code" class="form-control" required>
                        </div>
                        <button type="submit" id="saveBtn" class="btn btn-success">
                            Save
                        </button>

                    </form>

                </div>

                <script>
                document.getElementById("compoundForm").addEventListener("submit", function(e) {

                    e.preventDefault();

                    let formData = new FormData(this);

                    fetch("qur_compound_master.php?action=save", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {

                            if (data.trim() == "success") {

                                bootstrap.Modal.getInstance(
                                    document.getElementById("compoundModal")
                                ).hide();

                                document.getElementById("compoundForm").reset();

                                document.getElementById("compound_id").value = "";

                                document.getElementById("compoundModalLabel").innerHTML = "Add Compound";

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
                            field: 'compound_code',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'im_code',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'id',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'polymer',
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
            .addEventListener('click', () => table.download('csv', 'compound_master.csv'));
    });
    </script>

    <?php } ?>


    <?php if($page=="recycle"){ ?>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Compound Recycle Bin</h1>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div id="compound_table"></div>
                        <!-- disply table   -->
                        <script>
                        const table = new Tabulator("#compound_table", {
                            ajaxURL: "qur_compound_master.php?action=list1",
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
                                    width: 100,
                                    sorter: "number",
                                    resizable: false,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Polymer Name",
                                    field: "polymer",
                                    resizable: false,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Compund Code",
                                    field: "compound_code",
                                    width: 180,
                                    resizable: false,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "IM Code",
                                    field: "im_code",
                                    width: 150,
                                    resizable: false,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Created By",
                                    field: "created_by",
                                    width: 160,
                                    resizable: false,
                                    hozAlign: "center",
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Deleted By",
                                    field: "updated_by",
                                    width: 160,
                                    resizable: false,
                                    hozAlign: "center",
                                    headerHozAlign: "center"

                                },
                                {
                                    title: "Action",
                                    width: 100,
                                    resizable: false,
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

                            if (confirm("Restore this Compund?")) {

                                fetch("qur_compound_master.php?action=restore", {
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
                                            alert("Compund restored successfully.");
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