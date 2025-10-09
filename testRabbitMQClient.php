<?php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Vary: Origin');

require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('cookiesetter.php');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Handle preflight request
    http_response_code(200);
    exit(0);
}

header('Content-Type: application/json; charset=utf-8');

function json_response($data, $code=200): void {
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  exit;
} 

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    json_response(['status' => 'error', 'message' => 'Invalid request method.'], 405);
}

$type = $input['type'] ?? '';

if ($type === 'register'){
  json_response(['status' => 'success', 'message' => 'Registration Successful']);
} elseif ($type === 'login'){
  json_response(['status' => 'success', 'message' => 'Login Successful']);
} else{
  json_response(['status' => 'error', 'message' => 'Unknown type.'], 400);
}

if (stripos($ct, needle: 'application/json') !== false && $raw !== '') {
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_response(['status' => 'error', 'message' => 'Invalid JSON format.'], 400);
    }
} else {
    $data = $_POST;
}

$client = new rabbitMQClient("testRabbitMQ.ini","sharedServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$type = strtolower($data['type'] ?? '');
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';
$email = $data['email'] ?? '';    
$session_id_val = $data['sessioId'] ?? $data['sessionid'] ?? '';

if (!in_array($type, ['login', 'register', 'validate_session'], true)) {
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
  $client = new rabbitMQClient("testRabbitMQ.ini","sharedServer");

  $rmq = $client->send_request($request);

  if (is_array($rmq)) {
    $rmq = ['returnCode' => 99, 'message' => (string) $rmq];
  }

  $ok = ($rmq['returnCode'] ?? 1) === 0;

  if ($ok && isset($rmq['session']) && is_array($rmq['session'])) {
      // Set session cookie
      create_my_session_cookie($rmq['session']);
  }

  if ($type === 'validate_session' && !$ok) {
    erase_my_session_cookies();
  }
  json_response([
    'status' => $ok ? 'success' : 'error',
    'message' => $rmq['message'] ?? null,
    'data' => $rmq['data'] ?? $rmq,
    'cookie_set' => $ok && isset($rmq['session']),
    'session_valid' => $type === 'validate_session' ? $ok : null
  ], $ok ? 200 : 400);
} catch(Exception $e){
  json_response(['status' => 'error', 'message' => 'Server error: '.$e->getMessage()], 500);  
}

$response = $client->publish($request);

echo "client received response: ".PHP_EOL;
echo "\n\n";

echo $argv[0]." END".PHP_EOL;

