<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve user data (if needed) from the database for personalization
$sql = "SELECT full_name FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - FinTrack</title>
    <style>
        /* Global Styles */
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #005f73;
            color: white;
            padding: 15px 20px;
            font-size: 24px;
            font-weight: bold;
        }

        /* Main Content Section */
        .main {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 150px); /* Adjusts height minus header and footer */
            background: url('images/gen1.jpg') no-repeat center center / cover; /* Background image */
        }

        /* Overlay for content */
        .overlay {
            background: rgba(255, 255, 255, 0.8); /* Slightly transparent white background */
            border-radius: 10px;
            padding: 20px;
            max-width: 800px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Terms Content */
        .overlay h2 {
            color: #005f73;
            text-align: center;
            margin-bottom: 20px;
        }

        .overlay h3 {
            color: #005f73;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .overlay p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .overlay a {
            color: #005f73;
            text-decoration: underline;
        }

        /* Footer */
        footer {
            background-color: #005f73;
            color: white;
            text-align: center;
            padding: 12px;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        FinTrack
    </div>

    <!-- Main Section -->
    <div class="main">
        <div class="overlay">
            <h2>Terms and Conditions</h2>

            <h3>Introduction</h3>
            <p>Welcome to FinTrack, a service that helps you track your personal expenses and manage your budget effectively. By using this website or app, you agree to the following terms and conditions. Please read them carefully.</p>

            <h3>1. Account Registration</h3>
            <p>To access FinTrack services, you need to create an account. You agree to provide accurate and complete information during registration. It is your responsibility to maintain the confidentiality of your account information.</p>

            <h3>2. User Conduct</h3>
            <p>You agree to use FinTrack in accordance with all applicable laws and regulations. You will not use the service for any unlawful or fraudulent activities, including, but not limited to, attempting to hack, exploit, or interfere with the platform.</p>

            <h3>3. Privacy</h3>
            <p>FinTrack respects your privacy. Any data you provide to us will be used in accordance with our Privacy Policy. We take reasonable steps to protect your data, but we cannot guarantee its complete security.</p>

            <h3>4. Modifications</h3>
            <p>We reserve the right to modify or terminate FinTrack's services at any time without notice. We will do our best to inform you of any major changes, but it is your responsibility to check for updates.</p>

            <h3>5. Termination</h3>
            <p>We may suspend or terminate your account if you violate these Terms and Conditions or if we determine, in our sole discretion, that you are engaging in inappropriate activities.</p>

            <h3>6. Limitation of Liability</h3>
            <p>FinTrack is not liable for any damages, losses, or expenses resulting from the use of the platform. This includes, but is not limited to, any indirect, special, or consequential damages.</p>

            <h3>7. Changes to Terms</h3>
            <p>We may update these Terms and Conditions periodically. We will notify users of any major changes, but it is your responsibility to stay informed of any updates.</p>

            <h3>Contact Us</h3>
            <p>If you have any questions or concerns about these Terms and Conditions, please <a href="support.php">contact us</a>.</p>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        &copy; 2025 FinTrack. All rights reserved.
    </footer>
</body>
</html>
