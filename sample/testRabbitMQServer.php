#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($username,$password)
{
    // lookup username in database
    // check password
    return true;
    //return false if not valid
}

function doValidate($sessionId, $authToken)
{
    // Validate session with both session_id and auth_token
    // This is a placeholder - should check against database
    return ["returnCode" => 0, "message" => "Valid session"];
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return ["returnCode" => 1, "message" => "ERROR: unsupported message type"];
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'] ?? '', $request['password'] ?? '');
    case "validate_session":
      $sessionId = $request['sessionId'] ?? $request['session_id'] ?? '';
      $authToken = $request['authToken'] ?? $request['auth_token'] ?? $request['token'] ?? '';
      return doValidate($sessionId, $authToken);
    case "register":
      return ["returnCode" => 1, "message" => "Registration not implemented in this server"];
    default:
      return ["returnCode" => 1, "message" => "Unknown request type: " . $request['type']];
  }
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

