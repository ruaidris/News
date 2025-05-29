<?php


mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    
    $conn = new mysqli('127.0.0.1', 'root', '', 'news_portal', 3306);
   
    $conn->set_charset('utf8');

    
} catch (Exception $e) {
    echo " فشل الاتصال: " . $e->getMessage();


$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'news_portal';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);

}
    exit();
}