<?php
session_start();
include "../inc/db_cfg.php";
$action = $_REQUEST['action'] ?? '';

switch($action){
//========================= EMPLOYEES ============================
    case "employees":
        header("Content-Type:application/json");
        $sql = mysqli_query($conn,"
            SELECT
                id,
                employee_name
            FROM employee_master
            WHERE status='Active'
            ORDER BY employee_name
        ");
        $data=[];
        while($row=mysqli_fetch_assoc($sql)){
            $data[]=$row;
        }
        echo json_encode($data);
    break;
//======================== SUB DEPARTMENTS =============================
    case "sub_departments":
        header("Content-Type:application/json");
        $sql=mysqli_query($conn,"
            SELECT
                s.id,
                u.unit,
                d.department_name,
                s.sub_department_name
            FROM sub_department_master s
            LEFT JOIN unit_master u
                ON s.unit_id=u.id
            LEFT JOIN department_master d
                ON s.department_id=d.id
            ORDER BY
                u.unit,
                d.department_name,
                s.sub_department_name
        ");
        $data=[];
        while($row=mysqli_fetch_assoc($sql)){
            $data[]=$row;
        }
        echo json_encode($data);
    break;
//======================== MODULES =============================
    case "modules":
        header("Content-Type:application/json");
        $sql=mysqli_query($conn,"
            SELECT
                id,
                module_name
            FROM module_master
            WHERE status='1'
            ORDER BY module_name
        ");
        $data=[];
        while($row=mysqli_fetch_assoc($sql)){
            $data[]=$row;
        }
        echo json_encode($data);
    break;
//=========================== LIST ==========================
    case "list":
        header("Content-Type:application/json");
        $sql=mysqli_query($conn,"
            SELECT
                ua.id,
                e.employee_name,
                COALESCE((
                        SELECT GROUP_CONCAT(
                            CONCAT(
                                u.unit,' | ',
                                d.department_name,' | ',
                                s.sub_department_name)
                            ORDER BY
                                u.unit,
                                d.department_name,
                                s.sub_department_name
                            SEPARATOR ', ')
                        FROM sub_department_master s
                        LEFT JOIN unit_master u
                            ON s.unit_id=u.id
                        LEFT JOIN department_master d
                            ON s.department_id=d.id
                        WHERE FIND_IN_SET(s.id,ua.sub_department_ids)),''
                ) AS sub_departments,
                COALESCE((
                        SELECT GROUP_CONCAT(
                            module_name
                            ORDER BY module_name
                            SEPARATOR ', ')
                        FROM module_master
                        WHERE FIND_IN_SET(id,ua.module_ids)
                    ),'') AS modules,
                ua.created_by,
                ua.updated_by
            FROM user_access ua
            LEFT JOIN employee_master e
                ON ua.employee_id=e.id
            ORDER BY e.employee_name");
        $data=[];
        while($row=mysqli_fetch_assoc($sql)){
            $data[]=$row;
        }
        echo json_encode($data);
    break;
//============================ GET =========================
    case "get":
        
        $id=intval($_GET['id']);
        
        $sql = "SELECT id,employee_id,sub_department_ids,module_ids
                FROM user_access
                WHERE id='$id'";
        
        $result = mysqli_query($conn, $sql);
        
        echo json_encode(mysqli_fetch_assoc($result));
    break;
//====================== GET BY EMPLOYEE ======================
    case "get_by_employee":
        header("Content-Type:application/json");
        $employee_id = intval($_GET['employee_id'] ?? 0);
        $sql = mysqli_query($conn,"
            SELECT
                id,
                employee_id,
                sub_department_ids,
                module_ids
            FROM user_access
            WHERE employee_id='$employee_id'
            LIMIT 1
        ");
        $row = mysqli_fetch_assoc($sql);
        if (!$row) {
            echo json_encode([
                "status" => "not_found",
                "message" => "No access record found for this employee."
            ]);
            break;
        }
        echo json_encode([
            "status" => "success",
            "data" => $row
        ]);
    break;
//========================== SAVE ===========================
    case "save":
        header("Content-Type:application/json");
        $employee_id = intval($_POST['employee_id'] ?? 0);
        
        $sub_department_ids = isset($_POST['sub_department_ids'])
            ? implode(",", array_map('intval', (array)$_POST['sub_department_ids']))
            : "";
        $module_ids = isset($_POST['module_ids'])
            ? implode(",", array_map('intval', (array)$_POST['module_ids']))
            : "";
        $user = mysqli_real_escape_string($conn, $_SESSION['user_name'] ?? 'system');

        if ($employee_id <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Please select an employee."
            ]);
            break;
        }

        mysqli_begin_transaction($conn);
        try{
            // Check whether employee already has access
            $check=mysqli_query($conn,"
                SELECT id
                FROM user_access
                WHERE employee_id='$employee_id'
                LIMIT 1
            ");
            if ($check === false) {
                throw new Exception("Access lookup failed: " . mysqli_error($conn));
            }
            if(mysqli_num_rows($check)>0){
                $row=mysqli_fetch_assoc($check);
                $id=$row['id'];
                $sql="
                    UPDATE user_access
                    SET
                        sub_department_ids='$sub_department_ids',
                        module_ids='$module_ids',
                        updated_by='$user',
                        updated_at=NOW()
                    WHERE id='$id'
                ";
                if(!mysqli_query($conn,$sql)){
                    throw new Exception(mysqli_error($conn));
                }
                $mode="update";
            }else{
                $sql="
                    INSERT INTO user_access(
                        employee_id,
                        sub_department_ids,
                        module_ids,
                        created_by,
                        created_at
                    )VALUES(
                        '$employee_id',
                        '$sub_department_ids',
                        '$module_ids',
                        '$user',
                        NOW()
                    )";
                if(!mysqli_query($conn,$sql)){
                    throw new Exception(mysqli_error($conn));
                }
                $id=mysqli_insert_id($conn);
                $mode="insert";
            }
            $result=mysqli_query($conn,"
                SELECT
                    ua.id,
                    e.employee_name,(
                        SELECT GROUP_CONCAT(
                            CONCAT(
                                u.unit,' | ',
                                d.department_name,' | ',
                                s.sub_department_name)
                            ORDER BY
                                u.unit,
                                d.department_name,
                                s.sub_department_name
                            SEPARATOR ', ')
                        FROM sub_department_master s
                        LEFT JOIN unit_master u
                            ON s.unit_id=u.id
                        LEFT JOIN department_master d
                            ON s.department_id=d.id
                        WHERE FIND_IN_SET(s.id,ua.sub_department_ids)
                    ) AS sub_departments,(
                        SELECT GROUP_CONCAT(
                            module_name
                            ORDER BY module_name
                            SEPARATOR ', ')
                        FROM module_master
                        WHERE FIND_IN_SET(id,ua.module_ids)
                    ) AS modules,
                    ua.created_by,
                    ua.updated_by
                FROM user_access ua
                LEFT JOIN employee_master e
                    ON ua.employee_id=e.id
                WHERE ua.id='$id'
            ");
            if ($result === false) {
                throw new Exception("Saved, but response lookup failed: " . mysqli_error($conn));
            }
            mysqli_commit($conn);
            echo json_encode([
                "status"=>"success",
                "mode"=>$mode,
                "data"=>mysqli_fetch_assoc($result)
            ]);
        }catch(Exception $e){
            mysqli_rollback($conn);
            echo json_encode([
                "status"=>"error",
                "message"=>$e->getMessage()
            ]);
        }
    break;
//========================== DELETE ===========================
    case "delete":
        header("Content-Type:application/json");
        $id=intval($_POST['id']);
        if(mysqli_query($conn,"
            DELETE FROM user_access
            WHERE id='$id'
        ")){
            echo json_encode([
                "status"=>"success"
            ]);
        }else{
            echo json_encode([
                "status"=>"error",
                "message"=>mysqli_error($conn)
            ]);
        }
    break;
//========================= DEFAULT ============================
    default:
        header("Content-Type:application/json");
        echo json_encode([
            "status"=>"error",
            "message"=>"Invalid Action"
        ]);
    break;
}
?>