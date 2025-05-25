<?php
// add_news.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/init.php';

// تأمين المؤلف فقط
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'author') {
    header('Location: login.php'); exit;
}

$edit_mode   = false;
$id          = null;
$title       = '';
$summary     = '';
$body        = '';
$category_id = '';
$imageName   = '';
$keywords    = '';
$error       = '';

// في حالة التعديل
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $id        = intval($_GET['edit_id']);
    $stmt      = $conn->prepare("SELECT title, summary, body, image, category_id, keywords FROM news WHERE id = ? AND author_id = ?");
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
    $news = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($news) {
        $title       = $news['title'];
        $summary     = $news['summary'];
        $body        = $news['body'];
        $imageName   = $news['image'];
        $category_id = $news['category_id'];
        $keywords    = $news['keywords'];
    } else {
        header('Location: author_dashboard.php'); exit;
    }
}

// معالجة الفورم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $summary     = trim($_POST['summary']);
    $body        = trim($_POST['body']);
    $category_id = intval($_POST['category_id']);
    $keywords    = trim($_POST['keywords'] ?? '');
    $date        = date('Y-m-d');
    $author_id   = $_SESSION['user_id'];

    // رفع الصورة
    if (!empty($_FILES['image']['name'])) {
        $imageName  = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . "/uploads/" . $imageName);
    }

    if ($edit_mode) {
        $stmt = $conn->prepare(
            "UPDATE news SET title=?, summary=?, body=?, image=?, category_id=?, keywords=?, status='pending' WHERE id=? AND author_id=?"
        );
        $stmt->bind_param("ssssiisi", $title, $summary, $body, $imageName, $category_id, $keywords, $id, $author_id);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO news (title, summary, body, image, dateposted, category_id, author_id, status, keywords) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)"
        );
        $stmt->bind_param("ssssisisi", $title, $summary, $body, $imageName, $date, $category_id, $author_id, $keywords);
    }
    if ($stmt->execute()) {
        header('Location: author_dashboard.php'); exit;
    } else {
        $error = 'حدث خطأ: ' . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $edit_mode ? 'تعديل خبر' : 'إضافة خبر' ?> | News Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
  <style>
    body { background: #f8f9fa; font-family: Arial, sans-serif; padding: 30px; }
    .container { max-width: 700px; margin: auto; background: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .btn-custom { background: #0069d9; color: #fff; border: none; }
    .btn-custom:hover { background: #0056b3; }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="mb-4 text-center"><?= $edit_mode ? '✏️ تعديل الخبر' : '📰 إضافة خبر جديد' ?></h2>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">العنوان</label>
        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($title) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">موجز</label>
        <textarea name="summary" class="form-control" rows="2" required><?= htmlspecialchars($summary) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">المحتوى</label>
        <textarea name="body" class="form-control" rows="5" required><?= htmlspecialchars($body) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">القسم</label>
        <select name="category_id" class="form-select" required>
          <option value="">-- اختر تصنيفًا --</option>
          <?php $cats = $conn->query("SELECT id, name FROM category"); while($cat=$cats->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id']==$category_id?'selected':'' ?>><?= htmlspecialchars($cat['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">الكلمات المفتاحية</label>
        <input type="text" name="keywords" class="form-control" placeholder="مثال: سياسة, اقتصاد" value="<?= htmlspecialchars($keywords) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">الصورة</label>
        <input type="file" name="image" class="form-control">
        <?php if ($edit_mode && $imageName): ?>
          <img src="uploads/<?= htmlspecialchars($imageName) ?>" alt="" class="mt-2" style="max-width:200px;">
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-custom w-100"><?= $edit_mode ? 'تحديث الخبر' : 'إضافة الخبر' ?></button>
    </form>
  </div>
</body>
</html>
