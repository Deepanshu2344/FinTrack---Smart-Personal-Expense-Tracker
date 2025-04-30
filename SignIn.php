<?php
session_start();
include('db_connect.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Query to find user by Email
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $row['password'])) {
                // Store user details in the session
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['full_name'] = $row['full_name'];
                $_SESSION['email'] = $row['email'];

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
                    $mail->addAddress($email, $row['full_name']);

                    // Email Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Login Alert - FinTrack';
                    $mail->Body    = "<h3>Hello {$row['full_name']},</h3>
                                      <p>We noticed a login to your <strong>FinTrack</strong> account.</p>
                                      <p>If this was you, no action is needed. If you did not log in, please reset your password immediately.</p>
                                      <br>
                                      <p>Best Regards,<br>FinTrack Team</p>";

                    // Send Email
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
                }

                // Redirect to Dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error_message = "Invalid password!";
            }
        } else {
            $error_message = "Email not found!";
        }
    } else {
        $error_message = "Please fill in both email and password!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FinTrack</title>
    <link rel="stylesheet" href="master.css">
</head>
<body>   
    <div class="header">
        <div>FinTrack</div>
    </div>    

    <div class="main">
        <div class="overlay">
            <h1 style="color: #005f73;">Login to FinTrack</h1>
            <?php if (isset($error_message)) { echo "<p style='color: red; font-weight: bold;'>$error_message</p>"; } ?>
            <form method="POST" action="signin.php">
                <label for="email" class="form-label">Email:</label><br>
                <input type="email" name="email" id="email" required class="form-input"><br>
                <label for="password" class="form-label">Password:</label><br>
                <input type="password" name="password" id="password" required class="form-input"><br><br>
                <button type="submit" class="form-button">Login</button>
                <div style="text-align: center; margin-top: 10px;">
                <a href="forgot_password.php" style="text-decoration: none; color: #007bff;">Forgot Password</a></div>
            </form>
        </div>
    </div>

    <footer>&copy; 2025 FinTrack. All rights reserved.</footer>
</body>
</html>
