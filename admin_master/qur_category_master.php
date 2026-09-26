<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
// =================== GET TABLE ===================
    case "list":
        $sql = "SELECT id, category_name, created_by, updated_by
                FROM category_master
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

        $sql = "SELECT id, category_name, created_by, updated_by
                FROM category_master
                WHERE id = '$id'";

        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== SAVE / UPDATE ===================
    case "save":
        $id = $_POST['id'] ?? "";
        $category_name = mysqli_real_escape_string($conn, $_POST['category_name'] ?? '');
        $user_id = $_SESSION['user_name'] ?? 0;
        // Check duplicate category name
        if ($id == "") {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM category_master
                WHERE LOWER(TRIM(category_name)) = LOWER(TRIM('$category_name'))"
            );

        } else {

            $check = mysqli_query(
                $conn,
                "SELECT id
                FROM category_master
                WHERE LOWER(TRIM(category_name)) = LOWER(TRIM('$category_name'))
                AND id != '$id'"
            );

        }

        if (mysqli_num_rows($check) > 0) {
            echo "Category name already exists.";
            exit;
        }

        if ($id == "") {
            $sql = "INSERT INTO category_master
                    (
                        category_name,
                        created_by
                    )
                    VALUES
                    (
                        '$category_name',
                            '$user_id'
                    )";
        } else {
            $sql = "UPDATE category_master
                    SET
                        category_name = '$category_name',
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
        $user_id = $_SESSION['user_name'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO hist_category_master
                (
                    id,
                    category_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    category_name,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM category_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM category_master WHERE id = '$id'";

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
        $sql = "SELECT id, category_name, created_by, updated_by
                FROM hist_category_master
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
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_name'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO category_master
                (
                    id,
                    category_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    category_name,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_category_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_category_master WHERE id = '$id'";

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
// =================== Default ===================
    default:
        echo json_encode([
            "status" => "Invalid Action"
        ]);
    break;
}
?>