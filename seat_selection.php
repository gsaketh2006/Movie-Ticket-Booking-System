<?php
session_start();

$servername = "localhost";
$username = "root";  
$password = "";  
$dbname = "ticket_booking_system";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


error_reporting(E_ALL);
ini_set('display_errors', 1);


$loggedIn = isset($_SESSION['user_id']);
$userId = $loggedIn ? $_SESSION['user_id'] : null;

// Get parameters from URL
$show_id = isset($_GET['show_id']) ? intval($_GET['show_id']) : 0;
$movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$theatre = isset($_GET['theatre']) ? $_GET['theatre'] : '';
$show_time = isset($_GET['show_time']) ? $_GET['show_time'] : '';

// Get movie name
$movie_name = "Unknown Movie";
if ($movie_id > 0) {
    $movie_query = "SELECT name FROM add_movie WHERE movie_id = ?";
    $stmt = $conn->prepare($movie_query);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $movie_name = $row['name'];
    }
    $stmt->close();
}

// Get all seats with their status for the selected show
$bookedSeats = [];
$heldSeats = [];
if ($show_id > 0) {
    
    $sql = "SELECT seats_selected, status FROM booking WHERE show_id = ? AND booking_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $show_id, $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $seats = explode(",", $row['seats_selected']);
        if ($row['status'] == 'confirmed') {
            $bookedSeats = array_merge($bookedSeats, $seats);
        } elseif ($row['status'] == 'hold') {
            $heldSeats = array_merge($heldSeats, $seats);
        }
    }
    $stmt->close();
}


function generateBookingID() {
    return 'BKG' . time() . rand(1000, 9999);
}


