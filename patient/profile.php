<?php
require_once __DIR__ . '/../config.php';
ensurePatient();

$patient_id = $_SESSION['user_id'];
$name = $region_id = $profile_pic = $address = $gender = '';
$age = 0;
$region_name = '';

$stmt = $db->prepare("
    SELECT name, region_id, profile_pic, age, gender, address
    FROM patients
    WHERE id = ?
");
$stmt->bind_param('i', $patient_id);
$stmt->execute();
$stmt->bind_result($name, $region_id, $profile_pic, $age, $gender, $address);
$stmt->fetch();
$stmt->close();

if ($region_id) {
    $r = $db->prepare("SELECT name FROM regions WHERE id = ?");
    $r->bind_param('i', $region_id);
    $r->execute();
    $r->bind_result($region_name);
    $r->fetch();
    $r->close();
}

$uploadDir = __DIR__ . '/../uploads/patients/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age_input     = (int)($_POST['age'] ?? 0);
    $gender_input  = esc($_POST['gender'] ?? '');
    $address_input = esc($_POST['address'] ?? '');

    if ($age_input < 0) {
        set_flash('error','Please enter a valid age.');
        redirect('patient/profile.php');
    }

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $tmp  = $_FILES['profile_pic']['tmp_name'];
            $orig = $_FILES['profile_pic']['name'];
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (!in_array($ext, $allowed)) {
                set_flash('error','Only JPG, JPEG, PNG & GIF allowed.');
                redirect('patient/profile.php');
            }
            $newFile = uniqid("pat_{$patient_id}_") . ".{$ext}";
            $dest = $uploadDir . $newFile;
            if (move_uploaded_file($tmp, $dest)) {
                $profile_pic = 'uploads/patients/' . $newFile;
            } else {
                set_flash('error','Failed to save uploaded file.');
                redirect('patient/profile.php');
            }
        } else {
            set_flash('error','Upload error code: ' . $_FILES['profile_pic']['error']);
            redirect('patient/profile.php');
        }
    }

    $u = $db->prepare("
        UPDATE patients
        SET profile_pic = ?,
            age         = ?,
            gender      = ?,
            address     = ?
        WHERE id = ?
    ");
    $u->bind_param(
        'sissi',
        $profile_pic,
        $age_input,
        $gender_input,
        $address_input,
        $patient_id
    );
    if ($u->execute()) {
        set_flash('success','');
        redirect('patient_dashboard.php');
    } else {
        set_flash('error','Update failed: ' . $db->error);
    }
    $u->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include '../templates/header.php'; ?>
  <title>Patient Profile</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
  <div class="flash-container"><?php show_flash(); ?></div>

  <div class="patient-profile-container">
    <h1>Update Your Profile</h1>
    <form method="post" enctype="multipart/form-data">
      <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
      <p><strong>Region:</strong> <?= htmlspecialchars($region_name) ?></p>

      <label>
        Age:
        <input type="number" name="age" value="<?= htmlspecialchars($age) ?>" min="0" required>
      </label>

      <label>
        Gender:
        <select name="gender" required>
          <option value="">-- Select Gender --</option>
          <option value="Male"   <?= $gender==='Male'   ? 'selected' : '' ?>>Male</option>
          <option value="Female" <?= $gender==='Female' ? 'selected' : '' ?>>Female</option>
          <option value="Other"  <?= $gender==='Other'  ? 'selected' : '' ?>>Other</option>
        </select>
      </label>

      <label>
        Address:
        <textarea name="address" rows="3" required><?= htmlspecialchars($address) ?></textarea>
      </label>

      <label>
        Profile Picture:
        <input type="file" name="profile_pic" accept="image/*">
      </label>
      <?php if ($profile_pic): ?>
        <img src="../<?= htmlspecialchars($profile_pic) ?>" alt="Profile Pic" width="150">
      <?php endif; ?>

      <button type="submit">Save Profile</button>
    </form>
  </div>

  <?php include '../templates/footer.php'; ?>
</body>
</html>
