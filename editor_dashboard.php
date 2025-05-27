<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/init.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor') {
    header('Location: login.php');
    exit;
}


if (isset($_GET['approve_id']) || isset($_GET['deny_id']) || isset($_GET['delete_id'])) {
    if (isset($_GET['approve_id'])) {
        $nid = intval($_GET['approve_id']);
        $stmt = $conn->prepare("UPDATE news SET status = 'Approved' WHERE id = ?");
        $stmt->bind_param("i", $nid);
    } elseif (isset($_GET['deny_id'])) {
        $nid = intval($_GET['deny_id']);
        $stmt = $conn->prepare("UPDATE news SET status = 'Denied' WHERE id = ?");
        $stmt->bind_param("i", $nid);
    } else {
        $nid = intval($_GET['delete_id']);
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param("i", $nid);
    }
    $stmt->execute();
    header('Location: editor_dashboard.php');
    exit;
}


$sql = "
  SELECT n.id, n.title,
         DATE_FORMAT(n.dateposted, '%Y-%m-%d') AS dp,
         n.status, u.name AS author_name
  FROM news n
  JOIN user u ON n.author_id = u.id
  ORDER BY n.dateposted DESC
";
$result   = $conn->query($sql);
$newsList = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>لوحة المحرر | News Portal</title>
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
      max-width: 1000px;
      margin: auto;
      background: #fff;
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
      background: #fff;
      border: 1px solid #ced4da;
      color: #333;
      transition: background 0.2s, box-shadow 0.2s;
    }
    .btn-light-custom:hover {
      background: #e9ecef;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .btn-outline-approve {
      border: 1px solid #198754;
      color: #198754;
    }
    .btn-outline-approve:hover {
      background: #198754;
      color: #fff;
    }
    .btn-outline-deny {
      border: 1px solid #dc3545;
      color: #dc3545;
    }
    .btn-outline-deny:hover {
      background: #dc3545;
      color: #fff;
    }
    .col-date {
      white-space: nowrap;
      width: 120px;
      text-align: center;
    }
    .table thead {
      background: #f8f9fa;
    }
  </style>
</head>
<body>
  <div class="panel">
    <h2>📝 لوحة المحرر</h2>

    <div class="d-flex justify-content-end mb-4">
      <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4">
        <i class="bi bi-box-arrow-left"></i> تسجيل خروج
      </a>
    </div>

    <?php if (empty($newsList)): ?>
      <div class="alert alert-info text-center">لا توجد أخبار.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>العنوان</th>
              <th class="col-date">تاريخ النشر</th>
              <th>المؤلف</th>
              <th>الحالة</th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($newsList as $n): ?>
              <tr>
                <td><?= htmlspecialchars($n['title']) ?></td>
                <td class="col-date"><?= htmlspecialchars($n['dp']) ?></td>
                <td><?= htmlspecialchars($n['author_name']) ?></td>
                <td><?= ucfirst(htmlspecialchars($n['status'])) ?></td>
                <td>
                  <a href="?approve_id=<?= $n['id'] ?>"
                     class="btn btn-sm btn-outline-approve rounded-pill me-1"
                     onclick="return confirm('موافقة على النشر؟');">
                    <i class="bi bi-check-circle"></i>
                  </a>
                  <a href="?deny_id=<?= $n['id'] ?>"
                     class="btn btn-sm btn-outline-deny rounded-pill me-1"
                     onclick="return confirm('رفض النشر؟');">
                    <i class="bi bi-x-circle"></i>
                  </a>
                  <a href="?delete_id=<?= $n['id'] ?>"
                     class="btn btn-sm btn-outline-danger rounded-pill"
                     onclick="return confirm('حذف الخبر نهائيًا؟');">
                    <i class="bi bi-trash"></i>
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
