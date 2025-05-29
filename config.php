<?php

session_start();

$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'doctor_appointment';

$db = new mysqli($host, $user, $password, $dbname);
if ($db->connect_error) {
    die('Database connection failed: ' . $db->connect_error);
}
$db->set_charset('utf8mb4');

function esc(string $str): string {
    global $db;
    return $db->real_escape_string(trim($str));
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id'], $_SESSION['role']);
}
function isDoctor(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'doctor';
}
function isPatient(): bool {
    return isLoggedIn() && $_SESSION['role'] === 'patient';
}

function ensureDoctor(): void {
    if (!isDoctor()) {
        redirect('../login.php');
    }
}
function ensurePatient(): void {
    if (!isPatient()) {
        redirect('../login.php');
    }
}

function set_flash(string $type, string $msg): void {
    $_SESSION['flash'][$type][] = $msg;
}
function show_flash(): void {
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $type => $msgs) {
        foreach ($msgs as $m) {
            echo "<div class='alert alert-{$type}'>" . htmlspecialchars($m) . "</div>";
        }
    }
    unset($_SESSION['flash']);
}