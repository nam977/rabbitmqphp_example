<?php
header("Content-Type: application/json");
$myjson_input = json_decode(file_get_contents("php://input"), true);

if (!isset($myjson_input["type"]))
{
	$msg = "NO POST MESSAGE SET, POLITELY FUCK OFF";
	echo json_encode($msg);
	exit(0);
}

$sql_connection = new mysqli("127.0.0.1", "testuser", 'Password1234!', "testdb");

if ($sql_connection->connect_error){
	$response = "Failed to connect to SQL Database";	
	echo json_encode($response);
	exit(0);
}

$request = $myjson_input;
$response = "unsupported request type, politely FUCK OFF";

if ($request["type"] == 'login'){
	$username = $myjson_input["uname"];
	$password = $myjson_input["pword"];
	$mysqlstatement = $sql_connection->prepare("SELECT password FROM users WHERE username = ?");
	$mysqlstatement->bind_param("s", $username);
	$mysqlstatement->execute();
	$mysqlstatement->bind_result($actual_password);
	$mysqlstatement->fetch();		
	$result = array();
	if (password_verify($password, $actual_password)){
		$result["status"] = 'success';
		$result['message'] = 'Login successful';
		echo json_encode($result);
		exit(0);
	}else{
		$result["status"] = "Error";
		$result['message'] = "Invalid Username or password!";
		echo json_encode($result);
		exit(0);
	}
}
echo json_encode(value: $response);
exit(0);

?>
