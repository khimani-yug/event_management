<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: login.php");
    exit;
}
include 'db.php';

$student_id = $_SESSION['user_id'];
$event_id = (int)$_GET['event_id'];

// Security: Using Prepared Statements for all database operations

// 1. Check already registered
$stmt_check = mysqli_prepare($conn, "SELECT * FROM registrations WHERE student_id = ? AND event_id = ?");
mysqli_stmt_bind_param($stmt_check, "ii", $student_id, $event_id);
mysqli_stmt_execute($stmt_check);
$check_result = mysqli_stmt_get_result($stmt_check);
mysqli_stmt_close($stmt_check);

if (mysqli_num_rows($check_result) == 0) {
    // 2. Check limit
    $stmt_count = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM registrations WHERE event_id = ?");
    mysqli_stmt_bind_param($stmt_count, "i", $event_id);
    mysqli_stmt_execute($stmt_count);
    $count_result = mysqli_stmt_get_result($stmt_count);
    $countRow = mysqli_fetch_assoc($count_result);
    mysqli_stmt_close($stmt_count);

    $stmt_event = mysqli_prepare($conn, "SELECT participant_limit FROM events WHERE id = ?");
    mysqli_stmt_bind_param($stmt_event, "i", $event_id);
    mysqli_stmt_execute($stmt_event);
    $event_result = mysqli_stmt_get_result($stmt_event);
    $eventRow = mysqli_fetch_assoc($event_result);
    mysqli_stmt_close($stmt_event);

    if ($countRow['cnt'] < $eventRow['participant_limit']) {
        // 3. Register student
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO registrations (student_id, event_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "ii", $student_id, $event_id);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);

        // TODO: Add email sending here upon successful registration

        header("Location: student_dashboard.php");
        exit;
    } else {
        echo "Event registration full.";
        exit;
    }
} else {
    echo "Already registered for this event.";
    exit;
}
?>