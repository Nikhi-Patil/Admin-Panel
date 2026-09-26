<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";

$page = $_GET['page'] ?? 'currency';
?>

<?php if ($page == "currency") { ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Currency List</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#currencyModal">
                            <i class="fas fa-plus"></i>
                            Add Currency
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
                <div id="currency_table"></div>

                <script>
                const table = new Tabulator("#currency_table", {
                    ajaxURL: "qur_currency_master.php?action=list",
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
                            title: "ID",
                            field: "id",
                            width: 100,
                            sorter: "number",
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Currency Name",
                            field: "currency_name",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Currency Symbol",
                            field: "currency_symbol",
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Exchange Rate",
                            field: "exchange_rate",
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center",
                            formatter: function(cell) {
                                let value = parseFloat(cell.getValue());
                                return isNaN(value) ? "" : Number(value.toFixed(2));
                            }
                        },
                        {
                            title: "Created By",
                            field: "created_by",
                            width: 140,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Updated By",
                            field: "updated_by",
                            width: 140,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Action",
                            width: 140,
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

                <script>
                document.addEventListener("click", function(e) {
                    let deleteBtn = e.target.closest(".delete-btn");
                    if (deleteBtn) {
                        let id = deleteBtn.dataset.id;
                        if (confirm("Delete this currency?")) {
                            fetch("qur_currency_master.php?action=delete", {
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
                });

                document.addEventListener("click", function(e) {
                    let editBtn = e.target.closest(".edit-btn");
                    if (editBtn) {
                        let id = editBtn.dataset.id;
                        fetch("qur_currency_master.php?action=get&id=" + encodeURIComponent(id))
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById("currency_id").value = data.id;
                                document.getElementById("currency_name").value = data.currency_name;
                                document.getElementById("currency_symbol").value = data.currency_symbol;
                                document.getElementById("exchange_rate").value =
                                    parseFloat(data.exchange_rate).toFixed(2);
                                document.getElementById("currencyModalLabel").innerHTML = "Edit Currency";
                                document.getElementById("saveBtn").innerHTML = "Update";
                                new bootstrap.Modal(document.getElementById("currencyModal")).show();
                            });
                    }
                });

                document.querySelector('[data-bs-target="#currencyModal"]').addEventListener("click", function() {
                    document.getElementById("currencyForm").reset();
                    document.getElementById("currency_id").value = "";
                    document.getElementById("currencyModalLabel").innerHTML = "Add Currency";
                    document.getElementById("saveBtn").innerHTML = "Save";
                });
                </script>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="currencyModal" tabindex="-1" aria-labelledby="currencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="currencyModalLabel">Add Currency</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="currencyForm">
                    <input type="hidden" id="currency_id" name="id">
                    <div class="mb-3">
                        <label>Currency Name</label>
                        <input type="text" id="currency_name" name="currency_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Currency Symbol</label>
                        <input type="text" id="currency_symbol" name="currency_symbol" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Exchange Rate</label>
                        <input type="number" step="0.001" id="exchange_rate" name="exchange_rate" class="form-control"
                            required>
                    </div>
                    <button type="submit" id="saveBtn" class="btn btn-success">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
</main>
<script>
document.getElementById("currencyForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch("qur_currency_master.php?action=save", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                bootstrap.Modal.getInstance(document.getElementById("currencyModal")).hide();
                document.getElementById("currencyForm").reset();
                document.getElementById("currency_id").value = "";
                document.getElementById("currencyModalLabel").innerHTML = "Add Currency";
                document.getElementById("saveBtn").innerHTML = "Save";
                table.replaceData();
            } else {
                alert(data);
            }
        });
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('table-filter').addEventListener('input', (e) => {
        const value = e.target.value;
        if (value) {
            table.setFilter([
                [{
                        field: 'id',
                        type: 'like',
                        value: value
                    },
                    {
                        field: 'currency_name',
                        type: 'like',
                        value: value
                    },
                    {
                        field: 'currency_symbol',
                        type: 'like',
                        value: value
                    },
                    {
                        field: 'exchange_rate',
                        type: 'like',
                        value: value
                    },
                    {
                        field: 'created_by',
                        type: 'like',
                        value: value
                    },
                    {
                        field: 'updated_by',
                        type: 'like',
                        value: value
                    }
                ],
            ]);
        } else {
            table.clearFilter();
        }
    });

    document.getElementById('print-table').addEventListener('click', () => table.print(false, true));
    document.getElementById('export-csv').addEventListener('click', () => table.download('csv',
        'currency_master.csv'));
});
</script>
<?php } ?>

<?php if ($page == "recycle") { ?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Currency Recycle Bin</h1>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div id="currency_table"></div>

                <script>
                const table = new Tabulator("#currency_table", {
                    ajaxURL: "qur_currency_master.php?action=list1",
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
                            title: "ID",
                            field: "id",
                            width: 100,
                            sorter: "number",
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Currency Name",
                            field: "currency_name",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Currency Symbol",
                            field: "currency_symbol",
                            width: 160,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Exchange Rate",
                            field: "exchange_rate",
                            width: 160,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Created By",
                            field: "created_by",
                            width: 140,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Deleted By",
                            field: "updated_by",
                            width: 140,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Action",
                            width: 120,
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

                document.addEventListener("click", function(e) {
                    let btn = e.target.closest(".recycle-btn");
                    if (!btn) return;

                    let id = btn.dataset.id;

                    if (confirm("Restore this currency?")) {
                        fetch("qur_currency_master.php?action=restore", {
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
                                    alert("Currency restored successfully.");
                                } else {
                                    alert(res.message || "Restore failed.");
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