<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    // FIX: Changed role from 'teacher' to 'student'
    $sql = "INSERT INTO users (username,email, password, role) VALUES ('$username','$email', '$password', 'student')";
    if (mysqli_query($conn, $sql)) {
        $success = "Student created successfully.";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-content">
<div class="header">
    <h2>Create Student</h2>
</div>
<form method="post" action="">
    Username: <input type="text" name="username" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    Email : <input type="text" name="email" required><br><br>
    <input type="submit" value="Create">
</form>
<?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<a href="teacher_dashboard.php" class="footer-link">Back to Dashboard</a>
</div>
</body>
</html>