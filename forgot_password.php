<?php
session_start();
include('db_connect.php');

// Add timezone synchronization
date_default_timezone_set('Asia/Kolkata'); // Set the timezone based on your region

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Handle Forgot Password Request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'])) {
        $email = $_POST['email'];

        // Check if email exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $token = bin2hex(random_bytes(50)); // Generate token
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour")); // Expiry time

            // Insert reset token into database
            $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_token_expiry=? WHERE email=?");
            $stmt->bind_param("sss", $token, $expires, $email);
            if (!$stmt->execute()) {
                die("<p class='error'>Failed to store reset token. Try again later.</p>");
            }

            // Send Email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'fintrack15@gmail.com'; // Your Gmail
                $mail->Password   = 'kmozmpupukyzyutp'; // Replace with App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('fintrack15@gmail.com', 'FinTrack Team');
                $mail->addAddress($email, $row['full_name']);

                $reset_link = "http://localhost:8080/FinTrack/forgot_password.php?token=$token";

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset - FinTrack';
                $mail->Body    = "<h3>Hello {$row['full_name']},</h3>
                                  <p>Click the link below to reset your password. This link will expire in 1 hour.</p>
                                  <a href='$reset_link' style='color: #007bff; text-decoration: none; font-weight: bold;'>Reset Password</a>
                                  <p>If you did not request this, please ignore this email.</p>
                                  <br>
                                  <p>Best Regards,<br>FinTrack Team</p>";

                $mail->send();
                echo "<p class='success'>A password reset link has been sent to your email.</p>";
            } catch (Exception $e) {
                echo "<p class='error'>Failed to send email. Try again later.</p>";
            }
        } else {
            echo "<p class='error'>Email not found!</p>";
        }
    }
}

// Handle Reset Password Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "<p class='error'>Passwords do not match.</p>";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Update Password in DB
        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_token_expiry=NULL WHERE email=?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            echo "<p class='success'>Password has been reset successfully! <a href='signin.php'>Login here</a></p>";
        } else {
            echo "<p class='error'>Error updating password.</p>";
        }
    }
}

// Check if Token is Present in URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token=? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("<p class='error'>Invalid or expired token. Request a new password reset.</p>");
    }

    $row = $result->fetch_assoc();
    $email = $row['email'];

    // Show Reset Password Form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reset Password</title>
        <link rel="stylesheet" href="master.css">
    </head>
    <body>
        <div class="header">FinTrack</div>
        <div class="main">
            <div class="overlay">
                <h2>Reset Password</h2>
                <form method="POST" action="forgot_password.php">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <label>New Password:</label>
                    <input type="password" name="new_password" required>
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" required>
                    <button type="submit" name="reset_password">Reset Password</button>
                </form>
            </div>
        </div>
        <footer>&copy; 2025 FinTrack. All rights reserved.</footer>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
    /* Global Styles */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-image: url('images/gen1.jpg'); /* Replace with your image path */
        background-size: cover; /* Ensure image covers the full page */
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        flex-direction: column;
        min-height: 98vh;
    }
    /* Dim overlay on the background image */
    .main::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0); /* Semi-transparent overlay */
    z-index: 0;
    }

    /* Overlay Container */
    .overlay {
        position: relative;
        z-index: 1;
        background: rgba(255, 255, 255, 0.8);
        padding: 50px;
        border-radius: 12px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        max-width: 500px; /* Adjust width */
        width: 100%; /* Responsive width */
        min-height: 30vh; /* Adjust minimum height */
        text-align: center;
        border: 2px solid #005f73;
    }

    /* Header Styles */
    .header {
        background-color: #005f73; /* Teal with slight transparency */
        color: white;
        text-align: center;
        padding: 15px;
        font-size: 24px;
        font-weight: bold;
    }

    /* Main Content */
    .main {
        flex: 1; /* Keeps footer at the bottom */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .form-container {
        background-color: white; /* Clean white background for form */
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        width: 100%;
        max-width: 400px;
        padding: 30px;
        text-align: center;
    }

    h2 {
        color: #005f73; /* Teal text color */
        margin-bottom: 20px;
    }

    /* Input Fields */
    input[type="email"] {
        width: 90%; /* Centers within the container */
        max-width: 350px;
        padding: 10px;
        margin: 10px auto;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 14px;
    }

    input[type="email"]:focus {
        border-color: #28a745; /* Green border on focus */
        outline: none; /* Removes default outline */
    }

    /* Submit Button */
    button {
        width: 90%; /* Matches input field width */
        max-width: 350px;
        background-color: #005f73; /* Teal button */
        color: white;
        border: none;
        padding: 10px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
        margin-top: 10px;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #218838; /* Green hover effect */
    }

    /* Footer Styles */
    footer {
    background-color: #005f73; /* Blue color */
    color: white;
    text-align: center;
    padding: 10px;
    position: relative;
    bottom: 0;
    width: 100%;
    }
</style>
</head>
<body>
    <div class="header">FinTrack</div>
    <div class="main">
        <div class="overlay">
            <h2>Forgot Password</h2>
            <form method="POST" action="forgot_password.php">
                <label>Email:</label>
                <input type="email" name="email" required>
                <button type="submit">Send Reset Link</button>
            </form>
        </div>
    </div>
    <footer>&copy; 2025 FinTrack. All rights reserved.</footer>
</body>
</html>