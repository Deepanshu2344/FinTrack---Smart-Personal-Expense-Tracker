<?php
header('Content-Type: application/json');
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize the response array
$response = [];

// Monthly Income vs Expense
$monthlyQuery = "SELECT MONTH(date) as month, 
    SUM(CASE WHEN transaction_type='credit' THEN amount ELSE 0 END) as income, 
    SUM(CASE WHEN transaction_type='debit' THEN amount ELSE 0 END) as expense 
    FROM expenses WHERE user_id=? GROUP BY MONTH(date)";
$stmt = $conn->prepare($monthlyQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$monthlyResult = $stmt->get_result();
$monthlyData = $monthlyResult->fetch_all(MYSQLI_ASSOC);
$response['monthly_data'] = $monthlyData;

// Expense Category Pie Chart
$categoryQuery = "SELECT category, SUM(amount) as total FROM expenses 
    WHERE user_id=? AND transaction_type='debit' GROUP BY category";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$categoryResult = $stmt->get_result();
$categoryExpenses = $categoryResult->fetch_all(MYSQLI_ASSOC);
$response['category_expenses'] = $categoryExpenses;

// Budget vs Actual Spending
$budgetQuery = "SELECT b.category, b.budget_amount, 
    COALESCE(SUM(e.amount), 0) as actual_spent 
    FROM budgets b LEFT JOIN expenses e ON b.category = e.category AND e.user_id = ? 
    AND e.transaction_type = 'debit' WHERE b.user_id = ? GROUP BY b.category";
$stmt = $conn->prepare($budgetQuery);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$budgetResult = $stmt->get_result();
$budgetData = $budgetResult->fetch_all(MYSQLI_ASSOC);
$response['budget_data'] = $budgetData;

// Expense by Payment Method
$paymentQuery = "SELECT mode_of_payment as payment_method, SUM(amount) as total FROM expenses 
    WHERE user_id=? AND transaction_type='debit' GROUP BY mode_of_payment";
$stmt = $conn->prepare($paymentQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$paymentResult = $stmt->get_result();
$paymentData = $paymentResult->fetch_all(MYSQLI_ASSOC);
$response['payment_data'] = $paymentData;

// Monthly Savings Trend
$savingsQuery = "SELECT MONTH(date) as month, 
    SUM(CASE WHEN transaction_type='credit' THEN amount ELSE 0 END) - 
    SUM(CASE WHEN transaction_type='debit' THEN amount ELSE 0 END) as savings 
    FROM expenses WHERE user_id=? GROUP BY MONTH(date)";
$stmt = $conn->prepare($savingsQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$savingsResult = $stmt->get_result();
$savingsData = $savingsResult->fetch_all(MYSQLI_ASSOC);
$response['savings_data'] = $savingsData;

// Calculate totals and savings rate
$totalIncome = array_sum(array_column($response['monthly_data'], 'income'));
$totalExpenses = array_sum(array_column($response['monthly_data'], 'expense'));
$savingsRate = ($totalIncome > 0) ? round((($totalIncome - $totalExpenses) / $totalIncome) * 100, 2) : 0;

$response['total_income'] = $totalIncome;
$response['total_expenses'] = $totalExpenses;
$response['savings_rate'] = $savingsRate;

// Generate Custom Advice
// Generate Custom Advice (already present)
if (!empty($categoryExpenses)) {
    $highestExpense = max(array_column($categoryExpenses, 'total'));
    $response['advice'] = ($highestExpense > 10000) ? 
        "Consider reducing spending in high-expense categories." : 
        "Your expenses are under control!";
} else {
    $response['advice'] = "No expenses recorded yet. Start tracking to gain insights!";
}



// Output the JSON response
echo json_encode($response);
?>
