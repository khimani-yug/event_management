<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
include 'db.php';

$teacher_id = $_SESSION['user_id'];

// Delete event (and its registrations)
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    // Delete registrations for this event
    $stmt_regs = mysqli_prepare($conn, "DELETE FROM registrations WHERE event_id = ?");
    mysqli_stmt_bind_param($stmt_regs, "i", $delete_id);
    mysqli_stmt_execute($stmt_regs);
    mysqli_stmt_close($stmt_regs);

    // Delete event belonging to this teacher
    $stmt_del = mysqli_prepare($conn, "DELETE FROM events WHERE id = ? AND teacher_id = ?");
    mysqli_stmt_bind_param($stmt_del, "ii", $delete_id, $teacher_id);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);

    header("Location: manage_events.php");
    exit;
}

// Load event for editing
$edit_event = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $stmt_edit = mysqli_prepare($conn, "SELECT * FROM events WHERE id = ? AND teacher_id = ?");
    mysqli_stmt_bind_param($stmt_edit, "ii", $edit_id, $teacher_id);
    mysqli_stmt_execute($stmt_edit);
    $edit_result = mysqli_stmt_get_result($stmt_edit);
    $edit_event = mysqli_fetch_assoc($edit_result);
    mysqli_stmt_close($stmt_edit);
}

