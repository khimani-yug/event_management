<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher'){
    header("Location: login.php");
    exit;
}
include 'db.php';

$teacher_id = $_SESSION['user_id'];

// Fetch teacher's events with event type name and participant count
$sql = "SELECT e.id, e.event_name, e.event_date, et.type_name,
        u.username AS teacher_name,
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
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="header">
    <h2>Welcome Teacher, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
    <nav>
        <a href="create_student.php">Create Student</a>
        <a href="manage_events.php">Manage Events</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<h3>All Events</h3>
<table>
    <tr>
        <th>Name</th><th>Date</th><th>Type</th><th>Teacher</th><th>Participants</th><th>Limit</th><th>Participants List</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>

        <td><?php echo htmlspecialchars($row['event_name']); ?></td>
        <td><?php echo htmlspecialchars($row['event_date']); ?></td>
        <td><?php echo htmlspecialchars($row['type_name']); ?></td>
        <td><?php echo htmlspecialchars($row['teacher_name']); ?></td>
        <td><?php echo $row['participant_count']; ?></td>
        <td><?php echo $row['participant_limit']; ?></td>
        <td><a href="event_participants.php?event_id=<?php echo $row['id']; ?>">View Participants</a></td>
    </tr>
    <?php } ?>
</table>


</body>
</html>
