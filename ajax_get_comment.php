<?php
// إيقاف عرض الأخطاء في JSON
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/init.php';

header('Content-Type: application/json; charset=utf-8');

$news_id = intval($_GET['news_id'] ?? 0);
if ($news_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid news ID'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Check which user table exists
    $userTable = 'user';
    $checkTable = $conn->query("SHOW TABLES LIKE 'users'");
    if ($checkTable->num_rows > 0) {
        $userTable = 'users';
    }
    
    // Check if comments table exists
    $checkComments = $conn->query("SHOW TABLES LIKE 'comments'");
    if ($checkComments->num_rows == 0) {
        echo json_encode([
            'success' => true,
            'comments' => [],
            'message' => 'No comments table found'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
      $res = $conn->prepare("
        SELECT c.content, c.dateposted, u.email, u.name
        FROM comments c
        JOIN `$userTable` u ON c.user_id = u.id
        WHERE c.news_id = ?
        ORDER BY c.dateposted DESC
    ");
    $res->bind_param('i', $news_id);
    $res->execute();
    $list = $res->get_result()->fetch_all(MYSQLI_ASSOC);
    $res->close();
    
    // Format dates for display
    foreach ($list as &$comment) {
        $comment['dateposted'] = date('j F Y - H:i', strtotime($comment['dateposted']));
        $comment['content'] = htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8');
        $comment['username'] = htmlspecialchars($comment['name'] ?? $comment['email'], ENT_QUOTES, 'UTF-8');
        // Remove email from output for privacy
        unset($comment['email'], $comment['name']);
    }
    
    echo json_encode([
        'success' => true,
        'comments' => $list,
        'total' => count($list)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch comments'
    ], JSON_UNESCAPED_UNICODE);
}
