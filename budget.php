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
$email_query = "SELECT email FROM users WHERE user_id = ?";
$stmt_email = $conn->prepare($email_query);
$stmt_email->bind_param("i", $user_id);
$stmt_email->execute();
$stmt_email->bind_result($email);
$stmt_email->fetch();
$stmt_email->close();

// Fetch budgets for the user, ordered by frequency
$budget_query = "SELECT category, budget_amount, frequency FROM budgets WHERE user_id='$user_id' ORDER BY FIELD(frequency, 'weekly', 'monthly', 'yearly')";
$budget_result = $conn->query($budget_query);

// Fetch total spending per category
$spending_query = "SELECT b.category, IFNULL(SUM(e.amount), 0) AS spent FROM budgets AS b LEFT JOIN expenses AS e ON b.category = e.category AND b.user_id = e.user_id AND e.transaction_type = 'debit' WHERE b.user_id = '$user_id' GROUP BY b.category";
$spending_result = $conn->query($spending_query);
$spending_data = [];
while ($row = $spending_result->fetch_assoc()) {
    $spending_data[$row['category']] = $row['spent'];
}

// Group budgets by frequency
$weekly_budgets = [];
$monthly_budgets = [];
$yearly_budgets = [];

while ($budget = $budget_result->fetch_assoc()) {
    if ($budget['frequency'] === 'weekly') {
        $weekly_budgets[] = $budget;
    } elseif ($budget['frequency'] === 'monthly') {
        $monthly_budgets[] = $budget;
    } else {
        $yearly_budgets[] = $budget;
    }
}

// Check for budgets exceeding the 75% limit and send email alerts
$alerted_categories = [];
foreach ($spending_data as $category => $spent) {
    foreach ($monthly_budgets as $budget) { // Or include weekly and yearly budgets as per the need
        if ($budget['category'] === $category && $budget['budget_amount'] > 0) {
            $percentage = ($spent / $budget['budget_amount']) * 100;
            if ($percentage > 75 && !in_array($category, $alerted_categories)) {
                sendBudgetAlertEmail($email, $category, $spent, $budget['budget_amount']);
                $alerted_categories[] = $category;
            }
        }
    }
}

// Function to send budget alert email
function sendBudgetAlertEmail($email, $category, $spent, $budget_amount) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Your SMTP Server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fintrack15@gmail.com'; // Your Gmail Address
        $mail->Password   = 'kmozmpupukyzyutp'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Email Content
        $mail->setFrom('fintrack15@gmail.com', 'FinTrack Notifications');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Budget Alert - $category Exceeded 75%";
        $mail->Body    = "
            <h3>Budget Alert</h3>
            <p>Your budget for <strong>$category</strong> has exceeded 75% of the allocated amount.</p>
            <p>Details:</p>
            <ul>
                <li><strong>Spent:</strong> ₹" . number_format($spent, 2) . "</li>
                <li><strong>Budget Amount:</strong> ₹" . number_format($budget_amount, 2) . "</li>
                <li><strong>Percentage Used:</strong> " . round(($spent / $budget_amount) * 100, 1) . "%</li>
            </ul>
            <p>Please monitor your spending closely to avoid exceeding your budget.</p>
            <p>Regards,<br>FinTrack Team</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

