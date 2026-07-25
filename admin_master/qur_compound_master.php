<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
// =================== download template of xecel ===================
    case "download_template":
        header("Content-Type: text/csv; charset=utf-8");
        header(
            "Content-Disposition: attachment; filename=compound_master_import_template.csv"
        );

        $output = fopen("php://output", "w");

        fputcsv($output, ["compound_code", "polymer", "im_code"]);
        fputcsv($output, ["CMP-001", "EPDM", "IM-001"]);

        fclose($output);
        exit;
    break;
// =================== bulk upload of xecel ===================
    case "bulk_upload":
        header("Content-Type: application/json");
        $transactionStarted = false;

        try {
            if (
                empty($_FILES["excel_file"]) ||
                $_FILES["excel_file"]["error"] !== UPLOAD_ERR_OK
            ) {
                throw new Exception("Please select a valid Excel file.");
            }

            $extension = strtolower(
                pathinfo($_FILES["excel_file"]["name"], PATHINFO_EXTENSION)
            );

            if (!in_array($extension, ["xlsx", "csv"], true)) {
                throw new Exception("Only .xlsx or .csv files are allowed.");
            }

            require_once __DIR__ . "/../../capex/includes/SimpleXLSX.php";

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

            $requiredHeaders = ["compound_code", "polymer", "im_code"];
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
                    "Required columns: compound_code, polymer, im_code"
                );
            }

            $userId = $_SESSION["user_id"] ?? "";

            if ((string)$userId === "") {
                throw new Exception("Session user not found. Please log in again.");
            }

            mysqli_begin_transaction($conn);
            $transactionStarted = true;

            $checkCompound = mysqli_prepare(
                $conn,
                "SELECT id FROM compound_master WHERE compound_code = ? LIMIT 1"
            );

            $insertCompound = mysqli_prepare(
                $conn,
                "INSERT INTO compound_master
                    (compound_code, polymer, im_code, created_by)
                VALUES (?, ?, ?, ?)"
            );

            if (!$checkCompound || !$insertCompound) {
                throw new Exception(mysqli_error($conn));
            }

            $seenCodes = [];
            $imported = 0;

            foreach (array_slice($rows, $headerRowIndex + 1) as $rowNumber => $row) {
                $excelRow = $rowNumber + $headerRowIndex + 2;

                $compoundCode = trim(
                    (string)($row[$headerIndexes["compound_code"]] ?? "")
                );
                $polymer = trim(
                    (string)($row[$headerIndexes["polymer"]] ?? "")
                );
                $imCode = trim(
                    (string)($row[$headerIndexes["im_code"]] ?? "")
                );

                if ($compoundCode === "" && $polymer === "" && $imCode === "") {
                    continue;
                }

                if ($compoundCode === "" || $polymer === "" || $imCode === "") {
                    throw new Exception("Row {$excelRow} has an empty required value.");
                }

                $codeKey = strtolower($compoundCode);

                if (isset($seenCodes[$codeKey])) {
                    throw new Exception(
                        "Row {$excelRow}: duplicate compound code '{$compoundCode}'."
                    );
                }

                $seenCodes[$codeKey] = true;

                mysqli_stmt_bind_param($checkCompound, "s", $compoundCode);
                mysqli_stmt_execute($checkCompound);
                mysqli_stmt_store_result($checkCompound);

                if (mysqli_stmt_num_rows($checkCompound) > 0) {
                    mysqli_stmt_free_result($checkCompound);

                    throw new Exception(
                        "Row {$excelRow}: compound code '{$compoundCode}' already exists."
                    );
                }

                mysqli_stmt_free_result($checkCompound);

                mysqli_stmt_bind_param(
                    $insertCompound,
                    "ssss",
                    $compoundCode,
                    $polymer,
                    $imCode,
                    $userId
                );

                if (!mysqli_stmt_execute($insertCompound)) {
                    throw new Exception(mysqli_stmt_error($insertCompound));
                }

                $imported++;
            }

            if ($imported === 0) {
                throw new Exception("No compound records were found.");
            }

            mysqli_commit($conn);

            echo json_encode([
                "status" => "success",
                "message" => "{$imported} compound(s) imported successfully."
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

        $sql = "SELECT id,polymer,im_code,compound_code,updated_by,created_by
                FROM compound_master
                ORDER BY id DESC";

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

        $id = intval($_GET['id']);

        $sql = "SELECT id,polymer,im_code,compound_code,updated_by,created_by
                FROM compound_master
                WHERE id='$id'";

        $result = mysqli_query($conn, $sql);

        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== SAVE / UPDATE ===================
    case "save":

        $id = $_POST['id'] ?? "";

        $compound_code = mysqli_real_escape_string($conn, $_POST['compound_code']);
        $im_code = mysqli_real_escape_string($conn, $_POST['im_code']);
        $polymer = mysqli_real_escape_string($conn, $_POST['polymer']);

        $user_id = $_SESSION['user_name'];
        $compound_code = trim($_POST["compound_code"] ?? "");
        

        $check = mysqli_prepare(
            $conn,
            "SELECT id
            FROM compound_master
            WHERE compound_code = ?
            AND id <> ?
            LIMIT 1"
        );

        mysqli_stmt_bind_param($check, "si", $compound_code, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            echo "Compound code already exists.";
            exit;
        }

        mysqli_stmt_close($check);

        if ($id == "") {

            $sql = "INSERT INTO compound_master
                (
                    compound_code,
                    im_code,
                    polymer,
                    created_by
                )
                VALUES
                (
                    '$compound_code',
                    '$im_code',
                    '$polymer',
                    '$user_id'
                )";

        } else {

            $sql = "UPDATE compound_master
                SET
                    compound_code='$compound_code',
                    im_code='$im_code',
                    polymer='$polymer',
                    updated_by='$user_id',
                    updated_at=NOW()
                WHERE id='$id'";
        }

        if(mysqli_query($conn,$sql)){
            echo "success";
        }else{
            echo mysqli_error($conn);
        }

    break;
// =================== DELETE ===================
    case "delete":

        header("Content-Type: application/json");

        // Validate ID
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            echo json_encode([
            "status" => "error",
            "message" => "Invalid record ID."
            ]);
            exit;
        }

        $id = intval($_POST['id']);

        // Check Session
        $user_id = $_SESSION['user_name'] ?? 0;

        if (!$user_id) {
            echo json_encode([
                "status" => "error",
                "message" => "Session expired. Please login again."
            ]);
            exit;
        }

        // Check whether record exists
        $check = mysqli_query($conn, "SELECT * FROM compound_master WHERE id='$id'");

        if (!$check) {
            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
            exit;
        }

        if (mysqli_num_rows($check) == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Record not found."
            ]);
            exit;
        }

        mysqli_begin_transaction($conn);

        try {

            // Copy record to history table
            $sql1 = "
                INSERT INTO hist_compound_master
                (
                    id,
                    compound_code,
                    polymer,
                    created_by,
                    created_at,
                    im_code,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    compound_code,
                    polymer,
                    created_by,
                    created_at,
                    im_code,
                    '$user_id',
                    NOW()
                FROM compound_master
                WHERE id='$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
            throw new Exception(mysqli_error($conn));
            }

            if (mysqli_affected_rows($conn) != 1) {
            throw new Exception("Failed to move record to history.");
            }

            // Delete from main table
            $sql2 = "DELETE FROM compound_master WHERE id='$id'";

            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(mysqli_error($conn));
            }

            if (mysqli_affected_rows($conn) != 1) {
                throw new Exception("Failed to delete record.");
            }

            mysqli_commit($conn);

            echo json_encode([
                "status" => "success",
                "message" => "Compound deleted successfully."
            ]);

        } catch (Exception $e) {

            mysqli_rollback($conn);

            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }

    break;
