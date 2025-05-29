<?php
require_once __DIR__ . '/../config.php';
ensureDoctor();

$doctor_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT name, region_id, hospital_id, profile_pic,
           qualification, age, experience, department_id
    FROM doctors
    WHERE id = ?
");
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$stmt->bind_result(
    $doctor_name,
    $region_id,
    $hospital_id,
    $profile_pic,
    $qualification,
    $age,
    $experience,
    $department_id
);
$stmt->fetch();
$stmt->close();

function resolveName($db, $table, $id) {
    if (!$id) return '';
    $stmt = $db->prepare("SELECT name FROM {$table} WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
    return $name;
}
$region_name     = resolveName($db, 'regions',     $region_id);
$hospital_name   = resolveName($db, 'hospitals',   $hospital_id);
$department_name = resolveName($db, 'departments', $department_id);

$stmt = $db->prepare("SELECT COUNT(*) FROM sessions WHERE doctor_id = ?");
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$stmt->bind_result($total_sessions);
$stmt->fetch();
$stmt->close();

$stmt = $db->prepare("
    SELECT COALESCE(SUM(sold_tickets),0)
    FROM sessions
    WHERE doctor_id = ?
");
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$stmt->bind_result($total_sold);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../templates/header.php'; ?>
  <title>Doctor Dashboard</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body class="doctor-dashboard">
  <?php show_flash(); ?>

  <section class="profile-overview">
    <div class="profile-pic-wrapper">
      <?php if ($profile_pic): ?>
        <img src="../<?= htmlspecialchars($profile_pic) ?>"
             alt="Dr. <?= htmlspecialchars($doctor_name) ?>"
             class="profile-pic">
      <?php else: ?>
        <div class="placeholder-pic">
          <?= strtoupper($doctor_name[0] ?? 'D') ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="profile-details">
      <h1>Dr. <?= htmlspecialchars($doctor_name) ?></h1>
      <p><strong>Hospital:</strong> <?= htmlspecialchars($hospital_name) ?></p>
      <p><strong>Region:</strong> <?= htmlspecialchars($region_name) ?></p>
      <p><strong>Qualification:</strong> <?= htmlspecialchars($qualification) ?></p>
      <p><strong>Department:</strong> <?= htmlspecialchars($department_name) ?></p>
      <p><strong>Age:</strong> <?= htmlspecialchars($age) ?> years</p>
      <p><strong>Experience:</strong> <?= htmlspecialchars($experience) ?> years</p>
    </div>
  </section>

  <div class="dashboard-stats">
    <div class="stat-card">
      <h2>Total Sessions</h2>
      <p><?= $total_sessions ?></p>
      <a href="sessions.php">Manage Sessions</a>
    </div>
    <div class="stat-card">
      <h2>Tickets Sold</h2>
      <p><?= $total_sold ?></p>
      <a href="sessions.php">View Details</a>
    </div>
  </div>

  <nav class="dashboard-nav">
    <a href="profile.php">Edit Profile</a> |
    <a href="sessions.php">Sessions</a> |
    <a href="../logout.php">Logout</a>
  </nav>

  <?php include '../templates/footer.php'; ?>
</body>
</html>