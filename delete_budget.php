<?php
session_start();
include 'db_connect.php';

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Handle the deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $user_id = $_SESSION['user_id'];
    $delete_category = $conn->real_escape_string($_POST['delete_category']);

    // SQL query to delete the budget
    $delete_query = "DELETE FROM budgets WHERE user_id='$user_id' AND category='$delete_category'";
    if ($conn->query($delete_query) === TRUE) {
        // Redirect to the budget page after deletion
        header("Location: budget.php");
        exit();
    } else {
        echo "Error: Could not delete the budget. " . $conn->error;
    }
} else {
    // Redirect to the budget page if accessed incorrectly
    header("Location: budget.php");
    exit();
}
?>
