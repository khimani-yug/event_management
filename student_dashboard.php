<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: login.php");
    exit;
}
include 'db.php';

$student_id = $_SESSION['user_id'];

$filter = isset($_GET['event_type']) ? (int)$_GET['event_type'] : 0;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

// Build WHERE clauses for all events
$conditions = ["e.event_date >= CURDATE()"];

if ($filter > 0) {
    $conditions[] = "e.event_type_id = $filter";
}

if ($search !== '') {
    $searchEsc = mysqli_real_escape_string($conn, $search);
    $conditions[] = "e.event_name LIKE '%$searchEsc%'";
}

$conditionSql = 'WHERE ' . implode(' AND ', $conditions);

// All upcoming events (optionally filtered by type & search)
$events_all = mysqli_query($conn, "SELECT e.*, et.type_name, et.image FROM events e
                                  JOIN event_types et ON e.event_type_id = et.id
                                  $conditionSql");

// Student's registered upcoming events (apply same filters for consistency)
// (Registered events listing moved to separate page student_registered_events.php)
$event_types = mysqli_query($conn, "SELECT * FROM event_types");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
<div class="main-content">
<div class="header">
    <h2>EventStack</h2>
    <nav>
        <a href="student_registered_events.php">My Registered Events</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<div class="filters-row">
    <!-- Search: its own form (textbox + clear + search icon) -->
    <form method="get" action="" class="search-form">
        <input type="text" id="searchInput" name="q" placeholder="Search by event name"
               value="<?php echo htmlspecialchars($search); ?>">
        <!-- keep current category while searching -->
        <input type="hidden" name="event_type" value="<?php echo (int)$filter; ?>">

        <button type="button" class="clear-btn"
                onclick="document.getElementById('searchInput').value=''; this.form.submit();">&times;</button>

        <span class="search-divider"></span>

        <button type="submit" class="search-btn">Search</button>
    </form>

    <!-- Filter: separate form with its own button and dropdown -->
    <form method="get" action="" class="filter-form">
        <!-- keep current search while filtering -->
        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search); ?>">

        <div class="filter-dropdown">
            <button type="button" class="filter-toggle">Filter</button>
            <div class="filter-panel">
                <label for="event_type">Categories</label>
                <select name="event_type" id="event_type">
                    <option value="0">All</option>
                    <?php while($et = mysqli_fetch_assoc($event_types)) { ?>
                    <option value="<?php echo $et['id']; ?>" <?php if($filter == $et['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($et['type_name']); ?>
                    </option>
                    <?php } ?>
                </select>
                <button type="submit" class="filter-apply-btn">Apply</button>
            </div>
        </div>
    </form>
</div>

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

</div>
</body>
</html>