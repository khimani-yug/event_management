<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
include 'db.php';

$teacher_id = $_SESSION['user_id'];

// Add event
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $event_name = mysqli_real_escape_string($conn, $_POST['event_name']);
    $event_type_id = (int)$_POST['event_type_id'];
    $details = mysqli_real_escape_string($conn, $_POST['details']);
    $participant_limit = (int)$_POST['participant_limit'];
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);

    $sql = "INSERT INTO events (teacher_id, event_type_id, event_name, details, event_date, participant_limit)
            VALUES ('$teacher_id', '$event_type_id', '$event_name', '$details', '$event_date', '$participant_limit')";

    mysqli_query($conn, $sql);
}

// Get events of this teacher
$result = mysqli_query($conn, "SELECT e.*, et.type_name FROM events e
                              JOIN event_types et ON e.event_type_id = et.id
                              WHERE e.teacher_id = '$teacher_id'");
$types = mysqli_query($conn, "SELECT * FROM event_types");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Events</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="header">
    <h2>Manage Your Events</h2>
</div>

<h3>Add Event</h3>
<form method="post" action="">
    Event Name: <input type="text" name="event_name" required><br><br>
    Event Date: <input type="date" name="event_date" required><br><br>
    Event Type:
    <select name="event_type_id" required>
        <?php while ($type = mysqli_fetch_assoc($types)) { ?>
            <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['type_name']); ?></option>
        <?php } ?>
    </select><br><br>
    Details:<br>
    <textarea name="details"></textarea><br><br>
    Participant Limit: <input type="number" name="participant_limit" min="1" value="1" required><br><br>
    <input type="submit" name="add" value="Add Event">
</form>

<h3>Your Events</h3>
<table>
<tr>
    <th>ID</th><th>Name</th><th>Date</th><th>Type</th><th>Details</th><th>Limit</th><th>Participants</th><th>Actions</th>
</tr>
<?php while ($event = mysqli_fetch_assoc($result)) {
    $event_id = $event['id'];
    $countResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM registrations WHERE event_id = '$event_id'");
    $countRow = mysqli_fetch_assoc($countResult);
?>
<tr>
    <td><?php echo $event['id']; ?></td>
    <td><?php echo htmlspecialchars($event['event_name']); ?></td>
    <td><?php echo htmlspecialchars($event['event_date']); ?></td>
    <td><?php echo htmlspecialchars($event['type_name']); ?></td>
    <td><?php echo htmlspecialchars($event['details']); ?></td>
    <td><?php echo $event['participant_limit']; ?></td>
    <td><?php echo $countRow['count']; ?></td>
    <td><a href="event_participants.php?event_id=<?php echo $event_id; ?>">View Participants</a></td>
</tr>
<?php } ?>
</table>

<a href="teacher_dashboard.php" class="footer-link">Back to Dashboard</a>
</body>
</html>
