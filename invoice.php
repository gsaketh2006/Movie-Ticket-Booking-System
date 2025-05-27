<?php
$conn = new mysqli("localhost", "root", "", "ticket_booking_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$booking = null;
$movie = null;
$theatre_name = null;
$show_time = null;
$total = 0; // Initialize total

$sql = "SELECT * FROM booking ORDER BY booking_id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $booking = $result->fetch_assoc();

    $movie_id = $booking['movie_id'];
    $movie_sql = "SELECT * FROM add_movie WHERE movie_id = $movie_id LIMIT 1";
    $movie_result = $conn->query($movie_sql);

    if ($movie_result && $movie_result->num_rows > 0) {
        $movie = $movie_result->fetch_assoc();
    }

    $show_id = $booking['show_id'];
    $show_sql = "SELECT theatre_id, show_time FROM shows WHERE show_id = $show_id LIMIT 1";
    $show_result = $conn->query($show_sql);

    if ($show_result && $show_result->num_rows > 0) {
        $show_row = $show_result->fetch_assoc();
        $theatre_id = $show_row['theatre_id'];
        $show_time = $show_row['show_time'];

        $theatre_sql = "SELECT theatre_name FROM theatres WHERE theatre_id = $theatre_id LIMIT 1";
        $theatre_result = $conn->query($theatre_sql);

        if ($theatre_result && $theatre_result->num_rows > 0) {
            $theatre_row = $theatre_result->fetch_assoc();
            $theatre_name = $theatre_row['theatre_name'];
        }
    }

    // Calculate subtotal, tax, etc.
    $seats = explode(",", $booking['seats_selected']);
    $subtotal = 0;
    foreach ($seats as $seat) {
        $seat = trim($seat);
        $row = strtoupper($seat[0] ?? '');
        if ($row == 'A') $cost = 350;
        elseif (in_array($row, range('B', 'L'))) $cost = 150;
        else $cost = 80;

        $subtotal += $cost;
    }

    $convenienceFee = 30;
    $tax = round($subtotal * 0.05);
    $total = $subtotal + $convenienceFee + $tax;

    // Insert into payments table
    $insert_sql = "INSERT INTO payments (booking_id, total, mode_of_payment, payment_status)
                   VALUES (?, ?, 'Pending', 'Unpaid')";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("id", $booking['booking_id'], $total);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Payment Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle, #000000, #1a1a1a);
            font-family: 'Segoe UI', sans-serif;
            color: white;
            padding: 40px;
        }
        .bill-container {
            max-width: 650px;
            margin: auto;
            background-color: #111;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 0 25px #6a5acd;
        }
        .bill-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .bill-header h2 {
            color: #dcd0ff;
            text-shadow: 1px 1px 5px #6a5acd;
        }
        .info-row p {
            margin: 6px 0;
        }
        table {
            width: 100%;
            margin-top: 15px;
            color: white;
            border: 1px solid #ccc;
        }
        th, td {
            border: 1px solid #666;
            padding: 6px;
            text-align: center;
        }
        .charges {
            margin-top: 15px;
            border-top: 1px dashed #aaa;
            border-bottom: 1px dashed #aaa;
            padding: 10px 0;
        }
        .charges p {
            display: flex;
            justify-content: space-between;
        }
        .total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 10px;
        }
        .pay-btn {
            width: 100%;
            background-color: #9370db;
            border: none;
            color: white;
            padding: 10px;
            font-size: 16px;
            border-radius: 6px;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .pay-btn:hover {
            background-color: #7a5dd0;
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="bill-header">
            <h2>🎟️ Moviemate Invoice</h2>
            <p><strong>Moviemate</strong> Payment Summary</p>
        </div>

        <?php if ($booking && $movie && $theatre_name && $show_time): ?>
        <div class="info-row">
            <p><strong>Movie Name:</strong> <?= htmlspecialchars($movie['name']) ?></p>
            <p><strong>Theatre:</strong> <?= htmlspecialchars($theatre_name) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($booking['booking_date']) ?></p>
            <p><strong>Show Timing:</strong> <?= htmlspecialchars($show_time) ?></p>
            <p><strong>Seats Booked:</strong> <?= htmlspecialchars($booking['seats_selected']) ?></p>
        </div>

        <table>
            <tr>
                <th>Seat No.</th>
                <th>Cost (₹)</th>
            </tr>
            <?php
            foreach ($seats as $seat) {
                $seat = trim($seat);
                $row = strtoupper($seat[0] ?? '');
                if ($row == 'A') $cost = 350;
                elseif (in_array($row, range('B', 'L'))) $cost = 150;
                else $cost = 80;

                echo "<tr><td>$seat</td><td>₹$cost</td></tr>";
            }
            ?>
        </table>

        <div class="charges">
            <p><span>Subtotal</span><span>₹<?= $subtotal ?></span></p>
            <p><span>Convenience Fee</span><span>₹<?= $convenienceFee ?></span></p>
            <p><span>Tax (5%)</span><span>₹<?= $tax ?></span></p>
        </div>

        <div class="total">
            <span>Total Amount</span><span>₹<?= $total ?></span>
        </div>

        <button class="pay-btn" onclick="window.location.href='mode_of_payment.php'">Proceed to Payment</button>

        <?php else: ?>
        <p class="text-center text-danger">❌ Booking or movie details not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
