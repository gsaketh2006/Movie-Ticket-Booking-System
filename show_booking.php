<?php


session_start();


$host = "localhost";
$username = "root";
$password = "";
$database = "ticket_booking_system";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$loggedIn = isset($_SESSION['user_id']);
$userId = $loggedIn ? $_SESSION['user_id'] : null;


$movieId = isset($_GET['id']) ? intval($_GET['id']) :
          (isset($_SESSION['selected_movie_id']) ? intval($_SESSION['selected_movie_id']) : 1);
$_SESSION['selected_movie_id'] = $movieId;


$movieQuery = "SELECT * FROM add_movie WHERE movie_id = $movieId";
$movieResult = $conn->query($movieQuery);
if ($movieResult->num_rows > 0) {
    $movieData = $movieResult->fetch_assoc();
    $movieName = $movieData['name'];
    $censorRating = $movieData['rating'];
    $genres = explode(',', $movieData['genre']);
    $language = $movieData['language'];
    $duration = $movieData['duration'];
    $releaseDate = isset($movieData['release_date']) ? $movieData['release_date'] : date('Y-m-d');
    $description = $movieData['description'];
} else {
    $movieName = "No movie Selected";
    $censorRating = "Not Known";
    $genres = ["Not Known"];
    $language = "Not Known";
    $duration = "N/A";
    $releaseDate = date('Y-m-d');
    $description = "No description available.";
}


function formatDuration($duration) {
    if (empty($duration)) return "N/A";
    if (!is_numeric($duration)) return htmlspecialchars($duration);
    $hours = floor($duration / 60);
    $minutes = $duration % 60;
    if ($hours > 0 && $minutes > 0) {
        return "{$hours}h {$minutes}m";
    } elseif ($hours > 0) {
        return "{$hours}h";
    } else {
        return "{$minutes}m";
    }
}


$theatersQuery = "SELECT t.theatre_id, t.theatre_name, t.location, t.address,
                s.show_id, s.show_time, s.start_date, s.end_date
                FROM theatres t
                JOIN shows s ON t.theatre_id = s.theatre_id
                WHERE s.movie_id = $movieId
                ORDER BY t.theatre_name, s.show_time";
$theatersResult = $conn->query($theatersQuery);


$theaters = [];
if ($theatersResult && $theatersResult->num_rows > 0) {
    while($row = $theatersResult->fetch_assoc()) {
        $theaterId = $row['theatre_id'];
        if (!isset($theaters[$theaterId])) {
            $theaters[$theaterId] = [
                'name' => $row['theatre_name'],
                'location' => $row['location'],
                'data_name' => strtolower(str_replace(' ', '', $row['theatre_name'] . ' ' . $row['location'])),
                'showtimes' => []
            ];
        }
        $theaters[$theaterId]['showtimes'][] = [
            'show_id' => $row['show_id'],
            'time' => date('h:i A', strtotime($row['show_time'])),
            'date' => $row['start_date'],
            'end_date' => $row['end_date']
        ];
    }
}


$dates = [];
$currentDate = new DateTime($releaseDate);
for ($i = 0; $i < 5; $i++) {
    $dateObj = clone $currentDate;
    $dateObj->modify("+$i day");
    $dates[] = [
        'date' => $dateObj->format('Y-m-d'),
        'formatted' => $dateObj->format('d M')
    ];
}


