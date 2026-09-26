<?php

session_start();

include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";

$page = $_GET['page'] ?? 'margin';

?>

<?php if ($page == "margin") { ?>
    <!-- HEADER -->
    <div class="app-content-header" style="padding: 6px 0.5rem;">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Margin Master</h1>
                </div>

                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <button
                                class="btn btn-success"
                                data-bs-toggle="modal"
                                data-bs-target="#marginModal"
                                id="addmarginBtn">
                                <i class="fas fa-plus"></i>
                                Add Margin
                            </button>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="app-content">
        <div class="container-fluid">
            <div class="card">
                <!-- CARD HEADER -->
                <div class="card-header">
                    <button
                        id="export-csv"
                        type="button"
                        class="btn btn-sm btn-warning">
                        <i class="fa-solid fa-file-csv"></i>
                        Export CSV
                    </button>
                    <button
                        id="print-table"
                        type="button"
                        class="btn btn-sm btn-info">
                        <i class="fa-solid fa-print"></i>
                        Print
                    </button>
                    <div class="card-tools m-0 d-flex align-items-center ms-auto">
                        <div
                            class="input-group input-group-sm"
                            style="width: 16rem">
                            <span class="input-group-text">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input
                                id="table-filter"
                                type="search"
                                class="form-control"
                                placeholder="Filter rows..."
                                aria-label="Filter rows">
                        </div>
                    </div>
                </div>
                <!-- TABLE BODY -->
                <div class="card-body">
                    <div id="margin_table"></div>
                    <script>
                        const margintable = new Tabulator("#margin_table", {
                            ajaxURL: "qur_margin_master.php?action=list",
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
                                    title: "SR",
                                    field: "id",
                                    width: 80,
                                    sorter: "number",
                                    hozAlign: "center",
                                    headerHozAlign: "center",
                                    resizable: false
                                },
                                {
                                    title: "Margin Type",
                                    field: "margin_type",
                                    sorter: "number",
                                    hozAlign: "center",
                                    headerHozAlign: "center",
                                    resizable: false
                                },
                                {
                                    title: "Percentage",
                                    field: "percentage",
                                    hozAlign: "center",
                                    headerHozAlign: "center",
                                    resizable: false,
                                    formatter: function(cell) {
                                        let value = parseFloat(cell.getValue());
                                        return isNaN(value) ?
                                            "" :
                                            value.toFixed(0) + "%";
                                    }
                                },
                                {
                                    title: "Created By",
                                    field: "created_by",
                                    width: 140,
                                    hozAlign: "center",
                                    headerHozAlign: "center",
                                    resizable: false
                                },
                                {
                                    title: "Updated By",
                                    field: "updated_by",
                                    width: 140,
                                    hozAlign: "center",
                                    headerHozAlign: "center",
                                    resizable: false
                                },
                                {
                                    title: "Action",
                                    width: 140,
                                    hozAlign: "center",
                                    headerHozAlign: "center",
                                    resizable: false,
                                    formatter: function(cell) {
                                        let row = cell.getRow().getData();
                                        return `
                                    <button
                                        class="btn btn-primary action-btn edit-btn"
                                        data-id="${row.id}"
                                        title="Edit"
                                    >
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                `;
                                    }
                                }

                            ]

                        });
                    </script>

                    <script>
                        // EDIT
                        document.addEventListener("click", function(e) {
                            let editBtn = e.target.closest(".edit-btn");
                            if (!editBtn) return;
                            let id = editBtn.dataset.id;

                            fetch(
                                    "qur_margin_master.php?action=get&id=" +
                                    encodeURIComponent(id)
                                )
                                .then(response => response.json())
                                .then(data => {
                                    document.getElementById("margin_id").value =
                                        data.id;
                                    document.getElementById("margin_type").value =
                                        data.margin_type;
                                    document.getElementById("percentage").value =
                                        parseFloat(data.percentage).toFixed(2);
                                    document.getElementById("marginModalLabel").innerHTML =
                                        "Edit Margin";
                                    document.getElementById("saveBtn").innerHTML =
                                        "Update";
                                    new bootstrap.Modal(
                                        document.getElementById("marginModal")
                                    ).show();
                                })
                                .catch(error => {
                                    console.error(error);
                                    alert("Failed to load Margin record.");
                                });
                        });

                        // ADD BUTTON
                        document
                            .getElementById("addmarginBtn")
                            .addEventListener("click", function() {
                                document
                                    .getElementById("marginForm")
                                    .reset();
                                document
                                    .getElementById("margin_id")
                                    .value = "";
                                document
                                    .getElementById("marginModalLabel")
                                    .innerHTML = "Add Margin";
                                document
                                    .getElementById("saveBtn")
                                    .innerHTML = "Save";
                            });
                    </script>
                </div>
            </div>
        </div>
    </div>

    <div
        class="modal fade"
        id="marginModal"
        tabindex="-1"
        aria-labelledby="marginModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <!-- MODAL HEADER -->
                <div class="modal-header">
                    <h5
                        class="modal-title"
                        id="marginModalLabel">
                        Add Margin
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>

                <!-- MODAL BODY -->
                <div class="modal-body">
                    <form id="marginForm">
                        <!-- Hidden ID -->
                        <input type="hidden" id="margin_id" name="id">
                        <div class="mb-3">
                            <label
                                for="margin_type"
                                class="form-label">
                                Margin Type
                            </label>
                            <div class="input-group">
                                <input
                                    type="text"
                                    id="margin_type"
                                    name="margin_type"
                                    class="form-control"
                                    placeholder="Enter Margin Type"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label
                                for="percentage"
                                class="form-label">
                                Percentage
                            </label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="percentage"
                                    name="percentage"
                                    class="form-control"
                                    placeholder="Enter percentage"
                                    required>
                                <span class="input-group-text">
                                    %
                                </span>
                            </div>
                        </div>

                        <!-- SAVE BUTTON -->
                        <button
                            type="submit"
                            id="saveBtn"
                            class="btn btn-success">
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- FORM SUBMIT -->
    <script>
        document
            .getElementById("marginForm")
            .addEventListener("submit", function(e) {

                e.preventDefault();

                let form = this;
                let formData = new FormData(form);

                let id = document.getElementById("margin_id").value;
                let margin_type = document.getElementById("margin_type").value;
                let percentage = document.getElementById("percentage").value;

                fetch("qur_margin_master.php?action=save", {
                        method: "POST",
                        body: formData
                    })

                    .then(response => response.json())

                    .then(data => {

                        if (data.status === "success") {

                            // EDIT EXISTING RECORD

                            if (id !== "") {

                                let row = margintable.getRow(id);

                                if (row) {

                                    row.update({
                                        margin_type: margin_type,
                                        percentage: parseFloat(percentage).toFixed(2)
                                    });

                                }

                            }

                            // ADD NEW RECORD
                            else {

                                // Reload only the table data
                                // without refreshing the page
                                margintable.replaceData();
                            }


                            //
                            // CLOSE MODAL
                            //

                            const modalElement =
                                document.getElementById("marginModal");

                            document.activeElement?.blur();

                            const modal =
                                bootstrap.Modal.getInstance(modalElement);

                            if (modal) {
                                modal.hide();
                            }


                            //
                            // RESET FORM
                            //

                            form.reset();

                            document.getElementById("margin_id").value = "";

                            document.getElementById("marginModalLabel").innerHTML =
                                "Add Margin";

                            document.getElementById("saveBtn").innerHTML =
                                "Save";


                        } else {

                            alert(
                                data.message ||
                                "Failed to save Margin."
                            );

                        }

                    })

                    .catch(error => {

                        console.error(error);

                        alert("Failed to save Margin.");

                    });

            });

        // SEARCH / FILTER
        document
            .addEventListener("DOMContentLoaded", function() {
                document
                    .getElementById("table-filter")
                    .addEventListener("input", function(e) {
                        const value = e.target.value;
                        if (value) {
                            margintable.setFilter([
                                [{
                                        field: "id",
                                        type: "like",
                                        value: value
                                    },
                                    {
                                        field: "margin_type",
                                        type: "like",
                                        value: value
                                    },
                                    {
                                        field: "percentage",
                                        type: "like",
                                        value: value
                                    },
                                    {
                                        field: "created_by",
                                        type: "like",
                                        value: value
                                    },
                                    {
                                        field: "updated_by",
                                        type: "like",
                                        value: value
                                    },
                                ]

                            ]);
                        } else {
                            margintable.clearFilter();
                        }
                    });

                // PRINT
                document
                    .getElementById("print-table")
                    .addEventListener("click", function() {
                        margintable.print(false, true);
                    });

                // EXPORT CSV
                document
                    .getElementById("export-csv")
                    .addEventListener("click", function() {
                        margintable.download(
                            "csv",
                            "margin_master.csv"
                        );
                    });
            });
    </script>
    </main>
<?php } ?>
<?php
include "../include/footer.php";
include "../include/footer_base.php";
?>