<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servername = "localhost:3307";
    $username_db = "root";
    $password_db = "";
    $dbname = "bappeda_data";

    // Create a connection
    $conn = new mysqli($servername, $username_db, $password_db, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve form input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the query
    $sql = "SELECT * FROM user_login WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $_SESSION['username'] = $username;
        header("Location: koneksi.php"); // Redirect to another page if login is successful
        exit();
    } else {
        $error = "Incorrect username or password.";
    }

    // Close connections
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Registration Form</title>
    <link rel="stylesheet" href="css/login.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="wrapper">
        <span class="bg-animate"></span>
        <div class="form-box login" data-aos="fade-up-left">
            <h2>Login</h2>
            <!-- Form action posts to the same page -->
            <form method="POST">
                <div class="input-box">
                    <input type="text" name="username" required>
                    <label>Username</label>
                    <i class='bx bxs-user'></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" required>
                    <label>Password</label>
                    <i class='bx bxs-lock-alt'></i>
                </div>
                <button type="submit" class="btn">Login</button>
                <?php if ($error): ?>
                    <p style="color: red;"><?= $error ?></p>
                <?php endif; ?>
                <div class="logreg-link">
                </div>
            </form>
        </div>
        <div class="info-text login" style="margin-top: -120px; position: relative; left: 300px;">
            <img src="logo.png" width="250px">
        </div>
    </div>  

    <script src="script.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
      AOS.init();
    </script>
</body>

</html>
