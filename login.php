<?php
require_once __DIR__ . '/config.php';

if (isLoggedIn()) {
    if (isDoctor())  redirect('doctor/doctor_dashboard.php');
    if (isPatient()) redirect('patient/patient_dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = (isset($_POST['role']) && $_POST['role'] === 'doctor') ? 'doctor' : 'patient';
} else {
    $role = (isset($_GET['role']) && $_GET['role'] === 'doctor') ? 'doctor' : 'patient';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = esc($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        set_flash('error', 'Email and password are required.');
        redirect("login.php?role={$role}");
    }

    $tbl = ($role === 'doctor') ? 'doctors' : 'patients';

    $stmt = $db->prepare("SELECT id, password FROM {$tbl} WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $hash);

    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role']    = $role;
        set_flash('success', ucfirst($role) . ' logged in.');

        $stmt->close();
        if ($role === 'doctor') {
            redirect('doctor/doctor_dashboard.php');
        } else {
            redirect('patient/patient_dashboard.php');
        }
    } else {
        $stmt->close();
        set_flash('error', 'Invalid credentials.');
        redirect("login.php?role={$role}");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'templates/header.php'; ?>
  <title>Login as <?= ucfirst($role) ?></title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
  <?php show_flash(); ?>

  <div class="login-container">
    <h1>Login as <?= ucfirst($role) ?></h1>
    <form method="post" action="login.php?role=<?= $role ?>">
      <input type="hidden" name="role" value="<?= $role ?>">

      <label>
        Email:
        <input type="email" name="email" required autofocus>
      </label>

      <label>
        Password:
        <input type="password" name="password" required>
      </label>

      <button type="submit">Login</button>
    </form>

    <p>
      Not registered? <a href="register.php">Create an account</a>
    </p>
  </div>

  <?php include 'templates/footer.php'; ?>
</body>
</html>