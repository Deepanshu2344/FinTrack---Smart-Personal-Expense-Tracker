<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Fetch user data.
$user_id = $_SESSION['user_id'];

// Fetch user's full name from the database
$user_query = "SELECT full_name FROM users WHERE user_id = '$user_id'";
$user_result = $conn->query($user_query);

if ($user_result->num_rows > 0) {
    $user_row = $user_result->fetch_assoc();
    $user_name = htmlspecialchars($user_row['full_name']); // Sanitize for security
} else {
    $user_name = 'User'; // Fallback if full_name is not found
}

// Fetch total income and total expenses.
$total_income_query = "SELECT SUM(amount) AS total FROM expenses WHERE user_id='$user_id' AND transaction_type='credit'";
$total_income_result = $conn->query($total_income_query);
$total_income = $total_income_result->fetch_assoc()['total'] ?? 0;

$total_expenses_query = "SELECT SUM(amount) AS total FROM expenses WHERE user_id='$user_id' AND transaction_type='debit'";
$total_expenses_result = $conn->query($total_expenses_query);
$total_expenses = $total_expenses_result->fetch_assoc()['total'] ?? 0;

// Calculate remaining balance.
$remaining_balance = $total_income - $total_expenses;

// Fetch all user transactions. (Make sure that EXPENSE ID column is named consistently; here assumed as expense_id)
$transactions_query = "SELECT * FROM expenses WHERE user_id='$user_id' ORDER BY date DESC";
$transactions_result = $conn->query($transactions_query);

