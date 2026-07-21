<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";

$page = $_GET['page'] ?? 'bop';
?>

<?php if ($page == "bop") { ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">BOP Master</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bopModal">
                            <i class="fas fa-plus"></i>
                            Add BOP
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
                <div id="bop_table"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bopModal" tabindex="-1" aria-labelledby="bopModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bopModalLabel">Add BOP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bopForm">
                    <input type="hidden" id="bop_id" name="id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Part No</label>
                            <select id="part_id" name="part_id" class="form-select" required>
                                <option value="">Select Part No</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>BOP Part Name</label>
                            <input type="text" id="bop_part_name" name="bop_part_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>BOP Part No</label>
                            <input type="text" id="bop_part_no" name="bop_part_no" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Supplier Name</label>
                            <select id="supplier_id" name="supplier_id" class="form-control" required>
                                <option value="">Select Supplier</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Quantity</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>UMO</label>
                            <select id="umo" name="umo" class="form-select" required>
                                <option value="">Select UMO</option>
                                <option value="kg">Kg</option>
                                <option value="mtrs">mtrs</option>
                                <option value="nos">Nos</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>FG Code</label>
                            <input type="text" id="fg_code" name="fg_code" class="form-control" required>
                        </div>

                    </div>

                    <button type="submit" id="saveBtn" class="btn btn-success">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let allPartNo = [];
let allSupplierNames = [];

function populatePartNoOptions(selectedId = "") {
    const select = document.getElementById("part_id");
    select.innerHTML = '<option value="">Select Part No</option>';

    allPartNo.forEach(item => {
        const selected = String(selectedId) === String(item.id) ? "selected" : "";
        select.innerHTML += `<option value="${item.id}"
                                data-fgcode="${item.fg_code || ''}"
                                ${selected}>
                                ${item.part_name} ${item.fg_code ? `(${item.fg_code})` : ""}
                            </option>`;
    });
}

function updateFGcode() {
    const part = document.getElementById("part_id");
    const option = part.options[part.selectedIndex];

    document.getElementById("fg_code").value =
        option ? option.dataset.fgcode || "" : "";
}

function populateSupplierOptions() {
    const select = document.getElementById("supplier_id");
    select.innerHTML = '<option value="">Select Supplier</option>';
    allSupplierNames.forEach(item => {
        const label = item.supplier_name ? `${item.supplier_name}` : item.supplier_name;
        select.innerHTML +=
            `<option value="${item.id}" data-supplier-name="${item.supplier_name || ''}">${label}</option>`;
    });
}

const table = new Tabulator("#bop_table", {
    ajaxURL: "qur_bop_master.php?action=list",
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
            title: "Part No",
            field: "part_no",
            width: 165,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "BOP Part Name",
            field: "bop_part_name",
            width: 150,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "BOP Part No",
            field: "bop_part_no",
            width: 100,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Supplier Name",
            field: "supplier_name",
            width: 105,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Quantity",
            field: "quantity",
            width: 105,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "UMO",
            field: "umo",
            width: 90,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "FG Code",
            field: "fg_code",
            width: 105,
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
                const row = cell.getRow().getData();
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

document.addEventListener("click", function(e) {
    const deleteBtn = e.target.closest(".delete-btn");
    const editBtn = e.target.closest(".edit-btn");

    if (deleteBtn) {
        const id = deleteBtn.dataset.id;
        if (confirm("Delete this part?")) {
            fetch("qur_bop_master.php?action=delete", {
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
        fetch("qur_bop_master.php?action=get&id=" + encodeURIComponent(id))
            .then(res => res.json())
            .then(data => {
                document.getElementById("bop_id").value = data.id;
                document.getElementById("part_id").value = data.part_id || "";
                document.getElementById("bop_part_name").value = data.bop_part_name || "";
                document.getElementById("bop_part_no").value = data.bop_part_no || "";
                document.getElementById("fg_code").value = data.fg_code || "";
                document.getElementById("supplier_id").value = data.supplier_name || "";
                document.getElementById("quantity").value = data.quantity || "";
                document.getElementById("umo").value = data.umo || "";
                populatePartNoOptions(data.part_id);
                document.getElementById("part_id").value = data.part_id || "";
                updateFGcode();


                document.getElementById("bopModalLabel").innerHTML = "Edit BOP";
                document.getElementById("saveBtn").innerHTML = "Update";
                new bootstrap.Modal(document.getElementById("bopModal")).show();
            });
    }
});

document.querySelector('[data-bs-target="#bopModal"]').addEventListener("click", function() {

    document.getElementById("bopForm").reset();
    document.getElementById("bop_id").value = "";
    document.getElementById("part_id").value = "";
    document.getElementById("bopModalLabel").innerHTML = "Add BOP";
    document.getElementById("saveBtn").innerHTML = "Save";
    document.getElementById("fg_code").value = "";
    document.getElementById("supplier_id").value = "";
    document.getElementById("quantity").value = "";
    document.getElementById("umo").value = "";
    document.getElementById("part_id").value = "";
});

document.getElementById("part_id").addEventListener("change", function() {
    updateFGcode();
});


document.getElementById("bopForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    fetch("qur_bop_master.php?action=save", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                bootstrap.Modal.getInstance(document.getElementById("bopModal")).hide();
                document.getElementById("bopForm").reset();
                document.getElementById("part_id").value = "";
                document.getElementById("bopModalLabel").innerHTML = "Add BOP";
                document.getElementById("saveBtn").innerHTML = "Save";
                document.getElementById("fg_code").value = "";
                document.getElementById("supplier_id").value = "";
                document.getElementById("quantity").value = "";
                document.getElementById("umo").value = "";
                document.getElementById("part_id").value = "";
                table.replaceData();
            } else {
                alert(data.message || "Save failed.");
            }
        });
});

function loadBOPLookups() {
    fetch("qur_bop_master.php?action=part")
        .then(res => res.json())
        .then(data => {
            allPartNo = data;
            populatePartNoOptions();
        });
    fetch("qur_bop_master.php?action=supplier")
        .then(res => res.json())
        .then(data => {
            allSupplierNames = data;
            populateSupplierOptions();
        });
}

document.addEventListener("DOMContentLoaded", () => {
    loadBOPLookups();

    document.getElementById("table-filter").addEventListener("input", (e) => {
        const value = e.target.value;
        if (value) {
            const term = value.toLowerCase();
            table.setFilter(function(data) {
                return [
                    data.part_no,
                    data.bop_part_name,
                    data.bop_part_no,
                    data.fg_code,
                    data.supplier_name,
                    data.quantity,
                    data.umo,
                    data.fg_code

                ].some(field => String(field ?? "").toLowerCase().includes(term));
            });
        } else {
            table.clearFilter();
        }
    });

    document.getElementById("print-table").addEventListener("click", () => table.print(false, true));
    document.getElementById("export-csv").addEventListener("click", () => table.download("csv",
        "bop_master.csv"));
});
</script>
</main>
<?php } ?>

<?php if ($page == "recycle") { ?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">BOP Recycle Bin</h1>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">BOP</h3>
            </div>
            <div class="card-body">
                <div id="part_table"></div>
                <script>
                const table = new Tabulator("#part_table", {
                    ajaxURL: "qur_bop_master.php?action=list1",
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
                            title: "Part No",
                            field: "part_no",
                            width: 165,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "BOP Part Name",
                            field: "bop_part_name",
                            width: 150,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "BOP Part No",
                            field: "bop_part_no",
                            width: 100,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },

                        {
                            title: "Supplier Name",
                            field: "supplier_name",
                            width: 105,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "Quantity",
                            field: "quantity",
                            width: 105,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "UMO",
                            field: "umo",
                            width: 90,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "FG Code",
                            field: "fg_code",
                            width: 105,
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
                                const row = cell.getRow().getData();
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
                    if (confirm("Restore this part?")) {
                        fetch("qur_bop_master.php?action=restore", {
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
                                    alert("BOP restored successfully.");
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