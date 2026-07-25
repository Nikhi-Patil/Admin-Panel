<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
// =================== GET TABLE ===================
    case "list":

        $sql = "SELECT id,supplier_name,location,email,contact_no,updated_by,created_by
                FROM supplier_master
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

        $sql = "SELECT id,supplier_name,location,email,contact_no,updated_by,created_by
                FROM supplier_master
                WHERE id='$id'";

        $result = mysqli_query($conn, $sql);

        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== SAVE / UPDATE ===================
    case "save":

        $id = $_POST['id'] ?? "";

        $supplier_name = mysqli_real_escape_string($conn, $_POST['supplier_name']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no']);
        $user_id = $_SESSION['user_name'];
        // Check duplicate supplier name
        if ($id == "") {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM supplier_master
                WHERE LOWER(TRIM(supplier_name)) = LOWER(TRIM('$supplier_name'))"
            );

        } else {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM supplier_master
                WHERE LOWER(TRIM(supplier_name)) = LOWER(TRIM('$supplier_name'))
                AND id != '$id'"
            );

        }

        if (mysqli_num_rows($check) > 0) {
            echo "Supplier name already exists.";
            exit;
        }

        if ($id == "") {

            $sql = "INSERT INTO supplier_master
                (
                    supplier_name,
                    location,
                    email,
                    contact_no,
                    created_by
                )
                VALUES
                (
                    '$supplier_name',
                    '$location',
                    '$email',
                    '$contact_no',
                    '$user_id'
                )";

        } else {

            $sql = "UPDATE supplier_master
                SET
                    supplier_name='$supplier_name',
                    location='$location',
                    email='$email',
                    contact_no='$contact_no',
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
                INSERT INTO hist_supplier_master
                (
                    id,
                    supplier_name,
                    location,
                    email,
                    contact_no,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    supplier_name,
                    location,
                    email,
                    contact_no,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM supplier_master
                WHERE id='$id'
                ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM supplier_master WHERE id='$id'";

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

        $sql = "SELECT id,supplier_name,email,contact_no,location,updated_by,created_by
                FROM hist_supplier_master
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
                INSERT INTO supplier_master
                (
                    id,
                    supplier_name,
                    email,
                    contact_no,
                    location,
                    created_by,
                    created_at, 
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    supplier_name,
                    email,
                    contact_no,
                    location,
                    created_by,
                    created_at,
                    '$user_id',
                    updated_at
                FROM hist_supplier_master
                WHERE id='$id'
            ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_supplier_master WHERE id='$id'";

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