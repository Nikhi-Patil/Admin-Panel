<?php

session_start();

include "../inc/db_cfg.php";

header("Content-Type: application/json");

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    case "list":

        $sql = "SELECT
                    id,
                    margin_type,
                    percentage,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                FROM margin_master
                ORDER BY id DESC";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
            exit;
        }

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        echo json_encode($data);
        exit;


    case "get":

        $id = intval($_GET['id'] ?? 0);

        $sql = "SELECT
                    id,
                    margin_type,
                    percentage,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                FROM margin_master
                WHERE id = '$id'";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
            exit;
        }

        echo json_encode(mysqli_fetch_assoc($result));
        exit;


    case "save":

        $id = $_POST['id'] ?? '';

        $margin_type = $_POST['margin_type'];
        $percentage = round(
            (float)($_POST['percentage'] ?? 0),
            2
        );

        $user_id = $_SESSION['user_name'] ?? 0;


        if ($id == "") {

            $sql = "INSERT INTO margin_master
                    (
                        margin_type,
                        percentage,
                        created_by,
                        created_at
                    )
                    VALUES
                    (
                        '$margin_type',
                        '$percentage',
                        '$user_id',
                        NOW()
                    )";
        } else {

            $sql = "UPDATE margin_master
                    SET
                        margin_type ='$margin_type',
                        percentage = '$percentage',
                        updated_by = '$user_id',
                        updated_at = NOW()
                    WHERE id = '$id'";
        }


        if (mysqli_query($conn, $sql)) {

            echo json_encode([
                "status" => "success"
            ]);
        } else {

            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
        }

        exit;
    
    default:

        echo json_encode([
            "status" => "error",
            "message" => "Invalid Action"
        ]);

        exit;
}
