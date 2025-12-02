<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';

// Delete event type
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    $stmt_del = mysqli_prepare($conn, "DELETE FROM event_types WHERE id = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $delete_id);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);

    header("Location: manage_event_types.php");
    exit;
}

// Load event type for editing
$edit_type = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt_edit = mysqli_prepare($conn, "SELECT * FROM event_types WHERE id = ?");
    mysqli_stmt_bind_param($stmt_edit, "i", $edit_id);
    mysqli_stmt_execute($stmt_edit);
    $edit_res = mysqli_stmt_get_result($stmt_edit);
    $edit_type = mysqli_fetch_assoc($edit_res);
    mysqli_stmt_close($stmt_edit);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Security: Use Prepared Statements
    $type_name = $_POST['type_name'];
    $type_id = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : 0;

    $imageName = isset($_FILES['image']['name']) ? $_FILES['image']['name'] : '';
    $imageTmp = isset($_FILES['image']['tmp_name']) ? $_FILES['image']['tmp_name'] : '';
    $targetDir = "uploads/";
    $targetFile = $imageName ? $targetDir . basename($imageName) : '';

    // Update existing type
    if ($type_id > 0) {
        // If a new image is uploaded, replace; otherwise only update type_name
        if ($imageName && move_uploaded_file($imageTmp, $targetFile)) {
            $stmt = mysqli_prepare($conn, "UPDATE event_types SET type_name = ?, image = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssi", $type_name, $targetFile, $type_id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE event_types SET type_name = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $type_name, $type_id);
        }

        if (mysqli_stmt_execute($stmt)) {
            $success = "Event type updated successfully.";
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        // Insert new type
        if ($imageName && move_uploaded_file($imageTmp, $targetFile)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO event_types (type_name, image) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $type_name, $targetFile);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Event type added successfully.";
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = "Failed to upload image.";
        }
    }

    header("Location: manage_event_types.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM event_types");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Event Types</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
<div class="main-content">
<div class="header">
    <h2>Manage Event Types</h2>
</div>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="type_id" value="<?php echo $edit_type ? (int)$edit_type['id'] : ''; ?>">

    <label for="type_name">Event Type:</label>
    <input type="text" id="type_name" name="type_name" required
           value="<?php echo $edit_type ? htmlspecialchars($edit_type['type_name']) : ''; ?>"><br><br>

    <label for="image">Image:</label>
    <input type="file" id="image" name="image" accept="image/*" <?php echo $edit_type ? '' : 'required'; ?>><br>
    <?php if ($edit_type && !empty($edit_type['image'])) { ?>
        <small>Current: <img src="<?php echo htmlspecialchars($edit_type['image']); ?>" width="40" height="40" alt="Current image"></small>
    <?php } ?>
    <br><br>

    <input type="submit" value="<?php echo $edit_type ? 'Update Event Type' : 'Add Event Type'; ?>">
</form>
<?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
<?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

<h3>Existing Event Types</h3>
<table>
<tr><th>ID</th><th>Type</th><th>Image</th><th>Actions</th></tr>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td data-label="ID"><?php echo $row['id']; ?></td>
    <td data-label="Type"><?php echo htmlspecialchars($row['type_name']); ?></td>
    <td data-label="Image"><img src="<?php echo htmlspecialchars($row['image']);?>" width="50" height="50" alt="Event Type Image"></td>
    <td data-label="Actions">
        <a href="manage_event_types.php?edit_id=<?php echo $row['id']; ?>">Edit</a> |
        <a href="manage_event_types.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this event type?');">Delete</a>
    </td>
</tr>
<?php } ?>
</table>
<a href="admin_dashboard.php" class="footer-link">Back to Dashboard</a>
</div>
</body>
</html>