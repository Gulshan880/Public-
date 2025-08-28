<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $plain_password = $_POST['password'];
    $ref_code = $_POST['referral_code'];
    
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    $my_referral_code = strtoupper(substr(md5(uniqid()), 0, 8));
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "INSERT INTO users (email, phone, password, referral_code, referred_by, wallet_balance, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $phone, $hashed_password, $my_referral_code, $ref_code, 0]);
        
        if (!empty($ref_code)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->execute([$ref_code]);
            $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($referrer) {
                $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + 50 WHERE id = ?");
                $stmt->execute([$referrer['id']]);
                
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status, created_at) VALUES (?, 'referral_bonus', 50, 'success', NOW())");
                $stmt->execute([$referrer['id']]);
            }
        }
        
        $user_id = $pdo->lastInsertId();
        $stmt = $pdo->prepare("UPDATE users SET wallet_balance = wallet_balance + 25 WHERE id = ?");
        $stmt->execute([$user_id]);
        
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, status, created_at) VALUES (?, 'welcome_bonus', 25, 'success', NOW())");
        $stmt->execute([$user_id]);
        
        $success = "Registration successful!";
        header("Location: login.php?success=" . urlencode($success));
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
</head>
<body>
    <h2>Sign Up</h2>
    
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="text" name="phone" placeholder="Phone" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="text" name="referral_code" placeholder="Referral Code (Optional)"><br><br>
        <button type="submit">Create Account</button>
    </form>
    
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>