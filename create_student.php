<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security: Use Prepared Statements
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')");
    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $password);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Student created successfully.";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Student</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="centered-form-page">
<div class="main-content">
<h2>Create Student</h2>
<form method="post" action="">
    <label for="username">Username:</label> <input type="text" id="username" name="username" required><br>
    <label for="password">Password:</label> <input type="password" id="password" name="password" required><br>
    <label for="email">Email:</label> <input type="email" id="email" name="email" required><br>
    <input type="submit" value="Create">
</form>
<?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
<a href="teacher_dashboard.php" class="footer-link">Back to Dashboard</a>
</div>
</body>
</html>