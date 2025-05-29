<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include 'templates/header.php'; ?>
  <title>Online Doctor Appointment System</title>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
  <?php show_flash(); ?>

  <header>
    <h1>Welcome to the Hostic</h1>
  </header>

  <section class="login_buton">
    <h2>Choose your login:</h2>
    <a href="login.php?role=patient"><button>Login as Patient</button></a>
    <a href="login.php?role=doctor"><button>Login as Doctor</button></a>
  </section>

  <section>
    <h2>How It Works</h2>
    <p>Patients can browse doctors by region, date,and department, then book and pay for appointments online.</p>
    <p>Doctors can set up session slots, see how many tickets sold, and manage their profile.</p>
  </section>

  <section>
    <h2>Our Partners</h2>
    <div class="gallery">
      <img src="assets/images/doctors/doctor1.png" alt="Doctor Photo">
      <img src="assets/images/hospitals/hospital1.png" alt="Hospital Photo">
    </div>
  </section>

  <?php include 'templates/footer.php'; ?>
</body>
</html>
