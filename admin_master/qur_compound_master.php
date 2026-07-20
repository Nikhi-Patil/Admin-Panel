<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
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

        $user_id = $_SESSION['user_id'];

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

        $id = intval($_POST['id']);

        mysqli_begin_transaction($conn);

        $user_id = $_SESSION['user_id'] ?? 0;

        if (!$user_id) {
            echo json_encode([
                "status" => "error",
                "message" => "Session expired. Please log in again."
            ]);
            break;
        }

        if (!$check_result) {
            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
            break;
        }
        
        try {

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

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM compound_master WHERE id='$id'";

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

        $user_id = $_SESSION['user_id'] ?? 0;

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