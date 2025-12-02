<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher'){
    header("Location: login.php");
    exit;
}
include 'db.php';

$teacher_id = $_SESSION['user_id'];

// Fetch teacher's live events (today & future) with event type name and participant count
$sql_live = "SELECT e.id, e.event_name, e.event_date, et.type_name,
        u.username AS teacher_name,
        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS participant_count,
        e.participant_limit
        FROM events e
        JOIN event_types et ON e.event_type_id = et.id
        JOIN users u ON e.teacher_id = u.id
        WHERE e.event_date >= CURDATE()
        ORDER BY e.event_date ASC";

$result_live = mysqli_query($conn, $sql_live);

// Fetch teacher's expired events (past)
$sql_expired = "SELECT e.id, e.event_name, e.event_date, et.type_name,
        u.username AS teacher_name,
        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS participant_count,
        e.participant_limit
        FROM events e
        JOIN event_types et ON e.event_type_id = et.id
        JOIN users u ON e.teacher_id = u.id
        WHERE e.event_date < CURDATE()
        ORDER BY e.event_date DESC";

$result_expired = mysqli_query($conn, $sql_expired);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
<div class="main-content">
<div class="header">
    <h2>EventStack</h2>
    <nav>
        <a href="create_student.php">Create Student</a>
        <a href="manage_events.php">Manage Events</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<h3>Live Events</h3>
<table>
    <tr>
        <th>Name</th><th>Date</th><th>Type</th><th>Teacher</th><th>Participants</th><th>Limit</th><th>Participants List</th>
    </tr>
    <?php if (mysqli_num_rows($result_live) > 0) { ?>
        <?php while ($row = mysqli_fetch_assoc($result_live)) { ?>
        <tr>
            <td data-label="Name"><?php echo htmlspecialchars($row['event_name']); ?></td>
            <td data-label="Date"><?php echo htmlspecialchars($row['event_date']); ?></td>
            <td data-label="Type"><?php echo htmlspecialchars($row['type_name']); ?></td>
            <td data-label="Teacher"><?php echo htmlspecialchars($row['teacher_name']); ?></td>
            <td data-label="Participants"><?php echo $row['participant_count']; ?></td>
            <td data-label="Limit"><?php echo $row['participant_limit']; ?></td>
            <td data-label="Participants List"><a href="event_participants.php?event_id=<?php echo $row['id']; ?>">View Participants</a></td>
        </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="7" style="text-align:center;">No live events.</td>
        </tr>
    <?php } ?>
</table>

<h3>Expired Events</h3>
<table>
    <tr>
        <th>Name</th><th>Date</th><th>Type</th><th>Teacher</th><th>Participants</th><th>Limit</th><th>Participants List</th>
    </tr>
    <?php if (mysqli_num_rows($result_expired) > 0) { ?>
        <?php while ($row = mysqli_fetch_assoc($result_expired)) { ?>
        <tr>
            <td data-label="Name"><?php echo htmlspecialchars($row['event_name']); ?></td>
            <td data-label="Date"><?php echo htmlspecialchars($row['event_date']); ?></td>
            <td data-label="Type"><?php echo htmlspecialchars($row['type_name']); ?></td>
            <td data-label="Teacher"><?php echo htmlspecialchars($row['teacher_name']); ?></td>
            <td data-label="Participants"><?php echo $row['participant_count']; ?></td>
            <td data-label="Limit"><?php echo $row['participant_limit']; ?></td>
            <td data-label="Participants List"><a href="event_participants.php?event_id=<?php echo $row['id']; ?>">View Participants</a></td>
        </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="7" style="text-align:center;">No expired events.</td>
        </tr>
    <?php } ?>
</table>

</div>
</body>
</html>