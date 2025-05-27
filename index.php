<?php
require_once 'init.php'; // اتصل بالداتا بيز وجلسة المستخدم

// جيب آخر 8 أخبار مع اسم الفئة
$stmt = $conn->prepare(<<<SQL
  SELECT 
    n.id, n.title, n.summary, n.image,
    DATE_FORMAT(n.dateposted, '%d %M %Y') AS datepost,
    c.name AS category_name
  FROM news n
  JOIN category c ON c.id = n.category_id
  WHERE n.status = 'Approved'
  ORDER BY n.dateposted DESC
  LIMIT 8
SQL
);
$stmt->execute();
$newsList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// جيب كل الفئات للنافبار
$cats = $conn->query("SELECT id, name FROM category");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>الرئيسية</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <img src="logo.png" class="logo" alt="logo">
        <div class="nav-links">
          <a href="index.php">الرئيسية</a>
          <?php while($cat = $cats->fetch_assoc()): ?>
            <a href="category.php?id=<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
          <?php endwhile ?>
        </div>
      </div>
      <div class="search-box">
        <input type="text" placeholder="ادخل كلمة للبحث">
        <i class="fas fa-search text-secondary"></i>
      </div>
      <div class="d-flex align-items-center">
        <div class="weather">
          <span>21°C</span><i class="fas fa-cloud ms-2"></i><span class="ms-1">الخليل</span>
        </div>
      </div>
    </div>
  </nav>

  <!-- Main content -->
  <main class="container mt-4">

    <!-- أول خبر كبير -->
    <?php if(isset($newsList[0])): $big = $newsList[0]; ?>
      <section class="row mb-4">
        <div class="col-md-12">
          <div class="card bg-dark text-white">
            <a href="details.php?id=<?= $big['id'] ?>">
              <img src="uploads/<?= $big['image'] ?>" class="card-img-top" alt="">
            </a>
            <div class="card-body">
              <h6 class="card-title text-secondary"><?= htmlspecialchars($big['category_name']) ?></h6>
              <h5 class="card-text"><?= htmlspecialchars($big['title']) ?></h5>
              <p class="card-text"><?= htmlspecialchars($big['summary']) ?></p>
            </div>
          </div>
        </div>
      </section>
    <?php endif ?>


    <!-- الأخبار الصغيرة -->
    <section class="row">
      <?php for($i=1; $i < count($newsList); $i++): 
        $n = $newsList[$i];
        // نكبس 3 أعمدة في كل سطر
        $col = ($i <= 3) ? 'col-md-3' : 'col-md-3'; 
      ?>
        <div class="<?= $col ?> mb-4">
          <div class="card border-0">
            <a href="details.php?id=<?= $n['id'] ?>">
              <img src="uploads/<?= $n['image'] ?>" class="card-img-top" alt="">
            </a>
            <div class="card-body">
              <h6 class="card-title text-secondary"><?= htmlspecialchars($n['category_name']) ?></h6>
              <p class="card-text"><?= htmlspecialchars($n['title']) ?></p>
            </div>
          </div>
        </div>
      <?php endfor ?>
    </section>

    <!-- مثال على الأكثر قراءة -->
    <aside class="col-12 col-md-3 mb-4">
      <div class="section-header">
        <h2 class="section-title">الأكثر قراءة</h2>
      </div>
      <ol class="list-unstyled">
        <?php
          // جيب 5 أخبار الأكثر قراءة (مثلاً بالـ likes)
          $mr = $conn->query("
            SELECT id, title 
            FROM news
            ORDER BY likes DESC
            LIMIT 5
          ");
          $rank = 1;
          while($r = $mr->fetch_assoc()):
        ?>
          <li class="most-read-item mb-3">
            <span class="most-read-number"><?= $rank++ ?></span>
            <a href="details.php?id=<?= $r['id'] ?>" class="most-read-link"><?= htmlspecialchars($r['title']) ?></a>
          </li>
        <?php endwhile ?>
      </ol>
    </aside>

  </main>

  <footer class="bg-light py-5">
    <div class="container">
      <!-- نفس الفوتر الثابت -->
    </div>
  </footer>

</body>
</html>