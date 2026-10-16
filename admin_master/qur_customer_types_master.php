<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    // =================== GET TABLE ===================
    case "list":
        $sql = "SELECT
                    id,
                    customer_types,
                    segment,
                    sub_segment,
                    created_by,
                    updated_by
                FROM customer_types_master
                ORDER BY id DESC";

        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        header("Content-Type: application/json");
        echo json_encode($data);
        break;

    // =================== GET SINGLE RECORD / EDIT ===================
    case "get":
        $id = intval($_GET['id'] ?? 0);
        $sql = "SELECT
                    id,
                    customer_types,
                    segment,
                    sub_segment,
                    created_by,
                    updated_by
                FROM customer_types_master
                WHERE id = '$id'";

        $result = mysqli_query($conn, $sql);
        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
        break;

    // =================== SAVE / UPDATE ===================
    case "save":
        $id = intval($_POST['id'] ?? 0);
        $customer_types = trim(
            mysqli_real_escape_string(
                $conn,
                $_POST['customer_types'] ?? ''
            )
        );
        $segment = trim(
            mysqli_real_escape_string(
                $conn,
                $_POST['segment'] ?? ''
            )
        );
        $sub_segment = trim(
            mysqli_real_escape_string(
                $conn,
                $_POST['sub_segment'] ?? ''
            )
        );
        $user_id = $_SESSION['user_name'] ?? 0;

        if ($customer_types == '') {
            echo "Customer Type is required.";
            exit;
        }
        if ($segment == '') {
            echo "Segment is required.";
            exit;
        }
        if ($sub_segment == '') {
            echo "Sub Segment is required.";
            exit;
        }
        if ($id == 0) {
            $check = mysqli_query(
                $conn,
                "SELECT id
                 FROM customer_types_master
                 WHERE LOWER(TRIM(customer_types))
                       = LOWER(TRIM('$customer_types'))
                 AND LOWER(TRIM(segment))
                       = LOWER(TRIM('$segment'))
                 AND LOWER(TRIM(sub_segment))
                       = LOWER(TRIM('$sub_segment'))"
            );
        } else {
            $check = mysqli_query(
                $conn,
                "SELECT id
                 FROM customer_types_master
                 WHERE LOWER(TRIM(customer_types))
                       = LOWER(TRIM('$customer_types'))
                 AND LOWER(TRIM(segment))
                       = LOWER(TRIM('$segment'))
                 AND LOWER(TRIM(sub_segment))
                       = LOWER(TRIM('$sub_segment'))
                 AND id != '$id'"
            );
        }
        if (!$check) {
            echo mysqli_error($conn);
            exit;
        }
        if (mysqli_num_rows($check) > 0) {
            echo "Customer Type with same Segment and Sub Segment already exists.";
            exit;
        }

        if ($id == 0) {
            $sql = "INSERT INTO customer_types_master
                    (
                        customer_types,
                        segment,
                        sub_segment,
                        created_by,
                        created_at
                    )
                    VALUES
                    (
                        '$customer_types',
                        '$segment',
                        '$sub_segment',
                        '$user_id',
                        NOW()
                    )";
        }

        // =================== UPDATE ===================
        else {
            $sql = "UPDATE customer_types_master
                    SET
                        customer_types = '$customer_types',
                        segment = '$segment',
                        sub_segment = '$sub_segment',
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
                INSERT INTO hist_customer_types_master
                (
                    id,
                    customer_types,
                    segment,
                    sub_segment,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    customer_types,
                    segment,
                    sub_segment,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM customer_types_master
                WHERE id = '$id'
            ";
            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }
            $sql2 = "
                DELETE FROM customer_types_master
                WHERE id = '$id'
            ";
            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_commit($conn);
            header("Content-Type: application/json");
            echo json_encode([
                "status" => "success"
            ]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            header("Content-Type: application/json");
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        break;

    // =================== GET TABLE IN RECYCLE ===================
    case "list1":

        $sql = "SELECT
                    id,
                    customer_types,
                    segment,
                    sub_segment,
                    created_by,
                    updated_by
                FROM hist_customer_types_master
                ORDER BY id DESC";

        $result = mysqli_query($conn, $sql);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);

        break;

    // =================== RESTORE ===================
    case "restore":

        $id = intval($_POST['id'] ?? 0);

        $user_id = $_SESSION['user_name'] ?? 0;

        mysqli_begin_transaction($conn);

        try {

            // =================== RESTORE RECORD ===================

            $sql1 = "
                INSERT INTO customer_types_master

                (
                    id,
                    customer_types,
                    segment,
                    sub_segment,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    customer_types,
                    segment,
                    sub_segment,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_customer_types_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }


            // =================== DELETE FROM HISTORY ===================

            $sql2 = "
                DELETE FROM hist_customer_types_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(mysqli_error($conn));
            }


            mysqli_commit($conn);

            header("Content-Type: application/json");

            echo json_encode([
                "status" => "success"
            ]);
        } catch (Exception $e) {

            mysqli_rollback($conn);

            header("Content-Type: application/json");

            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }

        break;

    // =================== DEFAULT ===================
    default:

        header("Content-Type: application/json");

        echo json_encode([
            "status" => "error",
            "message" => "Invalid Action"
        ]);

        break;
}
