<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
include 'db.php';

$event_id = (int)$_GET['event_id'];

// Get event name and date
$event_query = mysqli_query($conn, "SELECT event_name, event_date FROM events WHERE id = '$event_id'");
$event_row = mysqli_fetch_assoc($event_query);
$event_name = $event_row ? $event_row['event_name'] : "Unknown Event";
$event_date = $event_row ? $event_row['event_date'] : "Unknown Date";

$sql = "SELECT u.username FROM users u
        JOIN registrations r ON u.id = r.student_id
        WHERE r.event_id = '$event_id'";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Participants</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-content">
<h2>Participants for Event: <?php echo htmlspecialchars($event_name); ?> (<?php echo htmlspecialchars($event_date); ?>)</h2>
<table>
<tr><th>Student Username</th></tr>
<?php while($row = mysqli_fetch_assoc($result)) { ?>
<tr>
    <td data-label="Username"><?php echo htmlspecialchars($row['username']); ?></td>
</tr>
<?php } ?>
</table>
<a href="manage_events.php" class="footer-link">Back to Events</a>
</div>
</body>
</html>