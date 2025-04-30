<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    exit('User not logged in.');
}

$user_id = $_SESSION['user_id'];

$query = "SELECT recurring_id, category, amount, frequency, start_date, next_payment_date 
          FROM recurring_expenses WHERE user_id='$user_id' ORDER BY start_date DESC";
$result = $conn->query($query);

if (!$result) {
    echo "Error fetching recurring expenses: " . $conn->error;
    exit();
}

echo '<div class="recurring-container">';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
       echo '<div class="recurring-item">';
       echo '<strong>' . htmlspecialchars($row['category']) . "</strong> - ₹" . number_format($row['amount'], 2);
       echo '<p>Frequency: ' . $row['frequency'] . '</p>';
       echo '<p>Start Date: ' . $row['start_date'] . '</p>';
       if (!empty($row['next_payment_date'])) {
          echo '<p>Next Payment: ' . $row['next_payment_date'] . '</p>';
       }
       // Delete button calls the JavaScript function with the recurring ID.
       echo '<button class="delete-btn" onclick="deleteRecurring(' . $row['recurring_id'] . ')">Delete</button>';
       echo '<button class="edit-btn" onclick="editRecurring(' . $row['recurring_id'] . ')">Edit</button>';
       echo '</div>';
    }
} else {
    echo '<p>No recurring expenses found.</p>';
}
echo '</div>';
?>
