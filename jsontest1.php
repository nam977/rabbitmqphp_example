<?php
$myjson_input = array(
	"type" => "Login",
	"username" => "1234",
	"password" => "1234",
	"message" => "Hello World!",
);

$return_var = 0;
$myjson_input = json_encode($myjson_input,  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$output = [];
exec("./testRabbitMQClient.php " . escapeshellarg($myjson_input), $output, $return_var);
print_r($output);
print_r($return_var);
print_r($myjson_input);