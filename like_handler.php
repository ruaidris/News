<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/init.php';
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
}


$input = json_decode(file_get_contents('php://input'), true);
$news_id = isset($input['news_id']) ? intval($input['news_id']) : 0;

if ($news_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("
    INSERT IGNORE INTO news_likes (news_id, user_id)
    VALUES (?, ?)
");
$stmt->bind_param('ii', $news_id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("
    SELECT COUNT(*) AS cnt
    FROM news_likes
    WHERE news_id = ?
");
$stmt->bind_param('i', $news_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();


$stmt = $conn->prepare("
    UPDATE news
    SET likes = ?
    WHERE id = ?
");
$stmt->bind_param('ii', $count, $news_id);
$stmt->execute();
$stmt->close();


header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'likes'   => $count
]);
