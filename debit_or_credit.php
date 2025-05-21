<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "ticket_booking_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch latest booking and payment info, including showtime
$booking_sql = "SELECT b.movie_id, b.seats_selected, m.name, p.total, s.show_time, b.booking_id
                FROM booking b
                JOIN add_movie m ON b.movie_id = m.movie_id
                JOIN payments p ON b.booking_id = p.booking_id
                JOIN shows s ON b.show_id = s.show_id
                ORDER BY b.booking_id DESC LIMIT 1";

$result = $conn->query($booking_sql);
$movie_name = "N/A";
$total = "N/A";
$seats = "N/A"; 
$show_time = "N/A";  
$booking_id = null;

if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $movie_name = $row['name'];
  $total = $row['total'];
  $seats = $row['seats_selected'];
  $show_time = $row['show_time']; 
  $booking_id = $row['booking_id']; 
}

$paid_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Start a transaction to ensure both updates happen or none happen
  $conn->begin_transaction();
  
  try {
    // Update payment status to 'paid'
    $update_payment_status = "UPDATE payments SET payment_status = 'paid' WHERE booking_id = ?";
    $stmt = $conn->prepare($update_payment_status);
    $stmt->bind_param("s", $booking_id);
    $stmt->execute();
    $stmt->close();
    
    // Update booking status to 'confirmed'
    $update_booking_status = "UPDATE booking SET status = 'confirmed' WHERE booking_id = ?";
    $stmt = $conn->prepare($update_booking_status);
    $stmt->bind_param("s", $booking_id);
    $stmt->execute();
    $stmt->close();
    
    // If we get here without exceptions, commit the transaction
    $conn->commit();
    
    // Redirect after successful payment and status updates
    echo "<script>
        alert('✅ Payment successful! Booking confirmed.');
        window.location.href='front.php';
      </script>";
  } catch (Exception $e) {
    // An error occurred; rollback the transaction
    $conn->rollback();
    $paid_message = "❌ Payment failed. Please try again. Error: " . $e->getMessage();
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Page - Movie Ticket Booking</title>
  <style>
    * {
      box-sizing: border-box;
    }

    :root {
      --primary-color: #6a5acd;
      --secondary-color: #e6e6fa;
      --bg-color: #0a0a0a;
      --text-color: #fff;
      --accent-color: #9370db;
      --card-bg: rgba(20, 20, 30, 0.95);
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0a0a0a, #1c1c2c);
      color: var(--text-color);
      min-height: 100vh;
    }

    .main-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      padding: 40px 20px;
      gap: 30px;
    }

    .summary-box, .payment-form {
      background: var(--card-bg);
      border-radius: 20px;
      padding: 30px;
      flex: 1 1 320px;
      max-width: 500px;
      box-shadow: 0 0 25px rgba(255, 255, 255, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .summary-box:hover, .payment-form:hover {
      transform: scale(1.02);
      box-shadow: 0 0 30px var(--accent-color);
    }

    h2 {
      margin-top: 0;
      text-align: center;
      margin-bottom: 20px;
      color: var(--accent-color);
      text-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    }

    input[type="text"],
    input[type="checkbox"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 0px solid var(--accent-color);
      border-radius: 30px;
      background-color: rgba(0,0,0,0.3);
      color: var(--text-color);
      font-size: 16px;
      transition: all 0.3s ease;
    }

    input[type="text"]:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 15px var(--accent-color);
    }

    input::placeholder {
      color: #aaa;
    }

    .checkbox-wrapper {
      display: flex;
      align-items: center;
      margin: 10px 0;
    }

    .checkbox-wrapper input[type="checkbox"] {
      width: auto;
      margin-right: 10px;
      transform: scale(1.2);
      accent-color: var(--accent-color);
    }

    .checkbox-wrapper label {
      font-size: 15px;
      color: var(--secondary-color);
      cursor: pointer;
    }

    .btn {
      background-color: var(--accent-color);
      color: #000;
      font-size: 18px;
      font-weight: bold;
      border: none;
      padding: 12px;
      border-radius: 30px;
      cursor: pointer;
      width: 100%;
      margin-top: 10px;
      transition: all 0.3s ease;
    }

    .btn:hover {
      background-color: #7b68ee;
      transform: scale(1.05);
      box-shadow: 0 0 15px var(--accent-color);
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      margin: 10px 0;
      font-size: 17px;
      color: var(--secondary-color);
    }

    .summary-total {
      font-size: 20px;
      font-weight: bold;
      margin-top: 20px;
      border-top: 2px solid var(--accent-color);
      padding-top: 10px;
    }

    .highlight {
      color: var(--accent-color);
      font-weight: 600;
    }
    .success-message {
      text-align: center;
      color: #90ee90;
      font-size: 18px;
      margin-top: 15px;
      font-weight: bold;
      text-shadow: 0 0 5px #7fff7f;
    }
  </style>
</head>
<body>

<div class="main-container">

  <!-- Summary Box -->
  <div class="summary-box">
    <h2>Payment Summary</h2>

    <div class="summary-item"><span>Movie:</span><span class="highlight"><?php echo htmlspecialchars($movie_name); ?></span></div>
    <div class="summary-item"><span>Showtime:</span><span class="highlight"><?php echo htmlspecialchars($show_time); ?></span></div>
    <div class="summary-item"><span>Seats:</span><span class="highlight"><?php echo htmlspecialchars($seats); ?></span></div>

    <div class="summary-item summary-total">
      <span>Total Payable:</span>
      <span class="highlight">₹<?php echo htmlspecialchars($total); ?></span>
    </div>
  </div>

  <!-- Payment Form -->
  <div class="payment-form">
    <h2>Enter Payment Details</h2>
    <form action="" method="POST">
      <input type="text" placeholder="Cardholder Name" required>
      <input type="text" id="cardNumber" placeholder="Card Number (e.g. 1234-5678-9012-3456)" maxlength="19" required oninput="formatCardNumber()">
      <input type="text" id="expiry" placeholder="MM/YY" maxlength="5" required oninput="formatExpiry()">
      <input type="text" placeholder="CVV" maxlength="3" required>
      <button class="btn" type="submit">Pay Now</button>
    </form>

    <?php if (!empty($paid_message)): ?>
      <div class="success-message"><?php echo $paid_message; ?></div>
    <?php endif; ?>
  </div>
</div>

<script>
  function formatCardNumber() {
    const cardInput = document.getElementById("cardNumber");
    let value = cardInput.value.replace(/\D/g, "").substring(0, 16);
    cardInput.value = value.match(/.{1,4}/g)?.join("-") ?? "";
  }

  function formatExpiry() {
    const expiryInput = document.getElementById("expiry");
    let value = expiryInput.value.replace(/\D/g, "").substring(0, 4);
    if (value.length >= 3) {
      expiryInput.value = value.substring(0, 2) + "/" + value.substring(2);
    } else {
      expiryInput.value = value;
    }
  }
</script>

</body>
</html>