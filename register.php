<?php
$host = 'localhost';
$db   = 'ticket_booking_system';
$user = 'root';
$pass = ''; 
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $conn->real_escape_string($_POST['name']);
    $email    = $conn->real_escape_string($_POST['email']);
    $phone    = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    if ($password !== $cpassword) {
        echo "<script>alert('Passwords do not match.');</script>";
    } 
    else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check = $conn->query("SELECT email FROM registration WHERE email = '$email'");
        if ($check->num_rows > 0) 
        {
            echo "<script>alert('Email is already registered. Please log in.');</script>";
        } 
        else {
            $sql = "INSERT INTO registration (name, email, phone, password) 
                    VALUES ('$name', '$email', '$phone', '$hashed_password')";

            if ($conn->query($sql) === TRUE) 
            {
                echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
            } 
            else {
                echo "<script>alert('Error: " . $conn->error . "');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registration Form</title>
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

        .progress {
            width: 100%;
            height: 10px;
            background-color: #d3e9f6;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            width: 0%;
            background-color: #0077cc;
            transition: width 0.3s ease-in-out;
        }

        .form-footer {
            margin-top: 15px;
            font-size: 16px;
        }

        .form-footer a {
            color: #0048a4;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sign Up</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your full name" required oninput="updateProgress()">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required oninput="updateProgress()">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="Enter your 10-digit phone number"
                       required maxlength="10" pattern="\d{10}" 
                       oninput="updateProgress(); this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a strong password" required oninput="updateProgress()">
            </div>
            <div class="form-group">
                <label for="cpassword">Confirm Password</label>
                <input type="password" id="cpassword" name="cpassword" placeholder="Confirm your password" required oninput="updateProgress()">
            </div>

            <div class="progress">
                <div class="progress-bar" id="progress-bar"></div>
            </div>

            <button type="submit">Create Account</button>

            <div class="form-footer">
                Already have an account? <a href="login.php">Log In</a>
            </div>
        </form>
    </div>

    <script>
        function updateProgress() {
            const inputs = document.querySelectorAll('input');
            let filledInputs = 0;

            for (let i = 0; i < inputs.length; i++) {
                if (inputs[i].value !== '') {
                    filledInputs++;
                }
            }

            const progressBar = document.getElementById('progress-bar');
            const progressPercentage = (filledInputs / inputs.length) * 100;
            progressBar.style.width = progressPercentage + '%';
        }
    </script>
</body>
</html>
