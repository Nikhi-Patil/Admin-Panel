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

<?php if ($page == "plant") { ?>
<script>
let selectedUnitIds = [];
let selectedUnitNames = [];

function normalizeIds(value) {
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

function applySelectedUnits() {
    const button = document.getElementById("unitButton");

    document.querySelectorAll(".plant-unit-checkbox").forEach(cb => {
        cb.checked = selectedUnitIds.includes(cb.value);
    });

    if (button) {
        if (!selectedUnitIds.length) {
            button.textContent = "Select Units";
        } else if (selectedUnitNames.length && selectedUnitNames.length <= 3) {
            button.textContent = selectedUnitNames.join(", ");
        } else {
            button.textContent = `${selectedUnitIds.length} Units Selected`;
        }
    }
}

function setSelectedUnits(ids, names) {
    selectedUnitIds = normalizeIds(ids);
    selectedUnitNames = normalizeIds(names);
    applySelectedUnits();
}

function refreshUnitSummary() {
    selectedUnitIds = Array.from(document.querySelectorAll(".plant-unit-checkbox:checked")).map(cb => cb.value);
    selectedUnitNames = Array.from(document.querySelectorAll(".plant-unit-checkbox:checked")).map(cb => cb.dataset
        .name);
    applySelectedUnits();
}
</script>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Plant List</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#plantModal">
                            <i class="fas fa-plus"></i>
                            Add Plant
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
                    <div id="plant_table"></div>

                    <script>
                    const table = new Tabulator("#plant_table", {
                        ajaxURL: "qur_plant_master.php?action=list",
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
                                width: 120,
                                sorter: "number",
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Unit",
                                field: "unit",
                                resizable: false,
                                headerHozAlign: "center"
                            },
                            {
                                title: "Plant Name",
                                field: "plant_name",
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
                                width: 120,
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
                        if (e.target.closest(".delete-btn")) {
                            let id = e.target.closest(".delete-btn").dataset.id;

                            if (confirm("Delete this record?")) {
                                fetch("qur_plant_master.php?action=delete", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded"
                                        },
                                        body: "id=" + id
                                    })
                                    .then(res => res.json())
                                    .then(res => {
                                        if (res.status == "success") {
                                            table.deleteRow(id);
                                        } else {
                                            alert(res.message || "Delete failed.");
                                        }
                                    });
                            }
                        }
                    });

                    document.addEventListener("click", function(e) {
                        if (e.target.closest(".edit-btn")) {
                            let id = e.target.closest(".edit-btn").dataset.id;

                            fetch("qur_plant_master.php?action=get&id=" + id)
                                .then(response => response.json())
                                .then(data => {
                                    document.getElementById("plant_id").value = data.id;
                                    document.getElementById("plant_name").value = data.plant_name;

                                    setSelectedUnits(data.unit_id, data.unit);

                                    document.getElementById("plantModalLabel").innerHTML = "Edit Plant";
                                    document.getElementById("saveBtn").innerHTML = "Update";

                                    new bootstrap.Modal(document.getElementById("plantModal")).show();
                                });
                        }
                    });

                    document.querySelector('[data-bs-target="#plantModal"]').addEventListener("click", function() {
                        document.getElementById("plantForm").reset();
                        document.getElementById("plant_id").value = "";
                        setSelectedUnits([], []);
                        document.getElementById("plantModalLabel").innerHTML = "Add Plant";
                        document.getElementById("saveBtn").innerHTML = "Save";
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="plantModal" tabindex="-1" aria-labelledby="plantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="plantModalLabel">Add Plant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form id="plantForm">
                        <input type="hidden" id="plant_id" name="id">
                        <div class="mb-3">
                            <label for="plant_name" class="form-label">Plant Name</label>
                            <input type="text" id="plant_name" name="plant_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Units</label>
                            <div class="dropdown w-100">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start" type="button"
                                    id="unitButton" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    Select Units
                                </button>
                                <ul class="dropdown-menu w-100 p-2" id="unitMenu"
                                    style="max-height: 260px; overflow:auto;"></ul>
                            </div>
                        </div>
                        <button type="submit" id="saveBtn" class="btn btn-success">Save</button>
                    </form>
                </div>

                <script>
                function renderUnitCheckboxes(items) {
                    const holder = document.getElementById("unitMenu");

                    if (!items.length) {
                        holder.innerHTML = '<div class="text-muted">No units found.</div>';
                        return;
                    }

                    holder.innerHTML = items.map(item => `
                            <li>
                                <label class="dropdown-item d-flex align-items-center gap-2 mb-0">
                                    <input class="form-check-input plant-unit-checkbox m-0" type="checkbox"
                                        name="unit[]" value="${item.id}" data-name="${item.unit}" id="unit_${item.id}">
                                    <span>${item.unit}</span>
                                </label>
                            </li>
                        `).join('');

                    applySelectedUnits();
                }

                document.addEventListener("change", function(e) {
                    if (e.target.classList.contains("plant-unit-checkbox")) {
                        refreshUnitSummary();
                    }
                });

                document.getElementById("plantForm").addEventListener("submit", function(e) {
                    e.preventDefault();

                    let formData = new FormData(this);

                    fetch("qur_plant_master.php?action=save", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status == "success") {
                                bootstrap.Modal.getInstance(
                                    document.getElementById("plantModal")
                                ).hide();

                                document.getElementById("plantForm").reset();
                                document.getElementById("plant_id").value = "";
                                setSelectedUnits([], []);
                                document.getElementById("plantModalLabel").innerHTML = "Add Plant";
                                document.getElementById("saveBtn").innerHTML = "Save";

                                if (data.mode == "insert") {
                                    table.addRow(data.data, true).then(() => {
                                        table.setPage(1);
                                    });
                                } else {
                                    table.updateOrAddData([data.data]);
                                }
                            } else {
                                alert(data.message || "Save failed.");
                            }
                        });
                });

                fetch("qur_plant_master.php?action=units")
                    .then(res => res.json())
                    .then(data => {
                        renderUnitCheckboxes(data);
                    });
                </script>
            </div>
        </div>
    </div>

    </main>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('table-filter').addEventListener('input', (e) => {
            const value = e.target.value;
            if (value) {
                table.setFilter([{
                        field: 'unit',
                        type: 'like',
                        value: value
                    },
                    {
                        field: 'plant_name',
                        type: 'like',
                        value: value
                    },
                    {
                        field: 'id',
                        type: 'like',
                        value: value
                    }
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
            .addEventListener('click', () => table.download('csv', 'plant_master.csv'));
    });
    </script>

    <?php } ?>

    <?php if ($page == "recycle") { ?>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Plant Recycle Bin</h1>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <div id="plant_table"></div>
                        <script>
                        const table = new Tabulator("#plant_table", {
                            ajaxURL: "qur_plant_master.php?action=list1",
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
                                    width: 120,
                                    sorter: "number",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Plant Name",
                                    field: "plant_name",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },
                                {
                                    title: "Unit",
                                    field: "unit",
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
                                    title: "Deleted By",
                                    field: "updated_by",
                                    width: 150,
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

                            if (confirm("Restore this Plant?")) {
                                fetch("qur_plant_master.php?action=restore", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/x-www-form-urlencoded"
                                        },
                                        body: "id=" + id
                                    })
                                    .then(res => res.json())
                                    .then(res => {
                                        if (res.status == "success") {
                                            table.deleteRow(id);
                                            alert("Plant restored successfully.");
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