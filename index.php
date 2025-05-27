<?php
$host = "localhost";
$username = "root"; 
$password = "";     
$database = "ticket_booking_system";


session_start();


$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) 
{
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
$user_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_details = null;

// If user is logged in, fetch details from registration table
if ($user_logged_in) 
{
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT * FROM registration WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    if ($user_result && $user_result->num_rows > 0) {
        $user_details = $user_result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch movies
$sql = "SELECT * FROM add_movie";
$result = $conn->query($sql);
$movies = [];
$genres = [];
$languages = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
        if (!in_array($row['genre'], $genres)) {
            $genres[] = $row['genre'];
        }
        if (!in_array($row['language'], $languages)) {
            $languages[] = $row['language'];
        }
    }
}


$date_order = isset($_GET['date_order']) && $_GET['date_order'] === 'asc' ? 'asc' : 'desc';


usort($movies, function($a, $b) use ($date_order) {
    $timeA = strtotime($a['release_date']);
    $timeB = strtotime($b['release_date']);
    return $date_order === 'asc' ? $timeA - $timeB : $timeB - $timeA;
});
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moviemate</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
            background: linear-gradient(135deg, #0a0a0a, #1c1c2c);
            color: var(--text-color);
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .navbar {
            background: rgba(10, 10, 10, 0.95);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.8);
            padding: 1rem 2rem;
        }
        .navbar-brand {
            font-family: 'Georgia';
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--secondary-color);
            text-shadow: 3px 3px 10px var(--primary-color);
        }
        .navbar-nav .nav-link {
            color: #ffffff;
            font-size: 20px;
            font-weight: 600;
            margin-left: 15px;
            transition: color 0.3s;
        }
        .navbar-nav .nav-link:hover {
            color: var(--accent-color);
        }
        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 30px;
            transition: all 0.3s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #7b68ee;
            transform: scale(1.1);
            box-shadow: 0 0 15px var(--accent-color);
        }
        .carousel {
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 40px;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.1);
        }
        .carousel img {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            transition: transform 0.5s ease-in-out;
        }
        .carousel img:hover {
            transform: scale(1.03);
        }
        .container {
            padding: 30px;
            background: rgba(10, 10, 10, 0.9);
            border-radius: 25px;
            box-shadow: 0 0 25px var(--primary-color);
        }
        .input-group-text {
            background-color: #181818;
            border: 2px solid var(--accent-color);
            border-right: none;
            border-radius: 30px 0 0 30px;
            color: white;
        }
        .search-container .form-control {
            border: 2px solid var(--accent-color);
            border-left: none;
            border-radius: 0 30px 30px 0;
            background-color: #eacdff;
            color: rgb(0, 0, 0);
            padding: 12px 20px;
        }
        .search-container .form-control:focus {
            border-color: #7b68ee;
            box-shadow: 0 0 10px var(--accent-color);
            background-color: #ffffff;
            color: rgb(0, 0, 0);
        }
        .form-select {
            border: 2px solid var(--accent-color);
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
            background-color: #000000;
            color: white;
        }
        .form-select:focus {
            border-color: #7b68ee;
            box-shadow: 0 0 10px var(--accent-color);
        }
        .card {
            color: #ffffff;
            margin-bottom: 20px;
            background: rgba(20, 20, 30, 0.95);
            border-radius: 20px;
            padding: 1.2em;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 25px var(--accent-color);
        }
        .card img {
            height: 350px;
            object-fit: cover;
            border-radius: 15px;
            width: 100%;
        }
        h5 {
            color: var(--accent-color);
            font-weight: bold;
            margin-top: 10px;
            font-size: 1.2em;
        }
        .badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--accent-color);
            font-size: 0.9em;
            padding: 5px 10px;
            border-radius: 12px;
        }
        .stars {
            color: gold;
            font-size: 1em;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 14px;
            color: #aaa;
        }
        
      
        .profile-dropdown {
            background: #1d1b31;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 25px rgba(106, 90, 205, 0.6);
            width: 320px;
            border: 1px solid #403d66;
        }
        
        .profile-dropdown h6 {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 12px;
            margin-bottom: 18px;
            text-align: center;
        }
        
        .profile-dropdown .user-info {
            margin-bottom: 20px;
            background: rgba(15, 14, 26, 0.5);
            padding: 15px;
            border-radius: 10px;
        }
        
        .profile-dropdown .user-info p {
            margin-bottom: 12px;
            font-size: 1rem;
            color: #e0e0e0;
            display: flex;
            justify-content: space-between;
        }
        
        .profile-dropdown .user-info strong {
            color: var(--accent-color);
            font-weight: 600;
            display: inline-block;
            min-width: 80px;
        }
        
        .profile-dropdown .user-info span {
            color: white;
            flex-grow: 1;
            text-align: right;
            word-break: break-word;
        }
        
        .dropdown-menu {
            padding: 0;
            border: none;
            background: transparent;
        }
        
        .profile-btn {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #9370db, #6a5acd);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            font-weight: 600;
            border: 2px solid rgba(255, 255, 255, 0.3);
            margin-left: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(106, 90, 205, 0.5);
        }
        
        .profile-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px var(--accent-color);
            border-color: white;
        }
        
        .profile-action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
        }
        
        .profile-action-buttons .btn {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-outline-light {
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: white;
        }
        
        .btn-outline-danger {
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
        }
        
        .btn-outline-danger:hover {
            background-color: rgba(255, 107, 107, 0.1);
            color: #ff8f8f;
        }
        
        .not-logged-in-message {
            text-align: center;
            padding: 20px 15px;
            color: #e0e0e0;
        }
        
        .login-links {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 15px;
        }
        
        .login-links .btn {
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">🎬 Moviemate</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">🏠 Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#movieSection">🎥 Movies</a></li>
                    <li class="nav-item dropdown">
                        <!-- Profile button (always shown) -->
                        <button class="profile-btn dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $user_logged_in ? strtoupper(substr($user_details['name'] ?? 'U', 0, 1)) : '👤'; ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <div class="profile-dropdown">
                                <?php if ($user_logged_in && $user_details): ?>
                                    <!-- User is logged in - show profile details -->
                                    <h6>Welcome, <?php echo explode(' ', trim($user_details['name']))[0]; ?>!</h6>
                                    <div class="user-info">
                                        <p>
                                            <strong>Name:</strong> 
                                            <span><?php echo htmlspecialchars($user_details['name'] ?? 'Unknown'); ?></span>
                                        </p>
                                        <p>
                                            <strong>Email:</strong> 
                                            <span><?php echo htmlspecialchars($user_details['email'] ?? 'Unknown'); ?></span>
                                        </p>
                                        <p>
                                            <strong>Phone:</strong> 
                                            <span><?php echo htmlspecialchars($user_details['phone'] ?? 'Not provided'); ?></span>
                                        </p>
                                       
                                    </div>
                                    <div class="profile-action-buttons">
                                        <a href="logout.php" class="btn btn-outline-danger">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- User is not logged in - show login prompt -->
                                    <div class="not-logged-in-message">
                                        <h6>Not Logged In</h6>
                                        <p>Please login to view your profile and book tickets</p>
                                        <div class="login-links">
                                            <a href="login.php" class="btn btn-primary">
                                                <i class="bi bi-box-arrow-in-right"></i> Login
                                            </a>
                                            <a href="register.php" class="btn btn-outline-light">
                                                <i class="bi bi-person-plus"></i> Register
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Carousel -->
        <div id="movieCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $first = true;
                foreach ($movies as $movie) {
                    echo '<div class="carousel-item ' . ($first ? 'active' : '') . '">';
                    // Make banner clickable
                    echo '<a href="show_booking.php?id=' . $movie['movie_id'] . '">';
                    echo '<img src="' . $movie['banner'] . '" class="d-block w-100" alt="' . htmlspecialchars($movie['name']) . '">';
                    echo '</a>';
                    echo '</div>';
                    $first = false;
                }
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#movieCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#movieCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
        <div class="row mt-4" id="movieSection">
            <div class="col-md-3">
                <div class="search-container">
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-white border-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search Movie or Theatre" id="searchInput">
                    </div>
                </div>

                <h5 class="mt-4">🗣 Languages</h5>
                <select class="form-select" id="languageSelect">
                    <option value="all" selected>All</option>
                    <?php
                    foreach ($languages as $language) {
                        echo '<option value="' . strtolower($language) . '">' . ucfirst($language) . '</option>';
                    }
                    ?>
                </select>

                <h5>📊 Genre</h5>
                <select class="form-select" id="genreSelect">
                    <option value="all" selected>All Genres</option>
                    <?php
                    foreach ($genres as $genre) {
                        echo '<option value="' . strtolower(htmlspecialchars($genre)) . '">' . htmlspecialchars($genre) . '</option>';
                    }
                    ?>
                </select>
                
                <h5>📅 Date</h5>
                <select class="form-select" id="dateSelect">
                    <option value="desc" <?php if($date_order === 'desc') echo 'selected'; ?>>New to Old</option>
                    <option value="asc" <?php if($date_order === 'asc') echo 'selected'; ?>>Old to New</option>
                </select>
            </div>

            <div class="col-md-9">
                <h5>🎬 Movies Now Showing</h5>
                <div class="row" id="movieCards">
                    <?php
                    foreach ($movies as $movie) {
                        echo '<div class="col-md-4 movie-card" data-language="' . strtolower($movie['language']) . '" data-genre="' . strtolower($movie['genre']) . '">';
                        echo '<div class="card position-relative">';
                        echo '<span class="badge">' . htmlspecialchars($movie['language']) . '</span>';
                        // Make poster clickable
                        echo '<a href="show_booking.php?id=' . $movie['movie_id'] . '">';
                        echo '<img src="' . $movie['poster'] . '" class="card-img-top" alt="' . htmlspecialchars($movie['name']) . '">';
                        echo '</a>';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . htmlspecialchars($movie['name']) . '</h5>';
                        echo '<div class="stars">' . str_repeat('⭐', intval($movie['rating'])) . '</div>';
                        echo '<p class="card-text"><small>' . htmlspecialchars($movie['genre']) . '</small></p>';
                        echo '<a href="show_booking.php?id=' . $movie['movie_id'] . '"><button class="btn btn-primary mt-2">🎟 Book Now</button></a>';
                        echo '</div></div></div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="footer mt-5">
            --- Book your tickets only on moviemate ---
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById("searchInput");
            const languageSelect = document.getElementById("languageSelect");
            const genreSelect = document.getElementById("genreSelect");
            const dateSelect = document.getElementById("dateSelect");

            // Smooth scroll for the Movies link
            document.querySelector('a[href="#movieSection"]').addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('#movieSection').scrollIntoView({
                    behavior: 'smooth'
                });
            });

            function filterCards() {
                const query = searchInput.value.toLowerCase().trim();
                const selectedLanguage = languageSelect.value;
                const selectedGenre = genreSelect.value;

                const cards = document.querySelectorAll("#movieCards > div");
                cards.forEach(card => {
                    const title = card.querySelector(".card-title").textContent.toLowerCase();
                    const language = card.getAttribute("data-language");
                    const genre = card.getAttribute("data-genre");
                    
                    const matchesSearch = title.includes(query);
                    const matchesLanguage = (selectedLanguage === "all" || selectedLanguage === language);
                    const matchesGenre = (selectedGenre === "all" || selectedGenre === genre);
                    
                    card.style.display = (matchesSearch && matchesLanguage && matchesGenre) ? "block" : "none";
                });

                const carouselItems = document.querySelectorAll(".carousel-item");
                let firstVisible = null;

                carouselItems.forEach(item => {
                    const altText = item.querySelector("img").alt.toLowerCase();
                    if (query === "" || altText.includes(query)) {
                        item.style.display = "block";
                        if (!firstVisible) firstVisible = item;
                    } else {
                        item.style.display = "none";
                    }
                    item.classList.remove("active");
                });

                if (firstVisible) {
                    firstVisible.classList.add("active");
                }
            }

            searchInput.addEventListener("input", filterCards);
            languageSelect.addEventListener("change", filterCards);
            genreSelect.addEventListener("change", filterCards);

            // Date sort handler
            dateSelect.addEventListener("change", function () {
                const params = new URLSearchParams(window.location.search);
                params.set('date_order', this.value);
                window.location.search = params.toString();
            });
        });
    </script>
</body>
</html>