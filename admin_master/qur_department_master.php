<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {

// =================== GET TABLE ===================
    case "list":

        $sql = "SELECT id,department_name,updated_by,created_by
                FROM department_master
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

        $sql = "SELECT id,department_name,updated_by,created_by
                FROM department_master
                WHERE id='$id'";

        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json");

        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== SAVE / UPDATE ===================
    case "save":

        $id = $_POST['id'] ?? "";
        $department_name = mysqli_real_escape_string($conn, $_POST['department_name']);
        $user_id = $_SESSION['user_name'] ?? 0;
                // Check duplicate department name
        if ($id == "") {
            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM department_master
                WHERE LOWER(TRIM(department_name)) = LOWER(TRIM('$department_name'))"
            );
        } else {
            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM department_master
                WHERE LOWER(TRIM(department_name)) = LOWER(TRIM('$department_name'))
                AND id != '$id'"
            );
        }

        if (mysqli_num_rows($check) > 0) {
            echo "Department name already exists.";
            exit;
        }

        if ($id == "") {

            $sql = "INSERT INTO department_master
                    (department_name, created_by)
                    VALUES
                    ('$department_name','$user_id')";

        } else {

            $sql = "UPDATE department_master
                    SET
                        department_name='$department_name',
                        updated_by='$user_id'
                    WHERE id=$id";
        }

        if (mysqli_query($conn, $sql)) {
            echo "success";
        } else {
            echo mysqli_error($conn);
        }

    break;
// =================== DELETE ===================
    case "delete":

        $id = intval($_POST['id']);

        mysqli_begin_transaction($conn);

        try {

            $sql1 = "
            INSERT INTO hist_department_master
            (
                id,
                department_name,
                created_by,
                created_at,
                updated_by,
                updated_at
            )
            SELECT
                id,
                department_name,
                created_by,
                created_at,
                updated_by,
                updated_at
            FROM department_master
            WHERE id='$id'
            ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM department_master WHERE id='$id'";

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

        $sql = "SELECT id,department_name,updated_by,created_by
                FROM hist_department_master
                ORDER BY id DESC";

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);

    break;
// =================== RESTOR ===================
    case "restore":

        $id = intval($_POST['id']);

        mysqli_begin_transaction($conn);

        try {

            $sql1 = "
                INSERT INTO department_master
                (
                    id,
                    department_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    department_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                FROM hist_department_master
                WHERE id='$id'
            ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_department_master WHERE id='$id'";

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
// =================== DEFAULT ===================
    default:
        echo json_encode([
            "status"=>"Invalid Action"
        ]);
    break;
}
?>