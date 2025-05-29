<?php
require_once __DIR__ . '/../config.php';
ensurePatient();

$patient_id = $_SESSION['user_id'];

$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
if (!$session_id) {
    set_flash('error', 'Invalid session.');
    redirect('book.php');
}

$stmt = $db->prepare(
    "SELECT
         s.id,
         d.name        AS doctor_name,
         d.profile_pic,
         DATE_FORMAT(s.start_time, '%H:%i') AS start_time,
         DATE_FORMAT(s.end_time,   '%H:%i') AS end_time,
         s.price,
         s.max_tickets,
         s.sold_tickets
     FROM sessions s
     JOIN doctors d ON s.doctor_id = d.id
     WHERE s.id = ?"
);
$stmt->bind_param('i', $session_id);
$stmt->execute();
$stmt->bind_result(
    $sid,
    $doctor_name,
    $doctor_pic,
    $start_time,
    $end_time,
    $price,
    $max_tickets,
    $sold_tickets
);
if (!$stmt->fetch()) {
    $stmt->close();
    set_flash('error', 'Session not found.');
    redirect('book.php');
}
$stmt->close();

$available = $max_tickets - $sold_tickets;
if ($available <= 0) {
    set_flash('error', 'No tickets available for this session.');
    redirect('book.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    if ($quantity < 1 || $quantity > $available) {
        set_flash('error', 'Please select a valid number of tickets.');
        redirect("confirm.php?session_id={$session_id}");
    }
    $total_price = $quantity * $price;

    $db->begin_transaction();
    try {
        $ins = $db->prepare(
            "INSERT INTO appointments (patient_id, session_id, quantity, total_price)
             VALUES (?, ?, ?, ?)"
        );
        $ins->bind_param('iiii', $patient_id, $session_id, $quantity, $total_price);
        $ins->execute();
        $ins->close();

        $upd = $db->prepare(
            "UPDATE sessions SET sold_tickets = sold_tickets + ? WHERE id = ?"
        );
        $upd->bind_param('ii', $quantity, $session_id);
        $upd->execute();
        $upd->close();

        $db->commit();
        set_flash('success', 'Appointment booked successfully!');
        redirect('history.php');
    } catch (Exception $e) {
        $db->rollback();
        set_flash('error', 'Booking failed: ' . $e->getMessage());
        redirect("confirm.php?session_id={$session_id}");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../templates/header.php'; ?>
  <title>Confirm Appointment</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/sessions.css">
</head>
<body>
  <?php show_flash(); ?>

  <div class="session-card">
    <?php if ($doctor_pic): ?>
      <img src="../<?= htmlspecialchars($doctor_pic) ?>" alt="Dr. <?= htmlspecialchars($doctor_name) ?>">
    <?php else: ?>
      <?php
        $pure = preg_replace('/^Dr\.?\s*/i', '', $doctor_name);
        $initial = strtoupper(substr($pure, 0, 1));
      ?>
      <div class="placeholder-pic"><?= $initial ?></div>
    <?php endif; ?>
    <div class="session-info">
      <h2>Dr. <?= htmlspecialchars($doctor_name) ?></h2>
      <p><strong>Time:</strong> <?= $start_time ?> - <?= $end_time ?></p>
      <p><strong>Price per Ticket:</strong> $<?= number_format($price, 2) ?></p>
      <p><strong>Available Tickets:</strong> <?= $available ?></p>
    </div>
  </div>

  <form method="post" class="confirm-form">
    <label>
      Quantity:
      <select name="quantity" required>
        <?php for ($i = 1; $i <= $available; $i++): ?>
          <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <button type="submit">Confirm</button>
  </form>

  <?php include '../templates/footer.php'; ?>
</body>
</html>