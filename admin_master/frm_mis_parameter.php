<?php
session_start();
include "../inc/db_cfg.php";
include "../include/top_header.php";
include "../include/header.php";
include "../include/left_nav_bar.php";

$page = $_GET['page'] ?? 'mis_para';
?>

<?php if ($page == "mis_para") { ?>

<style id="MIS PARAMETER TABLE CSS">
#mis_para_table .tabulator-header,
#mis_para_recycle_table .tabulator-header {
    font-weight: 600;
}

#mis_para_table .tabulator-cell,
#mis_para_table .tabulator-col,
#mis_para_recycle_table .tabulator-cell,
#mis_para_recycle_table .tabulator-col {
    border-right: 1px solid #dee2e6;
}

#mis_para_table .tabulator-row,
#mis_para_recycle_table .tabulator-row {
    border-bottom: 1px solid #dee2e6;
}

#mis_para_table .tabulator-row .tabulator-cell,
#mis_para_recycle_table .tabulator-row .tabulator-cell {
    padding: 8px 10px;
}

#mis_para_table .action-btn,
#mis_para_recycle_table .action-btn {
    margin: 0 2px;
}
</style>

<div class="app-content-header" style="padding: 6px 0.5rem;">
    <div class="container-fluid">
        <div class="row">

            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">MIS Parameters List</h1>
            </div>

            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">

                        <button
                            class="btn btn-success"
                            data-bs-toggle="modal"
                            data-bs-target="#mis_paraModal">

                            <i class="fas fa-plus"></i>
                            Add MIS Parameters

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

                <div id="mis_para_table"></div>


                <script>

