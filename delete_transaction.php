<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Check if 'id' is set in the query string
if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Ensure the transaction belongs to the logged-in user
    $check_transaction_query = "SELECT * FROM expenses WHERE expense_id='$transaction_id' AND user_id='$user_id'";
    $result = $conn->query($check_transaction_query);

    if ($result->num_rows > 0) {
        // Proceed with deletion if the transaction exists
        $delete_query = "DELETE FROM expenses WHERE expense_id='$transaction_id' AND user_id='$user_id'";

        if ($conn->query($delete_query) === TRUE) {
            // Redirect back to the dashboard after successful deletion
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Error deleting transaction: " . $conn->error;
        }
    } else {
        // If the transaction does not exist or doesn't belong to the user, show an error
        echo "Transaction not found or unauthorized action.";
    }
} else {
    // If no transaction ID is provided, show an error
    echo "Transaction ID not specified.";
}
?>