$selectedDate = $dates[0]['date'];
if (isset($_GET['date'])) {
    $selectedDate = $_GET['date'];
}
$_SESSION['selected_date'] = $selectedDate;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moviemate 🎟️ - Movie Booking</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    color: var(--text-color);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    }
   
    header {
    background: rgba(10, 10, 10, 0.9); 
    padding: 2em 5em;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid var(--primary-color);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.5); 
    }
   
    header h1 {
     font-family: 'Georgia';
    font-size: 3.2em;
    font-weight: 700;
    color: var(--secondary-color);
    text-shadow: 2px 2px 5px var(--primary-color);
    letter-spacing: 1.7px;
    }
   
    .search-container {
    position: relative;
    }
   
    .search-container input[type="text"] {
    padding: 0.9em 2.7em; 
    border-radius: 35px; 
    border: none;
    width: 350px; 
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-color);
    outline: none;
    transition: background-color 0.4s;
    }
   
    .search-container input[type="text"]:focus {
    background-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
   
    .search-container .fa-search {
    position: absolute;
    left: 25px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--secondary-color);
    opacity: 0.8; 
    }
   
    .movie-details, .theaters {
    margin: 3.5em auto;
    width: 92%; 
    max-width: 1100px; 
    }
   
    .movie-details {
    padding: 3em; 
    background: rgba(10, 10, 10, 0.9);
    border-radius: 25px; 
    box-shadow: 0 0 25px var(--primary-color); 
    text-align: center;
    }
   
    .movie-details h2 {
    font-size: 2.8em; 
    font-weight: 600;
    margin-bottom: 0.7em;
    color: var(--text-color);
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6); 
    }
   
    .badge {
    background-color: var(--primary-color);
    padding: 0.7em 1.4em; 
    border-radius: 30px;
    font-size: 1.2em; 
    margin: 0 0.6em;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
    }
   
    .movie-info {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 2em;
    margin: 1em 0;
    }
   
    .movie-info-item {
    display: flex;
    align-items: center;
    }
   
    .movie-info-item i {
    margin-right: 0.5em;
    color: var(--accent-color);
    }
   
    .dates {
    display: flex;
    justify-content: center;
    margin-top: 2em;
    flex-wrap: wrap;
    }
   
    .dates button {
    margin: 0.5em 0.6em;
    padding: 0.9em 1.7em; 
    border: none;
    border-radius: 12px; 
    background-color: rgba(255, 255, 255, 0.08); 
    color: var(--text-color);
    cursor: pointer;
    transition: background-color 0.4s, color 0.4s, transform 0.2s; 
    font-size: 1.1em;
    }
   
    .dates button.active, .dates button:hover {
    background-color: var(--accent-color);
    color: #fff;
    box-shadow: 0 3px 7px rgba(0, 0, 0, 0.5); 
    transform: translateY(-2px); 
    }
   
    .theaters {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); 
    gap: 2.5em; /* More gap */
    }
   
    .theater {
    background: rgba(10, 10, 10, 0.95);
    border-radius: 20px; 
    padding: 2.5em; 
    box-shadow: 0 0 20px var(--secondary-color); 
    transition: transform 0.3s, box-shadow 0.3s;
    }
   
    .theater:hover {
    transform: translateY(-7px); 
    box-shadow: 0 7px 25px var(--secondary-color); 
    }
   
    .theater h3 {
    color: var(--secondary-color);
    font-size: 1.8em; 
    margin-bottom: 0.8em;
    text-shadow: 1px 1px 5px var(--secondary-color); 
    }
   
    .theater p {
    font-size: 1.1em;
    margin-bottom: 1.2em;
    opacity: 0.9;
    }
   
    .showtimes {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8em; 
    }
   
    .showtimes button {
    padding: 0.9em 1.7em; 
    border: 2px solid var(--accent-color);
    border-radius: 10px; 
    background-color: transparent;
    color: var(--accent-color);
    transition: background-color 0.4s, color 0.4s, transform 0.2s; 
    font-size: 1.1em;
    cursor: pointer;
    }
   
    .showtimes button:hover {
    background-color: var(--accent-color);
    color: #fff;
    transform: translateY(-2px); 
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
    }
   
    /* User Account Area */
    .user-account {
    display: flex;
    align-items: center;
    }

    .user-account a {
    color: var(--secondary-color);
    text-decoration: none;
    margin-left: 1em;
    transition: color 0.3s;
    }

    .user-account a:hover {
    color: var(--accent-color);
    }

    /* Footer Styles */
    footer {
    background: var(--bg-color);
    color: var(--secondary-color); 
    text-align: center;
    padding: 1.2em; 
    margin-top: auto;
    border-top: 2px solid var(--primary-color);
    opacity: 0.9;
    }
   
   
    @media (max-width: 768px) {
    header {
    padding: 1.5em;
    flex-direction: column;
    align-items: center;
    }
   
    header h1 {
    font-size: 2.5em;
    margin-bottom: 0.5em;
    }
   
    .search-container input[type="text"] {
    width: 90%;
    max-width: 300px;
    }
   
    .movie-details, .theaters {
    width: 95%;
    margin: 2em auto;
    }
   
    .theaters {
    grid-template-columns: 1fr;
    }
    }
    </style>
