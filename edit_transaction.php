<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Check if 'id' is set in the query string
if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Ensure the transaction belongs to the logged-in user
    $check_transaction_query = "SELECT * FROM expenses WHERE expense_id='$transaction_id' AND user_id='$user_id'";
    $result = $conn->query($check_transaction_query);

    if ($result->num_rows > 0) {
        $transaction = $result->fetch_assoc();

        // Handle form submission for editing transaction
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_transaction'])) {
            $category = $_POST['category'];
            $amount = $_POST['amount'];
            $date = $_POST['date'];
            $transaction_type = $_POST['transaction_type'];
            $mode_of_payment = $_POST['mode_of_payment'];

            // Update transaction in the database
            $update_query = "UPDATE expenses SET category='$category', amount='$amount', date='$date', transaction_type='$transaction_type', mode_of_payment='$mode_of_payment' WHERE expense_id='$transaction_id' AND user_id='$user_id'";
            
            if ($conn->query($update_query) === TRUE) {
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Error updating transaction: " . $conn->error;
            }
        }
    } else {
        echo "Transaction not found or unauthorized action.";
        exit();
    }
} else {
    echo "Transaction ID not specified.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction - FinTrack</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Form styling */
        .form-container {
            max-width: 450px;
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
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Edit Transaction</h1>
    </header>
    <main>
        <div class="form-container">
            <form action="edit_transaction.php?id=<?php echo $transaction_id; ?>" method="POST">
                <div class="form-group">
                    <label for="category">Category:</label>
                    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($transaction['category']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" id="amount" name="amount" value="<?php echo htmlspecialchars($transaction['amount']); ?>" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($transaction['date']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="transaction_type">Transaction Type:</label>
                    <select id="transaction_type" name="transaction_type" required>
                        <option value="debit" <?php echo ($transaction['transaction_type'] == 'debit') ? 'selected' : ''; ?>>Expense</option>
                        <option value="credit" <?php echo ($transaction['transaction_type'] == 'credit') ? 'selected' : ''; ?>>Income</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mode_of_payment">Payment Method:</label>
                    <select id="mode_of_payment" name="mode_of_payment" required>
                        <option value="cash" <?php echo ($transaction['mode_of_payment'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                        <option value="card" <?php echo ($transaction['mode_of_payment'] == 'card') ? 'selected' : ''; ?>>Card</option>
                        <option value="upi" <?php echo ($transaction['mode_of_payment'] == 'upi') ? 'selected' : ''; ?>>UPI</option>
                    </select>
                </div>
                
                <div class="button-group">
                    <a href="dashboard.php" class="cancel-button">Cancel</a>
                    <button type="submit" name="edit_transaction" class="form-button">Update Transaction</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>