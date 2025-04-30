<?php
session_start();
include('db_connect.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; 

$error_message = ""; // Initialize an error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate if passwords match
    if ($password !== $confirmPassword) {
        $error_message = "Passwords do not match!";
    }
    // Validate phone number format (must be exactly 10 digits)
    elseif (!preg_match('/^\d{10}$/', $phone)) {
        $error_message = "Phone number must be exactly 10 digits.";
    }
    // Validate password format
    elseif (!preg_match('/^(?=(.*[a-z]){1})(?=(.*[A-Z]){1})(?=(.*\d){1})(?=(.*[@$!%*#?&]){1}).{8,}$/', $password)) {
        $error_message = "Password must be at least 8 characters, include 1 uppercase letter, 1 lowercase letter, 1 digit, and 1 special character (@$!%*#?&).";
    } else {
        // Check if email already exists
        $sql_check_email = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql_check_email);

        if ($result->num_rows > 0) {
            $error_message = "This email is already registered. Please use a different email.";
        } else {
            // Generate a unique user ID
            $userId = uniqid("user_");

            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into database
            $sql = "INSERT INTO users (user_id, full_name, email, phone, password) VALUES ('$userId', '$fullName', '$email', '$phone', '$hashedPassword')";
            if ($conn->query($sql) === TRUE) {
                // Send Email
                $mail = new PHPMailer(true);
                try {
                    // SMTP Configuration
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com'; // Your SMTP Server
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'fintrack15@gmail.com'; // Your Gmail
                    $mail->Password   = 'kmozmpupukyzyutp'; // Replace with App Password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Sender & Recipient
                    $mail->setFrom('fintrack15@gmail.com', 'FinTrack Team');
                    $mail->addAddress($email, $row['fullName']);

                    // Email Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Welcome to FinTrack!';
                    $mail->Body    = "<h3>Hello {$row['fullName']},</h3>
                                      <p>Thank you for creating an account with <strong>FinTrack</strong>.</p>
                                      <p>We are excited to have you on board. Start tracking your finances effortlessly!</p>
                                      <br>
                                      <p>Best Regards,<br>FinTrack Team</p>";

                    // Send Email
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }
                // Registration successful
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - FinTrack</title>
    <link rel="stylesheet" href="master.css"> <!-- Link to master CSS file -->
    <script>
        function validatePhone(input) {
            // Remove non-numeric characters and enforce 10 digits
            input.value = input.value.replace(/\D/g, '').slice(0, 10);
        }
    </script>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div>FinTrack</div>
    </div>

    <!-- Main Section -->
    <div class="main">
        <div class="overlay">
            <h1 style="color: #005f73;">Create Your FinTrack Account</h1>
            <?php
            if (!empty($error_message)) {
                echo "<div style='color: red; font-size: 14px; font-weight: bold; margin-bottom: 15px; padding: 10px; border: 1px solid red; border-radius: 5px; width: 100%; box-sizing: border-box;'>$error_message</div>";
            }
            ?>
            <form method="post" action="signup.php">
                <label for="full_name" class="form-label">Full Name:</label><br>
                <input type="text" name="full_name" id="full_name" required class="form-input"><br>

                <label for="email" class="form-label">Email:</label><br>
                <input type="email" name="email" id="email" required class="form-input"><br>

                <label for="phone" class="form-label">Phone:</label><br>
                <input type="text" name="phone" id="phone" required class="form-input" maxlength="10" pattern="\d{10}" title="Phone number must be exactly 10 digits" oninput="validatePhone(this)"><br>

                <label for="password" class="form-label">Password:</label><br>
                <input type="password" name="password" id="password" required class="form-input"><br>

                <label for="confirm_password" class="form-label">Confirm Password:</label><br>
                <input type="password" name="confirm_password" id="confirm_password" required class="form-input"><br><br>

                <button type="submit" class="form-button">Register</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        &copy; 2025 FinTrack. All rights reserved.
    </footer>
</body>
</html>
