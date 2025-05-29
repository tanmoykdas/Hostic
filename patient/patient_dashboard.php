<?php
require_once __DIR__ . '/../config.php';
ensurePatient();

$patient_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT name, region_id, profile_pic, age, gender, address
    FROM patients
    WHERE id = ?
");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$stmt->bind_result($patient_name, $region_id, $profile_pic, $age, $gender, $address);
$stmt->fetch();
$stmt->close();

$region_name = '';
if ($region_id) {
    $r = $db->prepare("SELECT name FROM regions WHERE id = ?");
    $r->bind_param('i', $region_id);
    $r->execute();
    $r->bind_result($region_name);
    $r->fetch();
    $r->close();
}

$upcoming_count = 0;
$stmt = $db->prepare("
    SELECT COUNT(*)
    FROM appointments a
    JOIN sessions s ON a.session_id = s.id
    WHERE a.patient_id = ? AND s.start_time > NOW()
");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$stmt->bind_result($upcoming_count);
$stmt->fetch();
$stmt->close();

$past_count = 0;
$stmt = $db->prepare("
    SELECT COUNT(*)
    FROM appointments a
    JOIN sessions s ON a.session_id = s.id
    WHERE a.patient_id = ? AND s.start_time <= NOW()
");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$stmt->bind_result($past_count);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../templates/header.php'; ?>
  <title>Patient Dashboard</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="patient-dashboard">
  <?php show_flash(); ?>

  <section class="profile-overview">
    <?php if ($profile_pic): ?>
      <img src="../<?= htmlspecialchars($profile_pic) ?>"
           alt="<?= htmlspecialchars($patient_name) ?>"
           class="profile-pic">
    <?php else: ?>
      <div class="placeholder-pic">
        <?= strtoupper($patient_name[0] ?? 'P') ?>
      </div>
    <?php endif; ?>
    <div class="profile-details">
      <h1><?= htmlspecialchars($patient_name) ?></h1>
      <p><strong>Region:</strong> <?= htmlspecialchars($region_name) ?></p>
      <p><strong>Age:</strong> <?= htmlspecialchars($age) ?> years</p>
      <p><strong>Gender:</strong> <?= htmlspecialchars($gender) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
    </div>
  </section>

  <div class="dashboard-stats">
    <div class="stat-card">
      <h2>Upcoming Appointments</h2>
      <p><?= $upcoming_count ?></p>
      <a href="book.php">Book</a>
    </div>
    <div class="stat-card">
      <h2>Appointment History</h2>
      <p><?= $past_count ?></p>
      <a href="history.php">History</a>
    </div>
  </div>

  <nav class="dashboard-nav">
    <a href="profile.php">Edit Profile</a> |
    <a href="book.php">Book Appointment</a> |
    <a href="history.php">History</a> |
    <a href="../logout.php">Logout</a>
  </nav>

  <?php include '../templates/footer.php'; ?>
</body>
</html>
