<?php

session_start();

include "../inc/db_cfg.php";


$action = $_REQUEST['action'] ?? '';



/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/

$user_id = $_SESSION['user_name'] ?? '';



/*
|--------------------------------------------------------------------------
| ACTION SWITCH
|--------------------------------------------------------------------------
*/

switch ($action) {


    // =========================================================
    // GET TABLE
    // =========================================================

    case "list":

        $sql = "
            SELECT
                id,
                mis_para,
                status,
                created_by,
                created_at,
                updated_by,
                updated_at
            FROM mis_para_master
            ORDER BY id DESC
        ";

        $result = mysqli_query($conn, $sql);

        $data = [];

        if ($result) {

            while ($row = mysqli_fetch_assoc($result)) {

                $data[] = $row;

            }

        }

        header("Content-Type: application/json");

        echo json_encode($data);

        break;



    // =========================================================
    // GET SINGLE RECORD
    // =========================================================

    case "get":

        $id = intval($_GET['id'] ?? 0);


        if ($id <= 0) {

            header("Content-Type: application/json");

            echo json_encode([
                "status" => "error",
                "message" => "Invalid ID"
            ]);

            exit;

        }


        $sql = "
            SELECT
                id,
                mis_para,
                status,
                created_by,
                created_at,
                updated_by,
                updated_at
            FROM mis_para_master
            WHERE id = '$id'
            LIMIT 1
        ";


        $result = mysqli_query($conn, $sql);


        if (!$result) {

            header("Content-Type: application/json");

            echo json_encode([
                "status" => "error",
                "message" => mysqli_error($conn)
            ]);

            exit;

        }


        $row = mysqli_fetch_assoc($result);


        header("Content-Type: application/json");

        echo json_encode($row);

        break;



    // =========================================================
    // SAVE / UPDATE
    // =========================================================

    case "save":


        $id = intval($_POST['id'] ?? 0);


        // -----------------------------------------------------
        // MIS PARAMETER
        // -----------------------------------------------------

        $mis_para = trim(
            mysqli_real_escape_string(
                $conn,
                $_POST['mis_para'] ?? ''
            )
        );


        // -----------------------------------------------------
        // STATUS
        // -----------------------------------------------------

        $status = trim(
            mysqli_real_escape_string(
                $conn,
                $_POST['status'] ?? ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | REQUIRED VALIDATION
        |--------------------------------------------------------------------------
        */

        if ($mis_para == '') {

            echo "MIS Parameter Name is required.";

            exit;

        }


        if ($status == '') {

            echo "Status is required.";

            exit;

        }


        /*
        |--------------------------------------------------------------------------
        | STATUS VALIDATION
        |--------------------------------------------------------------------------
        |
        | Only Active / Inactive allowed
        |
        */

        if (
            $status !== 'active' &&
            $status !== 'inactive'
        ) {

            echo "Invalid status.";

            exit;

        }



        /*
        |--------------------------------------------------------------------------
        | DUPLICATE CHECK
        |--------------------------------------------------------------------------
        |
        | Same MIS Parameter is not allowed.
        |
        */

        if ($id == 0) {


            $check = mysqli_query(
                $conn,

                "
                SELECT id
                FROM mis_para_master
                WHERE LOWER(TRIM(mis_para))
                      = LOWER(TRIM('$mis_para'))
                "
            );


        } else {


            $check = mysqli_query(
                $conn,

                "
                SELECT id
                FROM mis_para_master
                WHERE LOWER(TRIM(mis_para))
                      = LOWER(TRIM('$mis_para'))
                AND id != '$id'
                "
            );

        }


        if (!$check) {

            echo mysqli_error($conn);

            exit;

        }


        if (mysqli_num_rows($check) > 0) {

            echo "MIS Parameter already exists.";

            exit;

        }



        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */

        if ($id == 0) {


            $sql = "
                INSERT INTO mis_para_master
                (
                    mis_para,
                    status,
                    created_by,
                    created_at
                )
                VALUES
                (
                    '$mis_para',
                    '$status',
                    '$user_id',
                    NOW()
                )
            ";


        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        else {


            /*
            |--------------------------------------------------------------------------
            | Check record exists
            |--------------------------------------------------------------------------
            */

            $checkRecord = mysqli_query(
                $conn,

                "
                SELECT id
                FROM mis_para_master
                WHERE id = '$id'
                LIMIT 1
                "
            );


            if (!$checkRecord) {

                echo mysqli_error($conn);

                exit;

            }


            if (mysqli_num_rows($checkRecord) == 0) {

                echo "MIS Parameter record not found.";

                exit;

            }



            $sql = "
                UPDATE mis_para_master
                SET
                    mis_para = '$mis_para',
                    status = '$status',
                    updated_by = '$user_id',
                    updated_at = NOW()
                WHERE id = '$id'
            ";

        }



        /*
        |--------------------------------------------------------------------------
        | EXECUTE SAVE / UPDATE
        |--------------------------------------------------------------------------
        */

        if (mysqli_query($conn, $sql)) {

            echo "success";

        } else {

            echo mysqli_error($conn);

        }


        break;



    // =========================================================
    // DELETE
    // =========================================================

    case "delete":


        $id = intval($_POST['id'] ?? 0);


        if ($id <= 0) {

            header("Content-Type: application/json");

            echo json_encode([
                "status" => "error",
                "message" => "Invalid ID"
            ]);

            exit;

        }



        /*
        |--------------------------------------------------------------------------
        | START TRANSACTION
        |--------------------------------------------------------------------------
        */

        mysqli_begin_transaction($conn);


        try {


            /*
            |--------------------------------------------------------------------------
            | CHECK RECORD
            |--------------------------------------------------------------------------
            */

            $check = mysqli_query(
                $conn,

                "
                SELECT
                    id,
                    mis_para,
                    status,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                FROM mis_para_master
                WHERE id = '$id'
                LIMIT 1
                "
            );


            if (!$check) {

                throw new Exception(
                    mysqli_error($conn)
                );

            }


            if (mysqli_num_rows($check) == 0) {

                throw new Exception(
                    "MIS Parameter record not found."
                );

            }



            /*
            |--------------------------------------------------------------------------
            | MOVE TO HISTORY
            |--------------------------------------------------------------------------
            */

            $sql1 = "

                INSERT INTO hist_mis_para_master
                (
                    id,
                    mis_para,
                    status,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )

                SELECT
                    id,
                    mis_para,
                    status,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()

                FROM mis_para_master

                WHERE id = '$id'

            ";


            if (!mysqli_query($conn, $sql1)) {

                throw new Exception(
                    mysqli_error($conn)
                );

            }



            /*
            |--------------------------------------------------------------------------
            | DELETE MAIN RECORD
            |--------------------------------------------------------------------------
            */

            $sql2 = "

                DELETE FROM mis_para_master

                WHERE id = '$id'

            ";


            if (!mysqli_query($conn, $sql2)) {

                throw new Exception(
                    mysqli_error($conn)
                );

            }



            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

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



    // =========================================================
    // GET RECYCLE BIN
    // =========================================================

    case "list1":


        $sql = "

            SELECT
                id,
                mis_para,
                status,
                created_by,
                created_at,
                updated_by,
                updated_at

            FROM hist_mis_para_master

            ORDER BY id DESC

        ";


        $result = mysqli_query($conn, $sql);


        $data = [];


        if ($result) {

            while ($row = mysqli_fetch_assoc($result)) {

                $data[] = $row;

            }

        }


        header("Content-Type: application/json");


        echo json_encode($data);


        break;



    // =========================================================
    // RESTORE
    // =========================================================

    case "restore":


        $id = intval($_POST['id'] ?? 0);


        if ($id <= 0) {

            header("Content-Type: application/json");

            echo json_encode([
                "status" => "error",
                "message" => "Invalid ID"
            ]);

            exit;

        }



        /*
        |--------------------------------------------------------------------------
        | START TRANSACTION
        |--------------------------------------------------------------------------
        */

        mysqli_begin_transaction($conn);


        try {


            /*
            |--------------------------------------------------------------------------
            | CHECK HISTORY RECORD
            |--------------------------------------------------------------------------
            */

            $check = mysqli_query(
                $conn,

                "
                SELECT
                    id,
                    mis_para,
                    status,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                FROM hist_mis_para_master
                WHERE id = '$id'
                LIMIT 1
                "
            );


            if (!$check) {

                throw new Exception(
                    mysqli_error($conn)
                );

            }


            if (mysqli_num_rows($check) == 0) {

                throw new Exception(
                    "MIS Parameter record not found in recycle bin."
                );

            }



            /*
            |--------------------------------------------------------------------------
            | CHECK DUPLICATE BEFORE RESTORE
            |--------------------------------------------------------------------------
            */

            $historyRow = mysqli_fetch_assoc($check);


            $restoreMisPara = mysqli_real_escape_string(
                $conn,
                $historyRow['mis_para']
            );


            $duplicate = mysqli_query(
                $conn,

                "
                SELECT id
                FROM mis_para_master
                WHERE LOWER(TRIM(mis_para))
                      = LOWER(TRIM('$restoreMisPara'))
                LIMIT 1
                "
            );


            if (!$duplicate) {

                throw new Exception(
                    mysqli_error($conn)
                );

            }


            if (mysqli_num_rows($duplicate) > 0) {

                throw new Exception(
                    "Same MIS Parameter already exists in active records."
                );

            }



            /*
            |--------------------------------------------------------------------------
            | RESTORE RECORD
            |--------------------------------------------------------------------------
            */

            $sql1 = "

                INSERT INTO mis_para_master
                (
                    id,
                    mis_para,
                    status,
                    created_by,
                    created_at,
                    updated_by,
                    updated_at
                )

                SELECT
                    id,
                    mis_para,
                    status,
                    created_by,
                    created_at,
                    '$user_id',
                    NOW()

                FROM hist_mis_para_master

                WHERE id = '$id'

            ";


            if (!mysqli_query($conn, $sql1)) {

                throw new Exception(
                    mysqli_error($conn)
                );

            }



            /*
            |--------------------------------------------------------------------------
            | DELETE FROM HISTORY
            |--------------------------------------------------------------------------
            */

            $sql2 = "

                DELETE FROM hist_mis_para_master

                WHERE id = '$id'

            ";


            if (!mysqli_query($conn, $sql2)) {

                throw new Exception(
                    mysqli_error($conn)
                );

            }



            /*
            |--------------------------------------------------------------------------
            | COMMIT
            |--------------------------------------------------------------------------
            */

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



    // =========================================================
    // DEFAULT
    // =========================================================

    default:


        header("Content-Type: application/json");


        echo json_encode([
            "status" => "error",
            "message" => "Invalid Action"
        ]);


        break;

}

?>