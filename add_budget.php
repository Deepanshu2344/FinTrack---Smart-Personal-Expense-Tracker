<?php
session_start();
include 'db_connect.php';

// Set Content-Type header to application/json
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "User not logged in."]);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $category = trim($_POST['category']);
    $budget_amount = trim($_POST['budget_amount']);
    $frequency = trim($_POST['frequency']);

    // Basic Validation
    if (empty($category)) {
        echo json_encode(["status" => "error", "message" => "Category cannot be empty."]);
        exit;
    }

    if (!is_numeric($budget_amount) || $budget_amount <= 0) {
        echo json_encode(["status" => "error", "message" => "Budget amount must be a positive number."]);
        exit;
    }


    // Check for duplicate budget
    $check_query = "SELECT 1 FROM budgets WHERE user_id=? AND category=? AND frequency=?";
    $stmt = $conn->prepare($check_query);
    if ($stmt === false) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("iss", $user_id, $category, $frequency);
    if ($stmt->execute() === false) {
        echo json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]);
        exit;
    }
    $result = $stmt->get_result();
    if ($result === false) {
        echo json_encode(["status" => "error", "message" => "Get result failed: " . $stmt->error]);
        exit;
    }
    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Budget already exists for this category and frequency."]);
        exit;
    }
    $stmt->close();

    // Insert budget
    $insert_query = "INSERT INTO budgets (user_id, category, budget_amount, frequency) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);

    if ($stmt === false) {
        echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("isds", $user_id, $category, $budget_amount, $frequency);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
$conn->close();
?>