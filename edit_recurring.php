<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Check if 'id' is set in the query string
if (isset($_GET['recurring_id'])) {
    $recurring_id = $_GET['recurring_id'];
    $user_id = $_SESSION['user_id'];

    // Ensure the recurring expense belongs to the logged-in user
    $check_recurring_query = "SELECT * FROM recurring_expenses WHERE recurring_id='$recurring_id' AND user_id='$user_id'";
    $result = $conn->query($check_recurring_query);

    if ($result->num_rows > 0) {
        $recurring = $result->fetch_assoc();

        // Handle form submission for editing recurring expense
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_recurring'])) {
            $category = $_POST['category'];
            $amount = $_POST['amount'];
            $frequency = $_POST['frequency'];
            $start_date = $_POST['start_date'];
            $next_payment_date = $_POST['next_payment_date'] ?: $start_date;

            // Update recurring expense in the database
            $update_query = "UPDATE recurring_expenses SET 
                category='$category', 
                amount='$amount', 
                frequency='$frequency', 
                start_date='$start_date', 
                next_payment_date='$next_payment_date' 
                WHERE recurring_id='$recurring_id' AND user_id='$user_id'";
            
            if ($conn->query($update_query) === TRUE) {
                header("Location: recurring.php");
                exit();
            } else {
                echo "Error updating recurring expense: " . $conn->error;
            }
        }
    } else {
        echo "Recurring expense not found or unauthorized action.";
        exit();
    }
} else {
    echo "Recurring expense ID not specified.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recurring Expense - FinTrack</title>
    <link rel="stylesheet" href="recurring.css">
    <style>
        /* Form styling */
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-button {
            background-color: #005f73;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        
        .form-button:hover {
            background-color: #004e66;
        }
        
        .cancel-button {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-right: 10px;
            display: inline-block;
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Edit Recurring Expense</h1>
    </header>
    <main>
        <div class="form-container">
        <form action="edit_recurring.php?recurring_id=<?php echo $recurring_id; ?>" method="POST">
        <div class="form-group">
                    <label for="category">Category:</label>
                    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($recurring['category']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" id="amount" name="amount" value="<?php echo htmlspecialchars($recurring['amount']); ?>" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="frequency">Frequency:</label>
                    <select id="frequency" name="frequency" required>
                        <option value="daily" <?php echo ($recurring['frequency'] == 'daily') ? 'selected' : ''; ?>>Daily</option>
                        <option value="weekly" <?php echo ($recurring['frequency'] == 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                        <option value="monthly" <?php echo ($recurring['frequency'] == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                        <option value="yearly" <?php echo ($recurring['frequency'] == 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($recurring['start_date']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="next_payment_date">Next Payment Date:</label>
                    <input type="date" id="next_payment_date" name="next_payment_date" value="<?php echo htmlspecialchars($recurring['next_payment_date']); ?>">
                </div>
                
                <div class="button-group">
                    <a href="recurring.php" class="cancel-button">Cancel</a>
                    <button type="submit" name="edit_recurring" class="form-button">Update Recurring Expense</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>