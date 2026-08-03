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
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#bopModal">
                            <i class="fas fa-plus"></i>
                            Add BOP
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">

                            <i class="fa-solid fa-file-excel"></i>

                            Bulk Upload

                        </button>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="bulkUploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-file-excel"></i>
                    Bulk Upload BOP Master
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <!-- STEP 1 -->

                <div class="card border-success mb-4">

                    <div class="card-header bg-success text-white">

                        <strong>
                            Step 1 : Download Template
                        </strong>

                    </div>

                    <div class="card-body">

                        <p class="mb-3">

                            Download the latest Excel template.
                            Fill all the required columns without
                            changing the header names.

                        </p>

                        <a href="qur_bop_master.php?action=download_template" class="btn btn-success">

                            <i class="fa-solid fa-download"></i>

                            Download Template

                        </a>

                    </div>

                </div>

                <!-- STEP 2 -->

                <div class="card border-primary">

                    <div class="card-header bg-primary text-white">

                        <strong>
                            Step 2 : Import Excel File
                        </strong>

                    </div>

                    <div class="card-body">

                        <form id="bulkUploadForm" enctype="multipart/form-data">

                            <label class="form-label">

                                Select Excel File

                            </label>

                            <input type="file" name="excel_file" accept=".xlsx,.csv" class="form-control mb-3" required>

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
                            <label> Main Part No</label>
                            <select id="part_id" name="part_id" class="form-select" required>
                                <option value="">Select Part No</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>FG Code</label>
                            <input type="text" id="fg_code" name="fg_code" class="form-control" readonly>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label>BOP Part No</label>
                            <input type="text" id="bop_part_no" name="bop_part_no" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>BOP Part Name</label>
                            <input type="text" id="bop_part_name" name="bop_part_name" class="form-control" required>
                        </div>


                        <div class="col-md-6 mb-3">
                            <label>BOP ERP Code</label>
                            <input type="text" id="bop_erp_code" name="bop_erp_code" class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Supplier</label>

                            <div class="dropdown w-100">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button"
                                    id="supplierButton" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    Select Suppliers
                                </button>

                                <ul class="dropdown-menu w-100 p-2" id="supplierMenu"
                                    style="max-height:260px;overflow:auto;">
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Quantity Per Part</label>
                            <input type="number" id="bop_quantity" name="bop_quantity" class="form-control" required>
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
let selectedSupplierIds = [];
let selectedSupplierNames = [];

function populatePartNoOptions(selectedId = "") {
    const select = document.getElementById("part_id");
    select.innerHTML = '<option value="">Select Part No</option>';

    allPartNo.forEach(item => {
        const selected = String(selectedId) === String(item.id) ? "selected" : "";
        select.innerHTML += `<option value="${item.id}"
                                data-fgcode="${item.fg_code || ''}"
                                ${selected}>
                                ${item.part_name}
                            </option>`;
        // ${item.fg_code ? `(${item.fg_code})` : ""}
    });
}

function updateFGcode() {
    const part = document.getElementById("part_id");
    const option = part.options[part.selectedIndex];

    document.getElementById("fg_code").value =
        option ? option.dataset.fgcode || "" : "";
}

function populateSupplierOptions() {

    const holder = document.getElementById("supplierMenu");

    holder.innerHTML = allSupplierNames.map(item => `
        <li>
            <label class="dropdown-item d-flex align-items-center gap-2 mb-0">
                <input
                    class="form-check-input supplier-checkbox m-0"
                    type="checkbox"
                    name="supplier_id[]"
                    value="${item.id}"
                    data-name="${item.supplier_name}"
                    id="supplier_${item.id}">

                <span>${item.supplier_name}</span>
            </label>
        </li>
    `).join("");

    applySelectedSuppliers();
}



function normalizeIds(value) {

    if (Array.isArray(value))
        return value.map(v => String(v).trim()).filter(Boolean);

    if (!value)
        return [];

    return String(value)
        .split(",")
        .map(v => v.trim())
        .filter(Boolean);
}

function applySelectedSuppliers() {

    document.querySelectorAll(".supplier-checkbox").forEach(cb => {
        cb.checked = selectedSupplierIds.includes(cb.value);
    });

    const btn = document.getElementById("supplierButton");

    if (!selectedSupplierIds.length) {

        btn.textContent = "Select Suppliers";

    } else if (selectedSupplierNames.length <= 3) {

        btn.textContent = selectedSupplierNames.join(", ");

    } else {

        btn.textContent = `${selectedSupplierIds.length} Suppliers Selected`;
    }
}