$display_date = date('d M Y', strtotime($selected_date));


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    error_log("Form submitted. POST data: " . print_r($_POST, true));
    
    // Get form data
    $seats = isset($_POST['seats']) ? $_POST['seats'] : '';
    $amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
    
    // Calculate number of seats selected
    $seatsArray = explode(',', $seats);
    $no_of_seats_selected = count($seatsArray);
    
   
    error_log("Parsed data - Seats: '$seats', Number of seats: $no_of_seats_selected, Amount: $amount, User ID: $userId, Show ID: $show_id");

    if ($show_id > 0 && !empty($seats) && $amount > 0 && $userId) {
        // Check if seats are still available
        $unavailableSeats = [];

        $sql = "SELECT seats_selected FROM booking WHERE show_id = ? AND booking_date = ? AND (status = 'confirmed' OR status = 'hold')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $show_id, $selected_date);
        $stmt->execute();
        $result = $stmt->get_result();

        
        error_log("Checking seat availability for show_id: $show_id, date: $selected_date");

        while ($row = $result->fetch_assoc()) {
            $alreadyBooked = explode(',', $row['seats_selected']);
            error_log("Already booked or held seats: " . implode(',', $alreadyBooked));
            foreach ($seatsArray as $seat) {
                if (in_array($seat, $alreadyBooked)) {
                    $unavailableSeats[] = $seat;
                }
            }
        }
        $stmt->close();

        if (!empty($unavailableSeats)) {
            $error_message = 'Some seats are no longer available: ' . implode(', ', $unavailableSeats);
            error_log("Unavailable seats detected: " . implode(', ', $unavailableSeats));
        } else {
            // Generate booking ID
            $booking_id = generateBookingID();
            $status = "hold"; // Set status to "hold" by default

           
            error_log("Attempting to insert booking - Booking ID: $booking_id, User ID: $userId, Movie ID: $movie_id, Show ID: $show_id, Seats: $seats, Number of seats: $no_of_seats_selected, Amount: $amount, Date: $selected_date");

            // Insert booking into database with the no_of_seats_selected column 
            // and without booking_time column
            $sql = "INSERT INTO booking (booking_id, id, movie_id, show_id, seats_selected, no_of_seats_selected, cost, status, booking_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "siiisiiss",
                $booking_id,
                $userId,
                $movie_id,
                $show_id,
                $seats,
                $no_of_seats_selected,
                $amount,
                $status,
                $selected_date
            );
            
            if ($stmt->execute()) {
                header("Location: invoice.php?booking_id=" . $booking_id);
                exit;
            } else {
                // Show error, do not redirect
                $error_message = 'Error: ' . $stmt->error;
                error_log("SQL Error during booking insert: " . $stmt->error);
            }
            
            $stmt->close();
        }
    } else {
        if (!$userId) {
            $error_message = 'You must be logged in to book tickets';
            error_log("Booking failed: User not logged in");
        } else if (empty($seats)) {
            $error_message = 'No seats selected';
            error_log("Booking failed: No seats selected");
        } else if ($amount <= 0) {
            $error_message = 'Invalid amount';
            error_log("Booking failed: Invalid amount ($amount)");
        } else if ($show_id <= 0) {
            $error_message = 'Invalid show selected';
            error_log("Booking failed: Invalid show ID ($show_id)");
        } else {
            $error_message = 'Missing required booking information';
            error_log("Booking failed: Missing required information - Seats: '$seats', Amount: $amount, User ID: $userId, Show ID: $show_id");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Moviemate 🎟️ - Seat Selection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6a5acd; 
            --secondary-color: #e6e6fa; 
            --bg-color: #0a0a0a; 
            --text-color: #fff;
            --accent-color: #9370db; 
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom, var(--bg-color), #222); 
            color: white;
            text-align: center;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .screen {
            width: 80%;
            height: 100px;
            margin: 30px auto 10px auto;
            border-radius: 10px;
            background: radial-gradient(circle at center, #c0c0c0, #c0c0c0, #888888);
            box-shadow: inset 0 -15px 20px rgba(0, 0, 0, 0.3), 0 5px 15px rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.6rem;
            font-weight: bold;
            color: #222;
            text-shadow: 1px 1px 2px rgba(255,255,255,0.6);
            border: 2px solid rgba(255,255,255,0.15);
        }
        .theater-container {
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            gap: 0px;
            padding: 20px;
        }
        .row-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .row-label {
            width: 40px;
            text-align: right;
            font-weight: bold;
            font-size: 1rem;
            color: #ffcc00;
        }
        .row-container {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .seat {
            width: 40px;
            height: 40px;
            background: linear-gradient(to top, #2b01478a, #474747);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: inset 3px 3px 5px rgba(255, 255, 255, 0.5);
            font-size: 15px;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .seat:hover { background: #250039; }
        .seat.selected {
            background: #00ff00;
            box-shadow: 0 0 15px #00ff00;
        }
        .seat.sold {
            background: #ff4d4d;
            cursor: not-allowed;
        }
        .seat.hold {
            background: #ffcc00;
            cursor: not-allowed;
        }
        .aisle {
            width: 22px;
            height: 40px;
        }
        .book-btn {
            margin-top: 30px;
            padding: 14px 30px;
            background: linear-gradient(to right, #ff416c, #ff4d4d);
            color: white;
            font-size: 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .book-btn:hover { transform: scale(1.08); }
        .summary {
            margin-top: 20px;
            font-size: 18px;
            color: #00ff00;
        }
        .legend {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: linear-gradient(to bottom, var(--bg-color), #222);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 14px;
        }
        .legend .seat {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            margin-right: 10px;
        }
        .price-label {
            width: 100%;
            text-align: center;
            color: lightgray;
            font-size: 16px;
            margin: 10px 0 20px 0;
        }
        header {
            background: rgba(10, 10, 10, 0.9);
            padding: 1em 2em;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid var(--primary-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
        }
        header h1 {
            font-family: 'Georgia';
            font-size: 2.5em;
            font-weight: 700;
            color: var(--secondary-color);
            text-shadow: 2px 2px 5px var(--primary-color);
            letter-spacing: 1.7px;
            margin: 0;
        }
        h4 {
            font-family: 'Georgia';
            color: rgb(0, 208, 255);
            font-weight: bold;
            margin: 20px 0;
        }
        .seats-remaining {
            margin-top: 20px;
            font-size: 18px;
            color: #ffcc00;
        }
        .movie-info {
            margin: 20px auto;
            max-width: 800px;
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--primary-color);
        }
        .total-cost {
            margin-top: 10px;
            font-size: 20px;
            color: #ff9900;
            font-weight: bold;
        }
        .error-message {
            background-color: #ffcccc;
            color: #cc0000;
            padding: 10px;
            border-radius: 5px;
            margin: 10px auto;
            max-width: 600px;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php" style="text-decoration: none;">
            <h1>🎬 Moviemate</h1>
        </a>
    </header>
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <div class="movie-info">
            <h4>
                🎬 Movie: <span id="movieName"><?php echo htmlspecialchars($movie_name); ?></span> | 
                🏢 Theater: <span id="theaterName"><?php echo htmlspecialchars($theatre); ?></span> | 
                🗓️ Date: <span id="date"><?php echo htmlspecialchars($display_date); ?></span> | 
                🕒 Time: <span id="time"><?php echo htmlspecialchars($show_time); ?></span>
            </h4>
        </div>

        <div class="seats-remaining">
            Seats Remaining: <span id="seatsRemaining">0</span>
        </div>
        <div class="theater-container" id="theater"></div>
        <div class="screen mt-4">🎥 Big Screen</div>
        
        <form id="bookingForm" method="POST" action="">
            <input type="hidden" name="seats" id="seatsInput" value="">
            <input type="hidden" name="amount" id="amountInput" value="0">
            <input type="hidden" name="no_of_seats" id="noOfSeatsInput" value="0">
            
            <div class="summary">
                Selected Seats: <span id="selectedSeats">None</span><br/>
                Number of Seats: <span id="seatCount">0</span>
            </div>
            <div class="total-cost">
                Total Cost: ₹<span id="totalAmount">0</span>
            </div>
            <button type="submit" class="btn btn-danger mt-3 book-btn" id="bookBtn">Book Now</button>
        </form>
    </div>
    <div class="legend mt-4 text-start">
        <div class="d-flex align-items-center mb-2">
            <div class="seat selected" style="box-shadow: none;"></div> <span>Selected</span>
        </div>
        <div class="d-flex align-items-center mb-2">
            <div class="seat"></div> <span>Available</span>
        </div>
        <div class="d-flex align-items-center mb-2">
            <div class="seat hold"></div> <span>On Hold</span>
        </div>
        <div class="d-flex align-items-center mb-2">
            <div class="seat sold"></div> <span>Confirmed</span>
        </div>
    </div>
    <script>
        
        const bookedSeats = <?php echo json_encode($bookedSeats); ?>;
        const heldSeats = <?php echo json_encode($heldSeats); ?>;
        console.log("Booked seats:", bookedSeats);
        console.log("Seats on hold:", heldSeats);

        const rows = "PONMLKJIHGFEDCBA".split("");
        const seatsPerRow = 20;
        let selectedSeats = [];

        function renderSeats() {
            const theater = document.getElementById("theater");
            theater.innerHTML = "";

            rows.forEach((row) => {
                const rowWrapper = document.createElement("div");
                rowWrapper.classList.add("row-wrapper");

                const rowLabel = document.createElement("div");
                rowLabel.classList.add("row-label");
                rowLabel.textContent = row;

                const rowContainer = document.createElement("div");
                rowContainer.classList.add("row-container");

                for (let i = 1; i <= seatsPerRow; i++) {
                    let seatNumber = row === 'A' ? i - 1 : i;

                    if (row === 'A' && i === 1) {
                        const emptySpace = document.createElement("div");
                        emptySpace.classList.add("aisle");
                        rowContainer.appendChild(emptySpace);
                        continue;
                    }

                    if (row !== 'A') {
                        if (row >= 'B' && row <= 'K') {
                            if (i === 11) {
                                const aisle = document.createElement("div");
                                aisle.classList.add("aisle");
                                rowContainer.appendChild(aisle);
                            }
                        } else {
                            if (i === 8 || i === 15) {
                                const aisle = document.createElement("div");
                                aisle.classList.add("aisle");
                                rowContainer.appendChild(aisle);
                            }
                        }
                    }

                    const seat = document.createElement("div");
                    seat.classList.add("seat", "border", "border-light", "p-2");
                    seat.dataset.seat = `${row}${seatNumber}`;
                    seat.textContent = seatNumber;

                    const seatId = `${row}${seatNumber}`;
                    if (bookedSeats.includes(seatId)) {
                        seat.classList.add("sold");
                    } else if (heldSeats.includes(seatId)) {
                        seat.classList.add("hold");
                    }

                    seat.addEventListener("click", () => toggleSeat(seat));
                    rowContainer.appendChild(seat);
                }

                rowWrapper.appendChild(rowLabel);
                rowWrapper.appendChild(rowContainer);
                theater.appendChild(rowWrapper);

                if (row === 'L') addPriceLabel(theater, "₹80 per ticket");
                if (row === 'B') addPriceLabel(theater, "₹150 per ticket");
                if (row === 'A') addPriceLabel(theater, "₹350 per ticket");
            });
            updateSeatsRemaining();
        }

        function addPriceLabel(theater, text) {
            const walkway = document.createElement("div");
            walkway.style.height = "20px";
            walkway.style.width = "100%";
            theater.appendChild(walkway);

            const label = document.createElement("div");
            label.classList.add("price-label");
            label.textContent = text;
            theater.appendChild(label);
        }

        function toggleSeat(seat) {
            if (seat.classList.contains("sold") || seat.classList.contains("hold")) return;
            
            seat.classList.toggle("selected");
            const seatID = seat.dataset.seat;
            
            if (selectedSeats.includes(seatID)) {
                selectedSeats = selectedSeats.filter(s => s !== seatID);
            } else {
                selectedSeats.push(seatID);
            }
            
            updateSummary();
            console.log("Selected seats updated:", selectedSeats);
        }

        function updateSummary() {
            document.getElementById("selectedSeats").textContent = selectedSeats.length ? selectedSeats.join(", ") : "None";
            document.getElementById("seatCount").textContent = selectedSeats.length;
            
            // Calculate total cost
            let total = 0;
            selectedSeats.forEach(seatID => {
                const row = seatID[0];
                if (row === 'A') total += 350;
                else if (row >= 'B' && row <= 'L') total += 150;
                else total += 80;
            });
            
            document.getElementById("totalAmount").textContent = total;
            
            // Update form inputs
            document.getElementById("seatsInput").value = selectedSeats.join(",");
            document.getElementById("amountInput").value = total;
            document.getElementById("noOfSeatsInput").value = selectedSeats.length;
            
            console.log("Form inputs updated - Seats:", document.getElementById("seatsInput").value);
            console.log("Form inputs updated - Number of seats:", document.getElementById("noOfSeatsInput").value);
            console.log("Form inputs updated - Amount:", document.getElementById("amountInput").value);
            
            updateSeatsRemaining();
        }

        function updateSeatsRemaining() {
            const totalSeats = rows.length * seatsPerRow - 1;
            const unavailableSeats = bookedSeats.length + heldSeats.length;
            const remaining = totalSeats - unavailableSeats;
            document.getElementById("seatsRemaining").textContent = remaining;
        }

       
        document.getElementById("bookingForm").addEventListener("submit", function(e) {
            
            document.getElementById("seatsInput").value = selectedSeats.join(",");
            document.getElementById("amountInput").value = document.getElementById("totalAmount").textContent;
            document.getElementById("noOfSeatsInput").value = selectedSeats.length;
            
            console.log("Form submission - Seats:", document.getElementById("seatsInput").value);
            console.log("Form submission - Number of seats:", document.getElementById("noOfSeatsInput").value);
            console.log("Form submission - Amount:", document.getElementById("amountInput").value);

            if (selectedSeats.length === 0) {
                e.preventDefault();
                alert("Please select at least one seat.");
                return false;
            }
            return true;
        });

        // Initialize seats
        window.onload = function() {
            renderSeats();
            console.log("Page fully loaded, seats rendered");
        };
    </script>
</body>
</html>