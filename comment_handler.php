<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/init.php';
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit;
}


$news_id = isset($_POST['news_id']) ? intval($_POST['news_id']) : 0;
$content = trim($_POST['content'] ?? '');


if ($news_id <= 0 || $content === '') { 
    header("Location: details.php?id=$news_id");
    exit;
}


$stmt = $conn->prepare("
    INSERT INTO comments (news_id, user_id, content)
    VALUES (?, ?, ?)
");
$stmt->bind_param('iis', $news_id, $_SESSION['user_id'], $content);
$stmt->execute();
$stmt->close();

header("Location: details.php?id=$news_id");
exit;
