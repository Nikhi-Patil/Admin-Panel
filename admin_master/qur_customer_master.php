<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
// =================== download template of xecel ===================
    case "download_template":

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=customer_master_import_template.csv");

        $output = fopen("php://output", "w");

        fputcsv($output, ["customer_name", "sub_customer", "geo_type", "zone"]);
        fputcsv($output, ["ABC Ltd", "ABC Pune", "Domastic", "North"]);

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

            $extension = strtolower(pathinfo($_FILES["excel_file"]["name"], PATHINFO_EXTENSION));

            if (!in_array($extension, ["xlsx", "csv"], true)) {
                throw new Exception("Only .xlsx or .csv files are allowed.");
            }

            require_once __DIR__ . "/../../capex/includes/SimpleXLSX.php";

            if ($extension == "csv") {

                $rows = [];

                $handle = fopen($_FILES["excel_file"]["tmp_name"], "rb");

                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = $row;
                }

                fclose($handle);

            } else {

                $xlsx = \Shuchkin\SimpleXLSX::parse($_FILES["excel_file"]["tmp_name"]);

                if (!$xlsx) {
                    throw new Exception(\Shuchkin\SimpleXLSX::parseError());
                }

                $rows = $xlsx->rows();
            }

            $requiredHeaders = [
                "customer_name",
                "sub_customer",
                "geo_type",
                "zone"
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
                throw new Exception("Required columns: customer_name, sub_customer, geo_type, zone");
            }

            $userId = $_SESSION["user_id"] ?? "";

            if ($userId == "") {
                throw new Exception("Session expired.");
            }

            mysqli_begin_transaction($conn);
            $transactionStarted = true;

            $checkCustomer = mysqli_prepare(
                $conn,
                "SELECT id FROM customer_master
                WHERE customer_name=? AND sub_customer=?
                LIMIT 1"
            );

            $insertCustomer = mysqli_prepare(
                $conn,
                "INSERT INTO customer_master
                (
                    customer_name,
                    sub_customer,
                    geo_type,
                    zone,
                    created_by
                )
                VALUES
                (
                    ?,?,?,?,?
                )"
            );

            if (!$checkCustomer || !$insertCustomer) {
                throw new Exception(mysqli_error($conn));
            }

            $seen = [];
            $imported = 0;

            foreach (array_slice($rows, $headerRowIndex + 1) as $rowNumber => $row) {

                $excelRow = $rowNumber + $headerRowIndex + 2;

                $customer_name = trim((string)($row[$headerIndexes["customer_name"]] ?? ""));
                $sub_customer = trim((string)($row[$headerIndexes["sub_customer"]] ?? ""));
                $geo_type = trim((string)($row[$headerIndexes["geo_type"]] ?? ""));
                $zone = trim((string)($row[$headerIndexes["zone"]] ?? ""));

                if (
                    $customer_name=="" &&
                    $sub_customer=="" &&
                    $geo_type=="" &&
                    $zone==""
                ){
                    continue;
                }

                if (
                    $customer_name=="" ||
                    $sub_customer=="" ||
                    $geo_type=="" ||
                    $zone==""
                ){
                    throw new Exception("Row {$excelRow} has empty required value.");
                }

                $key = strtolower($customer_name)."|".strtolower($sub_customer);

                if(isset($seen[$key])){
                    throw new Exception("Duplicate customer found in Excel at row {$excelRow}.");
                }

                $seen[$key]=true;

                mysqli_stmt_bind_param(
                    $checkCustomer,
                    "ss",
                    $customer_name,
                    $sub_customer
                );

                mysqli_stmt_execute($checkCustomer);
                mysqli_stmt_store_result($checkCustomer);

                if(mysqli_stmt_num_rows($checkCustomer)>0){

                    mysqli_stmt_free_result($checkCustomer);

                    throw new Exception(
                        "Customer '{$customer_name}' / '{$sub_customer}' already exists."
                    );
                }

                mysqli_stmt_free_result($checkCustomer);

                mysqli_stmt_bind_param(
                    $insertCustomer,
                    "sssss",
                    $customer_name,
                    $sub_customer,
                    $geo_type,
                    $zone,
                    $userId
                );

                if(!mysqli_stmt_execute($insertCustomer)){
                    throw new Exception(mysqli_stmt_error($insertCustomer));
                }

                $imported++;
            }

            if($imported==0){
                throw new Exception("No customer records found.");
            }

            mysqli_commit($conn);

            echo json_encode([
                "status"=>"success",
                "message"=>"{$imported} customer(s) imported successfully."
            ]);

        } catch(Throwable $e){

            if($transactionStarted){
                mysqli_rollback($conn);
            }

            echo json_encode([
                "status"=>"error",
                "message"=>$e->getMessage()
            ]);
        }

    break;
