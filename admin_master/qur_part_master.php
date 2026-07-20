<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case "list":
                $sql = "SELECT
                    p.id,
                    p.part_name,
                    p.part_no,
                    p.fg_code,
                    p.im_code,
                    p.department_id,
                    p.sub_department_id,
                    p.customer_id,
                    d.department_name,
                    sd.sub_department_name,
                    c.customer_name,
                    c.sub_customer,
                    p.created_by,
                    p.updated_by
                FROM part_master p
                LEFT JOIN department_master d ON p.department_id = d.id
                LEFT JOIN sub_department_master sd ON p.sub_department_id = sd.id
                LEFT JOIN customer_master c ON p.customer_id = c.id
                ORDER BY p.id DESC";

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
                    p.id,
                    p.part_name,
                    p.part_no,
                    p.fg_code,
                    p.im_code,
                    p.department_id,
                    p.sub_department_id,
                    p.customer_id,
                    d.department_name,
                    sd.sub_department_name,
                    c.customer_name,
                    c.sub_customer,
                    p.created_by,
                    p.updated_by
                FROM part_master p
                LEFT JOIN department_master d ON p.department_id = d.id
                LEFT JOIN sub_department_master sd ON p.sub_department_id = sd.id
                LEFT JOIN customer_master c ON p.customer_id = c.id
                WHERE p.id = '$id'";

        $result = mysqli_query($conn, $sql);
        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
        break;

    case "departments":
        $sql = "SELECT id, department_name
                FROM department_master
                ORDER BY department_name";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        header("Content-Type: application/json");
        echo json_encode($data);
        break;

    case "sub_departments":
        $sql = "SELECT s.id, s.department_id, d.department_name, s.sub_department_name
                FROM sub_department_master s
                LEFT JOIN department_master d ON s.department_id = d.id
                ORDER BY sub_department_name";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        header("Content-Type: application/json");
        echo json_encode($data);
        break;

    case "customers":
        $sql = "SELECT id, customer_name, sub_customer
                FROM customer_master
                ORDER BY customer_name";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        header("Content-Type: application/json");
        echo json_encode($data);
        break;

    case "save":
        header("Content-Type: application/json");

        $id = $_POST['id'] ?? "";
        $part_name = mysqli_real_escape_string($conn, $_POST['part_name'] ?? '');
        $part_no = mysqli_real_escape_string($conn, $_POST['part_no'] ?? '');
        $fg_code = mysqli_real_escape_string($conn, $_POST['fg_code'] ?? '');
        $im_code = mysqli_real_escape_string($conn, $_POST['im_code'] ?? '');
        $sub_department_id = intval($_POST['sub_department_id'] ?? 0);
        $customer_id = intval($_POST['customer_id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $dept_result = mysqli_query($conn, "SELECT department_id FROM sub_department_master WHERE id = '$sub_department_id' LIMIT 1");
            if (!$dept_result || mysqli_num_rows($dept_result) === 0) {
                throw new Exception("Invalid sub department selected.");
            }

            $dept_row = mysqli_fetch_assoc($dept_result);
            $department_id = intval($dept_row['department_id']);

            if ($id == "") {
                $sql = "INSERT INTO part_master
                        (
                            part_name,
                            part_no,
                            fg_code,
                            im_code,
                            department_id,
                            sub_department_id,
                            customer_id,
                            created_by
                        )
                        VALUES
                        (
                            '$part_name',
                            '$part_no',
                            '$fg_code',
                            '$im_code',
                            '$department_id',
                            '$sub_department_id',
                            '$customer_id',
                            '$user_id'
                        )";
            } else {
                $sql = "UPDATE part_master
                        SET
                            part_name = '$part_name',
                            part_no = '$part_no',
                            fg_code = '$fg_code',
                            im_code = '$im_code',
                            department_id = '$department_id',
                            sub_department_id = '$sub_department_id',
                            customer_id = '$customer_id',
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

    case "delete":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO hist_part_master
                (
                    id,
                    part_name,
                    part_no,
                    fg_code,
                    im_code,
                    department_id,
                    sub_department_id,
                    customer_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    part_name,
                    part_no,
                    fg_code,
                    im_code,
                    department_id,
                    sub_department_id,
                    customer_id,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM part_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM part_master WHERE id = '$id'";
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
                    p.id,
                    p.part_name,
                    p.part_no,
                    p.fg_code,
                    p.im_code,
                    p.department_id,
                    p.sub_department_id,
                    p.customer_id,
                    d.department_name,
                    sd.sub_department_name,
                    c.customer_name,
                    c.sub_customer,
                    p.created_by,
                    p.updated_by
                FROM hist_part_master p
                LEFT JOIN department_master d ON p.department_id = d.id
                LEFT JOIN sub_department_master sd ON p.sub_department_id = sd.id
                LEFT JOIN customer_master c ON p.customer_id = c.id
                ORDER BY p.id DESC";

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
                INSERT INTO part_master
                (
                    id,
                    part_name,
                    part_no,
                    fg_code,
                    im_code,
                    department_id,
                    sub_department_id,
                    customer_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    part_name,
                    part_no,
                    fg_code,
                    im_code,
                    department_id,
                    sub_department_id,
                    customer_id,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_part_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_part_master WHERE id = '$id'";
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
        echo json_encode(["status" => "Invalid Action"]);
        break;
}
?>
