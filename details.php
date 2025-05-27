<?php
require_once 'init.php';

$id = intval($_GET['id'] ?? 0);

// جيب تفاصيل الخبر
$stmt = $conn->prepare(<<<SQL
  SELECT 
    n.*, 
    c.name AS category_name, 
    u.name AS author_name,
    DATE_FORMAT(n.dateposted,'%d %M %Y') AS d,
    (SELECT COUNT(*) FROM news_likes WHERE news_id = n.id) AS likes
  FROM news n
  JOIN category c ON c.id = n.category_id
  JOIN user u ON u.id = n.author_id
  WHERE n.id = ?
SQL
);
$stmt->bind_param('i', $id);
$stmt->execute();
$art = $stmt->get_result()->fetch_assoc() ?: [];

// “اقرأ أيضاً” (5 مقالات من نفس الفئة)
$rel = $conn->prepare("
  SELECT id, title 
  FROM news 
  WHERE category_id = ? AND id <> ?
  ORDER BY dateposted DESC
  LIMIT 5
");
$rel->bind_param('ii',$art['category_id'],$id);
$rel->execute();
$related = $rel->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($art['title']) ?></title>
  <!-- CSS وروابط -->
</head>
<body>

  <!-- Navbar مثل index.php -->

  <header class="px-4 pt-10">
    <div class="text-sm text-gray-600 mb-1 font-semibold">
      <?= htmlspecialchars($art['category_name']) ?> - <?= htmlspecialchars($art['author_name']) ?>
    </div>
    <h1 class="text-3xl font-extrabold mb-3"><?= htmlspecialchars($art['title']) ?></h1>
    <div class="flex items-center gap-4 text-gray-600 text-sm mb-6">
      <i class="far fa-calendar-alt"></i> <span><?= $art['d'] ?></span>
      <i class="fas fa-heart text-red-500"></i> <span><?= $art['likes'] ?></span>
    </div>
  </header>

  <main id="main" class="px-4 flex gap-8">
    <aside>
      <!-- “المزيد من …” و الإعلانات لو تبغى -->
    </aside>
    <article id="art">
      <figure>
        <img src="uploads/<?= $art['image'] ?>" alt="" />
        <figcaption class="text-sm text-gray-500 mt-2"><?= htmlspecialchars($art['caption'] ?? '') ?></figcaption>
      </figure>
      <div class="prose mt-6">
        <?= nl2br(htmlspecialchars($art['body'])) ?>
      </div>

      <h3 class="font-semibold mt-12 mb-2 underline">إقرأ أيضاً</h3>
      <ul>
        <?php foreach($related as $r): ?>
          <li style="font-size:1.125rem;">
            <a href="details.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['title']) ?></a>
          </li>
        <?php endforeach ?>
      </ul>
    </article>
  </main>

  <!-- Footer مثل index.php -->

</body>
</html>
