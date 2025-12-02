<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $type_name = mysqli_real_escape_string($conn, $_POST['type_name']);
    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($imageName);

    if(move_uploaded_file($imageTmp, $targetFile)) {
        $sql = "INSERT INTO event_types (type_name, image) VALUES ('$type_name', '$targetFile')";
        if (mysqli_query($conn, $sql)) {
            $success = "Event type added successfully.";
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    } else {
        $error = "Failed to upload image.";
    }
}

$result = mysqli_query($conn, "SELECT * FROM event_types");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Event Types</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="header">
    <h2>Manage Event Types</h2>
</div>

<form method="post" enctype="multipart/form-data">
    Event Type: <input type="text" name="type_name" required><br><br>
    Image: <input type="file" name="image" accept="image/*" required><br><br>
    <input type="submit" value="Add Event Type">
</form>
<?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
<?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

<h3>Existing Event Types</h3>
<table>
<tr><th>ID</th><th>Type</th><th>Image</th></tr>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo htmlspecialchars($row['type_name']); ?></td>
    <td><img src="<?php echo htmlspecialchars($row['image']);?>" width="50" height="50" alt="Event Type Image"></td>
</tr>
<?php } ?>
</table>
<a href="admin_dashboard.php" class="footer-link">Back to Dashboard</a>
</body>
</html>
