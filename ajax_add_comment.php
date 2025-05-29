<?php
// تمكين تسجيل الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/comment_errors.log');

require_once __DIR__ . '/init.php';
header('Content-Type: application/json; charset=utf-8');

// Log for debugging
file_put_contents(__DIR__ . '/comment_debug.log', 
    date('Y-m-d H:i:s') . " - Comment request started\n" . 
    "POST data: " . print_r($_POST, true) . 
    "Session: " . print_r($_SESSION, true) . "\n\n", 
    FILE_APPEND
);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول أولاً'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate input
if (!isset($_POST['news_id']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'error' => 'بيانات مفقودة'], JSON_UNESCAPED_UNICODE);
    exit;
}

$news_id = (int)$_POST['news_id'];
$content = trim($_POST['content']);
$user_id = $_SESSION['user_id'];

// Validate content
if (empty($content)) {
    echo json_encode(['success' => false, 'error' => 'لا يمكن أن يكون التعليق فارغاً'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'error' => 'التعليق طويل جداً (أقصى حد 1000 حرف)'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Check if news exists
$stmt = $conn->prepare("SELECT id FROM news WHERE id = ?");
$stmt->bind_param('i', $news_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->fetch_assoc()) {
    echo json_encode(['success' => false, 'error' => 'الخبر غير موجود'], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    exit;
}
$stmt->close();

// Get user information
$userTable = 'user';
$checkTable = $conn->query("SHOW TABLES LIKE 'users'");
if ($checkTable->num_rows > 0) {
    $userTable = 'users';
}

$stmt = $conn->prepare("SELECT email FROM `$userTable` WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'المستخدم غير موجود'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (news_id, user_id, content, dateposted) VALUES (?, ?, ?, NOW())");
$stmt->bind_param('iis', $news_id, $user_id, $content);

if ($stmt->execute()) {
    file_put_contents(__DIR__ . '/comment_debug.log', 
        date('Y-m-d H:i:s') . " - Comment inserted successfully - ID: " . $conn->insert_id . "\n\n", 
        FILE_APPEND
    );
    echo json_encode([
        'success' => true,
        'message' => 'تم إضافة التعليق بنجاح',
        'comment_id' => $conn->insert_id
    ], JSON_UNESCAPED_UNICODE);
} else {
    file_put_contents(__DIR__ . '/comment_debug.log', 
        date('Y-m-d H:i:s') . " - Comment insert failed: " . $stmt->error . "\n\n", 
        FILE_APPEND
    );
    echo json_encode([
        'success' => false,
        'error' => 'فشل في إضافة التعليق: ' . $stmt->error
    ], JSON_UNESCAPED_UNICODE);
}
$stmt->close();
