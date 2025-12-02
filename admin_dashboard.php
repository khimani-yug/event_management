<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';

// Fetch all events with event type name and teacher username
$sql = "SELECT e.id, e.event_name, e.event_date, et.type_name, u.username AS teacher_name,
        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS participant_count,
        e.participant_limit
        FROM events e
        JOIN event_types et ON e.event_type_id = et.id
        JOIN users u ON e.teacher_id = u.id
        ORDER BY e.event_date DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-content">
<div class="header">
    <h2>Welcome Admin, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
    <nav>
        <a href="create_teacher.php">Create Teacher</a>
        <a href="manage_event_types.php">Manage Event Types</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<h3>All Events</h3>
<table>
    <tr>
        <th>Event Name</th><th>Date</th><th>Type</th><th>Teacher</th><th>Participants</th><th>Limit</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td data-label="Event Name"><?php echo htmlspecialchars($row['event_name']); ?></td>
        <td data-label="Date"><?php echo htmlspecialchars($row['event_date']); ?></td>
        <td data-label="Type"><?php echo htmlspecialchars($row['type_name']); ?></td>
        <td data-label="Teacher"><?php echo htmlspecialchars($row['teacher_name']); ?></td>
        <td data-label="Participants"><?php echo $row['participant_count']; ?></td>
        <td data-label="Limit"><?php echo $row['participant_limit']; ?></td>
    </tr>
    <?php } ?>
</table>

</div>
</body>
</html>