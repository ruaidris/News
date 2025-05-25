<?php
// category.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/init.php';

// تأمين وصول الفئة
if (!isset($_GET['id'])) {
    header('Location: index.php'); exit;
}
$cat_id = intval($_GET['id']);

// جلب بيانات الفئة
$stmt = $conn->prepare("SELECT name, description FROM category WHERE id = ?");
$stmt->bind_param('i', $cat_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$category) { header('Location: index.php'); exit; }

// جلب الأخبار التابعة للفئة المعتمدة
$stmt = $conn->prepare(
  "SELECT n.id, n.title, n.summary, n.image, DATE_FORMAT(n.dateposted, '%Y-%m-%d') AS dp
   FROM news n
   WHERE n.category_id = ? AND n.status = 'Approved'
   ORDER BY n.dateposted DESC"
);
$stmt->bind_param('i', $cat_id);
$stmt->execute();
$newsList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="logo.png" alt="Profile" class="logo">
                <div class="nav-links">
                    <a href="index.php">الرئيسية</a>
                    <?php
                    $categories = $conn->query("SELECT id, name FROM category ORDER BY name");
                    while ($cat = $categories->fetch_assoc()): ?>
                        <a href="category.php?id=<?php echo $cat['id']; ?>"> <?php echo htmlspecialchars($cat['name']); ?></a>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="search-box">
                <input type="text" placeholder="ادخل كلمة للبحث">
                <i class="fas fa-search text-secondary"></i>
            </div>
        </div>
    </nav>

    <main class="container mt-4">
        <h1 class="mb-4"><?php echo htmlspecialchars($category['name']); ?></h1>
        <div class="row">
            <?php if ($newsList): ?>
                <?php foreach ($newsList as $news): ?>
                    <div class="col-md-4">
                        <div class="card bg-dark text-white">
                            <img src="uploads/<?php echo htmlspecialchars($news['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($news['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"> <?php echo htmlspecialchars($news['title']); ?></h5>
                                <p class="card-text"> <?php echo mb_substr(strip_tags($news['summary']), 0, 100, 'UTF-8'); ?>...</p>
                                <p class="card-text text-muted small"> <?php echo htmlspecialchars($news['dp']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>لا توجد أخبار في هذه الفئة.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3 text-start">
                    <img src="logo.png" alt="Profile" class="end-logo mb-2">
                    <p class="footer-text"><?= htmlspecialchars($category['description']) ?></p>
                </div>
                <div class="col-md-3">
                    <h6>روابط</h6>
                    <ul class="list-unstyled">
                        <?php
                        $categories->data_seek(0);
                        while ($cat = $categories->fetch_assoc()): ?>
                            <li><a href="category.php?id=<?php echo $cat['id']; ?>"> <?php echo htmlspecialchars($cat['name']); ?></a></li>
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
