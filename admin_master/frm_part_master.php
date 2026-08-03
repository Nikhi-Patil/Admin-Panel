<?php
    session_start();
    include "../inc/db_cfg.php";
    include "../include/top_header.php";
    include "../include/header.php";
    include "../include/left_nav_bar.php";

    $page = $_GET['page'] ?? 'part';
?>

<?php if ($page == "part") { ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Part Master</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#partModal">
                            <i class="fas fa-plus"></i>
                            Add Part
                        </button>
                        <button class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#partImportModal">
                            <i class="fa-solid fa-file-excel"></i>
                            Excel Bulk Upload
                        </button>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="partImportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="fa-solid fa-file-excel"></i>
                    Bulk Upload Part Master
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
                            Download the latest Part Master Excel template.
                            Fill all the required columns without changing
                            the header names.
                        </p>

                        <a href="qur_part_master.php?action=download_template" class="btn btn-success">

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

                        <form id="partImportForm" enctype="multipart/form-data">

                            <label class="form-label">
                                Select Excel File
                            </label>

                            <input type="file" id="part_excel_file" name="excel_file" class="form-control mb-3"
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
                <div id="part_table"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="partModal" tabindex="-1" aria-labelledby="partModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="partModalLabel">Add Part</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="partForm">
                    <input type="hidden" id="part_id" name="id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Part Name</label>
                            <input type="text" id="part_name" name="part_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Part No</label>
                            <input type="text" id="part_no" name="part_no" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label> FG Code</label>
                            <input type="text" id="fg_code" name="fg_code" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Inter Unit/Dept. Code</label>
                            <input type="text" id="inter_code" name="inter_code" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label> IM-Compound Code</label>
                            <input type="text" id="im_code" name="im_code" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Sub Department</label>
                            <select id="sub_department_id" name="sub_department_id" class="form-select" required>
                                <option value="">Select Sub Department</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Department</label>
                            <input type="text" id="department_name" class="form-control" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Unit</label>
                            <input type="text" id="unit_name" class="form-control" readonly>
                        </div>
                    </div>
                    <button type="submit" id="saveBtn" class="btn btn-success">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
//script for the 
let allSubDepartments = [];
let allCustomers = [];

//script for the Subdepartment Option 
function populateSubDepartmentOptions(selectedId = "") {
    const select = document.getElementById("sub_department_id");
    select.innerHTML = '<option value="">Select Sub Department</option>';
    allSubDepartments.forEach(item => {
        const selected = String(selectedId) === String(item.id) ? "selected" : "";

        select.innerHTML += ` 
                    <option
                        value="${item.id}"
                        data-department-name="${item.department_name}"
                        data-unit="${item.unit}"
                        ${selected}>
                        ${item.unit} | ${item.department_name} | ${item.sub_department_name}
                    </option>`;

    });
}
//script for the department Option 
function updateDepartmentName() {
    const option = document.getElementById("sub_department_id").selectedOptions[0];
    document.getElementById("department_name").value =
        option.dataset.departmentName || "";
    document.getElementById("unit_name").value =
        option.dataset.unit || "";
}
//script for the Display Table
const table = new Tabulator("#part_table", {
    ajaxURL: "qur_part_master.php?action=list",
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
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "Part Name",
            field: "part_name",
            width: 150,
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "Part No",
            field: "part_no",
            width: 100,
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "FG Code",
            field: "fg_code",
            width: 105,
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "IM Code",
            field: "im_code",
            width: 105,
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "Inter Unit/Dept Code",
            field: "inter_code",
            width: 210,
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "Unit",
            field: "unit",
            width: 140,
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "Department",
            field: "department_name",
            width: 140,
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "Sub Department",
            field: "sub_department_name",
            width: 165,
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "Created By",
            field: "created_by",
            width: 130,
            hozAlign: "center",
            resizable: false,
            headerHozAlign: "center"
        },
        {
            title: "Updated By",
            field: "updated_by",
            width: 130,
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
                const row = cell.getRow().getData();
                return ` <
                    button class = "btn btn-primary action-btn edit-btn"
                data - id = "${row.id}" >
                    <
                    i class = "fa-solid fa-pen" > < /i> < /
                button > <
                    button class = "btn btn-danger action-btn delete-btn"
                data - id = "${row.id}" >
                    <
                    i class = "fa-solid fa-trash" > < /i> < /
                button >
                    `;
            }
        }
    ]
});

