<?php
session_start();
include "../inc/db_cfg.php";

/*
|--------------------------------------------------------------------------
| Employee Master Backend
|--------------------------------------------------------------------------
| Actions:
|   list
|   get
|   designations
|   get_sub_departments
|   save
|
| Login table is intentionally NOT modified by this file.
| Employee credentials are stored only in employee_master.
|--------------------------------------------------------------------------
*/

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

$generateTempPassword = function (int $length = 10): string {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@#';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }

    return $password;
};

$action = $_REQUEST['action'] ?? '';

/*
|--------------------------------------------------------------------------
| Helper: Return JSON and stop
|--------------------------------------------------------------------------
*/
function jsonResponse(array $data): void
{
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data);
    exit;
}

/*
|--------------------------------------------------------------------------
| Helper: Convert array/scalar select value into one ID
|--------------------------------------------------------------------------
| Frontend currently sends unit[], department[], sub_department[].
| This helper safely handles both array and normal scalar values.
|--------------------------------------------------------------------------
*/
function getMultiPostValue(string $key): string
{
    if (!isset($_POST[$key])) {
        return '';
    }

    $value = $_POST[$key];

    if (!is_array($value)) {
        $value = [$value];
    }

    $ids = [];

    foreach ($value as $item) {
        foreach (explode(',', trim((string)$item)) as $part) {
            $part = trim($part);

            if ($part !== '' && ctype_digit($part) && (int)$part > 0) {
                $ids[] = (int)$part;
            }
        }
    }

    return implode(',', array_values(array_unique($ids)));
}

/*
|--------------------------------------------------------------------------
| Helper: Escape value
|--------------------------------------------------------------------------
*/
function dbEscape(mysqli $conn, string $value): string
{
    return mysqli_real_escape_string($conn, $value);
}