// =================== GET TABLE ===================
    case "list":

        $sql = "SELECT id, customer_name, sub_customer, geo_type, zone, updated_by, created_by
                FROM customer_master
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

        $sql = "SELECT id, customer_name, sub_customer, geo_type, zone, updated_by, created_by
                FROM customer_master
                WHERE id='$id'";

        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== SAVE / UPDATE ===================
    case "save":

        $id = $_POST['id'] ?? "";

        $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
        $sub_customer = mysqli_real_escape_string($conn, $_POST['sub_customer']);
        $geo_type = mysqli_real_escape_string($conn, $_POST['geo_type']);
        $zone = mysqli_real_escape_string($conn, $_POST['zone']);
        $user_id = $_SESSION['user_name'];
        // Check duplicate Customer + Sub Customer
        if ($id == "") {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM customer_master
                WHERE LOWER(TRIM(customer_name)) = LOWER(TRIM('$customer_name'))
                AND LOWER(TRIM(sub_customer)) = LOWER(TRIM('$sub_customer'))"
            );

        } else {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM customer_master
                WHERE LOWER(TRIM(customer_name)) = LOWER(TRIM('$customer_name'))
                AND LOWER(TRIM(sub_customer)) = LOWER(TRIM('$sub_customer'))
                AND id != '$id'"
            );

        }

        if (mysqli_num_rows($check) > 0) {
            echo "This customer and sub customer combination already exists.";
            exit;
        }

        if ($id == "") {

            $sql = "INSERT INTO customer_master
                (
                    customer_name,
                    sub_customer,
                    geo_type,
                    zone,
                    created_by
                )
                VALUES
                (
                    '$customer_name',
                    '$sub_customer',
                    '$geo_type',
                    '$zone',
                    '$user_id'
                )";

        } else {

            $sql = "UPDATE customer_master
                SET
                    customer_name='$customer_name',
                    sub_customer='$sub_customer',
                    geo_type='$geo_type',
                    zone='$zone',
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

        $id = intval($_POST['id']);

        mysqli_begin_transaction($conn);

        $user_id = $_SESSION['user_name'] ?? 0;

        try {

            $sql1 = "
                INSERT INTO hist_customer_master
                (
                    id,
                    customer_name,
                    sub_customer,
                    geo_type,
                    zone,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    customer_name,
                    sub_customer,
                    geo_type,
                    zone,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM customer_master
                WHERE id='$id'
                ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM customer_master WHERE id='$id'";

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
// =================== GET TABLE IN RECYCLE ===================
    case "list1":

        $sql = "SELECT id, customer_name, sub_customer, geo_type, zone, updated_by, created_by
                FROM hist_customer_master
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

        try {

            $sql1 = "
                INSERT INTO customer_master
                (
                    id,
                    customer_name,
                    sub_customer,
                    geo_type,
                    zone,
                    created_by,
                    created_at, 
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    customer_name,
                    sub_customer,
                    geo_type,
                    zone,
                    created_by,
                    created_at,
                    '$user_id',
                    updated_at
                FROM hist_customer_master
                WHERE id='$id'
            ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_customer_master WHERE id='$id'";

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