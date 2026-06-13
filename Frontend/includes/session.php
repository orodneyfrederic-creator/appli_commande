<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in = isset($_SESSION['user']);
$current_user = $is_logged_in ? $_SESSION['user'] : null;

function require_login() {
    global $is_logged_in;
    if (!$is_logged_in) {
        header("Location: /appli_commande/Frontend/login.php");
        exit();
    }
}

function require_admin() {
    global $is_logged_in, $current_user;
    if (!$is_logged_in || ($current_user['role'] ?? 'client') !== 'admin') {
        header("Location: /appli_commande/Frontend/index.php");
        exit();
    }
}
?>
