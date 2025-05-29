<?php
require_once __DIR__ . '/../config.php';
ensurePatient();

$patient_id = $_SESSION['user_id'];

$stmt = $db->prepare(
    "SELECT
         a.id,
         d.name        AS doctor_name,
         d.profile_pic,
         DATE_FORMAT(s.start_time, '%Y-%m-%d') AS appt_date,
         DATE_FORMAT(s.start_time, '%H:%i') AS start_time,
         DATE_FORMAT(s.end_time,   '%H:%i') AS end_time,
         a.quantity,
         a.total_price
     FROM appointments a
     JOIN sessions s ON a.session_id = s.id
     JOIN doctors d  ON s.doctor_id   = d.id
     WHERE a.patient_id = ?
     ORDER BY s.start_time DESC"
);
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$stmt->bind_result(
    $appt_id,
    $doctor_name,
    $doctor_pic,
    $appt_date,
    $start_time,
    $end_time,
    $quantity,
    $total_price
);
$appointments = [];
while ($stmt->fetch()) {
    $pure = preg_replace('/^Dr\.?\s*/i','', $doctor_name);
    $initial = strtoupper(substr($pure, 0, 1));

    $appointments[] = [
        'id'           => $appt_id,
        'doctor_name'  => $doctor_name,
        'profile_pic'  => $doctor_pic,
        'initial'      => $initial,
        'date'         => $appt_date,
        'start_time'   => $start_time,
        'end_time'     => $end_time,
        'quantity'     => $quantity,
        'total_price'  => $total_price
    ];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../templates/header.php'; ?>
  <title>Appointment History</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/sessions.css">
</head>
<body>
  <div class="nav-back">
    <a href="patient_dashboard.php">← Back to Dashboard</a>
  </div>
  <h1 class="history-title">Appointment History</h1>
  <div class="appointments">
    <?php if (empty($appointments)): ?>
      <p style="text-align:center;">You have no past appointments.</p>
    <?php else: ?>
      <?php foreach ($appointments as $a): ?>
        <div class="appt-card">
          <?php if ($a['profile_pic']): ?>
            <img src="../<?= htmlspecialchars($a['profile_pic']) ?>" alt="Dr. <?= htmlspecialchars($a['doctor_name']) ?>">
          <?php else: ?>
            <div class="placeholder-pic"><?= $a['initial'] ?></div>
          <?php endif; ?>
          <div class="appt-info">
            <h3>Dr. <?= htmlspecialchars($a['doctor_name']) ?></h3>
            <p><strong>Date:</strong> <?= htmlspecialchars($a['date']) ?></p>
            <p><strong>Time:</strong> <?= htmlspecialchars($a['start_time']) ?> - <?= htmlspecialchars($a['end_time']) ?></p>
            <p><strong>Tickets:</strong> <?= htmlspecialchars($a['quantity']) ?></p>
            <p><strong>Total Paid:</strong> $<?= number_format($a['total_price'],2) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <?php include '../templates/footer.php'; ?>
</body>
</html>