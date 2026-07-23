<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";

$page = $_GET['page'] ?? 'incoterms';
?>

<?php if ($page == "incoterms") { ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Incoterms List</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#incotermsModal">
                            <i class="fas fa-plus"></i>
                            Add Incoterms
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
                <div id="incoterms_table"></div>

                <script>
                const table = new Tabulator("#incoterms_table", {
                    ajaxURL: "qur_incoterms_master.php?action=list",
                    ajaxConfig: "GET",
                    layout: "fitColumns",
                    pagination: true,
                    paginationSize: 10,
                    columns: [{
                            title: "ID",
                            field: "id",
                            width: 120,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "Incoterms",
                            field: "incoterms",
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
                            width: 140,
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

                <script>
                document.addEventListener("click", function(e) {
                    let deleteBtn = e.target.closest(".delete-btn");
                    if (deleteBtn) {
                        let id = deleteBtn.dataset.id;
                        if (confirm("Delete this incoterms record?")) {
                            fetch("qur_incoterms_master.php?action=delete", {
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
                        fetch("qur_incoterms_master.php?action=get&id=" + encodeURIComponent(id))
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById("incoterms_id").value = data.id;
                                document.getElementById("incoterms").value = data.incoterms;
                                document.getElementById("incotermsModalLabel").innerHTML = "Edit Incoterms";
                                document.getElementById("saveBtn").innerHTML = "Update";
                                new bootstrap.Modal(document.getElementById("incotermsModal")).show();
                            });
                    }
                });

                document.querySelector('[data-bs-target="#incotermsModal"]').addEventListener("click", function() {
                    document.getElementById("incotermsForm").reset();
                    document.getElementById("incoterms_id").value = "";
                    document.getElementById("incotermsModalLabel").innerHTML = "Add Incoterms";
                    document.getElementById("saveBtn").innerHTML = "Save";
                });
                </script>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="incotermsModal" tabindex="-1" aria-labelledby="incotermsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="incotermsModalLabel">Add Incoterms</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="incotermsForm">
                    <input type="hidden" id="incoterms_id" name="id">
                    <div class="mb-3">
                        <label>Incoterms</label>
                        <input type="text" id="incoterms" name="incoterms" class="form-control" required>
                    </div>
                    <button type="submit" id="saveBtn" class="btn btn-success">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
</main>
<script>
document.getElementById("incotermsForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch("qur_incoterms_master.php?action=save", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                bootstrap.Modal.getInstance(document.getElementById("incotermsModal")).hide();
                document.getElementById("incotermsForm").reset();
                document.getElementById("incoterms_id").value = "";
                document.getElementById("incotermsModalLabel").innerHTML = "Add Incoterms";
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
                        field: 'incoterms',
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
        'incoterms_master.csv'));
});
</script>
<?php } ?>

<?php if ($page == "recycle") { ?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Incoterms Recycle Bin</h1>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div id="incoterms_table"></div>

                <script>
                const table = new Tabulator("#incoterms_table", {
                    ajaxURL: "qur_incoterms_master.php?action=list1",
                    ajaxConfig: "GET",
                    layout: "fitColumns",
                    pagination: true,
                    paginationSize: 10,
                    columns: [{
                            title: "ID",
                            field: "id",
                            width: 120,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "Incoterms",
                            field: "incoterms",
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
                            width: 120,
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
                    let btn = e.target.closest(".recycle-btn");
                    if (!btn) return;

                    let id = btn.dataset.id;

                    if (confirm("Restore this incoterms record?")) {
                        fetch("qur_incoterms_master.php?action=restore", {
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
                                    alert("Incoterms restored successfully.");
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