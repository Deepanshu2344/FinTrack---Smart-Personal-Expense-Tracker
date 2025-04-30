<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Retrieve data from users table (existing columns)
$sql = "SELECT full_name, email, phone FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $email, $phone);
$stmt->fetch();
$stmt->close();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_account"])) {
    $new_full_name = trim($_POST["full_name"]);
    $new_email = trim($_POST["email"]);
    $new_phone = trim($_POST["phone"]);
    $new_password = trim($_POST["password"]);

    // Password validation (only if user entered a new password)
    if (!empty($new_password)) {
        if (!preg_match('/^(?=(.*[a-z]){1})(?=(.*[A-Z]){1})(?=(.*\d){1})(?=(.*[@$!%*#?&]){1}).{8,}$/', $new_password)) {
            echo "<script>alert('Password must be at least 8 characters, include 1 uppercase letter, 1 lowercase letter, 1 digit, and 1 special character (@$!%*#?&).'); window.location.href='settings.php';</script>";
            exit(); // Stop execution if password is invalid
        }
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $new_full_name, $new_email, $new_phone, $hashed_password, $user_id);
    } else {
        $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $new_full_name, $new_email, $new_phone, $user_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Account updated successfully!'); window.location.href='settings.php';</script>";
    } else {
        echo "<script>alert('Error updating account. Please try again.');</script>";
    }

    $stmt->close();
}


// Set default values for extra settings (not stored in DB)
$budgetAlerts = true;           // Budget Alerts ON by default
$recurringReminders = false;    // Recurring Reminders OFF by default
$theme = "light";               // Default theme is Light
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FinTrack Settings</title>
  <link rel="stylesheet" href="settings.css">
  <script>
    // Toggle edit mode for fields (for full_name, email, or phone)
    function toggleEdit(field) {
      var displayDiv = document.getElementById(field + "-display");
      var editDiv = document.getElementById(field + "-edit");
      if (displayDiv.style.display === "none") {
        displayDiv.style.display = "block";
        editDiv.style.display = "none";
      } else {
        displayDiv.style.display = "none";
        editDiv.style.display = "block";
      }
    }
    // Confirmation for account deletion
    function confirmDeleteAccount() {
      return confirm("Are you sure you want to delete your account? This action cannot be undone.");
    }
    // Toggle Dark/Light theme on the settings page
    document.addEventListener("DOMContentLoaded", function () {
      // Set initial theme from default value.
      if ("<?php echo $theme; ?>" === "dark") {
          document.body.classList.add("dark-theme");
      }
      
      // Listen for changes on theme radio inputs.
      var themeRadios = document.getElementsByName("theme");
      for (var i = 0; i < themeRadios.length; i++) {
          themeRadios[i].addEventListener("change", function() {
              if (this.value === "dark") {
                  document.body.classList.add("dark-theme");
              } else {
                  document.body.classList.remove("dark-theme");
              }
              // Optionally trigger AJAX to persist changes.
          });
      }
    });
    // Toggle for the navigation menu (if needed)
    function toggleMenu() {
      document.getElementById("menu").classList.toggle("show");
    }

    // FUnction to validate password
    function validatePassword() {
        var password = document.getElementById("password").value;
        var errorMessage = document.getElementById("password-error");

        var regex = /^(?=(.*[a-z]){1})(?=(.*[A-Z]){1})(?=(.*\d){1})(?=(.*[@$!%*#?&]){1}).{8,}$/;
    
        if (!regex.test(password)) {
        errorMessage.innerHTML = "Password must be at least 8 characters, include 1 uppercase letter, 1 lowercase letter, 1 digit, and 1 special character (@$!%*#?&).";
        } else {
            errorMessage.innerHTML = "";
        }
    }

  </script>
</head>
<body>
  <!-- Header -->
  <header>
    <div class="left-side">
        <div class="hamburger" onclick="toggleMenu()">☰</div>
    </div>
    <div class="center-side">
        <h1>FinTrack Settings</h1>
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
  <div class="settings-container">
    <!-- Section: Account Details -->
    <!-- Section: Account Details -->
<section class="settings-section" id="account-details">
    <h2>Account Details</h2>
    
    <!-- Account Update Form -->
    <form method="POST" action="settings.php">
        <div class="account-field">
            <label>Full Name:</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>">
        </div>

        <div class="account-field">
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
        </div>

        <div class="account-field">
            <label>Phone:</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
        </div>

        <div class="account-field">
            <label>New Password:</label>
            <input type="password" name="password" id="password" placeholder="Enter new password" onkeyup="validatePassword()">
            <p id="password-error" style="color: red; font-size: 14px;"></p>
        </div>

        <button type="submit" name="update_account">Save Changes</button>
    </form>
</section>



    <!-- Section: Notifications 
    <section class="settings-section" id="notifications-section">
        <h2>Notifications</h2>
        
        <!-- Budget Alerts Toggle 
        <div class="toggle-switch">
            <label for="budgetAlerts">Budget Alerts:</label>
            <input type="checkbox" id="budgetAlerts" name="budgetAlerts" <?php if ($budgetAlerts) echo "checked"; ?>>
        </div>
        
        <!-- Recurring Expense Reminders Toggle 
        <div class="toggle-switch">
            <label for="recurringReminders">Recurring Expense Reminders:</label>
            <input type="checkbox" id="recurringReminders" name="recurringReminders" <?php if ($recurringReminders) echo "checked"; ?>>
        </div>
    </section> -->

    <!-- Section: Data Management -->
    <section class="settings-section" id="data-management">
        <h2>Data Management</h2>
        
        <!-- Export Data Button -->
        <div class="data-management">
            <button type="button" onclick="location.href='export_data.php'">Export All Data</button>
            
            <!-- Delete Account Button -->
            <button type="button" onclick="if(confirmDeleteAccount()) location.href='delete_account.php'">Delete My Account</button>
        </div>
    </section>

    <!-- Section: Support and Terms -->
    <section class="settings-section" id="support-section">
        <h2>Support & Terms</h2>
        
        <!-- Support Button -->
        <div class="button-container">
            <button type="button" onclick="location.href='support.php'">Support</button>
            
            <!-- Terms and Conditions Button -->
            <button type="button" onclick="location.href='terms_conditions.php'">Terms & Conditions</button>
        </div>
    </section>
</div>
  
  
<!-- Footer -->
  <footer>
    &copy; 2025 FinTrack. All rights reserved.
  </footer>
  
</body>
</html>

<style>
  /* Add the rest of your styling as in the previous code... */
</style>
