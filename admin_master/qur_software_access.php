<?php
session_start();
include "../inc/db_cfg.php";

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case "module":
        header("Content-Type:application/json");
        $sql=mysqli_query($conn,"
                SELECT id,module_name
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

    case "save_module":
        header("Content-Type:application/json");
        $id = $_POST['id'] ?? "";
        $module_name = mysqli_real_escape_string(
            $conn,
            trim($_POST['module_name'])
        );
        $user = $_SESSION['user_name'];
        $check = mysqli_query($conn,"
            SELECT id
            FROM module_master
            WHERE module_name='$module_name'
            AND id<>'$id'
            AND status='1'
        ");
        if(mysqli_num_rows($check)>0){
            echo json_encode([
                "status"=>"error",
                "message"=>"Software Module already exists."
            ]);
            exit;
        }
        $user=$_SESSION['user_name'];
        if($id==""){
            mysqli_query($conn,"
                INSERT INTO module_master(
                    module_name,
                    created_by
                )
                VALUES(
                    '$module_name',
                    '$user'
                )
            ");
            echo json_encode([
                "status"=>"success",
                "message"=>"Software Module Added Successfully."
            ]);
        }else{
            mysqli_query($conn,"
                UPDATE module_master
                SET
                module_name='$module_name',
                updated_by='$user',
                updated_at=NOW()
                WHERE id='$id'
            ");
            echo json_encode([
                "status"=>"success",
                "message"=>"Software Module Updated Successfully."
            ]);
        }
    break;
    
    case "load_master":
        $module = intval($_GET['module_id']);
        $selected = [];
        $sql = mysqli_query($conn,"
            SELECT master_id
            FROM module_permission
            WHERE module_id='$module'
        ");
        while($row=mysqli_fetch_assoc($sql))
        {
            $selected[]=$row['master_id'];
        }
        $master=mysqli_query($conn,"
            SELECT
                id,
                master_name
            FROM master_master
            WHERE status='1'
            ORDER BY master_name
        ");
        echo '<div class="row">';
        while($row=mysqli_fetch_assoc($master)){
            $checked=in_array($row['id'],$selected)
            ?"checked":"";
            echo '
            <div class="col-md-4 mb-3">
                <div class="form-check">
                    <input
                        type="checkbox"
                        class="form-check-input master-checkbox"
                        name="master_id[]"
                        value="'.$row['id'].'"
                        id="master'.$row['id'].'"
                        '.$checked.'>
                    <label
                        class="form-check-label"
                        for="master'.$row['id'].'">

                        '.$row['master_name'].'
                    </label>
                </div>
            </div>
            ';
        }
        echo '</div>';
    break;
    
    case "save_permission":
        header("Content-Type:application/json");
        $module = intval($_POST['module_id']);
        $user = $_SESSION['user_name'];
        $newPermissions = isset($_POST['master_id']) ? array_map('intval', $_POST['master_id']) : [];
        mysqli_begin_transaction($conn);
        try {
             // Get existing permissions
            $existingPermissions = [];
            $sql = mysqli_query($conn,"
                SELECT master_id
                FROM module_permission
                WHERE module_id='$module'
                AND status='1'
            ");
            while($row = mysqli_fetch_assoc($sql)){
                $existingPermissions[] = (int)$row['master_id'];
            }
            // Compare
            $toInsert = array_diff($newPermissions, $existingPermissions);
            $toDelete = array_diff($existingPermissions, $newPermissions);
            $toKeep   = array_intersect($existingPermissions, $newPermissions);
            // Insert new permissions
            foreach($toInsert as $master){
                mysqli_query($conn,"
                    INSERT INTO module_permission(
                        module_id,
                        master_id,
                        status,
                        created_by,
                        created_at
                    )
                    VALUES(
                        '$module',
                        '$master',
                        '1',
                        '$user',
                        NOW()
                    )
                ");
            }
            // Remove unchecked permissions
            foreach($toDelete as $master){
                mysqli_query($conn,"
                    DELETE FROM module_permission
                    WHERE module_id='$module'
                    AND master_id='$master'
                ");
            }
            // Update audit fields for unchanged permissions
            foreach($toKeep as $master){
                mysqli_query($conn,"
                    UPDATE module_permission
                    SET
                        updated_by='$user',
                        updated_at=NOW()
                    WHERE module_id='$module'
                    AND master_id='$master'
                ");
            }
            mysqli_commit($conn);
            echo json_encode([
                "status" => "success",
                "message" => "Permission Saved Successfully."
            ]);
        } catch(Exception $e) {
            mysqli_rollback($conn);
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    break;

    case "list":
        header("Content-Type:application/json");
        $sql = mysqli_query($conn,"
            SELECT
                mm.id,
                mm.module_name,
                COALESCE(
                    GROUP_CONCAT(
                        mst.master_name
                        ORDER BY mst.master_name
                        SEPARATOR ', '
                    ),
                    'No Masters Assigned'
                ) AS masters,
                mm.created_by,
                mm.updated_by
            FROM module_master mm
            LEFT JOIN module_permission mp
                ON mm.id = mp.module_id
                AND mp.status='1'
            LEFT JOIN master_master mst
                ON mst.id = mp.master_id
            WHERE mm.status='1'
            GROUP BY
                mm.id,
                mm.module_name,
                mm.created_by,
                mm.updated_by
            ORDER BY mm.module_name
        ");
        $data = [];
        while($row = mysqli_fetch_assoc($sql)){
            $data[] = $row;
        }
        echo json_encode($data);
    break;
    
    case "get_permission":
        header("Content-Type:application/json");
        $id = intval($_GET['id']);
        $sql = mysqli_query($conn,"
        SELECT module_id
        FROM module_permission
        WHERE module_id='$id'
        LIMIT 1
        ");
        echo json_encode(mysqli_fetch_assoc($sql));
    break;

    case "sync_master":
        header("Content-Type:application/json");
        $tables = mysqli_query($conn,"
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_name LIKE '%_master'
            AND table_name NOT LIKE 'hist_%'
        ");
        $count = 0;
        $ignoreTables = [
            'master_master',
            'module_master',
            'module_permission'
        ];
        while ($row = mysqli_fetch_assoc($tables)) {
            $table = $row['table_name'];
            // Ignore history tables
            if (strpos($table, 'hist_') === 0) {
                continue;
            }
            // Ignore internal tables
            if (in_array($table, $ignoreTables)) {
                continue;
            }
            $master_name = ucwords(str_replace("_", " ", $table));
            $check = mysqli_query($conn,"
                SELECT id
                FROM master_master
                WHERE table_name='$table'
            ");
            if (mysqli_num_rows($check) == 0) {
                mysqli_query($conn,"
                    INSERT INTO master_master
                    (master_name, table_name, status)
                    VALUES
                    ('$master_name', '$table', 1)
                ");
                $count++;
            }
        }
        echo json_encode([
            "status"=>"success",
            "message"=>"$count master(s) synchronized."
        ]);
    break;

    default:
        header("Content-Type:application/json");
        echo json_encode([
            "status"=>"error",
            "message"=>"Invalid Action"
        ]);
    break;
}
?>