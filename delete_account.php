<?php
session_start();
include 'db_connect.php'; // Database connection file

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must be logged in to delete your account.'); window.location.href='signin.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        session_destroy();
        echo "<script>alert('Your account has been deleted successfully.'); window.location.href='signup.php';</script>";
    } else {
        echo "<script>alert('Error deleting account. Please try again.'); window.location.href='settings.php';</script>";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account - FinTrack</title>
</head>
<body>
    <h2>Are you sure you want to delete your account?</h2>
    <p>This action is irreversible.</p>
    <form method="post">
        <button type="submit">Delete My Account</button>
        <a href="settings.php">Cancel</a>
    </form>
</body>
</html>
