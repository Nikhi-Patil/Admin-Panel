<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
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
        $user_id = $_SESSION['user_id'];

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

        $user_id = $_SESSION['user_id'] ?? 0;

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

        $user_id = $_SESSION['user_id'] ?? 0;

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
