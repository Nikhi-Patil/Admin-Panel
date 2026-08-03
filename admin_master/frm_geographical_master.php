<?php
session_start();

include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";
$page = $_GET['page'] ?? 'geographical';
?>

<?php if ($page == "geographical") { ?>
<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Geographical Master</h1>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#geoModal">
                            <i class="fas fa-plus"></i>
                            Add Geographical
                        </button>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="geoModal" tabindex="-1" aria-labelledby="geoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="geoModalLabel">Add Geographical</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                </button>
            </div>
            <div class="modal-body">
                <form id="geoForm">
                    <input type="hidden" id="geo_id" name="id">
                    <div class="mb-3">
                        <label for="geo_type">Domestic/Export</label>

                        <select id="geo_type" name="geo_type" class="form-control" required>
                            <option value="">Select Domestic/Export</option>
                            <option value="Domestic">Domestic</option>
                            <option value="Export">Export</option>
                        </select>
                    </div>

                    <div class="mb-3 geo-dependent-field" style="display:none;">
                        <label id="lbl_region" for="region_name"></label>

                        <input type="text" id="region_name" name="region_name" class="form-control">
                    </div>
                    <div class="mb-3 geo-dependent-field" style="display:none;">
                        <label id="lbl_location" for="location_name"></label>

                        <input type="text" id="location_name" name="location_name" class="form-control">
                    </div>
                    <button type="submit" id="saveGeoBtn" class="btn btn-success">
                        Save
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {

    const geoModal = document.getElementById("geoModal");
    const geoForm = document.getElementById("geoForm");
    const geoType = document.getElementById("geo_type");
    const regionField = document.getElementById("region_name");
    const locationField = document.getElementById("location_name");

    function resetGeoForm() {
        geoForm.reset();

        document.getElementById("geo_id").value = "";
        document.getElementById("geoModalLabel").innerHTML =
            "Add Geographical";
        document.getElementById("saveGeoBtn").innerHTML = "Save";

        document.querySelectorAll(".geo-dependent-field").forEach(function(field) {
            field.style.display = "none";
        });

        document.getElementById("lbl_region").innerHTML = "";
        document.getElementById("lbl_location").innerHTML = "";
    }

    function updateGeoFields() {
        document.querySelectorAll(".geo-dependent-field").forEach(function(field) {
            field.style.display = "block";
        });

        if (geoType.value === "Domestic") {
            document.getElementById("lbl_region").innerHTML = "Zone";
            document.getElementById("lbl_location").innerHTML = "State";
        } else if (geoType.value === "Export") {
            document.getElementById("lbl_region").innerHTML = "Continent";
            document.getElementById("lbl_location").innerHTML = "Country";
        } else {
            document.querySelectorAll(".geo-dependent-field").forEach(function(field) {
                field.style.display = "none";
            });
        }
    }

    geoType.addEventListener("change", updateGeoFields);

    document.querySelector('[data-bs-target="#geoModal"]')
        .addEventListener("click", resetGeoForm);

    geoModal.addEventListener("hidden.bs.modal", resetGeoForm);

    geoForm.addEventListener("submit", function(e) {
        e.preventDefault();

        fetch("qur_geographical_master.php?action=save", {
                method: "POST",
                body: new FormData(geoForm)
            })
            .then(response => response.text())
            .then(result => {
                if (result.trim() === "success") {
                    bootstrap.Modal.getInstance(geoModal).hide();
                    geographicalTable.replaceData();
                    resetGeoForm();
                } else {
                    alert(result);
                }
            })
            .catch(error => {
                console.error(error);
                alert("Error while saving data.");
            });
    });
});
</script>

