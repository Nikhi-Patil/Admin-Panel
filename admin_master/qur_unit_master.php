<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
// =================== GET TABLE ===================
    case "list":

        $sql = "SELECT id,address,location,unit,updated_by,created_by
                FROM unit_master
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

        $sql = "SELECT id,address,location,unit,updated_by,created_by
                FROM unit_master
                WHERE id='$id'";

        $result = mysqli_query($conn, $sql);

        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== SAVE / UPDATE ===================
    case "save":

        $id = $_POST['id'] ?? "";

        $unit = mysqli_real_escape_string($conn, $_POST['unit']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);

        $user_id = $_SESSION['user_name'];
        $unit = trim(mysqli_real_escape_string($conn, $_POST['unit']));

        // Check if unit already exists
        if ($id == "") {
            $check = mysqli_query($conn,
                "SELECT id FROM unit_master
                WHERE LOWER(TRIM(unit)) = LOWER(TRIM('$unit'))");
        } else {
            $check = mysqli_query($conn,
                "SELECT id FROM unit_master
                WHERE LOWER(TRIM(unit)) = LOWER(TRIM('$unit'))
                AND id != '$id'");
        }

        if (mysqli_num_rows($check) > 0) {
            echo "Unit name already exists.";
            exit;
        }

        if ($id == "") {

            $sql = "INSERT INTO unit_master
                (
                    unit,
                    location,
                    address,
                    created_by
                )
                VALUES
                (
                    '$unit',
                    '$location',
                    '$address',
                    '$user_id'
                )";

        } else {

            $sql = "UPDATE unit_master
                SET
                    unit='$unit',
                    location='$location',
                    address='$address',
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

        if (!$user_id) {
            echo json_encode([
                "status" => "error",
                "message" => "Session expired. Please log in again."
            ]);
            break;
        }

        $check_sql = "SELECT COUNT(*) AS cnt
                      FROM sub_department_master
                      WHERE unit_id='$id'";
        $check_result = mysqli_query($conn, $check_sql);

        if (!$check_result) {
            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
            break;
        }

        $check_row = mysqli_fetch_assoc($check_result);

        if (($check_row['cnt'] ?? 0) > 0) {
            echo json_encode([
                "status" => "error",
                "message" => "This unit is used in sub department records. Delete those first."
            ]);
            break;
        }
        
        try {

            $sql1 = "
                INSERT INTO hist_unit_master
                (
                    id,
                    unit,
                    address,
                    created_by,
                    created_at,
                    location,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    unit,
                    address,
                    created_by,
                    created_at,
                    location,
                    '$user_id',
                    NOW()
                FROM unit_master
                WHERE id='$id'
                ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM unit_master WHERE id='$id'";

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

        $sql = "SELECT id,address,location,unit,updated_by,created_by
                FROM hist_unit_master
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
                INSERT INTO unit_master
                (
                    id,
                    unit,
                    address,
                    created_by,
                    created_at,
                    location,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    unit,
                    address,
                    created_by,
                    created_at,
                    location,
                    '$user_id',
                    updated_at
                FROM hist_unit_master
                WHERE id='$id'
            ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_unit_master WHERE id='$id'";

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