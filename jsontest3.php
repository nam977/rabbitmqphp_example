<?php
$myjson_input = array(
	"type" => "Login",
	"username" => "fakepassword",
	"password" => "fakepassword",
	"message" => "test message"
);

$myjson_input = json_encode($myjson_input);

exec("./testRabbitMQClient.php jsonname '$myjson_input'", $output, $return_var);
print_r($output);
print_r($return_var);
print_r($myjson_input);
