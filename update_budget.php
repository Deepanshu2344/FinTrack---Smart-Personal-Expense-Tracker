<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? '';
    $budget_amount = $_POST['budget_amount'] ?? '';
    $frequency = $_POST['frequency'] ?? '';

    error_log("DEBUG - Incoming Data: Category: $category, Budget Amount: $budget_amount, Frequency: $frequency");

    if (empty($category) || empty($budget_amount) || empty($frequency)) {
        error_log("DEBUG - Missing Fields");
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit();
    }

    if (!is_numeric($budget_amount) || $budget_amount <= 0) {
        error_log("DEBUG - Invalid Budget Amount");
        echo json_encode(["status" => "error", "message" => "Budget amount must be a positive number"]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE budgets SET budget_amount = ?, frequency = ? WHERE user_id = ? AND category = ?");
    if ($stmt) {
        $stmt->bind_param("dsss", $budget_amount, $frequency, $user_id, $category);

        if ($stmt->execute()) {
            error_log("DEBUG - Budget Updated Successfully");
            echo json_encode([
                "status" => "success",
                "message" => "Budget updated successfully",
                "category" => $category,
                "budget_amount" => $budget_amount,
                "frequency" => $frequency
            ]);
        } else {
            error_log("DEBUG - SQL Error: " . $stmt->error);
            echo json_encode(["status" => "error", "message" => "Failed to update budget"]);
        }

        $stmt->close();
    } else {
        error_log("DEBUG - Prepare Statement Error: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "Failed to prepare statement"]);
    }

    $conn->close();
} else {
    error_log("DEBUG - Invalid Request Method");
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
