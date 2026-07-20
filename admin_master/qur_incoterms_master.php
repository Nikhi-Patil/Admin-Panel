<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case "list":
        $sql = "SELECT id, incoterms, created_by, updated_by
                FROM incoterms_master
                ORDER BY id DESC";

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

        $sql = "SELECT id, incoterms, created_by, updated_by
                FROM incoterms_master
                WHERE id = '$id'";

        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
        break;

    case "save":
        $id = $_POST['id'] ?? "";
        $incoterms = mysqli_real_escape_string($conn, $_POST['incoterms'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 0;

        if ($id == "") {
            $sql = "INSERT INTO incoterms_master
                    (
                        incoterms,
                        created_by
                    )
                    VALUES
                    (
                        '$incoterms',
                        '$user_id'
                    )";
        } else {
            $sql = "UPDATE incoterms_master
                    SET
                        incoterms = '$incoterms',
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
                INSERT INTO hist_incoterms_master
                (
                    id,
                    incoterms,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    incoterms,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM incoterms_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM incoterms_master WHERE id = '$id'";

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
        $sql = "SELECT id, incoterms, created_by, updated_by
                FROM hist_incoterms_master
                ORDER BY id DESC";

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
                INSERT INTO incoterms_master
                (
                    id,
                    incoterms,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    incoterms,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_incoterms_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_incoterms_master WHERE id = '$id'";

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

    default:
        echo json_encode([
            "status" => "Invalid Action"
        ]);
        break;
}
?>
