<?php
$hostName = 'localhost';
$userName = 'root';
$password = '';
$database = 'bkspace';
$conn = @new mysqli( $hostName, $userName, $password, $database);
if ($conn->connect_error) {
    die('Kết nối thất bại !!! '.$conn->connect_error);
} 
?>

