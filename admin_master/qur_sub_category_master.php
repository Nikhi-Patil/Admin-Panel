<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case "list":
        $sql = "SELECT
                    s.id,
                    s.supplier_id,
                    (
                        SELECT GROUP_CONCAT(sup.supplier_name ORDER BY sup.supplier_name SEPARATOR ', ')
                        FROM supplier_master sup
                        WHERE FIND_IN_SET(sup.id, s.supplier_id)
                    ) AS supplier_name,
                    s.category_id,
                    cat.category_name,
                    s.sub_category_name,
                    s.created_by,
                    s.updated_by
                FROM sub_category_master s
                LEFT JOIN category_master cat ON cat.id = s.category_id
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
        $id = intval($_GET['id'] ?? 0);

        $sql = "SELECT
                    s.id,
                    s.supplier_id,
                    (
                        SELECT GROUP_CONCAT(sup.supplier_name ORDER BY sup.supplier_name SEPARATOR ', ')
                        FROM supplier_master sup
                        WHERE FIND_IN_SET(sup.id, s.supplier_id)
                    ) AS supplier_name,
                    s.category_id,
                    cat.category_name,
                    s.sub_category_name,
                    s.created_by,
                    s.updated_by
                FROM sub_category_master s
                LEFT JOIN category_master cat ON cat.id = s.category_id
                WHERE s.id = '$id'";

        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
    break;

    case "save":
        $id = $_POST['id'] ?? "";
        $supplier_id_raw = trim($_POST['supplier_id'] ?? '');
        $supplier_ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $supplier_id_raw)))));
        $supplier_id = mysqli_real_escape_string($conn, implode(',', $supplier_ids));
        $category_id = intval($_POST['category_id'] ?? 0);
        $sub_category_name = mysqli_real_escape_string($conn, $_POST['sub_category_name'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 0;

        $supplier_value = !empty($supplier_ids) ? "'$supplier_id'" : "NULL";

        if ($id == "") {
            $sql = "INSERT INTO sub_category_master
                    (
                        supplier_id,
                        category_id,
                        sub_category_name,
                        created_by
                    )
                    VALUES
                    (
                        $supplier_value,
                        '$category_id',
                        '$sub_category_name',
                        '$user_id'
                    )";
        } else {
            $sql = "UPDATE sub_category_master
                    SET
                        supplier_id = $supplier_value,
                        category_id = '$category_id',
                        sub_category_name = '$sub_category_name',
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

    case "delete":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO hist_sub_category_master
                (
                    id,
                    supplier_id,
                    category_id,
                    sub_category_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    supplier_id,
                    category_id,
                    sub_category_name,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM sub_category_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM sub_category_master WHERE id = '$id'";

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

    case "list1":
        $sql = "SELECT
                    h.id,
                    h.supplier_id,
                    (
                        SELECT GROUP_CONCAT(sup.supplier_name ORDER BY sup.supplier_name SEPARATOR ', ')
                        FROM supplier_master sup
                        WHERE FIND_IN_SET(sup.id, h.supplier_id)
                    ) AS supplier_name,
                    h.category_id,
                    cat.category_name,
                    h.sub_category_name,
                    h.created_by,
                    h.updated_by
                FROM hist_sub_category_master h
                LEFT JOIN category_master cat ON cat.id = h.category_id
                ORDER BY h.id DESC";

        $result = mysqli_query($conn, $sql);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
    break;

    case "restore":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO sub_category_master
                (
                    id,
                    supplier_id,
                    category_id,
                    sub_category_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    supplier_id,
                    category_id,
                    sub_category_name,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_sub_category_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_sub_category_master WHERE id = '$id'";

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

    case "suppliers":
        $sql = "SELECT id, supplier_name
                FROM supplier_master
                ORDER BY supplier_name ASC";

        $result = mysqli_query($conn, $sql);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
    break;

    case "categories":
        $sql = "SELECT id, category_name
                FROM category_master
                ORDER BY category_name ASC";

        $result = mysqli_query($conn, $sql);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
    break;

    default:
        echo json_encode([
            "status" => "Invalid Action"
        ]);
    break;
}
?>
