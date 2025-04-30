<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Set headers to prompt the browser to download the CSV file.
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=fintrack_data_export_' . $user_id . '.csv');

// Open the output stream.
$output = fopen('php://output', 'w');

// ================================
// Section 1: Account Details
// ================================
fputcsv($output, array('Account Details'));
fputcsv($output, array('User ID', 'Full Name', 'Email', 'Phone'));

$sql = "SELECT user_id, full_name, email, phone FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
$stmt->close();
fputcsv($output, array(''));  // Blank line separator

// ================================
// Section 2: Transactions (Dashboard Data)
// ================================
fputcsv($output, array('Transactions'));
fputcsv($output, array('Transaction ID', 'Date', 'Category', 'Amount'));

$sql2 = "SELECT expense_id, date, category, amount FROM expenses WHERE user_id = ? ORDER BY date DESC";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
while ($row = $result2->fetch_assoc()) {
    fputcsv($output, $row);
}
$stmt2->close();
fputcsv($output, array(''));  // Blank line separator

// ================================
// Section 3: Budgets
// ================================
fputcsv($output, array('Budgets'));
fputcsv($output, array('Category', 'Budget Amount', 'Frequency'));

$sql3 = "SELECT category, budget_amount, frequency FROM budgets WHERE user_id = ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$result3 = $stmt3->get_result();
while ($row = $result3->fetch_assoc()) {
    fputcsv($output, $row);
}
$stmt3->close();
fputcsv($output, array(''));  // Blank line separator

// ================================
// Section 4: Recurring Expenses
// ================================
fputcsv($output, array('Recurring Expenses'));
fputcsv($output, array('Recurring ID', 'Category', 'Amount', 'Frequency', 'Start Date', 'Next Payment Date'));

$sql4 = "SELECT recurring_id, category, amount, frequency, start_date, next_payment_date FROM recurring_expenses WHERE user_id = ?";
$stmt4 = $conn->prepare($sql4);
$stmt4->bind_param("i", $user_id);
$stmt4->execute();
$result4 = $stmt4->get_result();
while ($row = $result4->fetch_assoc()) {
    fputcsv($output, $row);
}
$stmt4->close();
fputcsv($output, array(''));  // Blank line separator

// ================================
// Section 5: Insights (Visualizations Data)
// ================================

// 5.1 Overall Metrics
fputcsv($output, array('Insights - Overall Metrics'));
fputcsv($output, array('Metric', 'Value'));

// Total Expenses
$sql5 = "SELECT SUM(amount) AS total_expenses FROM expenses WHERE user_id = ?";
$stmt5 = $conn->prepare($sql5);
$stmt5->bind_param("i", $user_id);
$stmt5->execute();
$result5 = $stmt5->get_result();
$total_expenses = 0;
if ($row = $result5->fetch_assoc()) {
    $total_expenses = $row['total_expenses'];
}
$stmt5->close();
fputcsv($output, array('Total Expenses', $total_expenses));

// Total Budgets
$sql6 = "SELECT SUM(budget_amount) AS total_budget FROM budgets WHERE user_id = ?";
$stmt6 = $conn->prepare($sql6);
$stmt6->bind_param("i", $user_id);
$stmt6->execute();
$result6 = $stmt6->get_result();
$total_budget = 0;
if ($row = $result6->fetch_assoc()) {
    $total_budget = $row['total_budget'];
}
$stmt6->close();
fputcsv($output, array('Total Budgets', $total_budget));

// Budget Difference:
$budget_difference = $total_budget - $total_expenses;
fputcsv($output, array('Budget Difference', $budget_difference));
fputcsv($output, array(''));  // Blank line separator

// 5.2 Category-wise Expenses (for pie charts or bar graphs)
fputcsv($output, array('Insights - Expenses by Category'));
fputcsv($output, array('Category', 'Total Expenses'));
$sql7 = "SELECT category, SUM(amount) AS total_category_expense 
         FROM expenses 
         WHERE user_id = ? 
         GROUP BY category 
         ORDER BY total_category_expense DESC";
$stmt7 = $conn->prepare($sql7);
$stmt7->bind_param("i", $user_id);
$stmt7->execute();
$result7 = $stmt7->get_result();
while($row = $result7->fetch_assoc()){
    fputcsv($output, $row);
}
$stmt7->close();
fputcsv($output, array(''));  // Blank line separator

// 5.3 Monthly Expense Trend (for line charts)
fputcsv($output, array('Insights - Monthly Expenses Trend'));
fputcsv($output, array('Month', 'Total Expenses'));
$sql8 = "SELECT DATE_FORMAT(date, '%Y-%m') AS month, SUM(amount) AS total_monthly_expense 
         FROM expenses 
         WHERE user_id = ? 
         GROUP BY month 
         ORDER BY month ASC";
$stmt8 = $conn->prepare($sql8);
$stmt8->bind_param("i", $user_id);
$stmt8->execute();
$result8 = $stmt8->get_result();
while($row = $result8->fetch_assoc()){
    fputcsv($output, $row);
}
$stmt8->close();

// Close the output stream.
fclose($output);
exit();
?>
