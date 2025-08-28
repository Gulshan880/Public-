<?php
include 'config.php';

$today = date('Y-m-d H:i:s');

$sql = "SELECT * FROM investments WHERE status = 'active' AND end_date <= ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$today]);
$matured_investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($matured_investments as $investment) {
    $user_id = $investment['user_id'];
    $return_amount = $investment['return_amount'];
    
    $sql_update = "UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$return_amount, $user_id]);
    
    $sql_complete = "UPDATE investments SET status = 'completed' WHERE id = ?";
    $stmt_complete = $pdo->prepare($sql_complete);
    $stmt_complete->execute([$investment['id']]);
    
    $sql_transaction = "INSERT INTO transactions (user_id, type, amount, status, created_at) VALUES (?, 'investment_profit', ?, 'success', NOW())";
    $stmt_transaction = $pdo->prepare($sql_transaction);
    $stmt_transaction->execute([$user_id, $return_amount]);
}

echo "Cron job executed successfully. Processed " . count($matured_investments) . " investments.";
?>