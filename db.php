<?php
// db.php
$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'news_portal';

// أنشئ الاتصال
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// لاحقًا في السكربت الرئيسي سيغلق الاتصال تلقائيًا بنهاية التنفيذ
