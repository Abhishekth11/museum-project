<?php
// Redirect to the new dynamic event detail page
$event_id = $_GET['id'] ?? 1;
header("Location: dynamic-event-detail.php?id=" . $event_id);
exit;
?>
