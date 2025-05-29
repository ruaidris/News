<?php
require_once __DIR__ . '/init.php';
header('Content-Type: application/json; charset=utf-8');

try {
    // Check if comments table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'comments'");
    $commentsTableExists = $checkTable->num_rows > 0;
    
    $tableInfo = [];
    
    if ($commentsTableExists) {
        // Get table structure
        $descResult = $conn->query("DESCRIBE comments");
        $columns = [];
        while ($row = $descResult->fetch_assoc()) {
            $columns[] = $row;
        }
        $tableInfo['columns'] = $columns;
        
        // Get sample data
        $sampleResult = $conn->query("SELECT * FROM comments LIMIT 5");
        $sampleData = $sampleResult->fetch_all(MYSQLI_ASSOC);
        $tableInfo['sample_data'] = $sampleData;
        
        // Count total comments
        $countResult = $conn->query("SELECT COUNT(*) as total FROM comments");
        $count = $countResult->fetch_assoc();
        $tableInfo['total_comments'] = $count['total'];
    }
    
    // Check user table
    $userTable = 'user';
    $checkUserTable = $conn->query("SHOW TABLES LIKE 'users'");
    if ($checkUserTable->num_rows > 0) {
        $userTable = 'users';
    }
    
    echo json_encode([
        'success' => true,
        'comments_table_exists' => $commentsTableExists,
        'user_table' => $userTable,
        'table_info' => $tableInfo
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
