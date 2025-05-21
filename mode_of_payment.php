<?php
$conn = new mysqli("localhost", "root", "", "ticket_booking_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$redirectURL = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedMode = $_POST['mode'];

    // Get the latest unpaid payment
    $sql = "SELECT payment_id FROM payments WHERE payment_status = 'Unpaid' ORDER BY payment_id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $payment_id = $row['payment_id'];

        // Update payment record directly 
        $update_sql = "UPDATE payments 
                       SET mode_of_payment = '$selectedMode', payment_status = 'Processing' 
                       WHERE payment_id = $payment_id";
        $conn->query($update_sql);

        if ($selectedMode === 'debit/credit card') {
            $redirectURL = 'debit_or_credit.php';
    }
}
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-image: url('img2.png');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      background-repeat: no-repeat;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .payment-container {
      background-color: #fff;
      border-radius: 10px;
      padding: 45px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      width: 400px;
    }

    .payment-container h2 {
      text-align: center;
      margin-bottom: 25px;
      font-size: 24px;
    }

    .section {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 15px 20px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .section:hover {
      background-color: #f2f2f2;
    }

    .section h3 {
      margin: 0;
      font-size: 18px;
    }

    form {
      margin: 0;
    }

    button {
      background: none;
      border: none;
      width: 100%;
      text-align: left;
      padding: 0;
    }
  </style>
</head>
<body>

<div class="payment-container">
  <h2>💳 Payment</h2>

  <form method="POST">
    <button type="submit" name="mode" value="debit/credit card">
      <div class="section">
        <h3>💳 Debit & Credit Cards</h3>
      </div>
    </button>
  </form>
</div>

<?php if ($redirectURL): ?>
<script>
    window.location.href = "<?= $redirectURL ?>";
</script>
<?php endif; ?>

</body>
</html>
