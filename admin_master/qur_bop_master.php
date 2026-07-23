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
                    p.bop_quantity,
                    p.umo,
                    p.supplier_id,
                    p.part_id,
                    GROUP_CONCAT(DISTINCT d.supplier_name ORDER BY d.supplier_name SEPARATOR ', ') AS supplier_name,
                    sd.part_no,
                    sd.fg_code,
                    p.bop_erp_code,
                    p.created_by,
                    p.updated_by
                FROM bop_master p
                LEFT JOIN supplier_master d ON FIND_IN_SET(d.id, p.supplier_id)
                LEFT JOIN part_master sd ON p.part_id = sd.id
                GROUP BY p.id
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
                    p.bop_quantity,
                    p.umo,
                    p.supplier_id,
                    p.part_id,
                    GROUP_CONCAT(DISTINCT d.supplier_name ORDER BY d.supplier_name SEPARATOR ', ') AS supplier_name,
                    sd.part_no,
                    sd.fg_code,
                    p.bop_erp_code,
                    p.created_by,
                    p.updated_by
                FROM bop_master p
                LEFT JOIN supplier_master d ON FIND_IN_SET(d.id, p.supplier_id)
                LEFT JOIN part_master sd ON p.part_id = sd.id
                WHERE p.id = '$id'
                GROUP BY p.id";

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
        $bop_erp_code = mysqli_real_escape_string($conn, $_POST['bop_erp_code'] ?? '');
        $bop_quantity = intval($_POST['bop_quantity'] ?? 0);
        $umo = mysqli_real_escape_string($conn, $_POST['umo'] ?? '');
        $part_id = intval($_POST['part_id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $supplier_ids = $_POST['supplier_id'] ?? [];
            if (!is_array($supplier_ids)) {
                $supplier_ids = [$supplier_ids];
            }
            $supplier_ids = array_values(array_filter(array_map('intval', $supplier_ids)));
            $supplier_id = mysqli_real_escape_string($conn, implode(',', $supplier_ids));

            if ($id == "") {
                $sql = "INSERT INTO bop_master
                        (
                            bop_part_name,
                            bop_part_no,
                            bop_erp_code,
                            bop_quantity,
                            umo,
                            supplier_id,
                            part_id,
                            created_by
                        )
                        VALUES
                        (
                            '$bop_part_name',
                            '$bop_part_no',
                            '$bop_erp_code',
                            '$bop_quantity',
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
                            bop_erp_code = '$bop_erp_code',
                            bop_quantity='$bop_quantity',
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
                    bop_erp_code,
                    bop_quantity,
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
                    bop_erp_code,
                    bop_quantity,
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
                    sd.fg_code,
                    p.bop_quantity,
                    p.umo,
                    p.supplier_id,
                    p.part_id,
                    GROUP_CONCAT(DISTINCT d.supplier_name ORDER BY d.supplier_name SEPARATOR ', ') AS supplier_name,
                    sd.part_no,
                    p.bop_erp_code,
                    p.created_by,
                    p.updated_by
                FROM hist_bop_master p
                LEFT JOIN supplier_master d ON FIND_IN_SET(d.id, p.supplier_id)
                LEFT JOIN part_master sd ON p.part_id = sd.id
                GROUP BY p.id
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
                    bop_erp_code,
                    bop_quantity,
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
                    bop_erp_code,
                    bop_quantity,
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
