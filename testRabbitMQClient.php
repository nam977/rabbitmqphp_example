<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

header('Content-Type: application/json; charset=utf-8');

function json_response($data, $code=200) {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  exit;
} 

if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    json_response(['status' => 'error', 'message' => 'Invalid request method.'], 405);
}

$raw = file_get_contents('php://input');
$ct = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($ct, 'application/json') !== false && $raw !== '') {
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_response(['status' => 'error', 'message' => 'Invalid JSON format.'], 400);
    }
} else {
    $data = $_POST;
}

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$type = strtolower($data['type'] ?? $data['action'] ?? '');
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$email = $data['email'] ?? '';    

if (!in_array($type, ['login', 'register', 'test'])) {
    json_response(['status' => 'error', 'message' => 'Invalid action type.'], 400);
}

$request = [
    'type' => $type,
    'username' => $username,
    'password' => $password,
    'email' => $email,
    'message' => $msg
];

try{
  $client = new rabbitMQClient("testRabbitMQ.ini","testServer");

  $rmq = $client->send_request($request);

  if (is_array($rmq)) {
      json_response([
        'status' => $rmq['status'] ?? 'success',
        'message' => $rmq['message'] ?? null,
        'data' => $rmq['data'] ?? $rmq
      ]);
  } else{
    json_response(['status' => 'success', 'data' => $rmq]);
  }
} catch(Exception $e){
  json_response(['status' => 'error', 'message' => 'Server error: '.$e->getMessage()], 500);  
}

$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

