<?php
require_once __DIR__ . '/../config.php';
ensureDoctor();

$doctor_id = $_SESSION['user_id'];

$name = $qualification = $profile_pic = '';
$age = $experience = $department_id = 0;
$region_name = $hospital_name = '';

$stmt = $db->prepare("
    SELECT name, region_id, hospital_id, profile_pic, qualification, age, experience, department_id
    FROM doctors
    WHERE id = ?
");
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$stmt->bind_result(
    $name, $region_id, $hospital_id, $profile_pic,
    $qualification, $age, $experience, $department_id
);
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

if ($hospital_id) {
    $h = $db->prepare("SELECT name FROM hospitals WHERE id = ?");
    $h->bind_param('i', $hospital_id);
    $h->execute();
    $h->bind_result($hospital_name);
    $h->fetch();
    $h->close();
}

$depts = [];
$res = $db->query("SELECT id, name FROM departments ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $depts[] = $row;
}

$uploadDir = __DIR__ . '/../uploads/doctors/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hospital_input      = esc($_POST['hospital'] ?? '');
    $qualification_input = esc($_POST['qualification'] ?? '');
    $age_input           = (int)($_POST['age'] ?? 0);
    $experience_input    = (int)($_POST['experience'] ?? 0);
    $dept_input          = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;

    if ($dept_input < 1) {
        set_flash('error', 'Please select a valid department.');
        redirect('doctor/profile.php');
    }

    if ($hospital_input) {
        $h2 = $db->prepare("SELECT id FROM hospitals WHERE name = ? AND region_id = ?");
        $h2->bind_param('si', $hospital_input, $region_id);
        $h2->execute();
        $h2->bind_result($new_hospital_id);
        if (!$h2->fetch()) {
            $h2->close();
            $h2 = $db->prepare("INSERT INTO hospitals (name, region_id) VALUES (?, ?)");
            $h2->bind_param('si', $hospital_input, $region_id);
            $h2->execute();
            $new_hospital_id = $h2->insert_id;
        }
        $h2->close();
    } else {
        $new_hospital_id = $hospital_id;
    }

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $tmp  = $_FILES['profile_pic']['tmp_name'];
            $orig = $_FILES['profile_pic']['name'];
            $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            if (!in_array($ext, $allowed)) {
                set_flash('error','Only JPG, JPEG, PNG & GIF allowed.');
                redirect('doctor/profile.php');
            }
            $newFile = uniqid("doc_{$doctor_id}_") . ".{$ext}";
            $dest = $uploadDir . $newFile;
            if (move_uploaded_file($tmp, $dest)) {
                $profile_pic = 'uploads/doctors/' . $newFile;
            } else {
                set_flash('error','Failed to move uploaded file.');
                redirect('doctor/profile.php');
            }
        } else {
            set_flash('error','Upload error code: ' . $_FILES['profile_pic']['error']);
            redirect('doctor/profile.php');
        }
    }

    $u = $db->prepare("
        UPDATE doctors
        SET hospital_id    = ?,
            profile_pic    = ?,
            qualification  = ?,
            age            = ?,
            experience     = ?,
            department_id  = ?
        WHERE id = ?
    ");
    $u->bind_param(
        'issiiii',
        $new_hospital_id,
        $profile_pic,
        $qualification_input,
        $age_input,
        $experience_input,
        $dept_input,
        $doctor_id
    );
    if ($u->execute()) {
        set_flash('success', '');
        redirect('doctor_dashboard.php');
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
  <title>Doctor Profile</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>
  <?php show_flash(); ?>

  <div class="profile-form-container">
    <h1>Update Your Profile</h1>
    <form method="post" enctype="multipart/form-data">
      <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
      <p><strong>Region:</strong> <?= htmlspecialchars($region_name) ?></p>

      <label>
        Hospital:
        <input type="text" name="hospital" value="<?= htmlspecialchars($hospital_name) ?>">
      </label>

      <label>
        Qualification:
        <input type="text" name="qualification" value="<?= htmlspecialchars($qualification) ?>">
      </label>

      <label>
        Age:
        <input type="number" name="age" value="<?= htmlspecialchars($age) ?>">
      </label>

      <label>
        Experience (years):
        <input type="number" name="experience" value="<?= htmlspecialchars($experience) ?>">
      </label>

      <label>
        Department:
        <select name="department_id" required>
          <option value="">-- Select Department --</option>
          <?php foreach ($depts as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $d['id'] === $department_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Profile Picture:
        <input type="file" name="profile_pic" accept="image/*">
      </label>
      <?php if ($profile_pic): ?>
        <div>
          <img src="../<?= htmlspecialchars($profile_pic) ?>" alt="Profile Pic" width="150">
        </div>
      <?php endif; ?>

      <button type="submit">Save Profile</button>
    </form>
  </div>

  <?php include '../templates/footer.php'; ?>
</body>
</html>