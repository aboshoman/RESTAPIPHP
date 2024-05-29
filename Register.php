<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

header('Content-type: application/x-www-form-urlencoded');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !empty($_POST['fullName']) && !empty($_POST['phoneNumber']) && !empty($_POST['emailID'])
        && !empty($_POST['userName']) && !empty($_POST['userPassword'])
    ) {
        $fullName = $_POST['fullName'];
        $phoneNumber = $_POST['phoneNumber'];
        $emailID = $_POST['emailID'];
        $userName = $_POST['userName'];
        $userPassword = $_POST['userPassword'];
        try {
            require 'DBConnect.php';

            // Check for duplicate user
            $SELECT__USER__SQL = "SELECT * FROM `users` WHERE users.email_id=:emailID;";
            $duplicate__user__statement = $con->prepare($SELECT__USER__SQL);
            $duplicate__user__statement->bindParam(':emailID', $emailID, PDO::PARAM_STR);
            $duplicate__user__statement->execute();
            $duplicate__user__flag = $duplicate__user__statement->rowCount();

            if ($duplicate__user__flag > 0) {
                http_response_code(409); // Conflict
                $server__response__error = array(
                    "code" => http_response_code(),
                    "status" => false,
                    "message" => "هذا المستخدم مسجل بالفعل"
                );
                echo json_encode($server__response__error);
            } else {
                // Encrypt user password
                $password__hash = password_hash($userPassword, PASSWORD_DEFAULT);
                $data__parameters = [
                    "fullName" => $fullName,
                    "phoneNumber" => $phoneNumber,
                    "emailID" => $emailID,
                    "userName" => $userName,
                    "userPassword" => $password__hash
                ];

                // Insert data into the database
                $SQL__INSERT__QUERY = "INSERT INTO `users`(
                                                        `full_name`,
                                                        `phone_number`,
                                                        `email_id`,
                                                        `username`,
                                                        `password`
                                                    )
                                                    VALUES(
                                                        :fullName,
                                                        :phoneNumber,
                                                        :emailID,
                                                        :userName,
                                                        :userPassword
                                                    );";
                $insert__data__statement = $con->prepare($SQL__INSERT__QUERY);
                $insert__data__statement->execute($data__parameters);
                $insert__record__flag = $insert__data__statement->rowCount();

                if ($insert__record__flag > 0) {
                    $server__response__success = array(
                        "code" => http_response_code(201), // Created
                        "status" => true,
                        "message" => "تم إنشاء المستخدم بنجاح"
                    );
                    echo json_encode($server__response__success);
                } else {
                    http_response_code(404);
                    $server__response__error = array(
                        "code" => http_response_code(404),
                        "status" => false,
                        "message" => "فشل في إنشاء المستخدم. يرجى المحاولة مرة أخرى."
                    );
                    echo json_encode($server__response__error);
                }
            }
        } catch (Exception $ex) {
            http_response_code(404);
            $server__response__error = array(
                "code" => http_response_code(404),
                "status" => false,
                "message" => "!عفوًا!! حدث خطأ ما" . $ex->getMessage()
            );
            echo json_encode($server__response__error);
        } // end of try/catch
    } else {
        http_response_code(404);
        $server__response__error = array(
            "code" => http_response_code(404),
            "status" => false,
            "message" => "Invalid API parameters! Please contact the administrator or refer to the documentation for assistance."
        );
        echo json_encode($server__response__error);
    } // end of Parameters IF Condition
} else {
    http_response_code(404);
    $server__response__error = array(
        "code" => http_response_code(404),
        "status" => false,
        "message" => "Bad Request"
    );
    echo json_encode($server__response__error);
}