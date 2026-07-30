<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // =================== DOWNLOAD TEMPLATE ===================
    case "download_template":
        header("Content-Type: text/csv; charset=utf-8");
        header(
            "Content-Disposition: attachment; filename=part_master_import_template.csv"
        );

        $output = fopen("php://output", "w");

        fputcsv($output, [
            "part_name",
            "part_no",
            "fg_code",
            "im_code",
            "inter_code",
            "unit",
            "department",
            "sub_department"
        ]);

        // Sample row — users can replace it with their own data.
        fputcsv($output, [
            "Sample Part",
            "PT-001",
            "FG-001",
            "IM-001",
            "INTER-001",
            "unit 1",
            "Production",
            "Assembly"
        ]);

        fclose($output);
        exit;
    break;
    // =================== BULK UPLOAD ===================
    case "bulk_upload":
        header("Content-Type: application/json");
        $transactionStarted = false;

        try {
            if (empty($_FILES["excel_file"]) ||
                $_FILES["excel_file"]["error"] !== UPLOAD_ERR_OK) {
                throw new Exception("Please select a valid Excel file.");
            }

            $extension = strtolower(
                pathinfo($_FILES["excel_file"]["name"], PATHINFO_EXTENSION)
            );

            if (!in_array($extension, ["xlsx", "csv"], true)) {
                throw new Exception("Only .xlsx or .csv files are allowed.");
            }

            require_once __DIR__ . "/../include/SimpleXLSX.php";

            if ($extension === "csv") {
                $rows = [];
                $handle = fopen($_FILES["excel_file"]["tmp_name"], "rb");

                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = $row;
                }

                fclose($handle);
            } else {
                $xlsx = \Shuchkin\SimpleXLSX::parse(
                    $_FILES["excel_file"]["tmp_name"]
                );

                 if (!$xlsx) {
                    throw new Exception(\Shuchkin\SimpleXLSX::parseError());
                }

                $rows = $xlsx->rows();
            }

            $requiredHeaders = [
                "part_name",
                "part_no",
                "fg_code",
                "im_code",
                "inter_code",
                "unit",
                "department",
                "sub_department"
            ];

            $headerRowIndex = null;
            $headerIndexes = [];

            foreach ($rows as $rowIndex => $row) {
                $headers = array_map(
                    fn($value) => strtolower(trim((string)$value)),
                    $row
                );

                $indexes = [];
                foreach ($requiredHeaders as $header) {
                    $index = array_search($header, $headers, true);

                    if ($index === false) {
                        continue 2;
                    }

                    $indexes[$header] = $index;
                }

                $headerRowIndex = $rowIndex;
                $headerIndexes = $indexes;
                break;
            }

            if ($headerRowIndex === null) {
                throw new Exception(
                    "Required columns: part_name, part_no, fg_code, im_code, inter_code, unit, department, sub_department"
                );
            }

            $userId = $_SESSION["user_id"] ?? "";

            if ((string)$userId === "") {
                throw new Exception("Session user not found. Please log in again.");
            }

            mysqli_begin_transaction($conn);
            $transactionStarted = true;

            $checkPart = mysqli_prepare(
                $conn,
                "SELECT id FROM part_master WHERE part_no = ? LIMIT 1"
            );

            $findDepartment = mysqli_prepare(
                $conn,
                "SELECT s.id AS sub_department_id
                FROM sub_department_master s
                INNER JOIN department_master d
                    ON s.department_id = d.id
                INNER JOIN unit_master u
                    ON s.unit_id = u.id
                WHERE LOWER(u.unit)=LOWER(?)
                AND LOWER(d.department_name)=LOWER(?)
                AND LOWER(s.sub_department_name)=LOWER(?)
                LIMIT 1"
            );

            $insertPart = mysqli_prepare(
                $conn,
                "INSERT INTO part_master
                    (
                        part_name, part_no, fg_code, im_code,
                        inter_code,  sub_department_id,
                        created_by
                    )
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$checkPart || !$findDepartment || !$insertPart) {
                throw new Exception(mysqli_error($conn));
            }

            $imported = 0;
            $seenPartNos = [];

            foreach (array_slice($rows, $headerRowIndex + 1) as $rowNumber => $row) {
                $excelRow = $rowNumber + $headerRowIndex + 2;

                $partName = trim((string)($row[$headerIndexes["part_name"]] ?? ""));
                $partNo = trim((string)($row[$headerIndexes["part_no"]] ?? ""));
                $fgCode = trim((string)($row[$headerIndexes["fg_code"]] ?? ""));
                $imCode = trim((string)($row[$headerIndexes["im_code"]] ?? ""));
                $inter_code = trim((string)($row[$headerIndexes["inter_code"]] ?? ""));
                $unit = trim((string)($row[$headerIndexes["unit"]] ?? ""));
                $department = trim((string)($row[$headerIndexes["department"]] ?? ""));
                $subDepartment = trim((string)($row[$headerIndexes["sub_department"]] ?? ""));

                if (
                    $partName === "" && $partNo === "" && $fgCode === "" &&
                    $imCode === "" && $inter_code === "" &&
                    $department === "" && $subDepartment === ""
                ) {
                        continue;
                }

                if (
                    $partName === "" || $partNo === "" || $fgCode === "" ||
                    $imCode === "" || $inter_code === "" || 
                    $department === "" || $subDepartment === ""
                ) {
                    throw new Exception("Row {$excelRow} has an empty required value.");
                }

                $partNoKey = strtolower($partNo);
                if (isset($seenPartNos[$partNoKey])) {
                    throw new Exception("Row {$excelRow}: Part No '{$partNo}' is duplicated in this file.");
                }
                $seenPartNos[$partNoKey] = true;

                mysqli_stmt_bind_param($checkPart, "s", $partNo);
                mysqli_stmt_execute($checkPart);
                mysqli_stmt_store_result($checkPart);

                if (mysqli_stmt_num_rows($checkPart) > 0) {
                    mysqli_stmt_free_result($checkPart);
                    throw new Exception("Row {$excelRow}: Part No '{$partNo}' already exists.");
                }

                mysqli_stmt_free_result($checkPart);

                mysqli_stmt_bind_param(
                    $findDepartment,
                    "sss",
                    $unit,
                    $department,
                    $subDepartment
                );

                mysqli_stmt_execute($findDepartment);
                $departmentResult = mysqli_stmt_get_result($findDepartment);
                $departmentRow = mysqli_fetch_assoc($departmentResult);

                if (!$departmentRow) {
                    throw new Exception(
                        "Row {$excelRow}: Department/Sub Department was not found."
                    );
                }

                $subDepartmentId = (int)$departmentRow["sub_department_id"];

                mysqli_stmt_bind_param(
                    $insertPart,
                    "sssssis",
                    $partName,
                    $partNo,
                    $fgCode,
                    $imCode,
                    $inter_code,
                    $subDepartmentId,
                    $userId
                );

                if (!mysqli_stmt_execute($insertPart)) {
                    throw new Exception(mysqli_stmt_error($insertPart));
                }

                $imported++;
            }

            if ($imported === 0) {
                throw new Exception("No part records found after the header row.");
            }

            mysqli_commit($conn);

            echo json_encode([
                "status" => "success",
                "message" => "{$imported} part(s) imported successfully."
            ]);
        } catch (Throwable $e) {
            if ($transactionStarted) {
                mysqli_rollback($conn);
            }

            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    break;
    // =================== GET TABLE ===================
    case "list":
        $sql = "SELECT
                    p.id,
                    p.part_name,
                    p.part_no,
                    p.fg_code,
                    p.im_code,
                    p.inter_code,
                    p.sub_department_id,
                    d.department_name,
                    sd.sub_department_name,
                    u.unit,
                    p.created_by,
                    p.updated_by
                FROM part_master p
                LEFT JOIN sub_department_master sd
                    ON p.sub_department_id = sd.id
                LEFT JOIN department_master d
                    ON sd.department_id = d.id
                LEFT JOIN unit_master u
                    ON sd.unit_id = u.id
                ORDER BY p.id DESC";

        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
    break;
    // =================== GET SINGLE RECORD ===================
    case "get":
        $id = intval($_GET['id'] ?? 0);

        $sql = "SELECT
                    p.id,
                    p.part_name,
                    p.part_no,
                    p.fg_code,
                    p.im_code,
                    p.inter_code,
                    p.sub_department_id,
                    d.department_name,
                    sd.sub_department_name,
                    u.unit,
                    p.created_by,
                    p.updated_by
                FROM part_master p
                LEFT JOIN sub_department_master sd
                    ON p.sub_department_id = sd.id
                LEFT JOIN department_master d
                    ON sd.department_id = d.id
                LEFT JOIN unit_master u
                    ON sd.unit_id = u.id
                WHERE p.id='$id'    ";

        $result = mysqli_query($conn, $sql);
        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
    break;
    // =================== GET DEPARTMENT TABLE ===================
    case "departments":
        $sql = "SELECT id, department_name
                FROM department_master
                ORDER BY department_name";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        header("Content-Type: application/json");
        echo json_encode($data);
    break;
    // =================== GET SUBDEPARTMENT TABLE ===================
    case "sub_departments":
        $sql = "SELECT
                    s.id,
                    s.department_id,
                    s.unit_id,
                    s.sub_department_name,
                    d.department_name,
                    u.unit
                FROM sub_department_master s
                LEFT JOIN department_master d
                    ON s.department_id = d.id
                LEFT JOIN unit_master u
                    ON s.unit_id = u.id
                ORDER BY s.sub_department_name";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        header("Content-Type: application/json");
        echo json_encode($data);
    break;
    // =================== SAVE TABLE ===================
    case "save":
        header("Content-Type: application/json");

        $id = intval($_POST['id'] ?? 0);
        $part_name = mysqli_real_escape_string($conn, $_POST['part_name'] ?? '');
        $part_no_value = trim($_POST['part_no'] ?? '');
        $part_no = mysqli_real_escape_string($conn, $part_no_value);
        $fg_code = mysqli_real_escape_string($conn, $_POST['fg_code'] ?? '');
        $im_code = mysqli_real_escape_string($conn, $_POST['im_code'] ?? '');
        $inter_code = mysqli_real_escape_string($conn, $_POST['inter_code'] ?? '');
        $sub_department_id = intval($_POST['sub_department_id'] ?? 0);
        $user_id = $_SESSION['user_name'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            if ($part_no_value === '') {
                throw new Exception("Part No is required.");
            }

            $part_check = mysqli_prepare(
                $conn,
                "SELECT id FROM part_master WHERE part_no = ? AND id <> ? LIMIT 1"
            );
            if (!$part_check) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_stmt_bind_param($part_check, "si", $part_no_value, $id);
            mysqli_stmt_execute($part_check);
            mysqli_stmt_store_result($part_check);

            if (mysqli_stmt_num_rows($part_check) > 0) {
                mysqli_stmt_close($part_check);
                throw new Exception("Part No '{$part_no_value}' already exists.");
            }
            mysqli_stmt_close($part_check);

            
            if ($id === 0) {
                $sql = "INSERT INTO part_master
                        (
                            part_name,
                            part_no,
                            fg_code,
                            im_code,
                            inter_code,
                            sub_department_id,
                            created_by
                        )
                        VALUES
                        (
                            '$part_name',
                            '$part_no',
                            '$fg_code',
                            '$im_code',
                            '$inter_code',
                            '$sub_department_id',
                            '$user_id'
                        )";
            } else {
                $sql = "UPDATE part_master
                        SET
                            part_name = '$part_name',
                            part_no = '$part_no',
                            fg_code = '$fg_code',
                            im_code = '$im_code',
                            inter_code='$inter_code',
                            sub_department_id = '$sub_department_id',
                            updated_by = '$user_id',
                            updated_at = NOW()
                        WHERE id = '$id'";
            }

            if (!mysqli_query($conn, $sql)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_commit($conn);
            echo json_encode(["status" => "success"]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    break;
    // =================== DELETE TABLE ===================
    case "delete":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_name'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO hist_part_master
                (
                    id,
                    part_name,
                    part_no,
                    fg_code,
                    im_code,
                    inter_code,
                    sub_department_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    part_name,
                    part_no,
                    fg_code,
                    im_code,
                    inter_code,
                    sub_department_id,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM part_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM part_master WHERE id = '$id'";
            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_commit($conn);
            echo json_encode(["status" => "success"]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    break;
    // =================== GET RECYCLE TABLE ===================
    case "list1":
        $sql = "SELECT
                    p.id,
                    p.part_name,
                    p.part_no,
                    p.fg_code,
                    p.im_code,
                    p.inter_code,
                    p.sub_department_id,
                    d.department_name,
                    sd.sub_department_name,
                    u.unit,
                    p.created_by,
                    p.updated_by
                FROM hist_part_master p
                LEFT JOIN sub_department_master sd
                    ON p.sub_department_id = sd.id
                LEFT JOIN department_master d
                    ON sd.department_id = d.id
                LEFT JOIN unit_master u
                    ON sd.unit_id = u.id
                ORDER BY p.id DESC";

        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
    break;
    // =================== RESTORE TABLE ===================
    case "restore":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_name'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO part_master(
                    id,
                    part_name,
                    part_no,
                    fg_code,
                    im_code,
                    inter_code,
                    sub_department_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    part_name,
                    part_no,
                    fg_code,
                    im_code,
                    inter_code,
                    sub_department_id,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_part_master
                WHERE id ='$id'
                ";
            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_part_master WHERE id = '$id'";
            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_commit($conn);
            echo json_encode(["status" => "success"]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    break;
    // =================== DEFAULT ===================
    default:
        echo json_encode(["status" => "Invalid Action"]);
    break;
}
?>