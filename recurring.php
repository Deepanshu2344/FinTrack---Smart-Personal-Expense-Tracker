<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$user_id = $_SESSION['user_id'];

// Fetch user's email address
$email_query = "SELECT email FROM users WHERE user_id = ?";
$stmt_email = $conn->prepare($email_query);
$stmt_email->bind_param("i", $user_id);
$stmt_email->execute();
$stmt_email->bind_result($email);
$stmt_email->fetch();
$stmt_email->close();

// Check for recurring payments due in the next two days
$today = date("Y-m-d");
$two_days_later = date("Y-m-d", strtotime("+1 days"));

$recurring_query = "SELECT category, amount, next_payment_date FROM recurring_expenses WHERE user_id = ? AND next_payment_date BETWEEN ? AND ?";
$stmt_recurring = $conn->prepare($recurring_query);
$stmt_recurring->bind_param("iss", $user_id, $today, $two_days_later);
$stmt_recurring->execute();
$result = $stmt_recurring->get_result();

while ($row = $result->fetch_assoc()) {
    $category = $row['category'];
    $amount = $row['amount'];
    $next_payment_date = $row['next_payment_date'];

    sendRecurringPaymentReminder($email, $category, $amount, $next_payment_date);
}
$stmt_recurring->close();

// Function to send recurring payment reminder email
function sendRecurringPaymentReminder($email, $category, $amount, $next_payment_date) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fintrack15@gmail.com'; // Replace with your email
        $mail->Password   = 'kmozmpupukyzyutp';    // Replace with your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and Recipient
        $mail->setFrom('fintrack15@gmail.com', 'FinTrack Notifications');
        $mail->addAddress($email);

        // Email Content
        $mail->isHTML(true);
        $mail->Subject = "Upcoming Payment Reminder: $category";
        $mail->Body    = "
            <h3>Upcoming Payment Reminder</h3>
            <p>You have a recurring payment due soon:</p>
            <ul>
                <li><strong>Category:</strong> $category</li>
                <li><strong>Amount:</strong> ₹" . number_format($amount, 2) . "</li>
                <li><strong>Next Payment Date:</strong> $next_payment_date</li>
            </ul>
            <p>Please ensure sufficient funds are available for this payment.</p>
            <p>Regards,<br>FinTrack Team</p>
        ";

        $mail->send();
        error_log("Reminder email sent successfully to $email for $category.");
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: " . $mail->ErrorInfo);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FinTrack Recurring Expense</title>
    <link rel="stylesheet" href="recurring.css" />
</head>
<body>
    <!-- Header -->
    <header>
        <div class="left-side">
            <div class="hamburger" onclick="toggleMenu()">☰</div>
        </div>
        <div class="center-side">
            <h1>FinTrack Recurring Expense</h1>
        </div>
        <div class="right-side">
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <!-- Navigation -->
    <nav>
    <ul id="menu">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="budget.php">Budget</a></li>
        <li><a href="recurring.php">Recurring Expenses</a></li>
        <li><a href="insights.html">Insights</a></li>
        <li><a href="settings.php">Settings</a></li>
    </ul>
    </nav>

    <!-- Main Content -->
    <main>
        <h2>Your Recurring Expenses</h2>
        <!-- Recurring expenses overview -->
        <div id="recurringOverview">
            <?php include 'recurring_overview.php'; ?>
        </div>
        <!-- Floating Add Button -->
        <button class="add-recurring-btn" onclick="openRecurringModal()">+</button>
    </main>

    <!-- Modal for Adding a Recurring Expense -->
    <div id="recurringModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeRecurringModal()">&times;</span>
            <h2>Add Recurring Expense</h2>
            <form id="recurringForm">
                <label for="r_category">Category:</label>
                <input type="text" name="category" id="r_category" placeholder="e.g., Subscription" required>
                
                <label for="r_amount">Amount (₹):</label>
                <input type="number" step="0.01" name="amount" id="r_amount" placeholder="e.g., 299" required>
                
                <label for="r_frequency">Frequency:</label>
                <select name="frequency" id="r_frequency" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly" selected>Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
                
                <label for="r_start_date">Start Date:</label>
                <input type="date" name="start_date" id="r_start_date" required>
                
                <label for="r_next_payment_date">Next Payment Date (Optional):</label>
                <input type="date" name="next_payment_date" id="r_next_payment_date">
                
                <button type="submit">Add Expense</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        &copy; 2025 FinTrack. All rights reserved.
    </footer>

    <script>
        // Toggle navigation menu for mobile devices
        function toggleMenu() {
            document.getElementById("menu").classList.toggle("show");
        }

    // Open the recurring modal
function openRecurringModal() {
    document.getElementById("recurringModal").style.display = "block";
}

// Close the recurring modal and reset its form
function closeRecurringModal() {
    document.getElementById("recurringModal").style.display = "none";
    document.getElementById("recurringForm").reset();
}

// Handle the recurring expense form submission via AJAX
document.getElementById("recurringForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch("add_recurring.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
       if (data.status === "success") {
           closeRecurringModal();
           updateRecurringOverview();
       } else {
           alert("Error: " + data.message);
       }
    })
    .catch(error => {
       alert("Error: Unable to process your request.");
       console.error(error);
    });
});

// Function to update the recurring expenses overview dynamically
function updateRecurringOverview() {
    fetch("recurring_overview.php")
    .then(response => response.text())
    .then(html => {
        document.getElementById("recurringOverview").innerHTML = html;
    })
    .catch(error => {
       console.error("Error updating recurring overview:", error);
    });
}

// Delete recurring expense via AJAX
function deleteRecurring(recurring_id) {
    if (confirm("Are you sure you want to delete this expense?")) {
        const formData = new FormData();
        formData.append('recurring_id', recurring_id);
        fetch("delete_recurring.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === "success") {
                updateRecurringOverview();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            alert("Error: Unable to process your request.");
            console.error(error);
        });
    }
}

function editRecurring(recurring_id) {
    window.location.href = "edit_recurring.php?recurring_id=" + recurring_id;
}

</script>
</body>
</html>
