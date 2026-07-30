<?php
session_start();
include "../inc/db_cfg.php";
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case "list":
        $sql = "SELECT
                    id,
                    geo_type,
                    region_name,
                    location_name,
                    created_by,
                    updated_by
                FROM geographical_master
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
        $sql = "SELECT
                    id,
                    geo_type,
                    region_name,
                    location_name,
                    created_by,
                    updated_by
                FROM geographical_master
                WHERE id='$id'";
        $result = mysqli_query($conn, $sql);
        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
    break;

    case "save":
        $id = $_POST['id'] ?? "";
        $geo_type = mysqli_real_escape_string(
            $conn,
            $_POST['geo_type'] ?? ""
        );
        $region_name = mysqli_real_escape_string(
            $conn,
            $_POST['region_name'] ?? ""
        );
        $location_name = mysqli_real_escape_string(
            $conn,
            $_POST['location_name'] ?? ""
        );
        $user_id = $_SESSION['user_name'] ?? 0;
        if ($geo_type == "" || $region_name == "" 
        // || $location_name == ""
        ) {
            echo "Please fill all required fields.";
            break;
        }
        if ($id == "") {
            $sql = "INSERT INTO geographical_master(
                        geo_type,
                        region_name,
                        location_name,
                        created_by
                    )VALUES(
                        '$geo_type',
                        '$region_name',
                        '$location_name',
                        '$user_id'
                    )";
        } else {
            $id = intval($id);
            $sql = "UPDATE geographical_master
                    SET
                        geo_type='$geo_type',
                        region_name='$region_name',
                        location_name='$location_name',
                        updated_by='$user_id',
                        updated_at=NOW()
                    WHERE id='$id'";
        }
        if (mysqli_query($conn, $sql)) {
            echo "success";
        } else {
            echo mysqli_error($conn);
        }
    break;

    case "delete":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_name'] ?? 0;
        if (!$user_id) {
            echo json_encode([
                "status" => "error",
                "message" => "Session expired. Please log in again."
            ]);
            break;
        }
        mysqli_begin_transaction($conn);
        try {
            $sql1 = "
                INSERT INTO hist_geographical_master(
                    id,
                    geo_type,
                    region_name,
                    location_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )SELECT
                    id,
                    geo_type,
                    region_name,
                    location_name,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM geographical_master
                WHERE id='$id'
            ";
            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }
            $sql2 = "DELETE FROM geographical_master WHERE id='$id'";
            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_commit($conn);
            echo json_encode([
                "status" => "success"
            ]);
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
                    id,
                    geo_type,
                    region_name,
                    location_name,
                    created_by,
                    updated_by
                FROM hist_geographical_master
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
        $user_id = $_SESSION['user_name'] ?? 0;
        if (!$user_id) {
            echo json_encode([
                "status" => "error",
                "message" => "Session expired. Please log in again."
            ]);
            break;
        }
        mysqli_begin_transaction($conn);
        try {
            $sql1 = "
                INSERT INTO geographical_master(
                    id,
                    geo_type,
                    region_name,
                    location_name,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )SELECT
                    id,
                    geo_type,
                    region_name,
                    location_name,
                    created_by,
                    created_at,
                    '$user_id',
                    updated_at
                FROM hist_geographical_master
                WHERE id='$id'
            ";
            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }
            $sql2 = "DELETE FROM hist_geographical_master WHERE id='$id'";
            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_commit($conn);
            echo json_encode([
                "status" => "success"
            ]);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    break;

    default:
        header("Content-Type: application/json");
        echo json_encode([
            "status" => "error",
            "message" => "Invalid Action"
        ]);
    break;
}
?>