document.addEventListener("click", function(e) {
    const deleteBtn = e.target.closest(".delete-btn");
    const editBtn = e.target.closest(".edit-btn");

    //script for the Delete Button 
    if (deleteBtn) {
        const id = deleteBtn.dataset.id;
        if (confirm("Delete this part?")) {
            fetch("qur_part_master.php?action=delete", {
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
    //script for the Edit Button
    if (editBtn) {
        const id = editBtn.dataset.id;
        fetch("qur_part_master.php?action=get&id=" + encodeURIComponent(id))
            .then(res => res.json())
            .then(data => {
                document.getElementById("part_id").value = data.id || "";
                document.getElementById("part_name").value = data.part_name || "";
                document.getElementById("part_no").value = data.part_no || "";
                document.getElementById("fg_code").value = data.fg_code || "";
                document.getElementById("im_code").value = data.im_code || "";
                document.getElementById("inter_code").value = data.inter_code || "";
                populateSubDepartmentOptions(data.sub_department_id);
                document.getElementById("sub_department_id").value = data.sub_department_id || "";
                updateDepartmentName();


                document.getElementById("partModalLabel").innerHTML = "Edit Part";
                document.getElementById("saveBtn").innerHTML = "Update";
                new bootstrap.Modal(document.getElementById("partModal")).show();
            });
    }
});
//script for the reset the form
document.querySelector('[data-bs-target="#partModal"]').addEventListener("click", function() {
    document.getElementById("partForm").reset();
    document.getElementById("part_id").value = "";
    document.getElementById("partModalLabel").innerHTML = "Add Part";
    document.getElementById("saveBtn").innerHTML = "Save";
    document.getElementById("fg_code").value = "";
    document.getElementById("im_code").value = "";
    document.getElementById("inter_code").value = "";
    document.getElementById("sub_department_id").value = "";
    document.getElementById("department_name").value = "";
});

document.getElementById("sub_department_id").addEventListener("change", function() {
    updateDepartmentName();
});

//script for the save the data
document.getElementById("partForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch("qur_part_master.php?action=save", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                bootstrap.Modal.getInstance(document.getElementById("partModal")).hide();
                document.getElementById("partForm").reset();
                document.getElementById("part_id").value = "";
                document.getElementById("partModalLabel").innerHTML = "Add Part";
                document.getElementById("saveBtn").innerHTML = "Save";
                document.getElementById("fg_code").value = "";
                document.getElementById("im_code").value = "";
                document.getElementById("inter_code").value = "";
                document.getElementById("sub_department_id").value = "";
                document.getElementById("department_name").value = "";
                table.replaceData();
            } else {
                alert(data.message || "Save failed.");
            }
        });
});

function loadPartLookups() {
    fetch("qur_part_master.php?action=sub_departments")
        .then(res => res.json())
        .then(data => {
            allSubDepartments = data;
            populateSubDepartmentOptions();
        });
}
//script for the filter
document.addEventListener("DOMContentLoaded", () => {
    loadPartLookups();
    document.getElementById("table-filter").addEventListener("input", (e) => {
        const value = e.target.value;
        if (value) {
            const term = value.toLowerCase();
            table.setFilter(function(data) {
                return [
                    data.part_name,
                    data.part_no,
                    data.fg_code,
                    data.im_code,
                    data.inter_code,
                    data.sub_department_name,
                    data.department_name
                ].some(field => String(field ?? "").toLowerCase().includes(term));
            });
        } else {
            table.clearFilter();
        }
    });
    document.getElementById("print-table").addEventListener("click", () => table.print(false, true));
    document.getElementById("export-csv").addEventListener("click", () => table.download("csv",
        "part_master.csv"));
});
</script>
<script>
//script for the import Excel
document.getElementById("partImportForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const fileInput = document.getElementById("part_excel_file");

    if (!fileInput.files.length) {
        alert("Please select an Excel file.");
        return;
    }

    fetch("qur_part_master.php?action=bulk_upload", {
            method: "POST",
            body: new FormData(this)
        })
        .then(response => response.text())
        .then(text => {
            const result = JSON.parse(text);

            if (result.status === "success") {
                alert(result.message);
                bootstrap.Modal.getInstance(
                    document.getElementById("partImportModal")
                )?.hide();

                this.reset();
                table.replaceData();
            } else {
                alert(result.message || "Part import failed.");
            }
        })
        .catch(error => alert("Part import failed: " + error.message));
});
</script>
</main>
<?php } ?>

<?php if ($page == "recycle") { ?>
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Part Recycle Bin</h1>
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
                //script for the recycle table
                const table = new Tabulator("#part_table", {
                    ajaxURL: "qur_part_master.php?action=list1",
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
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Part Name",
                            field: "part_name",
                            width: 150,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Part No",
                            field: "part_no",
                            width: 100,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "FG Code",
                            field: "fg_code",
                            width: 105,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "IM Code",
                            field: "im_code",
                            width: 105,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Inter Unit/Dept Code",
                            field: "inter_code",
                            width: 105,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Unit",
                            field: "unit",
                            width: 140,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Department",
                            field: "department_name",
                            width: 140,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Sub Department",
                            field: "sub_department_name",
                            width: 165,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Created By",
                            field: "created_by",
                            width: 130,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Updated By",
                            field: "updated_by",
                            width: 130,
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
                //script for the restore button
                document.addEventListener("click", function(e) {
                    const btn = e.target.closest(".recycle-btn");
                    if (!btn) return;

                    const id = btn.dataset.id;
                    if (confirm("Restore this part?")) {
                        fetch("qur_part_master.php?action=restore", {
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
                                    alert("Part restored successfully.");
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