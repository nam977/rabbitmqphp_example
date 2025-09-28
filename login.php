<?php
if (!isset($_POST["type"]))
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

$request = $_POST;
$response = "unsupported request type, politely FUCK OFF";

switch ($request["type"]){
	/*
	case "login":
		$response = "login, yeah we can do that";
	break;
	*/
	case "login":
		$username = $_POST["uname"];
		$password = $_POST["pword"];
		$mysqlstatement = $sql_connection->prepare("SELECT password FROM users WHERE username = ?");
		$mysqlstatement->bind_param("s", $username);
		$mysqlstatement->execute();
		$mysqlstatement->bind_result($actual_password);
		$mysqlstatement->fetch();		

		if (password_verify($password, $actual_password)){
			$response = "login, yeah we can do that";
		}else{
			$response = "Invalid Username or password!";
		}

		$ok = password_verify($password, $hash);
		error_log("DEBUG verify_result=".($ok?'1':'0')." pw_len=".strlen($password));
		break;
}
echo json_encode($response);
exit(0);

?>
