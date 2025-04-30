<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "User not logged in."]);
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';
    $frequency = isset($_POST['frequency']) ? trim($_POST['frequency']) : '';
    $start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $next_payment_date = isset($_POST['next_payment_date']) ? trim($_POST['next_payment_date']) : null;
    
    if (empty($category) || empty($amount) || empty($frequency) || empty($start_date)) {
         echo json_encode(["status" => "error", "message" => "Category, amount, frequency, and start date are required."]);
         exit;
    }
    if (!is_numeric($amount) || $amount <= 0) {
         echo json_encode(["status" => "error", "message" => "Amount must be a positive number."]);
         exit;
    }
    
    // Convert an empty next_payment_date to null.
    if ($next_payment_date === '') {
        $next_payment_date = null;
    }
    
    // Insert the recurring expense record.
    $insert_query = "INSERT INTO recurring_expenses (user_id, category, amount, frequency, start_date, next_payment_date)
                     VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    if (!$stmt) {
         echo json_encode(["status" => "error", "message" => "Prepare error: " . $conn->error]);
         exit;
    }
    $stmt->bind_param("isdsss", $user_id, $category, $amount, $frequency, $start_date, $next_payment_date);
    if ($stmt->execute()) {
         echo json_encode(["status" => "success", "message" => "Recurring expense added successfully."]);
    } else {
         echo json_encode(["status" => "error", "message" => "Insert error: " . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>
