<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";

$page = $_GET['page'] ?? 'sub_category';
?>

<?php if ($page == "sub_category") { ?>
<script>
let selectedSupplierIds = [];
let selectedSupplierNames = [];

function normalizeSupplierIds(value) {
    if (Array.isArray(value)) {
        return value.map(v => String(v).trim()).filter(Boolean);
    }

    if (value === null || value === undefined) {
        return [];
    }

    return String(value)
        .split(",")
        .map(v => v.trim())
        .filter(Boolean);
}

function refreshSupplierSelection() {
    const hidden = document.getElementById("supplier_id");
    const button = document.getElementById("supplierButton");

    if (hidden) {
        hidden.value = selectedSupplierIds.join(",");
    }

    if (button) {
        if (!selectedSupplierIds.length) {
            button.textContent = "Select Supplier";
        } else if (selectedSupplierNames.length && selectedSupplierNames.length <= 3) {
            button.textContent = selectedSupplierNames.join(", ");
        } else {
            button.textContent = `${selectedSupplierIds.length} Suppliers Selected`;
        }
    }

    document.querySelectorAll(".supplier-option").forEach(cb => {
        cb.checked = selectedSupplierIds.includes(cb.value);
    });
}

function setSupplierSelection(ids, names) {
    selectedSupplierIds = normalizeSupplierIds(ids);
    selectedSupplierNames = normalizeSupplierIds(names);
    refreshSupplierSelection();
}

function setCategorySelection(id) {
    document.getElementById("category_id").value = id || "";
}
</script>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Sub Category List</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#subCategoryModal">
                            <i class="fas fa-plus"></i>
                            Add Sub Category
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
                <div id="sub_category_table"></div>

                <script>
                const table = new Tabulator("#sub_category_table", {
                    ajaxURL: "qur_sub_category_master.php?action=list",
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
                            width: 90,
                            sorter: "number",
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Supplier",
                            field: "supplier_name",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Category",
                            field: "category_name",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Sub Category",
                            field: "sub_category_name",
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
                document.addEventListener("change", function(e) {
                    if (e.target.classList.contains("supplier-option")) {
                        selectedSupplierIds = Array.from(document.querySelectorAll(".supplier-option:checked"))
                            .map(cb => cb.value);
                        selectedSupplierNames = Array.from(document.querySelectorAll(
                                ".supplier-option:checked"))
                            .map(cb => cb.dataset.name);
                        refreshSupplierSelection();
                    }
                });

                document.addEventListener("click", function(e) {
                    let deleteBtn = e.target.closest(".delete-btn");
                    if (deleteBtn) {
                        let id = deleteBtn.dataset.id;
                        if (confirm("Delete this sub category?")) {
                            fetch("qur_sub_category_master.php?action=delete", {
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
                        fetch("qur_sub_category_master.php?action=get&id=" + encodeURIComponent(id))
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById("sub_category_id").value = data.id;
                                setSupplierSelection(data.supplier_id, data.supplier_name);
                                setCategorySelection(data.category_id);
                                document.getElementById("sub_category_name").value = data.sub_category_name;
                                document.getElementById("subCategoryModalLabel").innerHTML =
                                    "Edit Sub Category";
                                document.getElementById("saveBtn").innerHTML = "Update";
                                new bootstrap.Modal(document.getElementById("subCategoryModal")).show();
                            });
                    }
                });

                document.querySelector('[data-bs-target="#subCategoryModal"]').addEventListener("click", function() {
                    document.getElementById("subCategoryForm").reset();
                    document.getElementById("sub_category_id").value = "";
                    setSupplierSelection("", "");
                    document.getElementById("category_id").value = "";
                    document.getElementById("subCategoryModalLabel").innerHTML = "Add Sub Category";
                    document.getElementById("saveBtn").innerHTML = "Save";
                });
                </script>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="subCategoryModal" tabindex="-1" aria-labelledby="subCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subCategoryModalLabel">Add Sub Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="subCategoryForm">
                    <input type="hidden" id="sub_category_id" name="id">
                    <input type="hidden" id="supplier_id" name="supplier_id">

                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button"
                                id="supplierButton" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                aria-expanded="false">
                                Select Supplier
                            </button>
                            <ul class="dropdown-menu w-100 p-2" id="supplierMenu"></ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <select id="category_id" name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sub Category Name</label>
                        <input type="text" id="sub_category_name" name="sub_category_name" class="form-control"
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
function loadSuppliers() {
    fetch("qur_sub_category_master.php?action=suppliers")
        .then(res => res.json())
        .then(data => {
            const menu = document.getElementById("supplierMenu");
            menu.innerHTML = "";

            data.forEach(item => {
                menu.innerHTML += `
                    <li>
                        <label class="dropdown-item d-flex align-items-center gap-2 mb-0">
                            <input type="checkbox" class="supplier-option form-check-input m-0"
                                value="${item.id}" data-name="${item.supplier_name}">
                            <span>${item.supplier_name}</span>
                        </label>
                    </li>
                `;
            });

            refreshSupplierSelection();
        });
}

function loadCategories() {
    fetch("qur_sub_category_master.php?action=categories")
        .then(res => res.json())
        .then(data => {
            const category = document.getElementById("category_id");
            category.innerHTML = '<option value="">Select Category</option>';

            data.forEach(item => {
                category.innerHTML += `<option value="${item.id}">${item.category_name}</option>`;
            });
        });
}

document.getElementById("subCategoryForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    fetch("qur_sub_category_master.php?action=save", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                bootstrap.Modal.getInstance(document.getElementById("subCategoryModal")).hide();
                document.getElementById("subCategoryForm").reset();
                document.getElementById("sub_category_id").value = "";
                setSupplierSelection("", "");
                document.getElementById("category_id").value = "";
                document.getElementById("subCategoryModalLabel").innerHTML = "Add Sub Category";
                document.getElementById("saveBtn").innerHTML = "Save";
                table.replaceData();
            } else {
                alert(data);
            }
        });
});

document.addEventListener('DOMContentLoaded', () => {
    loadSuppliers();
    loadCategories();

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
                        field: 'category_name',
                        type: 'like',
                        value: value
                    },
                    {
                        field: 'sub_category_name',
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
        'sub_category_master.csv'));
});
</script>
<?php } ?>

<?php if ($page == "recycle") { ?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Sub Category Recycle Bin</h1>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div id="sub_category_table"></div>

                <script>
                const table = new Tabulator("#sub_category_table", {
                    ajaxURL: "qur_sub_category_master.php?action=list1",
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
                            width: 90,
                            sorter: "number",
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Supplier",
                            field: "supplier_name",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Category",
                            field: "category_name",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Sub Category",
                            field: "sub_category_name",
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

                    if (confirm("Restore this sub category?")) {
                        fetch("qur_sub_category_master.php?action=restore", {
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
                                    alert("Sub category restored successfully.");
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