<?php
// index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// استدعاء ملف التهيئة (init.php + db.php)
require_once __DIR__ . '/init.php';

// جلب آخر الأخبار المعتمدة
$sql = "
  SELECT n.id, n.title, n.summary, n.image, DATE_FORMAT(n.dateposted, '%Y-%m-%d') AS dp, u.name AS author_name
  FROM news n
  JOIN user u ON n.author_id = u.id
  WHERE n.status = 'Approved'
  ORDER BY n.dateposted DESC
  LIMIT 5
";
$result   = $conn->query($sql);
$newsList = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>الرئيسية | News Portal</title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand text-white" href="index.php">News Portal</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link text-white" href="index.php">الرئيسية</a></li>
          <?php
          $cats = $conn->query("SELECT id,name FROM category ORDER BY name");
          while($cat = $cats->fetch_assoc()):
          ?>
          <li class="nav-item">
            <a class="nav-link text-white" href="category.php?id=<?= $cat['id'] ?>">
              <?= htmlspecialchars($cat['name']) ?>
            </a>
          </li>
          <?php endwhile; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="container mt-4">
    <h1 class="mb-4">📰 آخر الأخبار</h1>
    <div class="row">
      <?php if ($newsList): foreach($newsList as $news): ?>
      <div class="col-md-4 mb-4">
        <div class="card">
          <?php if ($news['image']): ?>
          <img src="uploads/<?= htmlspecialchars($news['image']) ?>"
               class="card-img-top" alt="<?= htmlspecialchars($news['title']) ?>">
          <?php endif; ?>
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($news['title']) ?></h5>
            <p class="card-text"><?= htmlspecialchars(mb_substr($news['summary'],0,100,'UTF-8')) ?>...</p>
            <p class="text-muted small"><?= htmlspecialchars($news['dp']) ?> | <?= htmlspecialchars($news['author_name']) ?></p>
            <a href="details.php?id=<?= $news['id'] ?>" class="btn btn-primary">اقرأ المزيد</a>
          </div>
        </div>
      </div>
      <?php endforeach; else: ?>
      <p>لا توجد أخبار معتمدة حالياً.</p>
      <?php endif; ?>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-light py-5">
    <div class="container">
      <div class="row">
        <div class="col-md-3">
          <h6>عن الموقع</h6>
          <p>منصة إخبارية عربية تغطي أبرز الأحداث والأخبار.</p>
        </div>
        <div class="col-md-3">
          <h6>روابط</h6>
          <ul class="list-unstyled">
            <?php
            $cats->data_seek(0);
            while($cat = $cats->fetch_assoc()):
            ?>
            <li><a href="category.php?id=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
            <?php endwhile; ?>
          </ul>
        </div>
        <div class="col-md-3">
          <h6>عن الموقع</h6>
          <ul class="list-unstyled">
            <li><a href="#">من نحن</a></li>
            <li><a href="#">اعلن لدينا</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h6><a href="#">اتصل بنا</a></h6>
        </div>
      </div>
    </div>
  </footer>
</body>
</html>


<?php
// details.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/init.php';

if (!isset($_GET['id'])) {
    header('Location: index.php'); exit;
}
$id = intval($_GET['id']);

$stmt = $conn->prepare(
  "SELECT n.title,n.body,n.image,DATE_FORMAT(n.dateposted,'%Y-%m-%d') AS dp,
          u.name AS author_name,c.name AS category_name,n.keywords
   FROM news n
   JOIN user u ON n.author_id = u.id
   JOIN category c ON n.category_id = c.id
   WHERE n.id=? AND n.status='Approved'"
);
$stmt->bind_param('i',$id);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();
$stmt->close();
if(!$news){ header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($news['title']) ?> | News Portal</title>
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar: استدعاء نفس navbar من index -->
  <?php include 'navbar.php'; ?>

  <main class="container mt-5">
    <h1><?= htmlspecialchars($news['title']) ?></h1>
    <p class="text-muted small"><?= htmlspecialchars($news['dp']) ?> | <?= htmlspecialchars($news['author_name']) ?> | <?= htmlspecialchars($news['category_name']) ?></p>
    <?php if($news['image']): ?>
      <img src="uploads/<?= htmlspecialchars($news['image']) ?>" class="img-fluid mb-4" alt="">
    <?php endif; ?>
    <div class="mb-4"><?= nl2br(htmlspecialchars($news['body'])) ?></div>
    <?php if($news['keywords']): ?>
    <div class="mb-4">
      <?php foreach (explode(',', $news['keywords']) as $kw): $kw = trim($kw); if (!$kw) continue; ?>
        <span class="badge bg-secondary me-1 mb-1"><?php echo htmlspecialchars($kw); ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </main>

  <!-- Footer: same as index -->
  <?php include 'footer.php'; ?>
</body>
</html>