switch ($action) {

    // =========================================================
    // GET EMPLOYEE TABLE
    // =========================================================
    case "list":

        $sql = "SELECT
                    e.id,
                    e.employee_name,
                    e.user_name,
                    e.email,
                    e.location,
                    e.contact_no,

                    e.unit,
                    e.department,
                    e.sub_department,
                    e.password,

                    GROUP_CONCAT(DISTINCT um.unit ORDER BY um.unit SEPARATOR ', ') AS unit_name,
                    GROUP_CONCAT(DISTINCT dm.department_name ORDER BY dm.department_name SEPARATOR ', ') AS department_name,
                    GROUP_CONCAT(DISTINCT sdm.sub_department_name ORDER BY sdm.sub_department_name SEPARATOR ', ') AS sub_department_display,

                    e.designation_id,
                    e.level,
                    e.status,
                    e.created_by,
                    e.updated_by,

                    d.designation AS designation_name

                FROM employee_master e

                LEFT JOIN designation_master d
                    ON e.designation_id = d.id

                LEFT JOIN unit_master um
                    ON FIND_IN_SET(um.id, e.unit)

                LEFT JOIN department_master dm
                    ON FIND_IN_SET(dm.id, e.department)

                LEFT JOIN sub_department_master sdm
                    ON FIND_IN_SET(sdm.id, e.sub_department)

                GROUP BY
                    e.id,
                    e.employee_name,
                    e.user_name,
                    e.email,
                    e.location,
                    e.contact_no,
                    e.unit,
                    e.department,
                    e.sub_department,
                    e.password,
                    e.designation_id,
                    e.level,
                    e.status,
                    e.created_by,
                    e.updated_by,
                    d.designation
                 ORDER BY e.id DESC";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            jsonResponse([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
        }

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        jsonResponse($data);

        break;


    // =========================================================
    // GET SINGLE EMPLOYEE
    // =========================================================
    case "get":

        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            jsonResponse([
                "status" => "error",
                "message" => "Invalid employee ID."
            ]);
        }

        $sql = "SELECT
                    e.id,
                    e.employee_name,
                    e.user_name,
                    e.email,
                    e.location,
                    e.contact_no,

                    e.unit,
                    e.department,
                    e.sub_department,
                    e.password,

                    GROUP_CONCAT(DISTINCT um.unit ORDER BY um.unit SEPARATOR ', ') AS unit_name,
                    GROUP_CONCAT(DISTINCT dm.department_name ORDER BY dm.department_name SEPARATOR ', ') AS department_name,
                    GROUP_CONCAT(DISTINCT sdm.sub_department_name ORDER BY sdm.sub_department_name SEPARATOR ', ') AS sub_department_display,

                    e.designation_id,
                    e.level,
                    e.status,
                    e.created_by,
                    e.updated_by,

                    d.designation AS designation_name

                FROM employee_master e

                LEFT JOIN designation_master d
                    ON e.designation_id = d.id

                LEFT JOIN unit_master um
                    ON FIND_IN_SET(um.id, e.unit)

                LEFT JOIN department_master dm
                    ON FIND_IN_SET(dm.id, e.department)

                LEFT JOIN sub_department_master sdm
                    ON FIND_IN_SET(sdm.id, e.sub_department)

                WHERE e.id = '$id'
                GROUP BY
                    e.id,
                    e.employee_name,
                    e.user_name,
                    e.email,
                    e.location,
                    e.contact_no,
                    e.unit,
                    e.department,
                    e.sub_department,
                    e.password,
                    e.designation_id,
                    e.level,
                    e.status,
                    e.created_by,
                    e.updated_by,
                    d.designation
                 LIMIT 1";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            jsonResponse([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
        }

        $row = mysqli_fetch_assoc($result);

        if (!$row) {
            jsonResponse([
                "status" => "error",
                "message" => "Employee not found."
            ]);
        }

        jsonResponse($row);

        break;


    // =========================================================
    // GET DESIGNATIONS
    // =========================================================
    case "designations":

        $sql = "SELECT
                    id,
                    designation
                FROM designation_master
                ORDER BY designation";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            jsonResponse([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
        }

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        jsonResponse($data);

        break;


    // =========================================================
    // GET SUB DEPARTMENTS
    // =========================================================
    case "get_sub_departments":

        $unitIds = $_POST['unit'] ?? $_GET['unit'] ?? [];
        $departmentIds = $_POST['department'] ?? $_GET['department'] ?? [];

        if (!is_array($unitIds)) {
            $unitIds = [$unitIds];
        }

        if (!is_array($departmentIds)) {
            $departmentIds = [$departmentIds];
        }

        $unitIds = array_values(array_filter(array_map('intval', $unitIds)));
        $departmentIds = array_values(array_filter(array_map('intval', $departmentIds)));

        if (count($unitIds) === 0 || count($departmentIds) === 0) {
            jsonResponse([]);
        }

        $unitList = implode(',', $unitIds);
        $departmentList = implode(',', $departmentIds);

        $sql = "SELECT
                    id,
                    unit_id,
                    department_id,
                    sub_department_name
                FROM sub_department_master
                WHERE unit_id IN ($unitList)
                  AND department_id IN ($departmentList)
                ORDER BY sub_department_name ASC";

        $result = mysqli_query($conn, $sql);

        if (!$result) {
            jsonResponse([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);
        }

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = [
                "id" => (int)$row['id'],
                "unit_id" => (int)$row['unit_id'],
                "department_id" => (int)$row['department_id'],
                "sub_department_name" => $row['sub_department_name']
            ];
        }

        jsonResponse($data);

        break;


    // =========================================================
    // SAVE / UPDATE EMPLOYEE
    // =========================================================
    case "save":

        header("Content-Type: application/json; charset=utf-8");

        $id = trim((string)($_POST['id'] ?? ''));

        $employee_name = dbEscape(
            $conn,
            trim((string)($_POST['employee_name'] ?? ''))
        );

        $user_name = dbEscape(
            $conn,
            trim((string)($_POST['user_name'] ?? ''))
        );

        $email = dbEscape(
            $conn,
            trim((string)($_POST['email'] ?? ''))
        );

        $location = dbEscape(
            $conn,
            trim((string)($_POST['location'] ?? ''))
        );

        $contact_no = dbEscape(
            $conn,
            trim((string)($_POST['contact_no'] ?? ''))
        );

        /*
         * Frontend sends:
         * unit[]
         * department[]
         * sub_department[]
         *
         * Convert them into one selected ID.
         */
        $unit = dbEscape(
            $conn,
            getMultiPostValue('unit')
        );

        $department = dbEscape(
            $conn,
            getMultiPostValue('department')
        );

        $sub_department = dbEscape(
            $conn,
            getMultiPostValue('sub_department')
        );

        $designation_id = dbEscape(
            $conn,
            trim((string)($_POST['designation_id'] ?? ''))
        );

        $level = dbEscape(
            $conn,
            trim((string)($_POST['level'] ?? ''))
        );

        $status = dbEscape(
            $conn,
            trim((string)($_POST['status'] ?? 'Active'))
        );

        $user_id = $_SESSION['user_name'] ?? '';
        $creator = substr((string)$user_id, 0, 10);

        /*
         * Basic validation
         */
        if (
            $employee_name === '' ||
            $user_name === '' ||
            $email === '' ||
            $location === '' ||
            $contact_no === '' ||
            $unit === '' ||
            $department === '' ||
            $sub_department === '' ||
            $designation_id === '' ||
            $level === ''
        ) {
            jsonResponse([
                "status" => "error",
                "message" => "Please fill all required fields."
            ]);
        }

        mysqli_begin_transaction($conn);

        try {

            // =====================================================
            // INSERT NEW EMPLOYEE
            // =====================================================
            if ($id === '') {

                // Generate the employee password.
                // This password is stored ONLY in employee_master.
                $temp_password = $generateTempPassword(10);

                $sql = "INSERT INTO employee_master
                        (
                            employee_name,
                            user_name,
                            email,
                            location,
                            contact_no,
                            unit,
                            department,
                            sub_department,
                            designation_id,
                            level,
                            status,
                            password,
                            created_by
                        )
                        VALUES
                        (
                            '$employee_name',
                            '$user_name',
                            '$email',
                            '$location',
                            '$contact_no',
                            '$unit',
                            '$department',
                            '$sub_department',
                            '$designation_id',
                            '$level',
                            '$status',
                            '$temp_password',
                            '$user_id'
                        )";

                if (!mysqli_query($conn, $sql)) {
                    throw new Exception(mysqli_error($conn));
                }

                mysqli_commit($conn);

                echo json_encode([
                    "status" => "success",
                    "temp_password" => $temp_password,
                    "username" => $email
                ]);

                exit;
            }


            // =====================================================
            // UPDATE EXISTING EMPLOYEE
            // =====================================================

            $employeeId = intval($id);

            if ($employeeId <= 0) {
                throw new Exception("Invalid employee ID.");
            }

            $sql = "UPDATE employee_master
                    SET
                        employee_name = '$employee_name',
                        user_name = '$user_name',
                        email = '$email',
                        location = '$location',
                        contact_no = '$contact_no',
                        unit = '$unit',
                        department = '$department',
                        sub_department = '$sub_department',
                        designation_id = '$designation_id',
                        level = '$level',
                        status = '$status',
                        updated_by = '$user_id',
                        updated_at = NOW()
                    WHERE id = '$employeeId'";

            if (!mysqli_query($conn, $sql)) {
                throw new Exception(mysqli_error($conn));
            }
            // =====================================================
            // COMMIT
            // =====================================================


            mysqli_commit($conn);

            $response = [
                "status" => "success"
            ];



            echo json_encode($response);

        } catch (Throwable $e) {

            mysqli_rollback($conn);

            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }

        break;


    // =========================================================
    // INVALID ACTION
    // =========================================================
    default:

        jsonResponse([
            "status" => "error",
            "message" => "Invalid Action"
        ]);

        break;
}
?>