</head>
<body>
    <header>
        <a href="index.php" style="text-decoration: none;">
            <h1>🎬 Moviemate</h1>
        </a>
    </header>

    <section class="movie-details">
        <h2><?php echo htmlspecialchars($movieName); ?></h2>
        <div class="movie-info">
            <span class="movie-info-item"><i class="fas fa-star"></i> <?php echo htmlspecialchars($censorRating); ?></span>
            <span class="movie-info-item"><i class="fas fa-language"></i> <?php echo htmlspecialchars($language); ?></span>
            <span class="movie-info-item"><i class="fas fa-clock"></i> <?php echo formatDuration($duration); ?></span>
        </div>
        <p>
            <?php foreach ($genres as $genre): ?>
                <span class="badge"><?php echo htmlspecialchars($genre); ?></span>
            <?php endforeach; ?>
        </p>
        <div class="about-movie" style="margin: 1.5em 0; text-align: left;">
            <h4>About the movie</h4>
            <p><?php echo nl2br(htmlspecialchars($description)); ?></p>
        </div>
        <div class="dates">
            <?php foreach ($dates as $date): ?>
                <button class="date-btn <?php echo ($date['date'] == $selectedDate) ? 'active' : ''; ?>"
                        data-date="<?php echo htmlspecialchars($date['date']); ?>">
                    <?php echo htmlspecialchars($date['formatted']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="theaters">
        <?php
        $anyTheaterShown = false;
        foreach ($theaters as $theater):
            $showsForDate = [];
            foreach ($theater['showtimes'] as $show) {
                if ($selectedDate >= $show['date'] && $selectedDate <= $show['end_date']) {
                    $showsForDate[] = $show;
                }
            }
            if (count($showsForDate) > 0):
                $anyTheaterShown = true;
        ?>
            <div class="theater" data-name="<?php echo htmlspecialchars($theater['data_name']); ?>">
                <h3><?php echo htmlspecialchars($theater['name']); ?>: <?php echo htmlspecialchars($theater['location']); ?></h3>
                <div class="showtimes">
                    <?php foreach ($showsForDate as $show): ?>
                        <button
                            data-show-id="<?php echo $show['show_id']; ?>"
                            data-time="<?php echo htmlspecialchars($show['time']); ?>"
                            data-theater="<?php echo htmlspecialchars($theater['name'] . ' ' . $theater['location']); ?>"
                        >
                            <?php echo htmlspecialchars($show['time']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php
            endif;
        endforeach;
        if (!$anyTheaterShown):
        ?>
            <div class="theater" style="grid-column: 1/-1; text-align: center;">
                <h3>No shows available for selected date</h3>
                <p>Please check back later or select another date.</p>
            </div>
        <?php endif; ?>
    </section>

    <footer>
        <p>---Book your seats only on moviemate---</p>
    </footer>

    <script>
    // Date button click
    document.querySelectorAll('.date-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const selectedDate = btn.dataset.date;
            window.location.href = "?id=<?php echo $movieId; ?>&date=" + selectedDate;
        });
    });

    // Showtime button click: redirect to seat_selection.php with all info
    document.querySelectorAll('.showtimes button').forEach(btn => {
        btn.addEventListener('click', () => {
            const showId = btn.dataset.showId;
            const movieId = <?php echo json_encode($movieId); ?>;
            const selectedDate = <?php echo json_encode($selectedDate); ?>;
            const theatre = btn.dataset.theater;
            const showTime = btn.dataset.time;
            window.location.href =
                `seat_selection.php?show_id=${showId}` +
                `&movie_id=${movieId}` +
                `&date=${encodeURIComponent(selectedDate)}` +
                `&theatre=${encodeURIComponent(theatre)}` +
                `&show_time=${encodeURIComponent(showTime)}`;
        });
    });
    </script>
</body>
</html>
<?php
$conn->close();
?>
