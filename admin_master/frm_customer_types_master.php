<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";

$page = $_GET['page'] ?? 'customer_types';
?>

<?php if ($page == "customer_types") { ?>

<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">

            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Customer Types List</h1>
            </div>

            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">

                        <button
                            class="btn btn-success"
                            data-bs-toggle="modal"
                            data-bs-target="#customer_typesModal">

                            <i class="fas fa-plus"></i>
                            Add Customer Types

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
                            placeholder="Filter rows&hellip;"
                            aria-label="Filter rows" />

                    </div>

                </div>

            </div>


            <div class="card-body">

                <div id="customer_types_table"></div>


                <script>

                const table = new Tabulator(
                    "#customer_types_table",
                    {

                        ajaxURL:
                            "qur_customer_types_master.php?action=list",

                        ajaxConfig: "GET",

                        layout: "fitColumns",

                        pagination: true,

                        paginationSize: 10,

                        paginationSizeSelector:
                            [10, 25, 50, 100],

                        initialSort: [{
                            column: "id",
                            dir: "asc"
                        }],

                        columns: [

                            {
                                title: "ID",
                                field: "id",
                                width: 90,
                                sorter: "number",
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center"
                            },

                            {
                                title: "Customer Types",
                                field: "customer_types",
                                resizable: false,
                                headerHozAlign: "center"
                            },

                            {
                                title: "Segment",
                                field: "segment",
                                resizable: false,
                                headerHozAlign: "center"
                            },

                            {
                                title: "Sub Segment",
                                field: "sub_segment",
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
                                width: 140,
                                hozAlign: "center",
                                resizable: false,
                                headerHozAlign: "center",

                                formatter: function(cell) {

                                    let row =
                                        cell.getRow().getData();

                                    return `

                                        <button
                                            class="btn btn-primary action-btn edit-btn"
                                            data-id="${row.id}">

                                            <i class="fa-solid fa-pen"></i>

                                        </button>

                                        <button
                                            class="btn btn-danger action-btn delete-btn"
                                            data-id="${row.id}">

                                            <i class="fa-solid fa-trash"></i>

                                        </button>

                                    `;

                                }
                            }

                        ]

                    }
                );

                </script>


                <script>

                // =================== DELETE ===================

                document.addEventListener(
                    "click",
                    function(e) {

                        let deleteBtn =
                            e.target.closest(".delete-btn");

                        if (deleteBtn) {

                            let id =
                                deleteBtn.dataset.id;

                            if (
                                confirm(
                                    "Delete this customer type record?"
                                )
                            ) {

                                fetch(
                                    "qur_customer_types_master.php?action=delete",
                                    {
                                        method: "POST",

                                        headers: {
                                            "Content-Type":
                                                "application/x-www-form-urlencoded"
                                        },

                                        body:
                                            "id=" +
                                            encodeURIComponent(id)
                                    }
                                )
                                .then(res => res.json())
                                .then(res => {

                                    if (
                                        res.status === "success"
                                    ) {

                                        table.replaceData();

                                        alert(
                                            "Deleted Successfully"
                                        );

                                    } else {

                                        alert(
                                            res.message ||
                                            "Delete failed."
                                        );

                                    }

                                });

                            }

                        }

                    }
                );


                // =================== EDIT ===================

                document.addEventListener(
                    "click",
                    function(e) {

                        let editBtn =
                            e.target.closest(".edit-btn");

                        if (editBtn) {

                            let id =
                                editBtn.dataset.id;

                            fetch(
                                "qur_customer_types_master.php?action=get&id=" +
                                encodeURIComponent(id)
                            )
                            .then(response => response.json())
                            .then(data => {

                                document.getElementById(
                                    "customer_types_id"
                                ).value = data.id;

                                document.getElementById(
                                    "customer_types"
                                ).value =
                                    data.customer_types;

                                document.getElementById(
                                    "segment"
                                ).value =
                                    data.segment;

                                document.getElementById(
                                    "sub_segment"
                                ).value =
                                    data.sub_segment;

                                document.getElementById(
                                    "customer_typesModalLabel"
                                ).innerHTML =
                                    "Edit Customer Types";

                                document.getElementById(
                                    "saveBtn"
                                ).innerHTML =
                                    "Update";

                                new bootstrap.Modal(
                                    document.getElementById(
                                        "customer_typesModal"
                                    )
                                ).show();

                            });

                        }

                    }
                );


                // =================== ADD ===================

                document.querySelector(
                    '[data-bs-target="#customer_typesModal"]'
                ).addEventListener(
                    "click",
                    function() {

                        document.getElementById(
                            "customer_typesForm"
                        ).reset();

                        document.getElementById(
                            "customer_types_id"
                        ).value = "";

                        document.getElementById(
                            "customer_typesModalLabel"
                        ).innerHTML =
                            "Add Customer Types";

                        document.getElementById(
                            "saveBtn"
                        ).innerHTML =
                            "Save";

                    }
                );

                </script>

            </div>

        </div>

    </div>

</div>


<!-- =================== ADD / EDIT MODAL =================== -->

<div
    class="modal fade"
    id="customer_typesModal"
    tabindex="-1"
    aria-labelledby="customer_typesModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="customer_typesModalLabel">

                    Add Customer Types

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">

                <form id="customer_typesForm">

                    <input
                        type="hidden"
                        id="customer_types_id"
                        name="id">


                    <div class="row">

                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Customer Types
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                id="customer_types"
                                name="customer_types"
                                class="form-control"
                                required>

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Segment
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                id="segment"
                                name="segment"
                                class="form-control"
                                required>

                        </div>


                        <div class="col-md-4 mb-3">

                            <label class="form-label">
                                Sub Segment
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                id="sub_segment"
                                name="sub_segment"
                                class="form-control"
                                required>

                        </div>

                    </div>


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


<script>

// =================== SAVE / UPDATE ===================

document.getElementById(
    "customer_typesForm"
).addEventListener(
    "submit",
    function(e) {

        e.preventDefault();

        let formData =
            new FormData(this);

        fetch(
            "qur_customer_types_master.php?action=save",
            {
                method: "POST",
                body: formData
            }
        )
        .then(response => response.text())
        .then(data => {

            if (data.trim() === "success") {

                bootstrap.Modal
                    .getInstance(
                        document.getElementById(
                            "customer_typesModal"
                        )
                    )
                    .hide();

                document.getElementById(
                    "customer_typesForm"
                ).reset();

                document.getElementById(
                    "customer_types_id"
                ).value = "";

                document.getElementById(
                    "customer_typesModalLabel"
                ).innerHTML =
                    "Add Customer Types";

                document.getElementById(
                    "saveBtn"
                ).innerHTML =
                    "Save";

                table.replaceData();

            } else {

                alert(data);

            }

        });

    }
);


// =================== SEARCH ===================

document.addEventListener(
    'DOMContentLoaded',
    () => {

        document.getElementById(
            'table-filter'
        ).addEventListener(
            'input',
            (e) => {

                const value =
                    e.target.value;

                if (value) {

                    table.setFilter([
                        [

                            {
                                field: 'id',
                                type: 'like',
                                value: value
                            },

                            {
                                field: 'customer_types',
                                type: 'like',
                                value: value
                            },

                            {
                                field: 'segment',
                                type: 'like',
                                value: value
                            },

                            {
                                field: 'sub_segment',
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

                        ]
                    ]);

                } else {

                    table.clearFilter();

                }

            }
        );


        // =================== PRINT ===================

        document.getElementById(
            'print-table'
        ).addEventListener(
            'click',
            () =>
                table.print(false, true)
        );


        // =================== EXPORT ===================

        document.getElementById(
            'export-csv'
        ).addEventListener(
            'click',
            () =>
                table.download(
                    'csv',
                    'customer_types_master.csv'
                )
        );

    }
);

</script>

<?php } ?>


<?php if ($page == "recycle") { ?>

<div class="app-content-header">

    <div class="container-fluid">

        <div class="row">

            <div class="col-sm-6">

                <h1 class="mb-0 fs-3">
                    Customer Types Recycle Bin
                </h1>

            </div>

        </div>

    </div>

</div>


<div class="app-content">

    <div class="container-fluid">

        <div class="card">

            <div class="card-body">

                <div id="customer_types_recycle_table"></div>


                <script>

                const recycleTable =
                    new Tabulator(
                        "#customer_types_recycle_table",
                        {

                            ajaxURL:
                                "qur_customer_types_master.php?action=list1",

                            ajaxConfig: "GET",

                            layout: "fitColumns",

                            pagination: true,

                            paginationSize: 10,

                            paginationSizeSelector:
                                [10, 25, 50, 100],

                            initialSort: [{
                                column: "id",
                                dir: "asc"
                            }],

                            columns: [

                                {
                                    title: "ID",
                                    field: "id",
                                    width: 90,
                                    sorter: "number",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },

                                {
                                    title: "Customer Types",
                                    field: "customer_types",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },

                                {
                                    title: "Segment",
                                    field: "segment",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },

                                {
                                    title: "Sub Segment",
                                    field: "sub_segment",
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
                                    width: 120,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center",

                                    formatter: function(cell) {

                                        let row =
                                            cell.getRow().getData();

                                        return `

                                            <button
                                                class="btn btn-primary action-btn recycle-btn"
                                                data-id="${row.id}">

                                                <i class="fa-solid fa-trash-arrow-up"></i>

                                            </button>

                                        `;

                                    }
                                }

                            ]

                        }
                    );


                document.addEventListener(
                    "click",
                    function(e) {

                        let btn =
                            e.target.closest(
                                ".recycle-btn"
                            );

                        if (!btn) return;

                        let id =
                            btn.dataset.id;

                        if (
                            confirm(
                                "Restore this customer type record?"
                            )
                        ) {

                            fetch(
                                "qur_customer_types_master.php?action=restore",
                                {
                                    method: "POST",

                                    headers: {
                                        "Content-Type":
                                            "application/x-www-form-urlencoded"
                                    },

                                    body:
                                        "id=" +
                                        encodeURIComponent(id)
                                }
                            )
                            .then(res => res.json())
                            .then(res => {

                                if (
                                    res.status === "success"
                                ) {

                                    recycleTable.replaceData();

                                    alert(
                                        "Customer Types restored successfully."
                                    );

                                } else {

                                    alert(
                                        res.message ||
                                        "Restore failed."
                                    );

                                }

                            });

                        }

                    }
                );

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