function setSelectedSuppliers(ids, names) {

    selectedSupplierIds = normalizeIds(ids);
    selectedSupplierNames = normalizeIds(names);

    applySelectedSuppliers();
}

function refreshSupplierSummary() {

    selectedSupplierIds =
        Array.from(document.querySelectorAll(".supplier-checkbox:checked"))
        .map(cb => cb.value);

    selectedSupplierNames =
        Array.from(document.querySelectorAll(".supplier-checkbox:checked"))
        .map(cb => cb.dataset.name);

    applySelectedSuppliers();
}
document.addEventListener("change", function(e) {

    if (e.target.classList.contains("supplier-checkbox")) {
        refreshSupplierSummary();
    }

})
const table = new Tabulator("#bop_table", {
    ajaxURL: "qur_bop_master.php?action=list",
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
            sorter: "number",
            width: 55,
            resizable: false,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Part No",
            field: "part_no",
            width: 135,
            resizable: false,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "FG Code",
            field: "fg_code",
            width: 135,
            resizable: false,
            hozAlign: "center",
            headerHozAlign: "center"
        },

        {
            title: "BOP Part Name",
            field: "bop_part_name",
            resizable: false,
            width: 160,
            headerHozAlign: "center"
        },
        {
            title: "BOP Part No",
            field: "bop_part_no",
            resizable: false,
            width: 140,
            headerHozAlign: "center"
        },
        {
            title: "BOP ERP Code",
            field: "bop_erp_code",
            resizable: false,
            width: 150,
            headerHozAlign: "center"
        },
        {
            title: "Supplier Name",
            field: "supplier_name",
            resizable: false,
            width: 170,
            headerHozAlign: "center"
        },
        {
            title: "Quantity",
            field: "bop_quantity",
            width: 110,
            resizable: false,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "UMO",
            field: "umo",
            width: 80,
            resizable: false,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Created By",
            field: "created_by",
            width: 120,
            resizable: false,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Updated By",
            field: "updated_by",
            width: 130,
            resizable: false,
            hozAlign: "center",
            headerHozAlign: "center"
        },
        {
            title: "Action",
            width: 90,
            resizable: false,
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
                document.getElementById("bop_erp_code").value = data.bop_erp_code || "";
                setSelectedSuppliers(data.supplier_id, data.supplier_name);
                document.getElementById("bop_quantity").value = data.bop_quantity || "";
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
    setSelectedSuppliers([], []);
    document.getElementById("bop_quantity").value = "";
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
                document.getElementById("bop_quantity").value = "";
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
                    data.bop_quantity,
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
<script>
document.getElementById("bulkUploadForm")
    .addEventListener("submit", function(e) {

        e.preventDefault();

        let fd = new FormData(this);

        fetch("qur_bop_master.php?action=bulk_upload", {

                method: "POST",

                body: fd

            })
            .then(r => r.json())
            .then(res => {

                alert(res.message);

                table.replaceData();

                bootstrap.Modal.getInstance(
                    document.getElementById("bulkUploadModal")
                ).hide();

            });

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
            <div class="card-body">
                <div id="part_table"></div>
                <script>
                const table = new Tabulator("#part_table", {
                    ajaxURL: "qur_bop_master.php?action=list1",
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
                            width: 55,
                            sorter: "number",
                            resizable: false,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "Part No",
                            field: "part_no",
                            width: 135,
                            resizable: false,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "FG Code",
                            field: "fg_code",
                            width: 135,
                            resizable: false,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },

                        {
                            title: "BOP Part Name",
                            field: "bop_part_name",
                            resizable: false,
                            width: 160,
                            headerHozAlign: "center"
                        },
                        {
                            title: "BOP Part No",
                            field: "bop_part_no",
                            resizable: false,
                            width: 140,
                            headerHozAlign: "center"
                        },
                        {
                            title: "BOP ERP Code",
                            field: "bop_erp_code",
                            resizable: false,
                            width: 150,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Supplier Name",
                            field: "supplier_name",
                            resizable: false,
                            width: 170,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Quantity",
                            field: "bop_quantity",
                            width: 110,
                            resizable: false,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "UMO",
                            field: "umo",
                            width: 80,
                            resizable: false,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "Created By",
                            field: "created_by",
                            width: 120,
                            resizable: false,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "Updated By",
                            field: "updated_by",
                            width: 130,
                            resizable: false,
                            hozAlign: "center",
                            headerHozAlign: "center"
                        },
                        {
                            title: "Action",
                            width: 90,
                            resizable: false,
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