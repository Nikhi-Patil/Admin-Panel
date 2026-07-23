<?php
session_start();
include "../inc/db_cfg.php";

$generateTempPassword = function(int $length = 10): string {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $password;
};

$action = $_REQUEST['action'] ?? '';

switch ($action) {
// =================== GET TABLE ===================
    case "list":
        $sql = "SELECT
                    e.id,
                    e.employee_name,
                    e.email,
                    e.location,
                    e.contact_no,
                    e.designation_id,
                    e.department_id,
                    e.level,
                    e.created_by,
                    e.updated_by,
                    d.designation AS designation_name,
                    dep.department_name AS department_name
                FROM employee_master e
                LEFT JOIN designation_master d ON e.designation_id = d.id
                LEFT JOIN department_master dep ON e.department_id = dep.id
                ORDER BY e.id DESC";

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
                    e.id,
                    e.employee_name,
                    e.email,
                    e.location,
                    e.contact_no,
                    e.designation_id,
                    e.department_id,
                    e.level,
                    e.created_by,
                    e.updated_by,
                    d.designation AS designation_name,
                    dep.department_name AS department_name
                FROM employee_master e
                LEFT JOIN designation_master d ON e.designation_id = d.id
                LEFT JOIN department_master dep ON e.department_id = dep.id
                WHERE e.id = '$id'";

        $result = mysqli_query($conn, $sql);
        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));
    break;
// =================== GET designations TABLE ===================
    case "designations":
        $sql = "SELECT id, designation
                FROM designation_master
                ORDER BY designation";
        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        header("Content-Type: application/json");
        echo json_encode($data);
    break;
// =================== GET DEPARTMENT TABLE ===================
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
// =================== GET SAVE ===================
    case "save":
        header("Content-Type: application/json");
        $id = $_POST['id'] ?? "";
        $employee_name = mysqli_real_escape_string($conn, $_POST['employee_name'] ?? '');
        $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
        $location = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
        $contact_no = mysqli_real_escape_string($conn, $_POST['contact_no'] ?? '');
        $designation_id = mysqli_real_escape_string($conn, $_POST['designation_id'] ?? '');
        $department_id = mysqli_real_escape_string($conn, $_POST['department_id'] ?? '');
        $level = mysqli_real_escape_string($conn, $_POST['level'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 0;
        $creator = substr((string)$user_id, 0, 10);

        mysqli_begin_transaction($conn);

        try {
            if ($id == "") {
                $sql = "INSERT INTO employee_master
                        (
                            employee_name,
                            email,
                            location,
                            contact_no,
                            designation_id,
                            level,
                            department_id,
                            created_by
                        )
                        VALUES
                        (
                            '$employee_name',
                            '$email',
                            '$location',
                            '$contact_no',
                            '$designation_id',
                            '$level',
                            '$department_id',
                            '$user_id'
                        )";

                if (!mysqli_query($conn, $sql)) {
                    throw new Exception(mysqli_error($conn));
                }

                $employee_id = mysqli_insert_id($conn);
                $temp_password = $generateTempPassword(10);
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

                $loginSql = "INSERT INTO login
                            (
                                username,
                                password,
                                user_id,
                                created_by,
                                first_login
                            )
                            VALUES
                            (
                                '$email',
                                '$hashed_password',
                                '$employee_id',
                                '$creator',
                                1
                            )";

                if (!mysqli_query($conn, $loginSql)) {
                    throw new Exception(mysqli_error($conn));
                }

                mysqli_commit($conn);
                echo json_encode([
                    "status" => "success",
                    "temp_password" => $temp_password,
                    "username" => $email
                ]);
            }

            $sql = "UPDATE employee_master
                    SET
                        employee_name = '$employee_name',
                        email = '$email',
                        location = '$location',
                        contact_no = '$contact_no',
                        designation_id = '$designation_id',
                        level = '$level',
                        department_id = '$department_id',
                        updated_by = '$user_id',
                        updated_at = NOW()
                    WHERE id = '$id'";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception(mysqli_error($conn));
            }

            $loginCheck = mysqli_query($conn, "SELECT id FROM login WHERE user_id = '$id' LIMIT 1");
            if ($loginCheck && mysqli_num_rows($loginCheck) > 0) {
                $loginSql = "UPDATE login
                            SET
                                username = '$email'
                            WHERE user_id = '$id'";

                if (!mysqli_query($conn, $loginSql)) {
                    throw new Exception(mysqli_error($conn));
                }
            } else {
                $temp_password = $generateTempPassword(10);
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                $loginSql = "INSERT INTO login
                            (
                                username,
                                password,
                                user_id,
                                created_by,
                                first_login
                            )
                            VALUES
                            (
                                '$email',
                                '$hashed_password',
                                '$id',
                                '$creator',
                                1
                            )";

                if (!mysqli_query($conn, $loginSql)) {
                    throw new Exception(mysqli_error($conn));
                }
            }

            mysqli_commit($conn);

            $response = ["status" => "success"];
            if (!empty($temp_password)) {
                $response["temp_password"] = $temp_password;
                $response["username"] = $email;
            }
            echo json_encode($response);

        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);

        }
    break;
// =================== GET DELETE ===================
    case "delete":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO hist_employee_master
                (
                    id,
                    employee_name,
                    email,
                    location,
                    contact_no,
                    designation_id,
                    level,
                    department_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    employee_name,
                    email,
                    location,
                    contact_no,
                    designation_id,
                    level,
                    department_id,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM employee_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM employee_master WHERE id = '$id'";
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
// =================== GET RESTORE TABLE ===================
    case "list1":
        $sql = "SELECT
                    e.id,
                    e.employee_name,
                    e.email,
                    e.location,
                    e.contact_no,
                    e.designation_id,
                    e.department_id,
                    e.level,
                    e.created_by,
                    e.updated_by,
                    d.designation AS designation_name,
                    dep.department_name AS department_name
                FROM hist_employee_master e
                LEFT JOIN designation_master d ON e.designation_id = d.id
                LEFT JOIN department_master dep ON e.department_id = dep.id
                ORDER BY e.id DESC";

        $result = mysqli_query($conn, $sql);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
    break;
// =================== GET REDTORE ===================
    case "restore":
        $id = intval($_POST['id'] ?? 0);
        $user_id = $_SESSION['user_id'] ?? 0;

        mysqli_begin_transaction($conn);

        try {
            $sql1 = "
                INSERT INTO employee_master
                (
                    id,
                    employee_name,
                    email,
                    location,
                    contact_no,
                    designation_id,
                    level,
                    department_id,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )
                SELECT
                    id,
                    employee_name,
                    email,
                    location,
                    contact_no,
                    designation_id,
                    level,
                    department_id,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()
                FROM hist_employee_master
                WHERE id = '$id'
            ";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_employee_master WHERE id = '$id'";
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
// =================== GET DEFAULT ===================
    default:
        echo json_encode(["status" => "Invalid Action"]);
    break;
}
?>