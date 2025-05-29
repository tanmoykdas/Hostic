<?php

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $role     = $_POST['role'] === 'doctor' ? 'doctor' : 'patient';
    $name     = esc($_POST['name'] ?? '');
    $region   = esc($_POST['region'] ?? '');
    $email    = esc($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$region || !$email || !$password) {
        set_flash('error', 'All fields are required.');
        redirect('register.php');
    }

    $stmt = $db->prepare("SELECT id FROM regions WHERE name = ?");
    $stmt->bind_param('s', $region);
    $stmt->execute();
    $stmt->bind_result($region_id);
    if (!$stmt->fetch()) {
        $stmt->close();
        $stmt = $db->prepare("INSERT INTO regions (name) VALUES (?)");
        $stmt->bind_param('s', $region);
        $stmt->execute();
        $region_id = $stmt->insert_id;
    }
    $stmt->close();

    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'doctor') {
        $hospital = esc($_POST['hospital'] ?? '');
        if (!$hospital) {
            set_flash('error', 'Hospital name is required for doctors.');
            redirect('register.php');
        }

        $stmt = $db->prepare("SELECT id FROM hospitals WHERE name = ? AND region_id = ?");
        $stmt->bind_param('si', $hospital, $region_id);
        $stmt->execute();
        $stmt->bind_result($hospital_id);
        if (!$stmt->fetch()) {
            $stmt->close();
            $stmt = $db->prepare("INSERT INTO hospitals (name, region_id) VALUES (?, ?)");
            $stmt->bind_param('si', $hospital, $region_id);
            $stmt->execute();
            $hospital_id = $stmt->insert_id;
        }
        $stmt->close();

        $stmt = $db->prepare("
            INSERT INTO doctors (name, region_id, hospital_id, email, password)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('siiss', $name, $region_id, $hospital_id, $email, $hash);
        $ok = $stmt->execute();
        $stmt->close();

    } else {
        $stmt = $db->prepare("
            INSERT INTO patients (name, region_id, email, password)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('siss', $name, $region_id, $email, $hash);
        $ok = $stmt->execute();
        $stmt->close();
    }

    if ($ok) {
        set_flash('success', ucfirst($role) . ' registered successfully. Please log in.');
        redirect('login.php?role=' . $role);
    } else {
        set_flash('error', 'Registration failed: ' . $db->error);
        redirect('register.php');
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'templates/header.php'; ?>
  <title>Register</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
  <?php show_flash(); ?>

  <div class="register-container">
    <h1>Create Your Account</h1>
    <form method="post" action="register.php">
      <label>
        Role:
        <select name="role" id="role">
          <option value="patient">Patient</option>
          <option value="doctor">Doctor</option>
        </select>
      </label>

      <label>
        Name:
        <input type="text" name="name" required>
      </label>

      <label>
        Region:
        <input type="text" name="region" required>
      </label>

      <div id="doctorFields" style="display:none;">
        <label>
          Hospital:
          <input type="text" name="hospital">
        </label>
      </div>

      <label>
        Email:
        <input type="email" name="email" required>
      </label>

      <label>
        Password:
        <input type="password" name="password" required>
      </label>

      <button type="submit">Register</button>
    </form>
  </div>

  <script>
    const roleSelect = document.getElementById('role');
    const docFields  = document.getElementById('doctorFields');
    function toggleDoctorFields() {
      docFields.style.display = roleSelect.value === 'doctor' ? 'block' : 'none';
    }
    roleSelect.addEventListener('change', toggleDoctorFields);
    window.addEventListener('load', toggleDoctorFields);
  </script>

  <?php include 'templates/footer.php'; ?>
</body>
</html>