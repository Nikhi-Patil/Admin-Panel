<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    // =================== GET TABLE ===================
    case "list":
                $sql = "SELECT
                    p.id,
                    p.bop_part_name,
                    p.bop_part_no,
                    p.quantity,
                    p.umo,
                    p.supplier_id,
                    p.part_id,
                    d.supplier_name,
                    sd.part_no,
                    sd.fg_code,
                    p.created_by,
                    p.updated_by
                FROM bop_master p
                LEFT JOIN supplier_master d ON p.supplier_id = d.id
                LEFT JOIN part_master sd ON p.part_id = sd.id
                ORDER BY p.id DESC";

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

                $sql = "SELECT
                    p.id,
                    p.bop_part_name,
                    p.bop_part_no,
                    p.quantity,
                    p.umo,
                    p.supplier_id,
                    p.part_id,
                    d.supplier_name,
                    sd.part_no,
                    p.fg_code,
                    p.created_by,
                    p.updated_by
                FROM bop_master p
                LEFT JOIN supplier_master d ON p.supplier_id = d.id
                LEFT JOIN part_master sd ON p.part_id = sd.id
                WHERE p.id = '$id'";

        $result = mysqli_query($conn, $sql);
        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
    break;
    // =================== GET DEPARTMENT TABLE ===================
    case "supplier":
        $sql = "SELECT id,supplier_name
                FROM supplier_master
                ORDER BY supplier_name";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        header("Content-Type: application/json");
        echo json_encode($data);
    break;
    // =================== GET SUBDEPARTMENT TABLE ===================
    case "part":
        $sql = "SELECT
                id,
                part_no AS part_name,
                fg_code
                FROM part_master
                ORDER BY part_no";

        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        header("Content-Type: application/json");
        echo json_encode($data);
    break;
    // =================== SAVE TABLE ===================
    case "save":
        header("Content-Type: application/json");

        $id = $_POST['id'] ?? "";
        $bop_part_name = mysqli_real_escape_string($conn, $_POST['bop_part_name'] ?? '');
        $bop_part_no = mysqli_real_escape_string($conn, $_POST['bop_part_no'] ?? '');
        $fg_code = mysqli_real_escape_string($conn, $_POST['fg_code'] ?? '');
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity'] ?? '');
        $umo = mysqli_real_escape_string($conn, $_POST['umo'] ?? '');
        $part_id = intval($_POST['part_id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $supplier_id = intval($_POST['supplier_id']);

            if ($id == "") {
                $sql = "INSERT INTO bop_master
                        (
                            bop_part_name,
                            bop_part_no,
                            fg_code,
                            quantity,
                            umo,
                            supplier_id,
                            part_id,
                            created_by
                        )
                        VALUES
                        (
                            '$bop_part_name',
                            '$bop_part_no',
                            '$fg_code',
                            '$quantity',
                            '$umo',
                            '$supplier_id',
                            '$part_id',
                            '$user_id'
                        )";
            } else {
                $sql = "UPDATE bop_master
                        SET
                            bop_part_name = '$bop_part_name',
                            bop_part_no = '$bop_part_no',
                            fg_code = '$fg_code',
                            quantity='$quantity',
                            umo='$umo',
                            supplier_id = '$supplier_id',
                            part_id = '$part_id',
                            updated_by = '$user_id',
                            updated_at = NOW()
                        WHERE id = '$id'";
            }

            if (!mysqli_query($conn, $sql)) {
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
    // =================== DELETE TABLE ===================
    case "delete":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO hist_bop_master
                (
                    id,
                    bop_part_name,
                    bop_part_no,
                    fg_code,
                    quantity,
                    umo,
                    supplier_id,
                    part_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    bop_part_name,
                    bop_part_no,
                    fg_code,
                    quantity,
                    umo,
                    supplier_id,
                    part_id,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM bop_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM bop_master WHERE id = '$id'";
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
    // =================== GET RECYCLE TABLE ===================
    case "list1":
        $sql = "SELECT
                    p.id,
                    p.bop_part_name,
                    p.bop_part_no,
                    p.fg_code,
                    p.quantity,
                    p.umo,
                    p.supplier_id,
                    p.part_id,
                    d.supplier_name,
                    sd.part_no,
                    p.created_by,
                    p.updated_by
                FROM hist_bop_master p
                LEFT JOIN supplier_master d ON p.supplier_id = d.id
                LEFT JOIN part_master sd ON p.part_id = sd.id
                ORDER BY p.id DESC";

        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
    break;
    // =================== RESTORE TABLE ===================
    case "restore":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO bop_master
                (
                    id,
                    bop_part_name,
                    bop_part_no,
                    fg_code,
                    quantity,
                    umo,
                    supplier_id,
                    part_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    bop_part_name,
                    bop_part_no,
                    fg_code,
                    quantity,
                    umo,
                    supplier_id,
                    part_id,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_bop_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_bop_master WHERE id = '$id'";
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
        echo json_encode(["status" => "Invalid Action"]);
    break;
}
?>