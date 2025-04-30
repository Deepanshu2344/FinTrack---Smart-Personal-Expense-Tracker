<?php
// db_connect.php
$conn = new mysqli("localhost", "root", "", "fintrack");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
