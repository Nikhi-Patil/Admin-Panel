<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // =========================================================
    // DOWNLOAD TEMPLATE
    // =========================================================
    case "download_template":

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=bop_master_import_template.csv");

        $output = fopen("php://output", "w");

        fputcsv($output, [
            "part_no",
            "bop_part_no",
            "bop_part_name",
            "bop_erp_code",
            "supplier_name",
            "commodity",
            "umo"
        ]);

        fputcsv($output, [
            "PT-001",
            "BOP-001",
            "Bolt",
            "ERP-001",
            "ABC Supplier",
            "Plastic",
            "Nos"
        ]);

        fclose($output);
        exit;

        break;


    // =========================================================
    // BULK UPLOAD
    // =========================================================
    case "bulk_upload":

        header("Content-Type: application/json; charset=utf-8");

        $transactionStarted = false;

        try {

            // -------------------------------------------------
            // CHECK FILE
            // -------------------------------------------------
            if (
                !isset($_FILES["excel_file"]) ||
                $_FILES["excel_file"]["error"] !== UPLOAD_ERR_OK
            ) {
                throw new Exception("Please select a valid Excel/CSV file.");
            }

            $extension = strtolower(
                pathinfo($_FILES["excel_file"]["name"], PATHINFO_EXTENSION)
            );

            if (!in_array($extension, ["xlsx", "csv"])) {
                throw new Exception("Only .xlsx or .csv files are allowed.");
            }


            // -------------------------------------------------
            // READ FILE
            // -------------------------------------------------
            require_once __DIR__ . "/../include/SimpleXLSX.php";

            $sheet = [];

            if ($extension === "csv") {

                $handle = fopen($_FILES["excel_file"]["tmp_name"], "r");

                if (!$handle) {
                    throw new Exception("Unable to open CSV file.");
                }

                while (($row = fgetcsv($handle)) !== false) {
                    $sheet[] = $row;
                }

                fclose($handle);
            } else {

                $xlsx = \Shuchkin\SimpleXLSX::parse(
                    $_FILES["excel_file"]["tmp_name"]
                );

                if (!$xlsx) {
                    throw new Exception(
                        \Shuchkin\SimpleXLSX::parseError()
                    );
                }

                $sheet = $xlsx->rows();
            }


            // -------------------------------------------------
            // BASIC CHECK
            // -------------------------------------------------
            if (count($sheet) <= 1) {
                throw new Exception("Excel file is empty.");
            }


            // -------------------------------------------------
            // EXPECTED HEADERS
            // -------------------------------------------------
            $expectedHeaders = [
                "part_no",
                "bop_part_no",
                "bop_part_name",
                "bop_erp_code",
                "supplier_name",
                "commodity",
                "umo"
            ];


            // -------------------------------------------------
            // READ HEADERS
            // -------------------------------------------------
            $headers = array_map(
                function ($value) {
                    return strtolower(trim((string)$value));
                },
                $sheet[0]
            );


            // -------------------------------------------------
            // HEADER VALIDATION
            // -------------------------------------------------
            if ($headers !== $expectedHeaders) {

                throw new Exception(
                    "Invalid Excel headers. Please download the latest template and use the same column names and order."
                );
            }


            // -------------------------------------------------
            // USER
            // -------------------------------------------------
            $user_id = $_SESSION['user_name'] ?? '';


            // -------------------------------------------------
            // START TRANSACTION
            // -------------------------------------------------
            mysqli_begin_transaction($conn);
            $transactionStarted = true;


            $inserted = 0;
            $duplicate = 0;
            $failed = 0;

            $errors = [];


            // =================================================
            // PROCESS EACH ROW
            // =================================================
            foreach ($sheet as $index => $row) {

                // Skip header
                if ($index === 0) {
                    continue;
                }


                $excelRow = $index + 1;


                // Skip completely empty row
                if (count(array_filter($row, function ($value) {
                    return trim((string)$value) !== "";
                })) === 0) {
                    continue;
                }


                // -------------------------------------------------
                // GET VALUES
                // -------------------------------------------------
                $part_no = trim((string)($row[0] ?? ""));
                $bop_part_no = trim((string)($row[1] ?? ""));
                $bop_part_name = trim((string)($row[2] ?? ""));
                $bop_erp_code = trim((string)($row[3] ?? ""));
                $supplier_names = trim((string)($row[4] ?? ""));
                $commodity = trim((string)($row[5] ?? ""));
                $umo = trim((string)($row[6] ?? ""));


                // -------------------------------------------------
                // REQUIRED FIELD VALIDATION
                // -------------------------------------------------
                $missing = [];

                if ($part_no === "") {
                    $missing[] = "part_no";
                }

                if ($bop_part_no === "") {
                    $missing[] = "bop_part_no";
                }

                if ($bop_part_name === "") {
                    $missing[] = "bop_part_name";
                }

                if ($supplier_names === "") {
                    $missing[] = "supplier_name";
                }

                if ($commodity === "") {
                    $missing[] = "commodity";
                }

                if ($umo === "") {
                    $missing[] = "umo";
                }


                if (!empty($missing)) {

                    $failed++;

                    $errors[] =
                        "Excel row {$excelRow}: Missing " .
                        implode(", ", $missing);

                    continue;
                }


                // =================================================
                // FIND PART
                // =================================================

                $part_no_escaped = mysqli_real_escape_string(
                    $conn,
                    $part_no
                );

                $part_sql = mysqli_query(
                    $conn,
                    "SELECT id
                     FROM part_master
                     WHERE TRIM(part_no) = '$part_no_escaped'
                     LIMIT 1"
                );


                if (!$part_sql) {

                    throw new Exception(
                        "Part lookup failed: " . mysqli_error($conn)
                    );
                }


                if (mysqli_num_rows($part_sql) === 0) {

                    $failed++;

                    $errors[] =
                        "Excel row {$excelRow}: Part No '{$part_no}' was not found in part_master.";

                    continue;
                }


                $part = mysqli_fetch_assoc($part_sql);

                $part_id = intval($part["id"]);


                // =================================================
                // FIND SUPPLIERS
                // =================================================

                $supplierArray = preg_split(
                    '/\s*,\s*/',
                    $supplier_names
                );

                $supplier_ids = [];

                $missingSuppliers = [];


                foreach ($supplierArray as $supplier_name) {

                    $supplier_name = trim($supplier_name);

                    if ($supplier_name === "") {
                        continue;
                    }


                    $supplier_name_escaped = mysqli_real_escape_string(
                        $conn,
                        $supplier_name
                    );


                    $supplier_sql = mysqli_query(
                        $conn,
                        "SELECT id
                         FROM supplier_master
                         WHERE TRIM(supplier_name) = '$supplier_name_escaped'
                         LIMIT 1"
                    );


                    if (!$supplier_sql) {

                        throw new Exception(
                            "Supplier lookup failed: " .
                                mysqli_error($conn)
                        );
                    }


                    if (mysqli_num_rows($supplier_sql) > 0) {

                        $supplier = mysqli_fetch_assoc(
                            $supplier_sql
                        );

                        $supplier_ids[] = intval(
                            $supplier["id"]
                        );
                    } else {

                        $missingSuppliers[] = $supplier_name;
                    }
                }


                // -------------------------------------------------
                // SUPPLIER VALIDATION
                // -------------------------------------------------
                if (!empty($missingSuppliers)) {

                    $failed++;

                    $errors[] =
                        "Excel row {$excelRow}: Supplier(s) not found: " .
                        implode(", ", $missingSuppliers);

                    continue;
                }


                if (empty($supplier_ids)) {

                    $failed++;

                    $errors[] =
                        "Excel row {$excelRow}: No valid supplier found.";

                    continue;
                }


                // Remove duplicate supplier IDs
                $supplier_ids = array_unique($supplier_ids);

                $supplier_id = implode(",", $supplier_ids);


                // =================================================
                // DUPLICATE CHECK
                // =================================================

                $bop_part_no_escaped = mysqli_real_escape_string(
                    $conn,
                    $bop_part_no
                );


                $dup = mysqli_query(
                    $conn,
                    "SELECT id
                     FROM bop_master
                     WHERE TRIM(bop_part_no) = '$bop_part_no_escaped'
                     LIMIT 1"
                );


                if (!$dup) {

                    throw new Exception(
                        "Duplicate check failed: " .
                            mysqli_error($conn)
                    );
                }


                if (mysqli_num_rows($dup) > 0) {

                    $duplicate++;

                    continue;
                }


                // =================================================
                // INSERT
                // =================================================

                $bop_part_name_sql = mysqli_real_escape_string(
                    $conn,
                    $bop_part_name
                );

                $bop_part_no_sql = mysqli_real_escape_string(
                    $conn,
                    $bop_part_no
                );

                $bop_erp_code_sql = mysqli_real_escape_string(
                    $conn,
                    $bop_erp_code
                );

                $commodity_sql = mysqli_real_escape_string(
                    $conn,
                    $commodity
                );

                $umo_sql = mysqli_real_escape_string(
                    $conn,
                    $umo
                );

                $user_sql = mysqli_real_escape_string(
                    $conn,
                    $user_id
                );


                $sql = "
                    INSERT INTO bop_master
                    (
                        bop_part_name,
                        bop_part_no,
                        bop_erp_code,
                        commodity,
                        umo,
                        supplier_id,
                        part_id,
                        created_by
                    )
                    VALUES
                    (
                        '$bop_part_name_sql',
                        '$bop_part_no_sql',
                        '$bop_erp_code_sql',
                        '$commodity_sql',
                        '$umo_sql',
                        '$supplier_id',
                        '$part_id',
                        '$user_sql'
                    )
                ";


                if (!mysqli_query($conn, $sql)) {

                    throw new Exception(
                        "Insert failed on Excel row {$excelRow}: " .
                            mysqli_error($conn)
                    );
                }


                if (mysqli_affected_rows($conn) > 0) {
                    $inserted++;
                }
            }


            // =================================================
            // COMMIT
            // =================================================

            mysqli_commit($conn);

            $transactionStarted = false;


            // =================================================
            // RESULT MESSAGE
            // =================================================

            if ($inserted > 0) {

                $message =
                    "Import completed. " .
                    "Inserted: {$inserted}, " .
                    "Duplicate: {$duplicate}, " .
                    "Failed: {$failed}.";
            } elseif ($duplicate > 0 && $failed === 0) {

                $message =
                    "No new records inserted. " .
                    "All {$duplicate} record(s) already exist.";
            } else {

                $message =
                    "No records were inserted. " .
                    "Failed: {$failed}.";
            }


            echo json_encode([
                "status" => "success",
                "message" => $message,
                "inserted" => $inserted,
                "duplicate" => $duplicate,
                "failed" => $failed,
                "errors" => $errors
            ]);
        } catch (Throwable $e) {

            if ($transactionStarted) {
                mysqli_rollback($conn);
            }


            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage(),
                "inserted" => $inserted ?? 0,
                "duplicate" => $duplicate ?? 0,
                "failed" => $failed ?? 0
            ]);
        }

        break;


    // =========================================================
    // GET TABLE
    // =========================================================
    case "list":

        header("Content-Type: application/json; charset=utf-8");

        // =========================================================
        // SERVER-SIDE PAGINATION
        // Only the requested page is fetched from MySQL.
        // =========================================================

        $page = max(1, intval($_GET['page'] ?? 1));

        $size = intval($_GET['size'] ?? 10);

        // Protect the server from unnecessarily large requests.
        if ($size < 1) {
            $size = 10;
        }

        if ($size > 100) {
            $size = 100;
        }

        $offset = ($page - 1) * $size;

        $search = trim((string)($_GET['search'] ?? ''));
        $searchEscaped = mysqli_real_escape_string($conn, $search);

        $searchWhere = "";

        if ($search !== "") {

            $searchWhere = "
                AND (
                    p.bop_part_name LIKE '%$searchEscaped%'
                    OR p.bop_part_no LIKE '%$searchEscaped%'
                    OR p.bop_erp_code LIKE '%$searchEscaped%'
                    OR p.commodity LIKE '%$searchEscaped%'
                    OR p.umo LIKE '%$searchEscaped%'
                    OR p.created_by LIKE '%$searchEscaped%'
                    OR p.updated_by LIKE '%$searchEscaped%'
                    OR sd.part_no LIKE '%$searchEscaped%'
                    OR sd.fg_code LIKE '%$searchEscaped%'
                    OR d.supplier_name LIKE '%$searchEscaped%'
                )
            ";
        }

        // =========================================================
        // TOTAL RECORD COUNT
        // =========================================================

        $countSql = "
            SELECT COUNT(DISTINCT p.id) AS total
            FROM bop_master p
            LEFT JOIN supplier_master d
                ON FIND_IN_SET(d.id, p.supplier_id)
            LEFT JOIN part_master sd
                ON p.part_id = sd.id
            WHERE 1=1
            $searchWhere
        ";

        $countResult = mysqli_query($conn, $countSql);

        if (!$countResult) {
            echo json_encode([
                "last_page" => 1,
                "data" => [],
                "total" => 0,
                "error" => mysqli_error($conn)
            ]);
            break;
        }

        $countRow = mysqli_fetch_assoc($countResult);
        $totalRecords = intval($countRow['total'] ?? 0);

        // =========================================================
        // FETCH ONLY CURRENT PAGE
        // =========================================================

        $sql = "
            SELECT
                p.id,
                p.bop_part_name,
                p.bop_part_no,
                p.commodity,
                p.umo,
                p.supplier_id,
                p.part_id,

                GROUP_CONCAT(
                    DISTINCT d.supplier_name
                    ORDER BY d.supplier_name
                    SEPARATOR ', '
                ) AS supplier_name,

                sd.part_no,
                sd.fg_code,

                p.bop_erp_code,
                p.created_by,
                p.updated_by

            FROM bop_master p

            LEFT JOIN supplier_master d
                ON FIND_IN_SET(d.id, p.supplier_id)

            LEFT JOIN part_master sd
                ON p.part_id = sd.id

            WHERE 1=1

            $searchWhere

            GROUP BY p.id

            ORDER BY p.id DESC

            LIMIT $size OFFSET $offset
        ";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            echo json_encode([
                "last_page" => 1,
                "data" => [],
                "total" => $totalRecords,
                "error" => mysqli_error($conn)
            ]);
            break;
        }

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        $lastPage = $totalRecords > 0
            ? (int)ceil($totalRecords / $size)
            : 1;

        echo json_encode([
            "last_page" => $lastPage,
            "last_row" => $totalRecords,
            "data" => $data
        ]);

        break;


    // =========================================================
    // GET SINGLE RECORD
    // =========================================================
    case "get":

        $id = intval($_GET['id'] ?? 0);


        $sql = "
            SELECT
                p.id,
                p.bop_part_name,
                p.bop_part_no,
                p.commodity,
                p.umo,
                p.supplier_id,
                p.part_id,
                GROUP_CONCAT(
                    DISTINCT d.supplier_name
                    ORDER BY d.supplier_name
                    SEPARATOR ', '
                ) AS supplier_name,
                sd.part_no,
                sd.fg_code,
                p.bop_erp_code,
                p.created_by,
                p.updated_by
            FROM bop_master p
            LEFT JOIN supplier_master d
                ON FIND_IN_SET(d.id, p.supplier_id)
            LEFT JOIN part_master sd
                ON p.part_id = sd.id
            WHERE p.id = '$id'
            GROUP BY p.id
        ";


        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json; charset=utf-8");

        echo json_encode(
            mysqli_fetch_assoc($result)
        );

        break;


    // =========================================================
    // GET SUPPLIERS
    // =========================================================
    case "supplier":

        $sql = "
            SELECT
                id,
                supplier_name
            FROM supplier_master
            ORDER BY supplier_name
        ";


        $result = mysqli_query($conn, $sql);

        $data = [];


        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }


        header("Content-Type: application/json; charset=utf-8");

        echo json_encode($data);

        break;


    // =========================================================
    // GET PARTS
    // =========================================================
    case "part":

        $sql = "
            SELECT
                id,
                part_no AS part_name,
                fg_code
            FROM part_master
            ORDER BY part_no
        ";


        $result = mysqli_query($conn, $sql);

        $data = [];


        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }


        header("Content-Type: application/json; charset=utf-8");

        echo json_encode($data);

        break;


    // =========================================================
    // SAVE / UPDATE
    // =========================================================
    case "save":

        header("Content-Type: application/json; charset=utf-8");

        $id = $_POST['id'] ?? "";

        $bop_part_name = mysqli_real_escape_string(
            $conn,
            $_POST['bop_part_name'] ?? ''
        );

        $bop_part_no = mysqli_real_escape_string(
            $conn,
            $_POST['bop_part_no'] ?? ''
        );

        $bop_erp_code = mysqli_real_escape_string(
            $conn,
            $_POST['bop_erp_code'] ?? ''
        );

        // IMPORTANT:
        // Commodity is TEXT, not integer
        $commodity = mysqli_real_escape_string(
            $conn,
            $_POST['commodity'] ?? ''
        );

        $umo = mysqli_real_escape_string(
            $conn,
            $_POST['umo'] ?? ''
        );

        $part_id = intval(
            $_POST['part_id'] ?? 0
        );

        $user_id = $_SESSION['user_name'] ?? '';


        mysqli_begin_transaction($conn);

        try {

            $supplier_ids = $_POST['supplier_id'] ?? [];


            if (!is_array($supplier_ids)) {
                $supplier_ids = [$supplier_ids];
            }


            $supplier_ids = array_values(
                array_filter(
                    array_map('intval', $supplier_ids)
                )
            );


            if (empty($supplier_ids)) {
                throw new Exception(
                    "Please select at least one supplier."
                );
            }


            $supplier_id = mysqli_real_escape_string(
                $conn,
                implode(',', $supplier_ids)
            );


            if ($id == "") {

                $sql = "
                    INSERT INTO bop_master
                    (
                        bop_part_name,
                        bop_part_no,
                        bop_erp_code,
                        commodity,
                        umo,
                        supplier_id,
                        part_id,
                        created_by
                    )
                    VALUES
                    (
                        '$bop_part_name',
                        '$bop_part_no',
                        '$bop_erp_code',
                        '$commodity',
                        '$umo',
                        '$supplier_id',
                        '$part_id',
                        '" . mysqli_real_escape_string($conn, $user_id) . "'
                    )
                ";
            } else {

                $id = intval($id);

                $sql = "
                    UPDATE bop_master
                    SET
                        bop_part_name = '$bop_part_name',
                        bop_part_no = '$bop_part_no',
                        bop_erp_code = '$bop_erp_code',
                        commodity = '$commodity',
                        umo = '$umo',
                        supplier_id = '$supplier_id',
                        part_id = '$part_id',
                        updated_by = '" . mysqli_real_escape_string($conn, $user_id) . "',
                        updated_at = NOW()
                    WHERE id = '$id'
                ";
            }


            if (!mysqli_query($conn, $sql)) {
                throw new Exception(
                    mysqli_error($conn)
                );
            }


            mysqli_commit($conn);

            echo json_encode([
                "status" => "success",
                "message" => "Saved successfully."
            ]);
        } catch (Throwable $e) {

            mysqli_rollback($conn);

            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }

        break;


    // =========================================================
    // DELETE
    // =========================================================
    case "delete":

        $id = intval($_POST['id'] ?? 0);

        $user_id = $_SESSION['user_name'] ?? '';


        mysqli_begin_transaction($conn);

        try {

            $sql1 = "
                INSERT INTO hist_bop_master
                (
                    id,
                    bop_part_name,
                    bop_part_no,
                    bop_erp_code,
                    commodity,
                    umo,
                    supplier_id,
                    part_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    bop_part_name,
                    bop_part_no,
                    bop_erp_code,
                    commodity,
                    umo,
                    supplier_id,
                    part_id,
                    created_by,
                    created_at,
                    '" . mysqli_real_escape_string($conn, $user_id) . "',
                    NOW()
                FROM bop_master
                WHERE id = '$id'
            ";


            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(
                    mysqli_error($conn)
                );
            }


            $sql2 = "
                DELETE FROM bop_master
                WHERE id = '$id'
            ";


            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(
                    mysqli_error($conn)
                );
            }


            mysqli_commit($conn);


            echo json_encode([
                "status" => "success"
            ]);
        } catch (Throwable $e) {

            mysqli_rollback($conn);

            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }

        break;


    // =========================================================
    // RECYCLE TABLE
    // =========================================================
    case "list1":

        $sql = "
            SELECT
                p.id,
                p.bop_part_name,
                p.bop_part_no,
                sd.fg_code,
                p.commodity,
                p.umo,
                p.supplier_id,
                p.part_id,
                GROUP_CONCAT(
                    DISTINCT d.supplier_name
                    ORDER BY d.supplier_name
                    SEPARATOR ', '
                ) AS supplier_name,
                sd.part_no,
                p.bop_erp_code,
                p.created_by,
                p.updated_by
            FROM hist_bop_master p
            LEFT JOIN supplier_master d
                ON FIND_IN_SET(d.id, p.supplier_id)
            LEFT JOIN part_master sd
                ON p.part_id = sd.id
            GROUP BY p.id
            ORDER BY p.id DESC
        ";


        $result = mysqli_query($conn, $sql);

        $data = [];


        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }


        header("Content-Type: application/json; charset=utf-8");

        echo json_encode($data);

        break;


    // =========================================================
    // RESTORE
    // =========================================================
    case "restore":

        $id = intval($_POST['id'] ?? 0);

        $user_id = $_SESSION['user_name'] ?? '';


        mysqli_begin_transaction($conn);

        try {

            $sql1 = "
                INSERT INTO bop_master
                (
                    id,
                    bop_part_name,
                    bop_part_no,
                    bop_erp_code,
                    commodity,
                    umo,
                    supplier_id,
                    part_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    bop_part_name,
                    bop_part_no,
                    bop_erp_code,
                    commodity,
                    umo,
                    supplier_id,
                    part_id,
                    created_by,
                    created_at,
                    '" . mysqli_real_escape_string($conn, $user_id) . "',
                    NOW()
                FROM hist_bop_master
                WHERE id = '$id'
            ";


            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(
                    mysqli_error($conn)
                );
            }


            $sql2 = "
                DELETE FROM hist_bop_master
                WHERE id = '$id'
            ";


            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(
                    mysqli_error($conn)
                );
            }


            mysqli_commit($conn);


            echo json_encode([
                "status" => "success"
            ]);
        } catch (Throwable $e) {

            mysqli_rollback($conn);

            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }

        break;


    // =========================================================
    // DEFAULT
    // =========================================================
    default:

        header("Content-Type: application/json; charset=utf-8");

        echo json_encode([
            "status" => "error",
            "message" => "Invalid Action"
        ]);

        break;
}