// Handle adding a new transaction.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_transaction'])) {
    // Retrieve posted values.
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'] ?: date("Y-m-d"); // Use current date if none selected.
    $transaction_type = $_POST['transaction_type'];
    $mode_of_payment = $_POST['mode_of_payment'];

    // Insert the new transaction including the mode_of_payment value.
    $sql = "INSERT INTO expenses (user_id, category, amount, date, transaction_type, mode_of_payment) 
            VALUES ('$user_id', '$category', '$amount', '$date', '$transaction_type', '$mode_of_payment')";
    
    if ($conn->query($sql) === TRUE) {
        // Update budget progress if it's an expense (debit)
        if ($transaction_type == 'debit') {
            // Get the current month and year
            $current_month = date('m');
            $current_year = date('Y');
            
            // Update budget progress for this category
          // ✅ Correct: Use `frequency` instead of `month` and `year`
            $update_budget_sql = "UPDATE budgets 
            SET spent = spent + $amount 
            WHERE user_id = '$user_id' 
            AND category = '$category' 
            AND frequency = 'monthly'"; // Change to 'weekly' or 'yearly' if needed

            $conn->query($update_budget_sql);
        }
        
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Handle deleting a transaction.
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM expenses WHERE expense_id='$delete_id' AND user_id='$user_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FinTrack</title>
    <!-- Link to the external CSS file -->
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <header>
        <div class="left-side">
            <div class="hamburger" onclick="toggleMenu()">☰</div>
        </div>
        <div class="center-side">
            <h1>FinTrack Dashboard</h1>
        </div>
        <div class="right-side">
            <a href="logout.php">Logout</a>
        </div>
    </header>
    
    <nav>
        <ul id="menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="budget.php">Budget</a></li>
            <li><a href="recurring.php">Recurring Expenses</a></li>
            <li><a href="insights.html">Insights</a></li>
            <li><a href="settings.php">Settings</a></li>
        </ul>
    </nav>

    <!-- Welcome Message -->
    <div class="welcome-message">
        <h2>Welcome, <?php echo $user_name; ?></h2>
    </div>

    <main>
        <!-- Overview Boxes Section -->
        <section class="overview-boxes">
            <div class="overview-box">
                <img src="images/income.png" alt="Income Icon">
                <h2>Total Income</h2>
                <p>₹<?php echo number_format($total_income, 2); ?></p>
            </div>
            <div class="overview-box">
                <img src="images/expense.png" alt="Expenses Icon">
                <h2>Total Expenses</h2>
                <p>₹<?php echo number_format($total_expenses, 2); ?></p>
            </div>
            <div class="overview-box">
                <img src="images/balance.png" alt="Balance Icon">
                <h2>Remaining Balance</h2>
                <p>₹<?php echo number_format($remaining_balance, 2); ?></p>
            </div>
        </section>

        <!-- Add Transaction Section -->
        <section>
            <h2>Add New Transaction</h2>
            <form action="dashboard.php" method="POST">
                <input type="text" name="category" placeholder="Category" required>
                <input type="number" name="amount" placeholder="Amount" step="0.01" required>
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                <select name="transaction_type" required>
                    <option value="debit">Expense</option>
                    <option value="credit">Income</option>
                </select>
                <!-- New dropdown for Mode of Payment -->
                <select name="mode_of_payment" required>
                    <option value="">--Select Payment Mode--</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="upi">UPI</option>
                </select>
                <button type="submit" name="add_transaction">Add Transaction</button>
            </form>
        </section>

        <!-- Transaction History Section -->
        <section>
            <h3>Your Transactions</h3>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Payment Mode</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($transaction = $transactions_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                            <td>₹<?php echo number_format($transaction['amount'], 2); ?></td>
                            <td><?php echo date("d-m-Y", strtotime($transaction['date'])); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($transaction['transaction_type'])); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($transaction['mode_of_payment'])); ?></td>
                            <td class="actions">
                            <a href="edit_transaction.php?id=<?php echo $transaction['expense_id']; ?>" class="edit-btn">Edit<br></a>
                            <a href="delete_transaction.php?id=<?php echo $transaction['expense_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this transaction?');">Delete</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        &copy; 2025 FinTrack. All rights reserved.
    </footer>

    <script>
        function toggleMenu() {
            var menu = document.getElementById("menu");
            menu.classList.toggle("show");
        }
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    
    async function fetchInsightsData() {
        
        const timeframe = document.getElementById('timeframe').value;
        const category = document.getElementById('category').value;
        const monthYear = document.getElementById('monthYearPicker').value;
        
        try {
            const response = await fetch(`insights_data.php?timeframe=${timeframe}&category=${category}&monthYear=${monthYear}`);
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (data.error) {
                console.error('Error:', data.error);
                // Redirect to login if not logged in
                if (data.error === 'Not logged in') {
                    window.location.href = 'signin.php';
                }
                return null;
            }
            
            return data;
        } catch (error) {
            console.error('Fetch error:', error);
            alert('Failed to load data. Please try again later.');
            return null;
        }
    }
    
    // Initialize all charts with live data
    async function initializeCharts() {
        const data = await fetchInsightsData();
        
        if (!data) {
            // If data fetch failed, use sample data for display purposes
            console.log('Using sample data for display');
            return;
        }
        
        // Update the charts with real data
        updateSummary(data);
        createIncomeExpenseChart(data);
        createExpenseCategoryChart(data);
        createMonthlyTrendChart(data);
        createBudgetComparisonChart(data);
        generateInsights(data);
    }
    
    // Update summary statistics
    function updateSummary(data) {
        const totalIncome = data.income.reduce((sum, item) => sum + parseFloat(item.amount), 0);
        const totalExpenses = data.expenses.reduce((sum, item) => sum + parseFloat(item.amount), 0);
        const savingsRate = totalIncome > 0 ? ((totalIncome - totalExpenses) / totalIncome * 100).toFixed(1) : 0;
        
        document.getElementById('total-income').textContent = `₹${totalIncome.toLocaleString('en-IN', {maximumFractionDigits: 2})}`;
        document.getElementById('total-expenses').textContent = `₹${totalExpenses.toLocaleString('en-IN', {maximumFractionDigits: 2})}`;
        document.getElementById('savings-rate').textContent = `${savingsRate}%`;
        
        // Find top expense category
        const expensesByCategory = data.expenses.reduce((acc, item) => {
            const category = item.category;
            acc[category] = (acc[category] || 0) + parseFloat(item.amount);
            return acc;
        }, {});
        
        let topExpenseCategory = '';
        let topExpenseAmount = 0;
        
        Object.entries(expensesByCategory).forEach(([category, amount]) => {
            if (amount > topExpenseAmount) {
                topExpenseAmount = amount;
                topExpenseCategory = category;
            }
        });
        
        document.getElementById('top-expense').textContent = topExpenseCategory ?
        </script>
</body>
</html>
