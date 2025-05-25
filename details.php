<?php
// details.php

// عرض الأخطاء للتصحيح
error_reporting(E_ALL);
ini_set('display_errors', 1);

// بدء الجلسة واستدعاء التهيئة (init.php يضمّ session_start() وdb.php)
require_once __DIR__ . '/init.php';

// 1) تأمين وصول المعرف عبر GET
if (!isset($_GET['id']) || !($id = intval($_GET['id']))) {
    header('Location: index.php');
    exit;
}

// 2) جلب بيانات الخبر (إن وجد ومعتمد)
$stmt = $conn->prepare("
    SELECT
      n.id,
      n.title,
      n.body,
      n.image,
      DATE_FORMAT(n.dateposted, '%Y-%m-%d') AS dp,
      u.name       AS author_name,
      c.name       AS category_name,
      n.likes,
      n.keywords
    FROM news n
    JOIN user     u ON n.author_id   = u.id
    JOIN category c ON n.category_id = c.id
    WHERE n.id = ? AND n.status = 'Approved'
");
$stmt->bind_param('i', $id);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();
$stmt->close();

// إن لم يُجد الخبر، عد إلى الرئيسية
if (!$news) {
    header('Location: index.php');
    exit;
}

// 3) جلب التعليقات المرتبطة بالخبر
$stmt = $conn->prepare("
    SELECT c.content, c.dateposted, u.name
    FROM comments c
    JOIN user u ON c.user_id = u.id
    WHERE c.news_id = ?
    ORDER BY c.dateposted DESC
");
$stmt->bind_param('i', $id);
$stmt->execute();
$comments = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($news['title']) ?> | موقع الأخبار</title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
  <style>
    body { background: #f8f9fa; padding: 30px; font-family: Arial, sans-serif; }
    .card-main {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .badge-keyword {
      background: #e0f0ff;
      color: #007bff;
      margin: 0 4px 4px 0;
    }
    .btn-like {
      background: none;
      border: none;
      color: #dc3545;
      font-size: 1.2em;
    }
  </style>
</head>
<body>

  <!-- بطاقة عرض الخبر -->
  <div class="card-main mb-4">
    <h1><?= htmlspecialchars($news['title']) ?></h1>
    <p class="text-muted">
      بواسطة <?= htmlspecialchars($news['author_name']) ?> |
      <?= htmlspecialchars($news['dp']) ?> |
      تصنيف: <?= htmlspecialchars($news['category_name']) ?>
    </p>

    <?php if ($news['image']): ?>
      <img src="uploads/<?= htmlspecialchars($news['image']) ?>"
           class="img-fluid rounded mb-4" alt="">
    <?php endif; ?>

    <div class="mb-4"><?= nl2br(htmlspecialchars($news['body'])) ?></div>

    <!-- الكلمات المفتاحية -->
    <?php if (!empty($news['keywords'])): ?>
      <div class="mb-4">
        <?php foreach (explode(',', $news['keywords']) as $kw):
          $kw = trim($kw);
          if (!$kw) continue;
        ?>
          <span class="badge badge-keyword"><?= htmlspecialchars($kw) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- زر الإعجاب -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
      <button id="like-btn" class="btn-like">
        <i class="fa-solid fa-heart"></i>
        <span id="like-count"><?= $news['likes'] ?></span>
      </button>
    <?php else: ?>
      <p class="text-muted">سجّل دخولك لتستطيع الإعجاب</p>
    <?php endif; ?>
  </div>

  <!-- قسم التعليقات -->
  <div class="card-main">
    <h4>التعليقات</h4>

    <!-- نموذج إضافة تعليق -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
      <form method="post" action="comment_handler.php" class="mb-4">
        <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
        <textarea name="content" class="form-control mb-2"
                  rows="3" placeholder="اكتب تعليقك هنا..." required></textarea>
        <button class="btn btn-primary btn-sm">أضف تعليق</button>
      </form>
    <?php else: ?>
      <p><a href="login.php">سجّل دخولك</a> للتعليق</p>
    <?php endif; ?>

    <!-- عرض التعليقات الموجودة -->
    <?php while ($c = $comments->fetch_assoc()): ?>
      <div class="border rounded p-3 mb-2 bg-white">
        <p class="mb-1">
          <strong><?= htmlspecialchars($c['name']) ?></strong>
          <span class="text-muted small"><?= $c['dateposted'] ?></span>
        </p>
        <p><?= nl2br(htmlspecialchars($c['content'])) ?></p>
      </div>
    <?php endwhile; ?>
  </div>

  <!-- سكربت AJAX للإعجاب -->
  <script>
    document.getElementById('like-btn')?.addEventListener('click', () => {
      fetch('like_handler.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ news_id: <?= $news['id'] ?> })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          document.getElementById('like-count').textContent = data.likes;
        }
      });
    });
  </script>

</body>
</html>
