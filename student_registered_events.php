<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: login.php");
    exit;
}
include 'db.php';

$student_id = $_SESSION['user_id'];

// Fetch upcoming events this student is registered for
$events_registered = mysqli_query($conn, "SELECT e.*, et.type_name, et.image FROM events e
  JOIN registrations r ON e.id = r.event_id
  JOIN event_types et ON e.event_type_id = et.id
  WHERE r.student_id = $student_id AND e.event_date >= CURDATE()
  ORDER BY e.event_date ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Registered Events</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
<div class="main-content">
<div class="header">
    <h2>My Registered Events</h2>
    <nav>
        <a href="student_dashboard.php">All Events</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<h3>Upcoming Registered Events</h3>
<?php if (mysqli_num_rows($events_registered) > 0) { ?>
    <?php while($rev = mysqli_fetch_assoc($events_registered)) { ?>
    <div class="event-card">
        <img src="<?php echo htmlspecialchars($rev['image']); ?>" alt="Event Image">
        <div class="event-details">
            <h3><?php echo htmlspecialchars($rev['event_name']); ?></h3>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($rev['type_name']); ?></p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($rev['event_date']); ?></p>
            <p><?php echo nl2br(htmlspecialchars($rev['details'])); ?></p>
        </div>
    </div>
    <?php } ?>
<?php } else { ?>
    <p>No upcoming registered events.</p>
<?php } ?>

</div>
</body>
</html>


