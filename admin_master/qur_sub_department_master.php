<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
// =================== GET UNIT ===================
    case "units":

        $sql = "SELECT id, unit
                FROM unit_master
                ORDER BY unit";

        $result = mysqli_query($conn,$sql);

        $data = [];

        while($row=mysqli_fetch_assoc($result)){
            $data[]=$row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);

    break;
// =================== GET DEPARTMENT ===================
    case "departments":

        $sql="SELECT id, department_name
            FROM department_master
            ORDER BY department_name";

        $result=mysqli_query($conn,$sql);

        $data=[];

        while($row=mysqli_fetch_assoc($result)){
            $data[]=$row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);

    break;
// =================== GET TABLE ===================
    case "list":

        $sql = "SELECT
                    s.id,
                    p.unit,
                    d.department_name,
                    s.sub_department_name,
                    s.created_by,
                    s.updated_by
                FROM sub_department_master s
                LEFT JOIN unit_master p
                    ON s.unit_id = p.id
                LEFT JOIN department_master d
                    ON s.department_id = d.id
                ORDER BY s.id DESC";

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        
        echo json_encode($data);

    break;
// =================== DELETE ===================
    case "delete":

        $id = intval($_POST['id']);

        mysqli_begin_transaction($conn);

        try {

            $sql1 = "
                INSERT INTO hist_sub_department_master
                (
                    id,
                    unit_id,
                    department_id,
                    sub_department_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    unit_id,
                    department_id,
                    sub_department_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                FROM sub_department_master
                WHERE id='$id'
            ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM sub_department_master WHERE id='$id'";

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
// =================== RESTORE ===================
    case "restore":

        $id = intval($_POST['id']);

        mysqli_begin_transaction($conn);

        try {

            $sql1 = "
                INSERT INTO sub_department_master
                (
                    id,
                    unit_id,
                    department_id,
                    sub_department_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    unit_id,
                    department_id,
                    sub_department_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                FROM hist_sub_department_master
                WHERE id='$id'
            ";

            if(!mysqli_query($conn,$sql1)){
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_sub_department_master WHERE id='$id'";

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
        
        $sql = "SELECT
                s.id,
                p.unit,
                d.department_name,
                s.sub_department_name,
                s.created_by,
                s.updated_by
            FROM hist_sub_department_master s
            LEFT JOIN unit_master p
                ON s.unit_id = p.id
            LEFT JOIN department_master d
                ON s.department_id = d.id
            ORDER BY s.id DESC";

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
        break;

        case "get":

        $id = intval($_GET['id']);

        $sql = "SELECT
                id,
                unit_id,
                department_id,
                sub_department_name
            FROM sub_department_master
            WHERE id='$id'
        ";

        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json");

        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== SAVE ===================
    case "save":

        header("Content-Type: application/json");

        $id = $_POST['id'] ?? "";
        $unit_id = mysqli_real_escape_string($conn, $_POST['unit_id']);
        $department_id = mysqli_real_escape_string($conn, $_POST['department_id']);
        $sub_department_name = mysqli_real_escape_string($conn, $_POST['sub_department_name']);
        $user_id = $_SESSION['user_id'] ?? 0;
        // Check duplicate sub department name
        if ($id == "") {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM sub_department_master
                WHERE LOWER(TRIM(sub_department_name)) = LOWER(TRIM('$sub_department_name'))"
            );

        } else {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM sub_department_master
                WHERE LOWER(TRIM(sub_department_name)) = LOWER(TRIM('$sub_department_name'))
                AND id != '$id'"
            );

        }

        if (mysqli_num_rows($check) > 0) {

            echo json_encode([
                "status" => "error",
                "message" => "Sub Department name already exists."
            ]);
            exit;
        }

        if ($id == "") {
            // Insert
            $sql = "INSERT INTO sub_department_master
                (
                    unit_id,
                    department_id,
                    sub_department_name,
                    created_by
                )
                VALUES
                (
                    '$unit_id',
                    '$department_id',
                    '$sub_department_name',
                    '$user_id'
                )";

        } else {
            // Update
            $sql = "UPDATE sub_department_master
                SET
                    unit_id = '$unit_id',
                    department_id = '$department_id',
                    sub_department_name = '$sub_department_name',
                    updated_by = '$user_id',
                    updated_at = NOW()
                WHERE id = '$id'
            ";

        }

        if (mysqli_query($conn, $sql)) {

            $mode = ($id == "") ? "insert" : "update";
            $record_id = ($id == "") ? mysqli_insert_id($conn) : intval($id);

            $fetch_sql = "SELECT
                    s.id,
                    p.unit,
                    d.department_name,
                    s.sub_department_name,
                    s.created_by,
                    s.updated_by
                FROM sub_department_master s
                LEFT JOIN unit_master p
                    ON s.unit_id = p.id
                LEFT JOIN department_master d
                    ON s.department_id = d.id
                WHERE s.id = '$record_id'
            ";

            $fetch_result = mysqli_query($conn, $fetch_sql);

            if (!$fetch_result) {
                echo json_encode([
                    "status" => "error",
                    "message" => mysqli_error($conn)
                ]);
                break;
            }

            echo json_encode([
                "status" => "success",
                "mode" => $mode,
                "data" => mysqli_fetch_assoc($fetch_result)
            ]);

        } else {

            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
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