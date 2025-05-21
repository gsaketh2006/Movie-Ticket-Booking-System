<?php
$conn = new mysqli("localhost", "root", "", "ticket_booking_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add Movie
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["movie_name"])) {
    $poster = $_FILES['poster']['name'];
    $banner = $_FILES['banner']['name'];
    $poster_tmp = $_FILES['poster']['tmp_name'];
    $banner_tmp = $_FILES['banner']['tmp_name'];

    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    move_uploaded_file($poster_tmp, $uploadDir . $poster);
    move_uploaded_file($banner_tmp, $uploadDir . $banner);

    $stmt = $conn->prepare("INSERT INTO add_movie (name, description, genre, language, duration, release_date, rating, poster, banner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisdss",
        $_POST['movie_name'],
        $_POST['description'],
        $_POST['genre'],
        $_POST['language'],
        $_POST['duration'],
        $_POST['release_date'],
        $_POST['rating'],
        $poster,
        $banner
    );
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Movie added successfully!');</script>";
}

// Handle Add Theatre
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["theatre_name"])) {
    $stmt = $conn->prepare("INSERT INTO theatres (theatre_name, location, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['theatre_name'], $_POST['location'], $_POST['address']);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Theatre added successfully!');</script>";
}

// Handle Add Show
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["movie_id"]) && isset($_POST["theatre_id"]) && isset($_POST["show_time"])) {
    $stmt = $conn->prepare("INSERT INTO shows (movie_id, theatre_id, show_time, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss",
        $_POST['movie_id'],
        $_POST['theatre_id'],
        $_POST['show_time'],
        $_POST['start_date'],
        $_POST['end_date']
    );
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Show added successfully!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-image: url('img1.png');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      background-repeat: no-repeat;
      padding: 20px;
      margin: 0;
      color: #333;
      line-height: 1.6;
    }
    .container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      max-width: 1800px;
      margin: 0 auto;
    }
    form {
      background: #fff;
      padding: 25px;
      width: 500px;
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 30px;
    }
    form:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    h1 {
      text-align: center;
      color:rgb(0, 146, 250);
      margin-bottom: 30px;
      font-size: 36px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    h2 {
      text-align: center;
      color: #2c3e50;
      border-bottom: 2px solid #eee;
      padding-bottom: 15px;
      margin-bottom: 25px;
      position: relative;
    }
    h2:after {
      content: '';
      position: absolute;
      bottom: -2px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 2px;
      background-color: #007bff;
    }
    label {
      display: block;
      margin-top: 15px;
      margin-bottom: 5px;
      color: #555;
      font-weight: bold;
    }
    input, textarea, select {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 6px;
      border: 1px solid #ddd;
      box-sizing: border-box;
      font-size: 16px;
      transition: border 0.3s ease, box-shadow 0.3s ease;
    }
    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: #007bff;
      box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }
    textarea {
      resize: vertical;
      min-height: 100px;
    }
    input[type="file"] {
      padding: 10px;
      background-color: #f8f9fa;
      border: 1px dashed #ced4da;
    }
    .form-section {
      margin-bottom: 25px;
      padding-bottom: 15px;
    }
    button {
      display: block;
      width: 100%;
      padding: 12px 20px;
      background: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: background 0.3s ease, transform 0.2s ease;
      margin-top: 15px;
    }
    button:hover {
      background: #0056b3;
      transform: scale(1.02);
    }
    .input-group {
      margin-bottom: 20px;
    }
    /* Responsive adjustments */
    @media (max-width: 768px) {
      form {
        width: 100%;
      }
    }
  </style>
</head>
<body>

<h1>Admin Panel</h1>

<div class="container">

  <!-- Add Movie Form -->
  <form action="" method="POST" enctype="multipart/form-data">
    <h2>Add Movie</h2>
    <div class="form-section">
      <label for="movie_name">Movie Name:</label>
      <input type="text" id="movie_name" name="movie_name" placeholder="Enter movie name" required>
      
      <label for="description">Description:</label>
      <textarea id="description" name="description" placeholder="Enter movie description" rows="3" required></textarea>
      
      <div class="input-group">
        <label for="genre">Genre:</label>
        <input type="text" id="genre" name="genre" placeholder="Action, Comedy, Drama, etc." required>
      </div>
      
      <div class="input-group">
        <label for="language">Language:</label>
        <input type="text" id="language" name="language" placeholder="Movie language" required>
      </div>
      
      <div class="input-group">
        <label for="duration">Duration (minutes):</label>
        <input type="number" id="duration" name="duration" placeholder="Duration in minutes" required>
      </div>
      
      <div class="input-group">
        <label for="release_date">Release Date:</label>
        <input type="date" id="release_date" name="release_date" required>
      </div>
      
      <div class="input-group">
        <label for="rating">Rating (0-5):</label>
        <input type="number" step="1" min="0" max="5" id="rating" name="rating" placeholder="Rating from 0 to 5" required>
      </div>
      
      <div class="input-group">
        <label for="poster">Poster Image:</label>
        <input type="file" id="poster" name="poster" accept="image/*" required>
      </div>
      
      <div class="input-group">
        <label for="banner">Banner Image:</label>
        <input type="file" id="banner" name="banner" accept="image/*" required>
      </div>
    </div>
    <button type="submit">Add Movie</button>
  </form>

  <!-- Add Theatre Form -->
  <form action="" method="POST">
    <h2>Add Theatre</h2>
    <div class="form-section">
      <label for="theatre_name">Theatre Name:</label>
      <input type="text" id="theatre_name" name="theatre_name" placeholder="Enter theatre name" required>
      
      <label for="location">Location:</label>
      <input type="text" id="location" name="location" placeholder="City, area, etc." required>
      
      <label for="address">Full Address:</label>
      <textarea id="address" name="address" placeholder="Enter complete address" rows="4" required></textarea>
    </div>
    <button type="submit">Add Theatre</button>
  </form>

  <!-- Add Show Form -->
  <form action="" method="POST">
    <h2>Add Show</h2>
    <div class="form-section">
      <label for="movie_id">Movie:</label>
      <select id="movie_id" name="movie_id" required>
        <option value="">Select Movie</option>
        <?php
        $movies = $conn->query("SELECT movie_id, name FROM add_movie");
        while($row = $movies->fetch_assoc()): ?>
          <option value="<?= $row['movie_id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
        <?php endwhile; ?>
      </select>
      
      <label for="theatre_id">Theatre:</label>
      <select id="theatre_id" name="theatre_id" required>
        <option value="">Select Theatre</option>
        <?php
        $theatres = $conn->query("SELECT theatre_id, theatre_name FROM theatres");
        while($row = $theatres->fetch_assoc()): ?>
          <option value="<?= $row['theatre_id'] ?>"><?= htmlspecialchars($row['theatre_name']) ?></option>
        <?php endwhile; ?>
      </select>
      
      <div class="input-group">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
      </div>
      
      <div class="input-group">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
      </div>
      
      <div class="input-group">
        <label for="show_time">Show Time:</label>
        <input type="time" id="show_time" name="show_time" required>
      </div>
    </div>
    <button type="submit">Add Show</button>
  </form>

</div>

</body>
</html>

<?php $conn->close(); ?>