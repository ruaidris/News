<?php
require_once __DIR__ . '/db.php';
$timeout = 300; //5
ini_set('session.gc_maxlifetime', $timeout);
session_set_cookie_params([
  'lifetime' => $timeout,
  'path'     => '/',
  'secure'   => false,
  'httponly' => true,
  'samesite' => 'Lax',
]);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $timeout) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
$_SESSION['last_activity'] = time();
