<?php

session_start();

$loginError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

  
    if ($email === 'admin@moviemate.com' && $password === 'Madmin@3*') {
        $_SESSION['admin'] = true;  // Set admin session
        header("Location: admin.php");
        exit();
    }

   
    $conn = new mysqli("localhost", "root", "", "ticket_booking_system");

   
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

  
    $stmt = $conn->prepare("SELECT id, password FROM registration WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $userId = $row['id'];
        $storedPassword = $row['password']; 

        
        if (password_verify($password, $storedPassword)) {
            
            $_SESSION['user_id'] = $userId;
            header("Location: index.php");
            exit();
        } else {
            $loginError = "Incorrect password.";
        }
    } else {
        $loginError = "Invalid email address.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <style>
        body {
            font-family: Georgia, serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #E3F7FC;
            background-image: url('img8.png');
            background-repeat: no-repeat;
            background-size: cover;
            font-size: 20px;
        }

        .container {
            width: 100%;
            max-width: 400px;
            text-align: center;
            background: rgba(255, 255, 255, 0.886);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgb(0, 133, 174);
        }

        h1 {
            margin-bottom: 20px;
            color: #0048a4;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 2px solid #086190;
            border-radius: 20px;
            font-size: 16px;
            outline: none;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #005F66;
            box-shadow: 0 0 5px rgba(0, 95, 102, 0.3);
        }

        .error-message {
            color: #D9534F;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        button {
            background: linear-gradient(135deg, #0063e1, #0048a4);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        button:hover {
            background: linear-gradient(135deg, #0048a4, #0063e1);
            transform: scale(1.05);
        }

        footer {
            margin-top: 15px;
            font-size: 16px;
        }

        footer a {
            color: #0048a4;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .small-note {
            font-size: 13px;
            color: #D9534F;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Login</h1>
    <?php if (!empty($loginError)): ?>
        <div class="error-message"><?php echo htmlspecialchars($loginError); ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your Email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your Password" required>
        </div>
        <button type="submit">Login</button>
        <footer>
            <p>Don't have an account? <a href="register.php">Create one</a></p>
        </footer>
    </form>
</div>
</body>
</html>