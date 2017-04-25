<?php
//Grabs the progress variable
session_start();
$percentage = $_SESSION["percentage"];
$job = $_SESSION["job"];
header('Content-Type: application/json');
echo json_encode(array("job" =>$job, "percentage" =>$percentage));
?>
