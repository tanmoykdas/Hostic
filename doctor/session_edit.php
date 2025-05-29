<?php
require_once __DIR__ . '/../config.php';
ensureDoctor();

$doctor_id = $_SESSION['user_id'];
$session_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("
    SELECT start_time, end_time, price, max_tickets
    FROM sessions
    WHERE id = ? AND doctor_id = ?
");
$stmt->bind_param('ii', $session_id, $doctor_id);
$stmt->execute();
$stmt->bind_result($start_time, $end_time, $price, $max_tickets);
if (!$stmt->fetch()) {
    $stmt->close();
    set_flash('error', 'Session not found or access denied.');
    redirect('doctor/sessions.php');
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_start  = esc($_POST['start_time'] ?? '');
    $new_end    = esc($_POST['end_time'] ?? '');
    $new_price  = esc($_POST['price'] ?? '');
    $new_max    = (int)($_POST['max_tickets'] ?? 0);

    if (!$new_start || !$new_end || !$new_price || $new_max < 1) {
        set_flash('error', 'All fields are required and must be valid.');
        redirect("doctor/session_edit.php?id={$session_id}");
    }

    $u = $db->prepare("
        UPDATE sessions
        SET start_time  = ?,
            end_time    = ?,
            price       = ?,
            max_tickets = ?
        WHERE id = ? AND doctor_id = ?
    ");
    $u->bind_param('ssdi ii', $new_start, $new_end, $new_price, $new_max, $session_id, $doctor_id);
    $u->bind_param('ssdiii', $new_start, $new_end, $new_price, $new_max, $session_id, $doctor_id);

    if ($u->execute()) {
        set_flash('success', 'Session updated successfully.');
        $u->close();
        redirect('doctor/sessions.php');
    } else {
        set_flash('error', 'Update failed: ' . $db->error);
    }
    $u->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../templates/header.php'; ?>
  <title>Edit Session</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/edit.css">
</head>
<body class="edit-session">
  <?php show_flash(); ?>

  <h1>Edit Session #<?= htmlspecialchars($session_id) ?></h1>
  <form method="post" action="session_edit.php?id=<?= $session_id ?>">
    <label>
      Start Time:
      <input type="datetime-local" name="start_time"
             value="<?= date('Y-m-d\TH:i', strtotime($start_time)) ?>" required>
    </label>

    <label>
      End Time:
      <input type="datetime-local" name="end_time"
             value="<?= date('Y-m-d\TH:i', strtotime($end_time)) ?>" required>
    </label>

    <label>
      Price:
      <input type="number" name="price" step="0.01"
             value="<?= htmlspecialchars($price) ?>" required>
    </label>

    <label>
      Max Tickets:
      <input type="number" name="max_tickets"
             value="<?= htmlspecialchars($max_tickets) ?>" min="1" required>
    </label>

    <button type="submit">Update Session</button>
    
  </form>
  <p><a href="sessions.php">Back to Sessions</a></p>
</body>
</html>
