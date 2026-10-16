<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    // =================== GET TABLE ===================
    case "list":

        $sql = "SELECT
        s.id,
        s.supplier_id,

        (
            SELECT GROUP_CONCAT(
                sup.supplier_name
                ORDER BY sup.supplier_name
                SEPARATOR ', '
            )
            FROM supplier_master sup
            WHERE FIND_IN_SET(sup.id, s.supplier_id)
        ) AS supplier_name,

        s.unit_id,
        u.unit,

        s.department_id,
        d.department_name,

        s.sub_department_id,
        sd.sub_department_name,

        s.category_id,
        cat.category_name,

        s.sub_category_name,
        s.created_by,
        s.updated_by

    FROM sub_category_master s

    LEFT JOIN unit_master u
        ON u.id = s.unit_id

    LEFT JOIN department_master d
        ON d.id = s.department_id

    LEFT JOIN sub_department_master sd
        ON sd.id = s.sub_department_id

    LEFT JOIN category_master cat
        ON cat.id = s.category_id

    ORDER BY s.id DESC";

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);

        break;
    // =================== GET SINGLE VALUE ===================
    case "get":

        $id = intval($_GET['id'] ?? 0);

        $sql = "SELECT
        s.id,

        s.supplier_id,

        (
            SELECT GROUP_CONCAT(
                sup.supplier_name
                ORDER BY sup.supplier_name
                SEPARATOR ', '
            )
            FROM supplier_master sup
            WHERE FIND_IN_SET(sup.id, s.supplier_id)
        ) AS supplier_name,

        s.unit_id,
        u.unit,

        s.department_id,
        d.department_name,

        s.sub_department_id,
        sd.sub_department_name,

        s.category_id,
        cat.category_name,

        s.sub_category_name,
        s.created_by,
        s.updated_by

    FROM sub_category_master s

    LEFT JOIN unit_master u
        ON u.id = s.unit_id

    LEFT JOIN department_master d
        ON d.id = s.department_id

    LEFT JOIN sub_department_master sd
        ON sd.id = s.sub_department_id

    LEFT JOIN category_master cat
        ON cat.id = s.category_id

    WHERE s.id = '$id'";

        $result = mysqli_query($conn, $sql);

        header("Content-Type: application/json");
        echo json_encode(mysqli_fetch_assoc($result));

        break;
    // =================== SAVE ===================
    case "save":
        $id = $_POST['id'] ?? "";
        $supplier_id_raw = trim($_POST['supplier_id'] ?? '');
        $supplier_ids = array_values(array_unique(array_filter(array_map('intval', explode(',', $supplier_id_raw)))));
        $supplier_id = mysqli_real_escape_string($conn, implode(',', $supplier_ids));
        $unit_id = intval($_POST['unit_id'] ?? 0);
        $department_id = intval($_POST['department_id'] ?? 0);
        $sub_department_id = mysqli_real_escape_string(
            $conn,
            $_POST['sub_department_id'] ?? ''
        );
        $category_id = intval($_POST['category_id'] ?? 0);

        $sub_category_name = mysqli_real_escape_string(
            $conn,
            $_POST['sub_category_name'] ?? ''
        );
        $user_id = $_SESSION['user_name'] ?? 0;

        $supplier_value = !empty($supplier_ids) ? "'$supplier_id'" : "NULL";
        // Check duplicate Category + Sub Category + Department
        if ($unit_id <= 0) {
            echo "Please select Unit.";
            exit;
        }

        if ($department_id <= 0) {
            echo "Please select Department.";
            exit;
        }

        if ($sub_department_id === '') {
            echo "Please select Sub Department.";
            exit;
        }

        if ($category_id <= 0) {
            echo "Please select Category.";
            exit;
        }

        if ($sub_category_name === '') {
            echo "Please enter Sub Category Name.";
            exit;
        }

        // Check duplicate Unit + Department + Sub Department + Category + Sub Category

        if ($id == "") {

            $check = mysqli_query(
                $conn,
                "SELECT id
         FROM sub_category_master
         WHERE unit_id = '$unit_id'
         AND department_id = '$department_id'
         AND sub_department_id = '$sub_department_id'
         AND category_id = '$category_id'
         AND LOWER(TRIM(sub_category_name)) =
             LOWER(TRIM('$sub_category_name'))"
            );
        } else {

            $check = mysqli_query(
                $conn,
                "SELECT id
         FROM sub_category_master
         WHERE unit_id = '$unit_id'
         AND department_id = '$department_id'
         AND sub_department_id = '$sub_department_id'
         AND category_id = '$category_id'
         AND LOWER(TRIM(sub_category_name)) =
             LOWER(TRIM('$sub_category_name'))
         AND id != '$id'"
            );
        }

        if (mysqli_num_rows($check) > 0) {
            echo "This sub category already exists for the selected Unit, Department, Sub Department and Category.";
            exit;
        }
        if ($id == "") {

            $sql = "INSERT INTO sub_category_master(
                supplier_id,
                unit_id,
                department_id,
                sub_department_id,
                category_id,
                sub_category_name,
                created_by
            ) VALUES(
                $supplier_value,
                '$unit_id',
                '$department_id',
                '$sub_department_id',
                '$category_id',
                '$sub_category_name',
                '$user_id'
            )";
        } else {

            $sql = "UPDATE sub_category_master
            SET
                supplier_id = $supplier_value,
                unit_id = '$unit_id',
                department_id = '$department_id',
                sub_department_id = '$sub_department_id',
                category_id = '$category_id',
                sub_category_name = '$sub_category_name',
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
    INSERT INTO hist_sub_category_master(
        id,
        supplier_id,
        unit_id,
        department_id,
        sub_department_id,
        category_id,
        sub_category_name,
        created_by,
        created_at,
        updated_by,
        updated_at
    )
    SELECT
        id,
        supplier_id,
        unit_id,
        department_id,
        sub_department_id,
        category_id,
        sub_category_name,
        created_by,
        created_at,
        '$user_id',
        NOW()
    FROM sub_category_master
    WHERE id = '$id'
";
            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM sub_category_master WHERE id = '$id'";

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

        $sql = "SELECT
        h.id,
        h.supplier_id,

        (
            SELECT GROUP_CONCAT(
                sup.supplier_name
                ORDER BY sup.supplier_name
                SEPARATOR ', '
            )
            FROM supplier_master sup
            WHERE FIND_IN_SET(sup.id, h.supplier_id)
        ) AS supplier_name,

        h.unit_id,
        u.unit,

        h.department_id,
        d.department_name,

        h.sub_department_id,
        sd.sub_department_name,

        h.category_id,
        cat.category_name,

        h.sub_category_name,
        h.created_by,
        h.updated_by

    FROM hist_sub_category_master h

    LEFT JOIN unit_master u
        ON u.id = h.unit_id

    LEFT JOIN department_master d
        ON d.id = h.department_id

    LEFT JOIN sub_department_master sd
        ON sd.id = h.sub_department_id

    LEFT JOIN category_master cat
        ON cat.id = h.category_id

    ORDER BY h.id DESC";

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
            $sql1 = "
    INSERT INTO sub_category_master
    (
        id,
        supplier_id,
        unit_id,
        department_id,
        sub_department_id,
        category_id,
        sub_category_name,
        created_by,
        created_at,
        updated_by,
        updated_at
    )
    SELECT
        id,
        supplier_id,
        unit_id,
        department_id,
        sub_department_id,
        category_id,
        sub_category_name,
        created_by,
        created_at,
        '$user_id',
        NOW()
    FROM hist_sub_category_master
    WHERE id = '$id'
";

            if (!mysqli_query($conn, $sql1)) {
                throw new Exception(mysqli_error($conn));
            }

            $sql2 = "DELETE FROM hist_sub_category_master WHERE id = '$id'";

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
    // =================== GET SUPPLIER ===================
    case "suppliers":
        $sql = "SELECT id, supplier_name
                FROM supplier_master
                ORDER BY supplier_name ASC";

        $result = mysqli_query($conn, $sql);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
        break;
    // =================== GET CATEGORY ===================
    case "categories":

        $sql = "SELECT id, category_name
                FROM category_master
                ORDER BY category_name ASC";

        $result = mysqli_query($conn, $sql);
        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);
        break;

    // =================== GET SUB DEPARTMENT ===================
    case "departments":

        $sql = "SELECT
                id,
                department_name
            FROM department_master
            ORDER BY department_name ASC";

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);

        break;

    // =================== GET UNIT ===================
    case "units":

        $sql = "SELECT
                id,
                unit
            FROM unit_master
            ORDER BY unit ASC";

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);

        break;

    // =================== GET SUB DEPARTMENT ===================
    case "sub_departments":

        $unit_id = intval($_GET['unit_id'] ?? 0);
        $department_id = intval($_GET['department_id'] ?? 0);

        $sql = "SELECT
                id,
                sub_department_name
            FROM sub_department_master
            WHERE 1=1";

        if ($unit_id > 0) {
            $sql .= " AND unit_id = '$unit_id'";
        }

        if ($department_id > 0) {
            $sql .= " AND department_id = '$department_id'";
        }

        $sql .= " ORDER BY sub_department_name ASC";

        $result = mysqli_query($conn, $sql);

        $data = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }

        header("Content-Type: application/json");
        echo json_encode($data);

        break;
    // =================== DEFAULT ===================
    default:
        echo json_encode([
            "status" => "Invalid Action"
        ]);
        break;
}