// Add or update event
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = $_POST['event_name'];
    $event_type_id = (int)$_POST['event_type_id'];
    $details = $_POST['details'];
    $participant_limit = (int)$_POST['participant_limit'];
    $event_date = $_POST['event_date'];

    // Update existing event
    if (!empty($_POST['event_id'])) {
        $event_id = (int)$_POST['event_id'];
        $stmt = mysqli_prepare($conn, "UPDATE events 
                                       SET event_type_id = ?, event_name = ?, details = ?, event_date = ?, participant_limit = ?
                                       WHERE id = ? AND teacher_id = ?");
        mysqli_stmt_bind_param($stmt, "isssiii", $event_type_id, $event_name, $details, $event_date, $participant_limit, $event_id, $teacher_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        // Insert new event
        $stmt = mysqli_prepare($conn, "INSERT INTO events (teacher_id, event_type_id, event_name, details, event_date, participant_limit)
                                       VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissii", $teacher_id, $event_type_id, $event_name, $details, $event_date, $participant_limit);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: manage_events.php");
    exit;
}

// Get events of this teacher, split into live (upcoming/today) and expired (past)
$liveEvents = mysqli_query($conn, "SELECT e.*, et.type_name FROM events e
                                  JOIN event_types et ON e.event_type_id = et.id
                                  WHERE e.teacher_id = '$teacher_id' AND e.event_date >= CURDATE()
                                  ORDER BY e.event_date ASC");

$expiredEvents = mysqli_query($conn, "SELECT e.*, et.type_name FROM events e
                                      JOIN event_types et ON e.event_type_id = et.id
                                      WHERE e.teacher_id = '$teacher_id' AND e.event_date < CURDATE()
                                      ORDER BY e.event_date DESC");
$types = mysqli_query($conn, "SELECT * FROM event_types");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Events</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
<div class="main-content">
<div class="header header-center">
    <h2>Manage Your Events</h2>
</div>

<h3><?php echo $edit_event ? 'Edit Event' : 'Add Event'; ?></h3>
<form method="post" action="">
    <input type="hidden" name="event_id" value="<?php echo $edit_event ? (int)$edit_event['id'] : ''; ?>">

    <label for="event_name">Event Name:</label>
    <input type="text" id="event_name" name="event_name" required
           value="<?php echo $edit_event ? htmlspecialchars($edit_event['event_name']) : ''; ?>"><br><br>

    <label for="event_date">Event Date:</label>
    <input type="date" id="event_date" name="event_date" required
           value="<?php echo $edit_event ? htmlspecialchars($edit_event['event_date']) : date('Y-m-d'); ?>"><br><br>

    <label for="event_type_id">Event Type:</label>
    <select name="event_type_id" id="event_type_id" required>
        <?php mysqli_data_seek($types, 0); // Reset result pointer ?>
        <?php while ($type = mysqli_fetch_assoc($types)) { ?>
            <option value="<?php echo $type['id']; ?>"
                <?php
                if ($edit_event && (int)$edit_event['event_type_id'] === (int)$type['id']) {
                    echo 'selected';
                }
                ?>>
                <?php echo htmlspecialchars($type['type_name']); ?>
            </option>
        <?php } ?>
    </select><br><br>

    <label for="details">Details:</label><br>
    <textarea name="details" id="details"><?php echo $edit_event ? htmlspecialchars($edit_event['details']) : ''; ?></textarea><br><br>

    <label for="participant_limit">Participant Limit:</label>
    <input type="number" id="participant_limit" name="participant_limit" min="1"
           value="<?php echo $edit_event ? (int)$edit_event['participant_limit'] : 1; ?>" required><br><br>

    <input type="submit" value="<?php echo $edit_event ? 'Update Event' : 'Add Event'; ?>">
</form>

<h3>Live Events</h3>
<table>
<tr>
    <th>ID</th><th>Name</th><th>Date</th><th>Type</th><th>Details</th><th>Limit</th><th>Participants</th><th>Actions</th>
</tr>
<?php if (mysqli_num_rows($liveEvents) > 0) { ?>
    <?php while ($event = mysqli_fetch_assoc($liveEvents)) {
        $event_id = $event['id'];
        $countResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM registrations WHERE event_id = '$event_id'");
        $countRow = mysqli_fetch_assoc($countResult);
    ?>
    <tr>
        <td data-label="ID"><?php echo $event['id']; ?></td>
        <td data-label="Name"><?php echo htmlspecialchars($event['event_name']); ?></td>
        <td data-label="Date"><?php echo htmlspecialchars($event['event_date']); ?></td>
        <td data-label="Type"><?php echo htmlspecialchars($event['type_name']); ?></td>
        <td data-label="Details"><?php echo htmlspecialchars($event['details']); ?></td>
        <td data-label="Limit"><?php echo $event['participant_limit']; ?></td>
        <td data-label="Participants"><?php echo $countRow['count']; ?></td>
        <td data-label="Actions">
            <a href="manage_events.php?edit_id=<?php echo $event_id; ?>">Edit</a> |
            <a href="manage_events.php?delete_id=<?php echo $event_id; ?>" onclick="return confirm('Delete this event?');">Delete</a> |
            <a href="event_participants.php?event_id=<?php echo $event_id; ?>">View Participants</a>
        </td>
    </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="8" style="text-align:center;">No live events.</td>
    </tr>
<?php } ?>
</table>

<h3>Expired Events</h3>
<table>
<tr>
    <th>ID</th><th>Name</th><th>Date</th><th>Type</th><th>Details</th><th>Limit</th><th>Participants</th><th>Actions</th>
</tr>
<?php if (mysqli_num_rows($expiredEvents) > 0) { ?>
    <?php while ($event = mysqli_fetch_assoc($expiredEvents)) {
        $event_id = $event['id'];
        $countResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM registrations WHERE event_id = '$event_id'");
        $countRow = mysqli_fetch_assoc($countResult);
    ?>
    <tr>
        <td data-label="ID"><?php echo $event['id']; ?></td>
        <td data-label="Name"><?php echo htmlspecialchars($event['event_name']); ?></td>
        <td data-label="Date"><?php echo htmlspecialchars($event['event_date']); ?></td>
        <td data-label="Type"><?php echo htmlspecialchars($event['type_name']); ?></td>
        <td data-label="Details"><?php echo htmlspecialchars($event['details']); ?></td>
        <td data-label="Limit"><?php echo $event['participant_limit']; ?></td>
        <td data-label="Participants"><?php echo $countRow['count']; ?></td>
        <td data-label="Actions">
            <a href="manage_events.php?edit_id=<?php echo $event_id; ?>">Edit</a> |
            <a href="manage_events.php?delete_id=<?php echo $event_id; ?>" onclick="return confirm('Delete this event?');">Delete</a> |
            <a href="event_participants.php?event_id=<?php echo $event_id; ?>">View Participants</a>
        </td>
    </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="8" style="text-align:center;">No expired events.</td>
    </tr>
<?php } ?>
</table>

<a href="teacher_dashboard.php" class="footer-link">Back to Dashboard</a>
</div>
</body>
</html>