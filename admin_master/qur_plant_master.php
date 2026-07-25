<?php
session_start();
include "../inc/db_cfg.php";

function send_json($payload) {
    header("Content-Type: application/json");
    echo json_encode($payload);
    exit;
}

function unit_names_sql($alias = 'p') {
    return "(SELECT GROUP_CONCAT(u.unit ORDER BY u.unit SEPARATOR ', ')
            FROM unit_master u
            WHERE FIND_IN_SET(u.id, $alias.unit_id))";
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {

// =================== GET UNIT ===================
    case 'units':
        $sql = "SELECT id, unit
                FROM unit_master
                ORDER BY unit";
        $result = mysqli_query($conn, $sql);

        if (!$result) {
            send_json([
                'status' => 'error',
                'message' => mysqli_error($conn)
            ]);
        }

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        send_json($data);
    break;
// =================== GET TABLE ===================
    case 'list':
        $sql = "SELECT
                    p.id,
                    " . unit_names_sql('p') . " AS unit,
                    p.plant_name,
                    p.created_by,
                    p.updated_by
                FROM plant_master p
                ORDER BY p.id DESC";

        $result = mysqli_query($conn, $sql);
        if (!$result) {
            send_json([
                'status' => 'error',
                'message' => mysqli_error($conn)
            ]);
        }

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        send_json($data);
    break;
// =================== GET SINGLE VALUE ===================
    case 'get':
        $id = intval($_GET['id']);

        $sql = "SELECT
                    id,
                    unit_id,
                    plant_name
                FROM plant_master
                WHERE id='$id'";

        $result = mysqli_query($conn, $sql);
        if (!$result) {
            send_json([
                'status' => 'error',
                'message' => mysqli_error($conn)
            ]);
        }

        send_json(mysqli_fetch_assoc($result));
    break;
// =================== SAVE ===================
    case 'save':    
        $id = $_POST['id'] ?? '';
        $unit_ids = $_POST['unit'] ?? [];

        if (!is_array($unit_ids)) {
            $unit_ids = [$unit_ids];
        }

        $unit_ids = array_values(array_filter(array_map('intval', $unit_ids)));
        $plant_name = mysqli_real_escape_string($conn, trim($_POST['plant_name'] ?? ''));
        $user_id = $_SESSION['user_name'] ?? '';
                // Check duplicate plant name
        if ($id == '') {
            $check = mysqli_query($conn,
                "SELECT id
                FROM plant_master
                WHERE LOWER(TRIM(plant_name)) = LOWER(TRIM('$plant_name'))");
        } else {
            $check = mysqli_query($conn,
                "SELECT id
                FROM plant_master
                WHERE LOWER(TRIM(plant_name)) = LOWER(TRIM('$plant_name'))
                AND id != '$id'");
        }

        if (mysqli_num_rows($check) > 0) {
            send_json([
                'status'  => 'error',
                'message' => 'Plant name already exists.'
            ]);
        }

        if ($user_id === '') {
            send_json([
                'status' => 'error',
                'message' => 'Session expired. Please log in again.'
            ]);
        }

        if (!$unit_ids) {
            send_json([
                'status' => 'error',
                'message' => 'Please select at least one unit.'
            ]);
        }

        if ($plant_name === '') {
            send_json([
                'status' => 'error',
                'message' => 'Plant name is required.'
            ]);
        }

        $unit_id = implode(',', $unit_ids);

        if ($id == '') {
            $sql = "INSERT INTO plant_master
                    (
                        unit_id,
                        plant_name,
                        created_by
                    )
                    VALUES
                    (
                        '$unit_id',
                        '$plant_name',
                        '$user_id'
                    )";
        } else {
            $sql = "UPDATE plant_master
                    SET
                        unit_id = '$unit_id',
                        plant_name = '$plant_name',
                        updated_by = '$user_id',
                        updated_at = NOW()
                    WHERE id = '$id'";
        }

        if (!mysqli_query($conn, $sql)) {
            send_json([
                'status' => 'error',
                'message' => mysqli_error($conn)
            ]);
        }

        $mode = ($id == '') ? 'insert' : 'update';
        $record_id = ($id == '') ? mysqli_insert_id($conn) : intval($id);

        $fetch_sql = "SELECT
                            p.id,
                            " . unit_names_sql('p') . " AS unit,
                            p.plant_name,
                            p.created_by,
                            p.updated_by
                      FROM plant_master p
                      WHERE p.id = '$record_id'";

        $fetch_result = mysqli_query($conn, $fetch_sql);
        if (!$fetch_result) {
            send_json([
                'status' => 'error',
                'message' => mysqli_error($conn)
            ]);
        }

        send_json([
            'status' => 'success',
            'mode' => $mode,
            'data' => mysqli_fetch_assoc($fetch_result)
        ]);
    break;
// =================== DELETE ===================
    case 'delete':
        $id = intval($_POST['id']);
        $user_id = $_SESSION['user_name'] ?? '';

        if ($user_id === '') {
            send_json([
                'status' => 'error',
                'message' => 'Session expired. Please log in again.'
            ]);
        }

        mysqli_begin_transaction($conn);
        try {
            $sql1 = "INSERT INTO hist_plant_master
                    (
                        id,
                        unit_id,
                        plant_name,
                        created_by,
                        created_at,
                        updated_by,
                        updated_at
                    )
                    SELECT
                        id,
                        unit_id,
                        plant_name,
                        created_by,
                        created_at,
                        '$user_id',
                        NOW()
                    FROM plant_master
                    WHERE id='$id'";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM plant_master WHERE id='$id'";
            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_commit($conn);
            send_json(['status' => 'success']);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            send_json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    break;
// =================== GET TABLE IN RECYCLE ===================
    case 'list1':
        $sql = "SELECT
                    p.id,
                    " . unit_names_sql('p') . " AS unit,
                    p.plant_name,
                    p.created_by,
                    p.updated_by
                FROM hist_plant_master p
                ORDER BY p.id DESC";

        $result = mysqli_query($conn, $sql);
        if (!$result) {
            send_json([
                'status' => 'error',
                'message' => mysqli_error($conn)
            ]);
        }

        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        send_json($data);

    break;
// =================== RESTORE ===================
    case 'restore':
        $id = intval($_POST['id']);
        $user_id = $_SESSION['user_name'] ?? '';

        if ($user_id === '') {
            send_json([
                'status' => 'error',
                'message' => 'Session expired. Please log in again.'
            ]);
        }

        mysqli_begin_transaction($conn);
        try {
            $sql1 = "INSERT INTO plant_master
                    (
                        id,
                        unit_id,
                        plant_name,
                        created_by,
                        created_at,
                        updated_by,
                        updated_at
                    )
                    SELECT
                        id,
                        unit_id,
                        plant_name,
                        created_by,
                        created_at,
                        '$user_id',
                        NOW()
                    FROM hist_plant_master
                    WHERE id='$id'";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_plant_master WHERE id='$id'";
            if (!mysqli_query($conn, $sql2)) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_commit($conn);
            send_json(['status' => 'success']);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            send_json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }

    break;
// =================== DEFALUT ===================
    default:
        send_json([
            'status' => 'Invalid Action'
        ]);
    break;

}
?>