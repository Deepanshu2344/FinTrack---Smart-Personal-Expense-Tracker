<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch budgets for the user
    $budget_query = "SELECT category, budget_amount, frequency FROM budgets WHERE user_id=?";
    $stmt = $conn->prepare($budget_query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $budgets = [];
    while ($row = $result->fetch_assoc()) {
        $budgets[] = $row;
    }
    
    // Fetch total spending per category
    $spending_query = "SELECT category, SUM(amount) AS spent FROM expenses WHERE user_id=? GROUP BY category";
    $stmt = $conn->prepare($spending_query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $spending = [];
    while ($row = $result->fetch_assoc()) {
        $spending[$row['category']] = $row['spent'];
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'budgets' => $budgets,
        'spending' => $spending
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>