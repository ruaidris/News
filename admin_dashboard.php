<?php
// admin_dashboard.php

// 1) عرض الأخطاء أثناء التطوير
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2) فتح السيشن والاتصال
require_once __DIR__ . '/init.php';

// 3) تأمين الصفحة للمسؤول فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// 4) معالجة الحذف (مع حماية عدم حذف المسؤول نفسه)
if (isset($_GET['delete_id'])) {
    $delId = intval($_GET['delete_id']);
    if ($delId !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        $stmt->bind_param("i", $delId);
        $stmt->execute();
    }
    header('Location: admin_dashboard.php');
    exit;
}

// 5) جلب كل المستخدمين
$result    = $conn->query("SELECT id, name, email, role FROM user ORDER BY id ASC");
$usersList = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>لوحة المسؤول | News Portal</title>
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
    .btn-outline-danger {
      border: 1px solid #dc3545;
      color: #dc3545;
    }
    .btn-outline-danger:hover {
      background: #dc3545;
      color: #fff;
    }
    .col-email {
      width: 250px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .table thead {
      background: #f8f9fa;
    }
  </style>
</head>
<body>
  <div class="panel">
    <h2>🛠️ لوحة المسؤول</h2>

    <div class="d-flex justify-content-between mb-4">
      <a href="register.php" class="btn btn-light-custom rounded-pill px-4">
        <i class="bi bi-person-plus"></i> إضافة مستخدم
      </a>
      <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4">
        <i class="bi bi-box-arrow-left"></i> تسجيل خروج
      </a>
    </div>

    <?php if (empty($usersList)): ?>
      <div class="alert alert-info text-center">لا يوجد مستخدمون.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>المعرف</th>
              <th>الاسم</th>
              <th class="col-email">البريد الإلكتروني</th>
              <th>الدور</th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usersList as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u['id']) ?></td>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td class="col-email" title="<?= htmlspecialchars($u['email']) ?>">
                  <?= htmlspecialchars($u['email']) ?>
                </td>
                <td><?= ucfirst(htmlspecialchars($u['role'])) ?></td>
                <td>
                  <!-- رابط تعديل (يمكن توجيه إلى صفحة register.php?edit_id=ID لاحقًا) -->
                  <a href="register.php?edit_id=<?= $u['id'] ?>"
                     class="btn btn-sm btn-light-custom rounded-pill me-2">
                    <i class="bi bi-pencil"></i> تعديل
                  </a>
                  <!-- زر الحذف (يحظر حذف نفسه) -->
                  <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                  <a href="?delete_id=<?= $u['id'] ?>"
                     class="btn btn-sm btn-outline-danger rounded-pill"
                     onclick="return confirm('حذف المستخدم نهائيًا؟');">
                    <i class="bi bi-trash"></i> حذف
                  </a>
                  <?php endif; ?>
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
