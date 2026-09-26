<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";
?>
<?php
$page = $_GET['page'] ?? 'molding_machine';
?>
<?php if($page=="molding_machine"){ ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Shift Rate</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#molding_machineModal">
                            <i class="fas fa-plus"></i>
                            Add Molding Machine
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

                    <div id="molding_machine_table"></div>
                    <!-- disply table   -->
                    <script>
                    const table = new Tabulator("#molding_machine_table", {
                        ajaxURL: "qur_molding_machine_master.php?action=list",
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
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Machine tonnage",
                                field: "machine_list",
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Shift Rate",
                                field: "shift_rate",
                                width: 150,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Molding Process",
                                field: "molding_process",
                                width: 150,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Created By",
                                field: "created_by",
                                width: 160,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Updated By",
                                field: "updated_by",
                                width: 160,
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

                                fetch("qur_molding_machine_master.php?action=delete", {
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

                            fetch("qur_molding_machine_master.php?action=get&id=" + id)
                                .then(response => response.json())
                                .then(data => {

                                    document.getElementById("molding_machine_id").value = data.id;
                                    document.getElementById("machine_list").value = data
                                        .machine_list;
                                    document.getElementById("shift_rate").value = data.shift_rate;

                                    document.getElementById("molding_process").value = data.molding_process;

                                    document.getElementById("molding_machineModalLabel").innerHTML =
                                        "Edit Molding Machine";
                                    document.getElementById("saveBtn").innerHTML = "Update";

                                    new bootstrap.Modal(document.getElementById("molding_machineModal"))
                                        .show();
                                });
                        }

                    });

                    // Add Plant Button
                    document.querySelector('[data-bs-target="#molding_machineModal"]').addEventListener("click",
                        function() {

                            document.getElementById("molding_machineForm").reset();

                            document.getElementById("molding_machine_id").value = "";

                            document.getElementById("molding_process").value = "";

                            document.getElementById("molding_machineModalLabel").innerHTML = "Add Molding Machine";

                            document.getElementById("saveBtn").innerHTML = "Save";

                        });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- form for  Molding Machine  -->
    <div class="modal fade" id="molding_machineModal" tabindex="-1" aria-labelledby="molding_machineModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="molding_machineModalLabel">Add Molding Machine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <form id="molding_machineForm">

                        <input type="hidden" id="molding_machine_id" name="id">

                        <div class="mb-3">
                            <label>Machine List</label>
                            <input type="text" id="machine_list" name="machine_list" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Molding Process</label>
                            <select class="form-select" id="molding_process" name="molding_process" required>
                                <option value="">Select Process</option>
                                <option value="Compression">Compression</option>
                                <option value="Transfer">Transfer</option>
                                <option value="Injection">Injection</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Shift Rate</label>
                            <input type="text" id="shift_rate" name="shift_rate" class="form-control" required>
                        </div>
                        <button type="submit" id="saveBtn" class="btn btn-success">
                            Save
                        </button>

                    </form>

                </div>

                <script>
                document.getElementById("molding_machineForm").addEventListener("submit", function(e) {

                    e.preventDefault();

                    let formData = new FormData(this);

                    fetch("qur_molding_machine_master.php?action=save", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {

                            if (data.trim() == "success") {

                                bootstrap.Modal.getInstance(
                                    document.getElementById("molding_machineModal")
                                ).hide();

                                document.getElementById("molding_machineForm").reset();

                                document.getElementById("molding_machine_id").value = "";

                                document.getElementById("molding_machineModalLabel").innerHTML =
                                    "Add Molding Machine";

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
                            field: 'machine_list',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'shift_rate',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'id',
                            type: 'like',
                            value: value
                        },
                        {
                            field: 'molding_process',
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
            .addEventListener('click', () => table.download('csv', 'molding_machine_master.csv'));
    });
    </script>

    <?php } ?>


    <?php if($page=="recycle"){ ?>

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Molding Machine Recycle Bin</h1>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div id="molding_machine_table"></div>
                        <!-- disply table   -->
                        <script>
                        const table = new Tabulator("#molding_machine_table", {
                            ajaxURL: "qur_molding_machine_master.php?action=list1",
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
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Machine List",
                                    field: "machine_list",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Shift Rate",
                                    field: "shift_rate",
                                    width: 150,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Molding Process",
                                    field: "molding_process",
                                    width: 150,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Created By",
                                    field: "created_by",
                                    width: 160,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Deleted By",
                                    field: "updated_by",
                                    width: 160,
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

                            if (confirm("Restore this Molding?")) {

                                fetch("qur_molding_machine_master.php?action=restore", {
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
                                            alert("Molding restored successfully.");
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