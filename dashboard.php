<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM investments WHERE user_id = ? AND status = 'active'");
$stmt->execute([$user_id]);
$active_investments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo $user['email']; ?></h2>
    
    <p>Your Referral Code: <strong><?php echo $user['referral_code']; ?></strong></p>
    <p>Share this link: <?php echo $base_url . "register.php?ref=" . $user['referral_code']; ?></p>
    
    <div style="border: 1px solid #000; padding: 10px; margin: 10px;">
        <h3>Wallet Balance: ₹<?php echo $user['wallet_balance']; ?></h3>
        <a href="withdraw.php"><button>Withdraw Money</button></a>
    </div>
    
    <div style="border: 1px solid #000; padding: 10px; margin: 10px;">
        <h3>Invest Now</h3>
        <p>Minimum Investment: ₹500</p>
        <a href="invest.php"><button>Invest Now</button></a>
    </div>
    
    <div style="border: 1px solid #000; padding: 10px; margin: 10px;">
        <h3>Your Active Investments</h3>
        <?php if (count($active_investments) > 0): ?>
            <ul>
            <?php foreach ($active_investments as $investment): ?>
                <li>
                    ₹<?php echo $investment['amount']; ?> - 
                    Start: <?php echo $investment['start_date']; ?> - 
                    End: <?php echo $investment['end_date']; ?>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No active investments.</p>
        <?php endif; ?>
    </div>
    
    <a href="logout.php">Logout</a>
</body>
</html>