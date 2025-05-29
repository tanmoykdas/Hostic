<?php
require_once __DIR__ . '/../config.php';
ensureDoctor();

$doctor_id  = $_SESSION['user_id'];
$session_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($session_id > 0) {
    $stmt = $db->prepare("
        DELETE FROM sessions
        WHERE id = ? AND doctor_id = ?
    ");
    $stmt->bind_param('ii', $session_id, $doctor_id);
    if ($stmt->execute()) {
        set_flash('success', 'Session deleted successfully.');
    } else {
        set_flash('error', 'Failed to delete session: ' . $db->error);
    }
    $stmt->close();
}
redirect('sessions.php');
