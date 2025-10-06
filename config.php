<?php
$currentFile = basename($_SERVER["SCRIPT_FILENAME"]);

$fileMap = [
    "jsontest1.php" => "jsontest1.php",
    "jsontest2.php" => "jsontest2.php",
    "jsontest3.php" => "jsontest3.php",
    "jsontest4.php" => "jsontest4.php",
    "jsontest5.php" => "jsontest5.php",
    "testRabbitMQClient.php" => "testRabbitMQClient.php",
];

$jsonFile = $fileMap[$currentFile] ?? "default.json";

$myjson_input = json_decode(file_get_contents($jsonFile), true);
?>