<di class="app-content">
    <div class="container-fluid">
        <div class="card">

            <div class="card-header d-flex align-items-center">
                <button id="export-csv" type="button" class="btn btn-sm btn-warning">
                    <i class="fa-solid fa-file-csv"></i>
                    Export CSV
                </button>
                <button id="print-table" type="button" class="btn btn-sm btn-info ms-2">
                    <i class="fa-solid fa-print"></i>
                    Print
                </button>
                <div class="card-tools m-0 d-flex align-items-center ms-auto">
                    <div class="input-group input-group-sm" style="width: 16rem">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input id="table-filter" type="search" class="form-control" placeholder="Filter rows..."
                            aria-label="Filter rows">
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div id="geographical_table"></div>
                <script>
                const geographicalTable = new Tabulator("#geographical_table", {
                    ajaxURL: "qur_geographical_master.php?action=list",
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
                            title: "Domestic/Export",
                            field: "geo_type",
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Region",
                            field: "region_name",
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center"
                        },
                        {
                            title: "Location",
                            field: "location_name",
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
                            width: 120,
                            hozAlign: "center",
                            resizable: false,
                            headerHozAlign: "center",
                            formatter: function(cell) {
                                const row = cell.getRow().getData();
                                return `
                                    <button class="btn btn-primary edit-geo"
                                        data-id="${row.id}">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-danger delete-geo"
                                        data-id="${row.id}">
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
                    const deleteButton = e.target.closest(".delete-geo");
                    if (deleteButton) {
                        const id = deleteButton.dataset.id;
                        if (confirm("Delete this geographical record?")) {
                            fetch("qur_geographical_master.php?action=delete", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/x-www-form-urlencoded"
                                    },
                                    body: "id=" + encodeURIComponent(id)
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.status === "success") {
                                        geographicalTable.replaceData();
                                        alert("Deleted Successfully");
                                    } else {
                                        alert(result.message || "Delete failed.");
                                    }
                                });
                        }
                    }

                    const editButton = e.target.closest(".edit-geo");
                    if (editButton) {
                        const id = editButton.dataset.id;
                        fetch("qur_geographical_master.php?action=get&id=" + id)
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById("geo_id").value = data.id;
                                document.getElementById("geo_type").value = data.geo_type;
                                document.getElementById("region_name").value = data.region_name;
                                document.getElementById("location_name").value = data.location_name;
                                updateGeoLabels();
                                document.getElementById("geoModalLabel").innerHTML =
                                    "Edit Geographical";
                                document.getElementById("saveGeoBtn").innerHTML = "Update";
                                new bootstrap.Modal(
                                    document.getElementById("geoModal")
                                ).show();
                            });
                    }
                });

                document.querySelector('[data-bs-target="#geoModal"]')
                    .addEventListener("click", function() {
                        document.getElementById("geoForm").reset();
                        document.getElementById("geo_id").value = "";
                        document.getElementById("geoModalLabel").innerHTML =
                            "Add Geographical";
                        document.getElementById("saveGeoBtn").innerHTML = "Save";
                        updateGeoLabels();
                    });

                document.getElementById("geoForm").addEventListener("submit", function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    fetch("qur_geographical_master.php?action=save", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.text())
                        .then(result => {
                            if (result.trim() === "success") {
                                bootstrap.Modal.getInstance(
                                    document.getElementById("geoModal")
                                ).hide();
                                document.getElementById("geoForm").reset();
                                document.getElementById("geo_id").value = "";
                                document.getElementById("geoModalLabel").innerHTML =
                                    "Add Geographical";
                                document.getElementById("saveGeoBtn").innerHTML = "Save";
                                updateGeoLabels();
                                geographicalTable.replaceData();
                            } else {
                                alert(result);
                            }
                        });
                });
                document.addEventListener("DOMContentLoaded", function() {
                    updateGeoLabels();
                    document.getElementById("table-filter")
                        .addEventListener("input", function(e) {
                            const value = e.target.value;
                            if (value) {
                                geographicalTable.setFilter([
                                    [{
                                            field: "geo_type",
                                            type: "like",
                                            value: value
                                        },
                                        {
                                            field: "region_name",
                                            type: "like",
                                            value: value
                                        },
                                        {
                                            field: "location_name",
                                            type: "like",
                                            value: value
                                        },
                                        {
                                            field: "id",
                                            type: "like",
                                            value: value
                                        }
                                    ]
                                ]);
                            } else {
                                geographicalTable.clearFilter();
                            }
                        });
                    document.getElementById("print-table")
                        .addEventListener("click", function() {
                            geographicalTable.print(false, true);
                        });
                    document.getElementById("export-csv")
                        .addEventListener("click", function() {
                            geographicalTable.download(
                                "csv",
                                "geographical_master.csv"
                            );
                        });
                });
                </script>
            </div>
        </div>
    </div>
    </div>

    </main>
    <?php } ?>

    <?php if ($page == "recycle") { ?>
    <div class="app-content-header">
        <div class="container-fluid">
            <h1 class="mb-0 fs-3">Geographical Recycle Bin</h1>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div id="geographical_recycle_table"></div>
                    <script>
                    const geographicalRecycleTable = new Tabulator(
                        "#geographical_recycle_table", {
                            ajaxURL: "qur_geographical_master.php?action=list1",
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
                                    resizable: false,
                                    headerHozAlign: "center",
                                    hozAlign: "center"
                                },
                                {
                                    title: "Domestic/Export",
                                    field: "geo_type",
                                    resizable: false,
                                    headerHozAlign: "center",
                                    hozAlign: "center"
                                },
                                {
                                    title: "Region",
                                    field: "region_name",
                                    resizable: false,
                                    headerHozAlign: "center",
                                    hozAlign: "center"
                                },
                                {
                                    title: "Location",
                                    field: "location_name",
                                    resizable: false,
                                    headerHozAlign: "center",
                                    hozAlign: "center"
                                },
                                {
                                    title: "Created By",
                                    field: "created_by",
                                    width: 160,
                                    resizable: false,
                                    headerHozAlign: "center",
                                    hozAlign: "center"
                                },
                                {
                                    title: "Deleted By",
                                    field: "updated_by",
                                    width: 160,
                                    resizable: false,
                                    headerHozAlign: "center",
                                    hozAlign: "center"
                                },
                                {
                                    title: "Action",
                                    width: 100,
                                    resizable: false,
                                    headerHozAlign: "center",
                                    hozAlign: "center",
                                    formatter: function(cell) {
                                        const row = cell.getRow().getData();
                                        return `
                                        <button class="btn btn-primary restore-geo"
                                            data-id="${row.id}">
                                            <i class="fa-solid fa-trash-arrow-up"></i>
                                        </button>
                                    `;
                                    }
                                }
                            ]
                        }
                    );
                    document.addEventListener("click", function(e) {
                        const button = e.target.closest(".restore-geo");
                        if (!button) return;
                        const id = button.dataset.id;
                        if (confirm("Restore this geographical record?")) {
                            fetch("qur_geographical_master.php?action=restore", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/x-www-form-urlencoded"
                                    },
                                    body: "id=" + encodeURIComponent(id)
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.status === "success") {
                                        geographicalRecycleTable.replaceData();
                                        alert("Geographical record restored successfully.");
                                    } else {
                                        alert(result.message || "Restore failed.");
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