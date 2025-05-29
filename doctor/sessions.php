<?php
require_once __DIR__ . '/../config.php';
ensureDoctor();

$doctor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_time  = esc($_POST['start_time']  ?? '');
    $end_time    = esc($_POST['end_time']    ?? '');
    $price       = esc($_POST['price']       ?? '');
    $max_tickets = (int)($_POST['max_tickets'] ?? 0);

    if (!$start_time || !$end_time || !$price || $max_tickets < 1) {
        set_flash('error', 'All fields are required and must be valid.');
        redirect('doctor_dashboard.php');
    }

    $stmt = $db->prepare("
        INSERT INTO sessions
            (doctor_id, start_time, end_time, price, max_tickets)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'issdi',
        $doctor_id,
        $start_time,
        $end_time,
        $price,
        $max_tickets
    );

    if ($stmt->execute()) {
        set_flash('success', 'Session created successfully.');
    } else {
        set_flash('error', 'Failed to create session: ' . $db->error);
    }
    $stmt->close();

    redirect('doctor_dashboard.php');
}

$sessions = [];
$stmt = $db->prepare("
    SELECT id, start_time, end_time, price, max_tickets, sold_tickets
    FROM sessions
    WHERE doctor_id = ?
    ORDER BY start_time DESC
");
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $sessions[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../templates/header.php'; ?>
  <title>Your Sessions</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/sessions.css">
</head>
<body>
  <?php show_flash(); ?>

  <div class="sessions-container">
    <h1>Your Sessions</h1>
    <?php if (!empty($sessions)): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Price</th>
            <th>Max Tickets</th>
            <th>Sold Tickets</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sessions as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['id']) ?></td>
            <td><?= htmlspecialchars($s['start_time']) ?></td>
            <td><?= htmlspecialchars($s['end_time']) ?></td>
            <td><?= htmlspecialchars($s['price']) ?></td>
            <td><?= htmlspecialchars($s['max_tickets']) ?></td>
            <td><?= htmlspecialchars($s['sold_tickets']) ?></td>
            <td>
              <a href="session_edit.php?id=<?= $s['id'] ?>">Edit</a> |
              <a href="session_delete.php?id=<?= $s['id'] ?>" 
                 onclick="return confirm('Delete this session?')">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No sessions created yet.</p>
    <?php endif; ?>

    <h2>Create New Session</h2>
    <form method="post" action="sessions.php">
      <label>
        Start Time:
        <input type="datetime-local" name="start_time" required>
      </label>
      <label>
        End Time:
        <input type="datetime-local" name="end_time" required>
      </label>
      <label>
        Price:
        <input type="number" name="price" step="0.01" required>
      </label>
      <label>
        Max Tickets:
        <input type="number" name="max_tickets" min="1" required>
      </label>
      <button type="submit">Create Session</button>
    </form>
  </div>
  <?php include '../templates/footer.php'; ?>
</body>
</html>
