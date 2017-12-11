<?php

require_once '../include/DbHandler.php';
require '../libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
$user_id = NULL;

$app->post('/register', function() use ($app) {

            verifyRequiredParams(array('email', 'password', 'name', 'birthday'));
    
            $response = array();
    
            $email = $app->request->post('email');
            $password = $app->request->post('password');
            $name = $app->request->post('name');
            $birthday = $app->request->post('birthday');
    
            validateEmail($email);
    
            $email = $app->request->post('email');
            $password = $app->request->post('password');

            // validating email address
            validateEmail($email);

            $db = new DbHandler();
            $res = $db->createUser($email, $password, $name, $birthday);

            if ($res == USER_CREATED_SUCCESSFULLY) {
                $response["error"] = false;
                $response["message"] = "You are successfully registered";
                echoResponse(201, $response);
            } else if ($res == USER_CREATE_FAILED) {
                $response["error"] = true;
                $response["message"] = "Oops! An error occurred while registereing";
                echoResponse(200, $response);
            } else if ($res == USER_ALREADY_EXISTED) {
                $response["error"] = true;
                $response["message"] = "Sorry, this email already existed";
                echoResponse(200, $response);
            }
        });

$app->post('/login', function() use ($app) {
            verifyRequiredParams(array('email', 'password'));

            $email = $app->request()->post('email');
            $password = $app->request()->post('password');
            $response = array();

            $db = new DbHandler();

            if ($db->checkLogin($email, $password)) {
                $user = $db->getUserByEmail($email);

                if ($user != null) {
                    $response['error'] = false;
                    $response['email'] = $user['email'];
                    $response['name'] = $user['name'];
                    $response['birthday'] = $user['birthday'];
                    $response['created_at'] = $user['created_at'];
                } else {
                    $response['error'] = true;
                    $response['message'] = "An error occurred. Please try again";
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'Login failed. Incorrect credentials';
            }

            echoResponse(200, $response);
       });

function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field.', ';
        }
    }
    
    if ($error) {
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) '.substr($error_fields, 0, -2).' is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoResponse(400, $response);
        $app->stop();
    }
}

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');
    
    echo json_encode($response);
}

$app->run();

?>
