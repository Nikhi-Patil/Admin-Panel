<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
// =================== GET TABLE ===================
    case "list":
        $sql = "SELECT id, designation, created_by, updated_by
                FROM designation_master
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
        $id = intval($_GET['id'] ?? 0);

        $sql = "SELECT id, designation, created_by, updated_by
                FROM designation_master
                WHERE id = '$id'";

        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== SAVE / UPDATE ===================
    case "save":
        $id = $_POST['id'] ?? "";
        $designation = mysqli_real_escape_string($conn, $_POST['designation'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 0;
        // Check duplicate designation
        if ($id == "") {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM designation_master
                WHERE LOWER(TRIM(designation)) = LOWER(TRIM('$designation'))"
            );

        } else {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM designation_master
                WHERE LOWER(TRIM(designation)) = LOWER(TRIM('$designation'))
                AND id != '$id'"
            );

        }

        if (mysqli_num_rows($check) > 0) {
            echo "Designation name already exists.";
            exit;
        }

        if ($id == "") {
            $sql = "INSERT INTO designation_master
                    (
                        designation,
                        created_by
                    )
                    VALUES
                    (
                        '$designation',
                        '$user_id'
                    )";
        } else {
            $sql = "UPDATE designation_master
                    SET
                        designation = '$designation',
                        updated_by = '$user_id',
                        updated_at = NOW()
                    WHERE id = '$id'";
        }

        if (mysqli_query($conn, $sql)) {
            echo "success";
        } else {
            echo mysqli_error($conn);
        }
    break;
// =================== DELETE ===================
    case "delete":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO hist_designation_master
                (
                    id,
                    designation,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    designation,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM designation_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM designation_master WHERE id = '$id'";

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
// =================== GET TABLE IN RECYCLE ===================
    case "list1":
        $sql = "SELECT id, designation, created_by, updated_by
                FROM hist_designation_master
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
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO designation_master
                (
                    id,
                    designation,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    designation,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_designation_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_designation_master WHERE id = '$id'";

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
        echo json_encode([
            "status" => "Invalid Action"
        ]);
    break;
}
?>