const table = new Tabulator(
    "#mis_para_table",
    {

        ajaxURL: "qur_mis_para_master.php?action=list",

        ajaxConfig: "GET",

        layout: "fitColumns",

        pagination: true,

        paginationSize: 10,

        paginationSizeSelector: [10, 25, 50, 100],

        initialSort: [
            {
                column: "id",
                dir: "desc"
            }
        ],

        columns: [

            /* =====================================================
               ID
            ===================================================== */

            {
                title: "ID",

                field: "id",

                width: 80,

                sorter: "number",

                hozAlign: "center",

                headerHozAlign: "center",

                resizable: false
            },


            /* =====================================================
               MIS PARAMETER
            ===================================================== */

            {
                title: "MIS Parameter",

                field: "mis_para",

                minWidth: 250,

                hozAlign: "left",

                headerHozAlign: "center",

                resizable: false
            },


            /* =====================================================
               STATUS
            ===================================================== */

            {
                title: "Status",

                field: "status",

                width: 120,

                hozAlign: "center",

                headerHozAlign: "center",

                resizable: false,

                formatter: function(cell) {

                    let status =
                        String(
                            cell.getValue() || ""
                        ).toLowerCase();


                    if (status === "active") {

                        return `
                            <span
                                class="badge bg-success"
                                style="
                                    font-size: 12px;
                                    padding: 6px 12px;
                                "
                            >
                                Active
                            </span>
                        `;

                    }


                    return `
                        <span
                            class="badge bg-danger"
                            style="
                                font-size: 12px;
                                padding: 6px 12px;
                            "
                        >
                            Inactive
                        </span>
                    `;

                }

            },


            /* =====================================================
               CREATED BY
            ===================================================== */

            {
                title: "Created By",

                field: "created_by",

                width: 150,

                hozAlign: "center",

                headerHozAlign: "center",

                resizable: false
            },


            /* =====================================================
               CREATED AT
            ===================================================== */

            {
                title: "Created At",

                field: "created_at",

                width: 170,

                hozAlign: "center",

                headerHozAlign: "center",

                resizable: false
            },


            /* =====================================================
               UPDATED BY
            ===================================================== */

            {
                title: "Updated By",

                field: "updated_by",

                width: 150,

                hozAlign: "center",

                headerHozAlign: "center",

                resizable: false
            },


            /* =====================================================
               UPDATED AT
            ===================================================== */

            {
                title: "Updated At",

                field: "updated_at",

                width: 170,

                hozAlign: "center",

                headerHozAlign: "center",

                resizable: false
            },


            /* =====================================================
               ACTION
            ===================================================== */

            {
                title: "Action",

                width: 130,

                hozAlign: "center",

                headerHozAlign: "center",

                resizable: false,

                formatter: function(cell) {

                    let row =
                        cell.getRow().getData();


                    return `

                        <button
                            type="button"
                            class="btn btn-sm btn-primary action-btn edit-btn"
                            data-id="${row.id}"
                            title="Edit"
                        >

                            <i class="fa-solid fa-pen"></i>

                        </button>


                        <button
                            type="button"
                            class="btn btn-sm btn-danger action-btn delete-btn"
                            data-id="${row.id}"
                            title="Delete"
                        >

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
                                    "Delete this MIS parameter record?"
                                )
                            ) {

                                fetch(
                                    "qur_mis_para_master.php?action=delete",
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

                        if (!editBtn) {
                            return;
                        }

                        let id =
                            editBtn.dataset.id;

                        fetch(
                            "qur_mis_para_master.php?action=get&id=" +
                            encodeURIComponent(id)
                        )
                        .then(response => response.json())
                        .then(data => {

                            if (!data || data.status === "error") {

                                alert(
                                    data.message ||
                                    "Unable to load MIS Parameter."
                                );

                                return;
                            }

                            document.getElementById(
                                "mis_parameter_id"
                            ).value = data.id || "";

                            document.getElementById(
                                "mis_para"
                            ).value = data.mis_para || "";

                            document.getElementById(
                                "status"
                            ).value = data.status || "";

                            document.getElementById(
                                "mis_paraModalLabel"
                            ).innerHTML =
                                "Edit MIS Parameter";

                            document.getElementById(
                                "saveBtn"
                            ).innerHTML =
                                '<i class="fa-solid fa-pen"></i> Update';

                            new bootstrap.Modal(
                                document.getElementById(
                                    "mis_paraModal"
                                )
                            ).show();

                        })
                        .catch(error => {

                            console.error(error);

                            alert(
                                "Unable to load MIS Parameter."
                            );

                        });

                    }
                );
                // =================== ADD ===================

                document.querySelector(
                    '[data-bs-target="#mis_paraModal"]'
                ).addEventListener(
                    "click",
                    function() {

                        document.getElementById(
                            "mis_paraForm"
                        ).reset();

                        document.getElementById(
                            "mis_parameter_id"
                        ).value = "";

                        document.getElementById(
                            "mis_paraModalLabel"
                        ).innerHTML =
                            "Add MIS Parameter";

                        document.getElementById(
                            "saveBtn"
                        ).innerHTML =
                            '<i class="fa-solid fa-check"></i> Save';

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
    id="mis_paraModal"
    tabindex="-1"
    aria-labelledby="mis_paraModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5
                    class="modal-title"
                    id="mis_paraModalLabel">

                    Add MIS Parameter

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>


            <div class="modal-body">

                <form id="mis_paraForm">

                    <input
                        type="hidden"
                        id="mis_parameter_id"
                        name="id"
                    >

                    <div class="row">

                        <!-- MIS PARAMETER NAME -->

                        <div class="col-md-6 mb-3">

                            <label
                                for="parameter_name"
                                class="form-label"
                            >
                                MIS Parameter Name
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                id="mis_para"
                                name="mis_para"
                                class="form-control"
                                placeholder="Enter MIS Parameter"
                                required
                            >

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-6 mb-3">

                            <label
                                for="status"
                                class="form-label"
                            >
                                Status
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                id="status"
                                name="status"
                                class="form-select"
                                required
                            >
                                <option value="">Select Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>

                        </div>

                    </div>


                    <div class="text-end">

                        <button
                            type="button"
                            class="btn btn-secondary me-2"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            id="saveBtn"
                            class="btn btn-success"
                        >
                            <i class="fa-solid fa-check"></i>
                            Save
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<script>

// =================== SAVE / UPDATE ===================

document.getElementById(
    "mis_paraForm"
).addEventListener(
    "submit",
    function(e) {

        e.preventDefault();

        let formData =
            new FormData(this);

        fetch(
            "qur_mis_para_master.php?action=save",
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
                            "mis_paraModal"
                        )
                    )
                    .hide();

                document.getElementById(
                    "mis_paraForm"
                ).reset();

                document.getElementById(
                    "mis_parameter_id"
                ).value = "";

                document.getElementById(
                    "mis_paraModalLabel"
                ).innerHTML =
                    "Add MIS Parameter";

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
                                    e.target.value.trim();

                                if (value) {

                                    table.setFilter([
                                        [
                                            {
                                                field: 'id',
                                                type: 'like',
                                                value: value
                                            },
                                            {
                                                field: 'mis_para',
                                                type: 'like',
                                                value: value
                                            },
                                            {
                                                field: 'status',
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
                    'mis_para_master.csv'
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
                    MIS Parameters Recycle Bin
                </h1>

            </div>

        </div>

    </div>

</div>


<div class="app-content">

    <div class="container-fluid">

        <div class="card">

            <div class="card-body">

                <div id="mis_para_recycle_table"></div>


                <script>
                const recycleTable =
                    new Tabulator(
                        "#mis_para_recycle_table",
                        {

                            ajaxURL:
                                "qur_mis_para_master.php?action=list1",

                            ajaxConfig: "GET",

                            layout: "fitColumns",

                            pagination: true,

                            paginationSize: 10,

                            paginationSizeSelector:
                                [10, 25, 50, 100],

                            initialSort: [
                                {
                                    column: "id",
                                    dir: "desc"
                                }
                            ],

                            columns: [

                                {
                                    title: "ID",
                                    field: "id",
                                    width: 80,
                                    sorter: "number",
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },

                                {
                                    title: "MIS Parameter",
                                    field: "mis_para",
                                    minWidth: 250,
                                    hozAlign: "left",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },

                                {
                                    title: "Status",
                                    field: "status",
                                    width: 120,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center",

                                    formatter: function(cell) {

                                        let status =
                                            String(
                                                cell.getValue() || ""
                                            ).toLowerCase();

                                        if (status === "active") {

                                            return `
                                                <span
                                                    class="badge bg-success"
                                                    style="
                                                        font-size: 12px;
                                                        padding: 6px 12px;
                                                    "
                                                >
                                                    Active
                                                </span>
                                            `;

                                        }

                                        return `
                                            <span
                                                class="badge bg-danger"
                                                style="
                                                    font-size: 12px;
                                                    padding: 6px 12px;
                                                "
                                            >
                                                Inactive
                                            </span>
                                        `;

                                    }

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
                                    title: "Created At",
                                    field: "created_at",
                                    width: 170,
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
                                    title: "Deleted At",
                                    field: "updated_at",
                                    width: 170,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center"
                                },

                                {
                                    title: "Action",
                                    width: 110,
                                    hozAlign: "center",
                                    resizable: false,
                                    headerHozAlign: "center",

                                    formatter: function(cell) {

                                        let row =
                                            cell.getRow().getData();

                                        return `

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary action-btn recycle-btn"
                                                data-id="${row.id}"
                                                title="Restore"
                                            >

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
                                "Restore this MIS parameter record?"
                            )
                        ) {

                            fetch(
                                "qur_mis_para_master.php?action=restore",
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
                                        "MIS Parameter restored successfully."
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
