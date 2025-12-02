<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $sql = "INSERT INTO users (username,email, password, role) VALUES ('$username','$email', '$password', 'teacher')";
    if (mysqli_query($conn, $sql)) {
        $success = "Teacher created successfully.";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Teacher</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="header">
    <h2>Create Teacher</h2>
</div>
<form method="post" action="">
    Username: <input type="text" name="username" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    Email : <input type="text" name="email" required><br><br>
    <input type="submit" value="Create">
</form>
<?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<a href="admin_dashboard.php" class="footer-link">Back to Dashboard</a>
</body>
</html>
