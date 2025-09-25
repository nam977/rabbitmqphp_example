#!/usr/bin/php
<?php

$mydb = new mysqli('127.0.0.1','testuser','Password1234!','testdb');

if ($mydb->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}

echo "successfully connected to database".PHP_EOL;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
	$username = $_POST["username"];
	$password = $_POST["password"];
	$email = $_POST["email"];
}
$pass_salt = bin2hex(random_bytes(16));
$password_hash = password_hash($password . $pass_salt, PASSWORD_BCRYPT);

$query = "INSERT INTO users (username, password_hash, password_salt, email) VALUES ('$username',  '$password_hash', '$pass_salt', '$email')";

$response = $mydb->query($query);
if ($mydb->errno != 0)
{
	echo "failed to execute query:".PHP_EOL;
	echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	exit(0);
}


?>
