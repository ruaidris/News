<?php
require_once 'init.php';

$cat_id = intval($_GET['id'] ?? 0);

// اسم الفئة للعنوان
$t = $conn->prepare("SELECT name FROM category WHERE id = ?");
$t->bind_param('i', $cat_id);
$t->execute();
$catName = $t->get_result()->fetch_assoc()['name'] ?? 'غير معروف';

// جيب كل الأخبار المعتمدة في الفئة
$stmt = $conn->prepare(<<<SQL
  SELECT id, title, summary, image, DATE_FORMAT(dateposted,'%d %M %Y') AS d
  FROM news
  WHERE category_id = ? AND status = 'Approved'
  ORDER BY dateposted DESC
SQL
);
$stmt->bind_param('i', $cat_id);
$stmt->execute();
$newsList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>قسم <?= htmlspecialchars($catName) ?></title>
  <!-- CSS وروابط مثل index.php -->
</head>
<body>

  <!-- Navbar زي index.php -->

  <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">
    <h2 class="text-xl font-extrabold border-b-4 border-blue-800 inline-block pb-1 mb-10">
      <?= htmlspecialchars($catName) ?>
    </h2>
    <div class="flex flex-col lg:flex-row gap-6">
      <div class="flex flex-col gap-6 w-full">
        <?php foreach($newsList as $n): ?>
          <article class="flex flex-col gap-2">
            <a href="details.php?id=<?= $n['id'] ?>">
              <img src="uploads/<?= $n['image'] ?>" class="w-full object-cover" height="140" width="350">
            </a>
            <p class="text-sm text-gray-500"><?= htmlspecialchars($catName) ?></p>
            <h3 class="font-extrabold text-lg leading-tight"><?= htmlspecialchars($n['title']) ?></h3>
            <p class="text-sm text-gray-700 leading-tight"><?= htmlspecialchars($n['summary']) ?></p>
          </article>
        <?php endforeach ?>
      </div>
    </div>
  </main>

  <!-- Footer مثل index.php -->

</body>
</html>