// Function to display a single budget item with progress indication and delete button
function display_budget_item($budget, $spending_data) {
    $category = $budget['category'];
    $budget_amount = $budget['budget_amount'];
    $frequency = $budget['frequency'];
    $spent = isset($spending_data[$category]) ? $spending_data[$category] : 0;
    $percentage = ($budget_amount > 0) ? min(100, ($spent / $budget_amount) * 100) : 0;
  
    // Set progress bar color thresholds
    if ($percentage <= 75) {
        $bar_color = '#4CAF50'; // green
    } elseif ($percentage <= 99) {
        $bar_color = '#FFC107'; // yellow
    } else {
        $bar_color = '#F44336'; // red
    }

    $display_percentage = ($percentage > 100) ? "100%+" : round($percentage, 1) . "%";
    $residual = max(0, $budget_amount - $spent);

    // Budget item container
    echo '<div class="budget-item" style="position: relative;">';
    echo '<button class="edit-button" onclick="openEditModal(\'' . htmlspecialchars($category) . '\', \'' . $budget_amount . '\', \'' . $frequency . '\')">Edit</button>';

    // Add delete button in the top-right corner
    echo '<form method="POST" action="delete_budget.php" style="position: absolute; top: -15px; right: 8px;">';
    echo '<input type="hidden" name="delete_category" value="' . htmlspecialchars($category) . '">';
    echo '<button type="submit" style="position: relative; top: 2px; left: 0px; 
        background:rgb(232, 55, 55); color: white; border: none; padding: 5px 10px; 
        font-size: 12px; cursor: pointer; border-radius: 5px;">Delete</button>';
    echo '</form>';
    
    // Display the budget details
    echo "<strong>" . htmlspecialchars($category) . "</strong> - ₹" . number_format($budget_amount, 2);
    echo '<div class="bar-container">';
        echo '<div class="bar" data-category="' . htmlspecialchars($category) . '" style="width:' . $percentage . '%; background-color:' . $bar_color . '; transition: width 0.5s;"></div>';
        echo '<div class="progress-text" data-category="' . htmlspecialchars($category) . '">' . $display_percentage . '</div>';
        echo '</div>';

    echo '<small>Residual: ₹' . number_format($residual, 2) . '</small>';

    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget - FinTrack</title>
    <link rel="stylesheet" href="budget.css" />
    <style>
.edit-button { position: absolute; bottom: 5px; right: 8px; background:rgb(34, 148, 61); color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 5px; }
.edit-button:hover { background:rgb(29, 153, 43); }
</style>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="left-side">
            <div class="hamburger" onclick="toggleMenu()">☰</div>
        </div>
        <div class="center-side">
            <h1>FinTrack Budget</h1>
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

    <main>
        <h2>Budget Overview</h2>
        <!-- Container for dynamic budget overview -->
        <div id="budgetOverview">
            <div class="budget-container">
                <!-- Weekly Budgets Column -->
                <div class="budget-column">
                    <div class="section-title">Weekly Budgets</div>
                    <?php
                    if (!empty($weekly_budgets)) {
                        foreach ($weekly_budgets as $budget) {
                            display_budget_item($budget, $spending_data);
                        }
                    } else {
                        echo '<p>No weekly budgets found.</p>';
                    }
                    ?>
                </div>

                <!-- Monthly Budgets Column -->
                <div class="budget-column">
                    <div class="section-title">Monthly Budgets</div>
                    <?php
                    if (!empty($monthly_budgets)) {
                        foreach ($monthly_budgets as $budget) {
                            display_budget_item($budget, $spending_data);
                        }
                    } else {
                        echo '<p>No monthly budgets found.</p>';
                    }
                    ?>
                </div>

                <!-- Yearly Budgets Column -->
                <div class="budget-column">
                    <div class="section-title">Yearly Budgets</div>
                    <?php
                    if (!empty($yearly_budgets)) {
                        foreach ($yearly_budgets as $budget) {
                            display_budget_item($budget, $spending_data);
                        }
                    } else {
                        echo '<p>No yearly budgets found.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Floating Add Budget Button -->
        <button class="add-budget-btn" onclick="openModal()">+</button>
    </main>

    <!-- Modal: Add Budget -->
    <div id="budgetModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">×</span>
            <h2>Add New Budget</h2>
            <form id="budgetForm">
                <label for="category">Budget Category:</label>
                <input type="text" name="category" id="category" placeholder="e.g., Groceries" required>

                <label for="budget_amount">Budget Amount (₹):</label>
                <input type="number" step="0.01" name="budget_amount" id="budget_amount" placeholder="e.g., 5000" required>

                <label for="frequency">Frequency:</label>
                <select name="frequency" id="frequency" required>
                    <option value="weekly">Weekly</option>
                    <option value="monthly" selected>Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>

                <button type="submit">Add Budget</button>
            </form>
        </div>
    </div>
    <!-- Edit Budget Modal -->
<div id="editBudgetModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeEditModal()">×</span>
        <h2>Edit Budget</h2>
        <form id="editBudgetForm">
            <input type="hidden" name="original_category" id="edit_original_category">
            <label for="edit_category">Budget Category:</label>
            <input type="text" name="category" id="edit_category" required>

            <label for="edit_budget_amount">Budget Amount (₹):</label>
            <input type="number" step="0.01" name="budget_amount" id="edit_budget_amount" required>

            <label for="edit_frequency">Frequency:</label>
            <select name="frequency" id="edit_frequency" required>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
            <button type="submit">Update Budget</button>
        </form>
    </div>
</div>
    <!-- Footer -->
    <footer>
        © 2025 FinTrack. All rights reserved.
    </footer>

    <!-- JavaScript for all functionality -->
    <script>

        // Navigation menu toggle
        function openEditModal(category, amount, frequency) {
    document.getElementById("edit_original_category").value = category;
    document.getElementById("edit_category").value = category;
    document.getElementById("edit_budget_amount").value = amount;
    document.getElementById("edit_frequency").value = frequency;
    document.getElementById("editBudgetModal").style.display = "flex";
}
function closeEditModal() {
    document.getElementById("editBudgetModal").style.display = "none";
}
document.getElementById("editBudgetForm").addEventListener("submit", function(e) {
    e.preventDefault();
    fetch("update_budget.php", {
        method: "POST",
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            closeEditModal();
            updateProgressBar();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Fetch error:", error);
        alert("Network error or server unavailable.");
    });
});
        function toggleMenu() {
            document.getElementById("menu").classList.toggle("show");
        }
        
        // Modal functions
        function openModal() {
            document.getElementById("budgetModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("budgetModal").style.display = "none";
        }

        // Handle form submission with AJAX
        document.getElementById("budgetForm").addEventListener("submit", function (e) {
            e.preventDefault(); // Prevent default form submission

            // Disable the submit button to prevent multiple submissions
            const submitButton = document.querySelector("#budgetForm button[type='submit']");
            submitButton.disabled = true;
            submitButton.textContent = "Adding..."; // Change button text

            fetch("add_budget.php", {
                    method: "POST",
                    body: new FormData(this)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === "success") {
                        closeModal();
                        location.reload(); // Simple reload instead of AJAX update
                        document.getElementById("budgetForm").reset(); // Clear form
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    alert("Network error or server unavailable. Check the console for details.");
                })
                .finally(() => {
                    // Re-enable the submit button
                    submitButton.disabled = false;
                    submitButton.textContent = "Add Budget"; // Restore original text
                });
        });
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById("budgetModal");
            if (event.target === modal) {
                closeModal();
            }
        };
    </script>
</body>
</html>