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
    $recurring_id = isset($_POST['recurring_id']) ? intval($_POST['recurring_id']) : 0;
    
    if ($recurring_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid expense ID."]);
        exit;
    }
    
    // Delete the recurring expense only if it belongs to the logged-in user.
    $delete_query = "DELETE FROM recurring_expenses WHERE recurring_id=? AND user_id=?";
    $stmt = $conn->prepare($delete_query);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Prepare error: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("ii", $recurring_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Recurring expense deleted."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete error: " . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit();
}
?>
