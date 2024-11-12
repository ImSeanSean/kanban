<?php
include "./get.php";
include "./post.php";
include "../config/database.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');

// Centralized CORS handling for OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Headers: *');
    exit;
}

$con = new Connection();
$pdo = $con->connect();

$get = new Get($pdo);
$post = new Post($pdo);



//echo $_REQUEST['request'];
if (isset($_REQUEST['request']))
    $request = explode('/', $_REQUEST['request']);
else {
    http_response_code(404);
}


switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        switch ($request[0]) {
            case 'get_tasks':
                echo json_encode($get->get_tasks($request[1]));
                break;
            case 'get_ordered_tasks':
                echo json_encode($get->get_ordered_tasks());
                break;
            default:
                http_response_code(403);
                break;
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        switch ($request[0]) {

            case 'create_task':
                echo json_encode($post->createTask($data));
                break;
            case 'update_task_order':
                echo json_encode($post->updateTask($data));
                break;
            case 'edit_task':
                echo json_encode($post->editTaskName($data));
                break;
            case 'delete_task':
                echo json_encode($post->deleteTask($data));
                break;
            default:
                http_response_code(403);
                break;
        }
        break;

    default:
        http_response_code(403);
        break;
}
