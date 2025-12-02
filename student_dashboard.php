<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: login.php");
    exit;
}
include 'db.php';

$student_id = $_SESSION['user_id'];

$filter = isset($_GET['event_type']) ? (int)$_GET['event_type'] : 0;

$condition = $filter > 0 ? "WHERE e.event_type_id = $filter AND e.event_date >= CURDATE()" : "WHERE e.event_date >= CURDATE()";

$events_all = mysqli_query($conn, "SELECT e.*, et.type_name, et.image FROM events e
                                  JOIN event_types et ON e.event_type_id = et.id
                                  $condition");

$events_registered = mysqli_query($conn, "SELECT e.*, et.type_name, et.image FROM events e
  JOIN registrations r ON e.id = r.event_id
  JOIN event_types et ON e.event_type_id = et.id
  WHERE r.student_id = $student_id AND e.event_date >= CURDATE()");
$event_types = mysqli_query($conn, "SELECT * FROM event_types");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main-content">
<div class="header">
    <h2>Welcome Student, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
    <a href="logout.php">Logout</a>
</div>

<form method="get" action="" class="filter-form">
    Filter by Event Type:
    <select name="event_type">
        <option value="0">All</option>
        <?php while($et = mysqli_fetch_assoc($event_types)) { ?>
        <option value="<?php echo $et['id']; ?>" <?php if($filter == $et['id']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($et['type_name']); ?>
        </option>
        <?php } ?>
    </select>
    <input type="submit" value="Filter">
</form>

<h3>All Events</h3>
<?php while($ev = mysqli_fetch_assoc($events_all)) {
    $event_id = $ev['id'];
    // Check if student already registered
    $checkReg = mysqli_query($conn, "SELECT * FROM registrations WHERE student_id = $student_id AND event_id = $event_id");
    $registered = mysqli_num_rows($checkReg) > 0;

    // Count participants
    $countPart = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM registrations WHERE event_id = $event_id");
    $countRow = mysqli_fetch_assoc($countPart);

    $canRegister = !$registered && ($countRow['cnt'] < $ev['participant_limit']);
?>
<div class="event-card">
    <img src="<?php echo htmlspecialchars($ev['image']); ?>" alt="Event Image">
    <div class="event-details">
        <h3><?php echo htmlspecialchars($ev['event_name']); ?></h3>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($ev['type_name']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($ev['event_date']); ?></p>
        <p><?php echo nl2br(htmlspecialchars($ev['details'])); ?></p>
        <p>
        <?php if($canRegister){ ?>
            <a href="register_event.php?event_id=<?php echo $event_id; ?>">Register</a>
        <?php } elseif($registered) {
            echo "<strong>Registered</strong>";
        } else {
            echo "<strong>Full</strong>";
        } ?>
        </p>
    </div>
</div>
<?php } ?>

<h3>Your Registered Events</h3>
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
</div>
</body>
</html>