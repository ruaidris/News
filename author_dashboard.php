<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/init.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'author') {
    header('Location: login.php');
    exit;
}


if (isset($_GET['delete_id'])) {
    $id   = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ? AND author_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    header('Location: author_dashboard.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT id, title, DATE_FORMAT(dateposted, '%Y-%m-%d') AS dp, status
    FROM news
    WHERE author_id = ?
    ORDER BY dateposted DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$newsList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>لوحة المؤلف | News Portal</title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css">
  <style>
    body {
      background: #f0f2f5;
      font-family: 'Segoe UI', Tahoma, sans-serif;
      padding: 40px;
    }
    .panel {
      max-width: 1500px;   
      margin: auto;
      background: #ffffff;
      border-radius: 10px;
      padding: 40px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #333;
    }
    .btn-light-custom {
      background: #ffffff;
      border: 1px solid #ced4da;
      color: #333;
      transition: background 0.2s, box-shadow 0.2s;
    }
    .btn-light-custom:hover {
      background: #e9ecef;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .btn-outline-custom {
      border: 1px solid #ff6b6b;
      color: #ff6b6b;
    }
    .btn-outline-custom:hover {
      background: #ff6b6b;
      color: #fff;
    }
    .table thead {
      background: #f8f9fa;
    }
    .table td, .table th {
      vertical-align: middle;
    }
  </style>
</head>
<body>
  <div class="panel">
    <h2>📋 لوحة المؤلف</h2>

    <div class="d-flex justify-content-between mb-4">
      <a href="add_news.php" class="btn btn-light-custom rounded-pill px-4">
        <i class="bi bi-plus-lg"></i> إضافة خبر
      </a>
      <a href="logout.php" class="btn btn-outline-custom rounded-pill px-4">
        <i class="bi bi-box-arrow-left"></i> تسجيل خروج
      </a>
    </div>

    <?php if (empty($newsList)): ?>
      <div class="alert alert-info text-center">لا توجد أخبار منشورة بعد.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered bg-white">
          <thead>
            <tr>
              <th>العنوان</th>
              <th>تاريخ النشر</th>
              <th>الحالة</th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($newsList as $news): ?>
              <tr>
                <td>
                  <a href="add_news.php?edit_id=<?= $news['id'] ?>"
                     class="text-decoration-none text-dark">
                    <?= htmlspecialchars($news['title']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($news['dp']) ?></td>
                <td><?= ucfirst(htmlspecialchars($news['status'])) ?></td>
                <td>
                  <a href="add_news.php?edit_id=<?= $news['id'] ?>"
                     class="btn btn-light-custom btn-sm rounded-pill me-2">
                    <i class="bi bi-pencil"></i> تعديل
                  </a>
                  <a href="?delete_id=<?= $news['id'] ?>"
                     class="btn btn-outline-custom btn-sm rounded-pill"
                     onclick="return confirm('متأكد أنك تريد حذف هذا الخبر؟');">
                    <i class="bi bi-trash"></i> حذف
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
