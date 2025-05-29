<?php
require_once __DIR__ . '/../config.php';
ensurePatient();

$patient_id = $_SESSION['user_id'];

$regions = [];
$res = $db->query("SELECT id, name FROM regions ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $regions[] = $row;
}

$departments = [];
$res = $db->query("SELECT id, name FROM departments ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $departments[] = $row;
}

$hospitals = [];
$selected_region = $_GET['region_id'] ?? '';
if ($selected_region) {
    $stmt = $db->prepare("SELECT id, name FROM hospitals WHERE region_id = ? ORDER BY name");
    $stmt->bind_param('i', $selected_region);
    $stmt->execute();
    $stmt->bind_result($hid, $hname);
    while ($stmt->fetch()) {
        $hospitals[] = ['id' => $hid, 'name' => $hname];
    }
    $stmt->close();
}

$sessions = [];
if (
    isset($_GET['region_id'], $_GET['hospital_id'], $_GET['department_id'], $_GET['date'])
    && $_GET['region_id'] && $_GET['hospital_id']
    && $_GET['department_id'] && $_GET['date']
) {
    $region_id     = (int) $_GET['region_id'];
    $hospital_id   = (int) $_GET['hospital_id'];
    $department_id = (int) $_GET['department_id'];
    $date_input    = $_GET['date'];

    $date = DateTime::createFromFormat('Y-m-d', $date_input);
    if ($date) {
        $date_str = $date->format('Y-m-d');

        $stmt = $db->prepare("
            SELECT
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
            WHERE d.region_id = ?
              AND d.hospital_id = ?
              AND d.department_id = ?
              AND DATE(s.start_time) = ?
            ORDER BY s.start_time
        ");
        $stmt->bind_param('iiis', $region_id, $hospital_id, $department_id, $date_str);
        $stmt->execute();
        $stmt->bind_result(
            $sid, $doc_name, $doc_pic,
            $start_time, $end_time,
            $price, $max_tix, $sold_tix
        );
        while ($stmt->fetch()) {
            $pure = preg_replace('/^Dr\.?\s*/i', '', $doc_name);
            $initial = strtoupper(substr($pure, 0, 1));

            $sessions[] = [
                'id'           => $sid,
                'doctor_name'  => $doc_name,
                'profile_pic'  => $doc_pic,
                'start_time'   => $start_time,
                'end_time'     => $end_time,
                'price'        => $price,
                'available'    => $max_tix - $sold_tix,
                'initial'      => $initial
            ];
        }
        $stmt->close();
    } else {
        set_flash('error','Invalid date format.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../templates/header.php'; ?>
  <title>Book Appointment</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/sessions.css">
</head>
<body>
  <?php show_flash(); ?>

  <div class="nav-back">
    <a href="patient_dashboard.php">← Back to Dashboard</a>
  </div>

  <h1 class="book-appointment-title">Book an Appointment</h1>
  <form method="get" class="book-form">
    <label>
      Region:
      <select name="region_id" required onchange="this.form.submit()">
        <option value="">-- Select Region --</option>
        <?php foreach ($regions as $r): ?>
          <option value="<?= $r['id'] ?>" <?= ($r['id'] == $selected_region ? 'selected' : '') ?>>
            <?= htmlspecialchars($r['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>
      Hospital:
      <select name="hospital_id" required <?= empty($hospitals) ? 'disabled' : '' ?> onchange="this.form.submit()">
        <option value="">-- Select Hospital --</option>
        <?php foreach ($hospitals as $h): ?>
          <option value="<?= $h['id'] ?>" <?= ($h['id'] == ($_GET['hospital_id'] ?? '') ? 'selected' : '') ?>>
            <?= htmlspecialchars($h['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>
      Department:
      <select name="department_id" required>
        <option value="">-- Select Department --</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= $d['id'] ?>" <?= ($d['id'] == ($_GET['department_id'] ?? '') ? 'selected' : '') ?>>
            <?= htmlspecialchars($d['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>
      Date:
      <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>" required>
    </label>

    <button type="submit" style="display:block; margin:auto; padding:0.5rem 1rem;">
      Search
    </button>
  </form>

  <?php if (!empty($sessions)): ?>
    <div class="sessions">
      <?php foreach ($sessions as $s): ?>
        <div class="session-card">
          <?php if ($s['profile_pic']): ?>
            <img src="../<?= htmlspecialchars($s['profile_pic']) ?>" alt="Dr. <?= htmlspecialchars($s['doctor_name']) ?>">
          <?php else: ?>
            <div class="placeholder-pic"><?= $s['initial'] ?></div>
          <?php endif; ?>
          <div class="session-info">
            <h3><?= htmlspecialchars($s['doctor_name']) ?></h3>
            <p><strong>Time:</strong> <?= $s['start_time'] ?> - <?= $s['end_time'] ?></p>
            <p><strong>Price:</strong> $<?= number_format($s['price'],2) ?></p>
            <p><strong>Available Tickets:</strong> <?= $s['available'] ?></p>
            <?php if ($s['available'] > 0): ?>
              <a href="confirm.php?session_id=<?= $s['id'] ?>">Confirm</a>
            <?php else: ?>
              <span>Sold Out</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php include '../templates/footer.php'; ?>
</body>
</html>
