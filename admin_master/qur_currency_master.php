<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
// =================== GET TABLE ===================
    case "list":
        $sql = "SELECT id, currency_name, currency_symbol, exchange_rate, created_by, updated_by
                FROM currency_master
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

        $sql = "SELECT id, currency_name, currency_symbol, exchange_rate, created_by, updated_by
                FROM currency_master
                WHERE id = '$id'";

        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== SAVE / UPDATE ===================
    case "save":
        $id = $_POST['id'] ?? "";
        $currency_name = mysqli_real_escape_string($conn, $_POST['currency_name'] ?? '');
        $currency_symbol = mysqli_real_escape_string($conn, $_POST['currency_symbol'] ?? '');
       $exchange_rate = round((float)($_POST['exchange_rate'] ?? 0), 2);
        $user_id = $_SESSION['user_name'] ?? 0;

        if ($id == "") {
            $sql = "INSERT INTO currency_master
                    (
                        currency_name,
                        currency_symbol,
                        exchange_rate,
                        created_by
                    )
                    VALUES
                    (
                        '$currency_name',
                        '$currency_symbol',
                        '$exchange_rate',
                        '$user_id'
                    )";
        } else {
            $sql = "UPDATE currency_master
                    SET
                        currency_name = '$currency_name',
                        currency_symbol = '$currency_symbol',
                        exchange_rate = '$exchange_rate',
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
                INSERT INTO hist_currency_master
                (
                    id,
                    currency_name,
                    currency_symbol,
                    exchange_rate,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    currency_name,
                    currency_symbol,
                    exchange_rate,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM currency_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM currency_master WHERE id = '$id'";

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
        $sql = "SELECT id, currency_name, currency_symbol, exchange_rate, created_by, updated_by
                FROM hist_currency_master
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
                INSERT INTO currency_master
                (
                    id,
                    currency_name,
                    currency_symbol,
                    exchange_rate,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    currency_name,
                    currency_symbol,
                    exchange_rate,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_currency_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_currency_master WHERE id = '$id'";

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