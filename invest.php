<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>निवेश करें - इन्वेस्टमेंट ऐप</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .investment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .investment-header {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        .investment-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn-invest {
            background: linear-gradient(45deg, #FF5722, #FF9800);
            border: none;
            padding: 12px 30px;
            font-size: 18px;
            border-radius: 50px;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .btn-invest:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 87, 34, 0.3);
        }
        .benefits-list {
            list-style-type: none;
            padding: 0;
        }
        .benefits-list li {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }
        .benefits-list li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
        }
        .razorpay-logo {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="investment-container">
            <div class="investment-header">
                <h2>निवेश करें</h2>
                <p>अपने ₹500 को 28 दिनों में दोगुना करें</p>
            </div>

            <?php
            include 'config.php';
            if (!isset($_SESSION['user_id'])) { 
                header("Location: login.php"); 
                exit(); 
            }
            $user_id = $_SESSION['user_id']; 
            $min_investment = 500;

            // RAZORPAY INTEGRATION START
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $amount = (int) $_POST['amount'];
                if ($amount < $min_investment) { 
                    echo '<div class="alert alert-danger">न्यूनतम निवेश ₹'.$min_investment.' है</div>'; 
                } else {
                    // RAZORPAY PAYMENT PROCESS
                    require_once 'razorpay-php/Razorpay.php';
                    use Razorpay\Api\Api;

                    // Razorpay Keys (यहाँ अपनी keys डालें)
                    $key_id = "rzp_live_R8s4TZqmuOIzBt";
                    $key_secret = "5EpotHKf6Ke79vUSK2ky9QC2";

                    $api = new Api($key_id, $key_secret);

                    try {
                        $order = $api->order->create(array(
                            'receipt' => 'rcptid_'.rand(1000,9999),
                            'amount' => $amount * 100, // Amount in paise
                            'currency' => 'INR',
                            'payment_capture' => 1
                        ));

                        // Save order ID in session to verify later
                        $_SESSION['razorpay_order_id'] = $order['id'];
                        $_SESSION['investment_amount'] = $amount;
            ?>
                        <!-- RAZORPAY CHECKOUT FORM -->
                        <form action="verify-payment.php" method="POST" id="razorpay-form">
                            <script src="https://checkout.razorpay.com/v1/checkout.js"
                                data-key="<?php echo $key_id; ?>"
                                data-amount="<?php echo $order['amount']; ?>"
                                data-currency="INR"
                                data-order_id="<?php echo $order['id']; ?>"
                                data-buttontext="अभी भुगतान करें"
                                data-name="इन्वेस्टमेंट ऐप"
                                data-description="₹<?php echo $amount; ?> का निवेश"
                                data-prefill.name="<?php echo $_SESSION['user_email']; ?>"
                                data-theme.color="#F37254"
                            ></script>
                        </form>
                        <div class="text-center mt-4">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">लोड हो रहा है...</span>
                            </div>
                            <p class="mt-2">भुगतान गेटवे लोड हो रहा है...</p>
                        </div>
            <?php
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">त्रुटि: '.$e->getMessage().'</div>';
                    }
                    exit();
                }
            }
            ?>

            <div class="investment-card">
                <h4>निवेश योजना</h4>
                <div class="mb-3">
                    <label for="investmentAmount" class="form-label">निवेश राशि (न्यूनतम ₹<?php echo $min_investment; ?>)</label>
                    <form method="POST">
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control form-control-lg" id="investmentAmount" 
                                name="amount" value="<?php echo $min_investment; ?>" 
                                min="<?php echo $min_investment; ?>" required>
                        </div>
                        <div class="form-text mb-3">28 दिनों में प्राप्त होगा: ₹<span id="returnAmount">1000</span></div>
                        
                        <button type="submit" class="btn btn-invest">निवेश करें</button>
                    </form>
                </div>
            </div>

            <div class="investment-card">
                <h4>निवेश के फायदे</h4>
                <ul class="benefits-list">
                    <li>28 दिनों में दोगुना रिटर्न</li>
                    <li>सुरक्षित और सुरक्षित लेनदेन</li>
                    <li>किसी भी समय निवेश ट्रैक करें</li>
                    <li>तुरंत रेफरल बोनस</li>
                </ul>
            </div>

            <div class="razorpay-logo">
                <img src="https://razorpay.com/assets/razorpay-glyph.svg" alt="Razorpay" width="100">
                <p class="mt-2 text-muted">सुरक्षित भुगतान Razorpay द्वारा</p>
            </div>

            <div class="text-center">
                <a href="dashboard.php" class="btn btn-outline-secondary">डैशबोर्ड पर वापस जाएं</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate return amount
        document.getElementById('investmentAmount').addEventListener('input', function() {
            const amount = parseInt(this.value) || 0;
            document.getElementById('returnAmount').textContent = amount * 2;
        });

        // Auto-submit form if Razorpay is loaded
        setTimeout(() => {
            const razorpayForm = document.getElementById('razorpay-form');
            if (razorpayForm) {
                document.querySelector('.spinner-border').style.display = 'none';
                razorpayForm.submit();
            }
        }, 2000);
    </script>
</body>
</html>