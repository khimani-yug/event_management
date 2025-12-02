<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: login.php");
    exit;
}
include 'db.php';

$student_id = $_SESSION['user_id'];
$event_id = (int)$_GET['event_id'];

// Check already registered
$check = mysqli_query($conn, "SELECT * FROM registrations WHERE student_id = '$student_id' AND event_id = '$event_id'");
if (mysqli_num_rows($check) == 0) {
    // Check limit
    $count = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM registrations WHERE event_id = '$event_id'");
    $countRow = mysqli_fetch_assoc($count);

    $event = mysqli_query($conn, "SELECT participant_limit FROM events WHERE id = '$event_id'");
    $eventRow = mysqli_fetch_assoc($event);

    if ($countRow['cnt'] < $eventRow['participant_limit']) {
        mysqli_query($conn, "INSERT INTO registrations (student_id, event_id) VALUES ('$student_id', '$event_id')");

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
