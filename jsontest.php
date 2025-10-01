<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once(__DIR__ . '/rabbitMQLib.inc');

// Ensure the class rabbitMQClient is defined in rabbitMQLib.inc
if (!class_exists('rabbitMQClient')) {
	die("Class 'rabbitMQClient' not found. Please check rabbitMQLib.inc.");
}

$myObjec= new rabbitMQClient("testRabbitMQ.ini","testServer");
$msg = $argv[1] ?? "test message";
$myObjec->request["type"] = "Login";
$myObjec->request["username"] ="nathan";
$myObjec->request["password"] = "1234";
$myObjec->request["message"] = $msg;


$myJson = json_encode($myObjec->request, JSON_PRETTY_PRINT);
$response = $myObjec->send_request($myObjec->request);
print_r($response);
?>