// =================== GET TABLE IN RECYCLE ===================
    case "list1":

        $sql = "SELECT id,polymer,im_code,compound_code,updated_by,created_by
                FROM hist_compound_master
                ORDER BY id DESC";

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
    break;
// =================== Restore ===================
    case "restore":

        $id = intval($_POST['id']);

        mysqli_begin_transaction($conn);

        $user_id = $_SESSION['user_name'] ?? 0;

        if (!$user_id) {
            echo json_encode([
                "status" => "error",
                "message" => "Session expired. Please log in again."
            ]);
            break;
        }

        try {

            $sql1 = "
                INSERT INTO compound_master
                (
                    id,
                    compound_code,
                    polymer,
                    created_by,
                    created_at,
                    im_code,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    compound_code,
                    polymer,
                    created_by,
                    created_at,
                    im_code,
                    '$user_id',
                    updated_at
                FROM hist_compound_master
                WHERE id='$id'
            ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_compound_master WHERE id='$id'";

            if(!mysqli_query($conn,$sql2)){
                throw new Exception(mysqli_error($conn));
            }

            mysqli_commit($conn);

            echo json_encode([
                "status"=>"success"
            ]);

        }catch(Exception $e){

            mysqli_rollback($conn);

            echo json_encode([
                "status"=>"error",
                "message"=>$e->getMessage()
            ]);

        }

    break; 
// =================== Default ===================
    default:
        echo json_encode([
            "status"=>"Invalid Action"
        ]);
    break;
    
}

?>