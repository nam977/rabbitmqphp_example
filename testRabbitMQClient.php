<?php
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

