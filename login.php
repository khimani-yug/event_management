<!-- http://localhost/Event_management/login.php -->
<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') header("Location: admin_dashboard.php");
    elseif ($_SESSION['role'] == 'teacher') header("Location: teacher_dashboard.php");
    else header("Location: student_dashboard.php");
    exit;
}
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; 
    $password = $_POST['password'];

    // Security: Use Prepared Statements
    $stmt = mysqli_prepare($conn, "SELECT id, username, password, role FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($row['role'] == 'teacher') {
                header("Location: teacher_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <!-- Cache-busting query to ensure latest CSS is loaded -->
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body class="centered-form-page">
<div class="main-content">
    <h2>Login to EventStack</h2>

<form method="post" action="">
    <label for="username">Username:</label> <input type="text" id="username" name="username" required><br>
    <label for="password">Password:</label> <input type="password" id="password" name="password" required><br>
    <input type="submit" value="Login">
</form>

<?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
</div>
</body>
</html>