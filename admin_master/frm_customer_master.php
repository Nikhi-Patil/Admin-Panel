<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";
?>
<?php
$page = $_GET['page'] ?? 'customer';
?>
<?php if($page=="customer"){ ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Customer List</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#customerModal">
                            <i class="fas fa-plus"></i>
                            Add Customer
                        </button>
                        <button class="btn btn-primary ms-2" data-bs-toggle="modal"
                            data-bs-target="#customerImportModal">
                            <i class="fa-solid fa-file-excel"></i>
                            Excel Bulk Upload
                        </button>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div class="modal fade" id="customerImportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-file-excel"></i>
                        Bulk Upload Customer Master
                    </h5>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- STEP 1 -->
                    <div class="card border-success mb-4">

                        <div class="card-header bg-success text-white">
                            <strong>Step 1 : Download Template</strong>
                        </div>

                        <div class="card-body">

                            <p class="mb-3">
                                Download the latest Customer Excel template.
                                Fill all the required columns without changing
                                the header names.
                            </p>

                            <a href="qur_customer_master.php?action=download_template" class="btn btn-success">
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

                            <form id="customerImportForm" enctype="multipart/form-data">

                                <label class="form-label">
                                    Select Excel File
                                </label>

                                <input type="file" id="customer_excel_file" name="excel_file" class="form-control mb-3"
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

                                <input id="table-filter" type="search" class="form-control"
                                    placeholder="Filter rows&hellip;" aria-label="Filter rows" />
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="customer_table"></div>
                    <!-- disply table   -->
                    <script>
                    const table = new Tabulator("#customer_table", {
                        ajaxURL: "qur_customer_master.php?action=list",
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
                                width: 55,
                                sorter: "number",
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Customer Name",
                                field: "customer_name",

                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Sub Customer Name",
                                field: "sub_customer",

                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Domestic/Export",
                                field: "geo_type",
                                width: 170,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Zone",
                                field: "zone",
                                width: 150,
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
                                width: 90,
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
                    <!-- script for the Excel Uploade and Download  -->
                    <script>
                    document.getElementById("customerImportForm").addEventListener("submit", function(e) {

                        e.preventDefault();

                        const fileInput = document.getElementById("customer_excel_file");

                        if (!fileInput.files.length) {
                            alert("Please select an Excel file.");
                            return;
                        }

                        fetch("qur_customer_master.php?action=bulk_upload", {
                                method: "POST",
                                body: new FormData(this)
                            })
                            .then(response => response.text())
                            .then(text => {

                                const result = JSON.parse(text);

                                if (result.status == "success") {

                                    alert(result.message);

                                    bootstrap.Modal.getInstance(
                                        document.getElementById("customerImportModal")
                                    )?.hide();

                                    this.reset();

                                    table.replaceData();

                                } else {

                                    alert(result.message);
                                }

                            })
                            .catch(error => alert(error.message));

                    });
                    </script>
                    <!-- script for the delete and edit button  -->
                    <script>
                    document.addEventListener("click", function(e) {

                        if (e.target.closest(".delete-btn")) {

                            let id = e.target.closest(".delete-btn").dataset.id;

                            if (confirm("Delete this record?")) {

                                fetch("qur_customer_master.php?action=delete", {
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

                            fetch("qur_customer_master.php?action=get&id=" + id)
                                .then(response => response.json())
                                .then(data => {

                                    document.getElementById("customer_id").value = data.id;
                                    document.getElementById("customer_name").value = data.customer_name;
                                    document.getElementById("sub_customer").value = data.sub_customer || "";
                                    document.getElementById("geo_type").value = data.geo_type || "";

                                    const zone = document.getElementById("zone");
                                    const zoneLabel = document.getElementById("zoneLabel");

                                    zone.innerHTML = '<option value="">Select Zone</option>';

                                    if (data.geo_type === "Domestic") {
                                        zoneLabel.innerHTML = "Zone *";
                                    } else if (data.geo_type === "Export") {
                                        zoneLabel.innerHTML = "Continent *";
                                    }

                                    fetch("qur_customer_master.php?action=geo_options&geo_type=" +
                                            encodeURIComponent(data.geo_type))
                                        .then(response => response.json())
                                        .then(options => {
                                            options.forEach(function(regionName) {
                                                const option = document.createElement("option");

                                                option.value = regionName;
                                                option.textContent = regionName;

                                                zone.appendChild(option);
                                            });

                                            zone.value = data.zone || "";
                                        });

                                    document.getElementById("customerModalLabel").innerHTML =
                                        "Edit Customer";
                                    document.getElementById("saveBtn").innerHTML = "Update";

                                    new bootstrap.Modal(document.getElementById("customerModal")).show();
                                });
                        }

                    });

                    // Add customer Button
                    document.querySelector('[data-bs-target="#customerModal"]').addEventListener("click", function() {

                        document.getElementById("customerForm").reset();

                        document.getElementById("customer_id").value = "";
                        document.getElementById("zone").innerHTML = '<option value="">Select Zone</option>';

                        document.getElementById("customerModalLabel").innerHTML = "Add Customer";

                        document.getElementById("saveBtn").innerHTML = "Save";

                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- form for  customer  -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form id="customerForm">

                        <input type="hidden" id="customer_id" name="id">

                        <div class="mb-3">
                            <label>Customer Name</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label> Sub Customer Name</label>
                            <input type="text" id="sub_customer" name="sub_customer" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Domestic/Export *</label>

                            <select id="geo_type" name="geo_type" class="form-select" required>

                                <option value="">Select Domestic/Export</option>
                                <option value="Domestic">Domestic</option>
                                <option value="Export">Export</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label id="zoneLabel" class="form-label">Zone *</label>

                            <select id="zone" name="zone" class="form-select" required>

                                <option value="">Select Zone</option>
                            </select>
                        </div>
                        <button type="submit" id="saveBtn" class="btn btn-success">
                            Save
                        </button>

                    </form>

                </div>

                <script>
                document.getElementById("geo_type").addEventListener("change", function() {

                    const geoType = this.value;
                    const zone = document.getElementById("zone");
                    const zoneLabel = document.getElementById("zoneLabel");

                    zone.innerHTML = '<option value="">Select Zone</option>';

                    if (geoType === "Domestic") {
                        zoneLabel.innerHTML = "Zone *";
                    } else if (geoType === "Export") {
                        zoneLabel.innerHTML = "Continent *";
                    } else {
                        zoneLabel.innerHTML = "Zone *";
                        return;
                    }

                    fetch("qur_customer_master.php?action=geo_options&geo_type=" +
                            encodeURIComponent(geoType))
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(function(regionName) {
                                const option = document.createElement("option");

                                option.value = regionName;
                                option.textContent = regionName;

                                zone.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error("Geographical data error:", error);
                            alert("Unable to load geographical data.");
                        });
                });
                document.getElementById("customerForm").addEventListener("submit", function(e) {

                    e.preventDefault();

                    let formData = new FormData(this);

                    fetch("qur_customer_master.php?action=save", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {

                            if (data.trim() == "success") {

                                bootstrap.Modal.getInstance(
                                    document.getElementById("customerModal")
                                ).hide();

                                document.getElementById("customerForm").reset();

                                document.getElementById("customer_id").value = "";

                                document.getElementById("customerModalLabel").innerHTML = "Add Customer";

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
                            field: 'customer_name',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'id',
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
            .addEventListener('click', () => table.download('csv', 'customer_master.csv'));
    });
    </script>

    <?php } ?>


    <?php if($page=="recycle"){ ?>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Customer Recycle Bin</h1>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div id="customer_table"></div>
                        <!-- disply table   -->
                        <script>
                        const table = new Tabulator("#customer_table", {
                            ajaxURL: "qur_customer_master.php?action=list1",
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
                                    width: 55,
                                    sorter: "number",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Customer Name",
                                    field: "customer_name",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Sub Customer Name",
                                    field: "sub_customer",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Domastic/Export",
                                    field: "geo_type",
                                    width: 170,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Zone",
                                    field: "zone",
                                    width: 150,
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
                                    width: 90,
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

                            if (confirm("Restore this Customer?")) {

                                fetch("qur_customer_master.php?action=restore", {
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
                                            alert("Customer restored successfully.");
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