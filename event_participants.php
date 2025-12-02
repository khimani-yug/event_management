<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
include 'db.php';

$event_id = (int)$_GET['event_id'];

// Security: Use Prepared Statements
// Get event name and date
$stmt_event = mysqli_prepare($conn, "SELECT event_name, event_date FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt_event, "i", $event_id);
mysqli_stmt_execute($stmt_event);
$event_query = mysqli_stmt_get_result($stmt_event);
$event_row = mysqli_fetch_assoc($event_query);
mysqli_stmt_close($stmt_event);

$event_name = $event_row ? $event_row['event_name'] : "Unknown Event";
$event_date = $event_row ? $event_row['event_date'] : "Unknown Date";

$stmt_participants = mysqli_prepare($conn, "SELECT u.username FROM users u JOIN registrations r ON u.id = r.student_id WHERE r.event_id = ?");
mysqli_stmt_bind_param($stmt_participants, "i", $event_id);
mysqli_stmt_execute($stmt_participants);
$result = mysqli_stmt_get_result($stmt_participants);
mysqli_stmt_close($stmt_participants);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Participants</title>
    <link rel="stylesheet" href="style.css?v=2">
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