<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/init.php';
$catId   = isset($_GET['cat_id']) ? intval($_GET['cat_id']) : null;
$catName = null;

if ($catId) {
    $stmt = $conn->prepare("SELECT name FROM category WHERE id = ?");
    $stmt->bind_param('i', $catId);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$res) die('الفئة غير موجودة');
    $catName = $res['name'];
} elseif (isset($_GET['cat']) && trim($_GET['cat']) !== '') {
    $catName = trim($_GET['cat']);
    $stmt = $conn->prepare("SELECT id FROM category WHERE name = ?");
    $stmt->bind_param('s', $catName);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$res) die('الفئة غير موجودة');
    $catId = $res['id'];
} else {
    die('فئة غير محددة');
}

$stmt = $conn->prepare(
    "SELECT id, title, summary, image, dateposted\n     FROM news\n     WHERE status = 'approved' AND category_id = ?\n     ORDER BY dateposted DESC"
);
$stmt->bind_param('i', $catId);
$stmt->execute();
$articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$ads = $conn->query(
    "SELECT image, link FROM ads WHERE status = 'active' ORDER BY id LIMIT 2"
)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($catName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .btn-custom {
            background: #0e2147;
            color: #fff;
            transition: background 0.3s;
        }
        .btn-custom:hover {
            background: #0056b3;
        }
        
        .navbar {
            background: #0e2147;
            padding: 1rem 0;
        }
        
        .search-box {
            background: #fff;
            border-radius: 9999px;
            padding: .5rem 1rem;
            flex: 1 1 auto;
        }
        
        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            text-align: right;
        }
        
        .weather {
            color: #fff;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        
        .nav-links a {
            color: #fff;
            margin: 0 .75rem;
            text-decoration: none;
        }
        
        .category-title {
            font-weight: 800;
            border-bottom: 4px solid #1e40af;
            display: inline-block;
            padding-bottom: 0.25rem;
            margin-bottom: 2.5rem;
        }
        
        .article-card {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .article-image {
            width: 100%;
            height: 10rem;
            object-fit: cover;
            border-radius: 0.375rem;
        }
        
        .article-meta {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .article-title {
            font-weight: 800;
            font-size: 1.125rem;
            line-height: 1.375;
            margin: 0.5rem 0;
        }
        
        .article-summary {
            font-size: 0.875rem;
            color: #374151;
            line-height: 1.375;
        }
        
        .article-row {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .article-row-image {
            width: 10rem;
            height: 6rem;
            object-fit: cover;
            border-radius: 0.375rem;
            flex-shrink: 0;
        }
        
        /* إعدادات الإعلانات */
        .ad-container {
            max-width: 280px;
            margin: 1rem auto;
        }
        
        .ad-container img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .ad-container:hover img {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .ad-label {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .ad-container {
                max-width: 100%;
            }
            
            .ad-container img {
                height: 120px;
            }
        }
    </style>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<main class="container mt-4">
  <h2 class="category-title">
    <?= htmlspecialchars($catName) ?>
  </h2>

  <div class="row">
    <div class="col-lg-4">
      <?php foreach(array_slice($articles,0,3) as $art): ?>
      <article class="article-card">
        <a href="details.php?id=<?= $art['id'] ?>">
            <img src="<?= htmlspecialchars($art['image']) ?>" alt="<?= htmlspecialchars($art['title']) ?>" class="article-image"/>
        </a>
        <p class="article-meta">
            <?= htmlspecialchars($catName) ?> - <?= date('Y-m-d', strtotime($art['dateposted'])) ?>
        </p>
        <a href="details.php?id=<?= $art['id'] ?>" class="text-decoration-none">
            <h3 class="article-title">
                <?= htmlspecialchars($art['title']) ?>
            </h3>
        </a>
        <p class="article-summary">
            <?= nl2br(htmlspecialchars($art['summary'])) ?>
        </p>
      </article>
      <?php endforeach; ?>      <!-- إعلان 1 -->
      <?php if(isset($ads[0])): ?>
      <div class="ad-container">
        <a href="<?= htmlspecialchars($ads[0]['link']) ?>" target="_blank">
          <img src="<?= htmlspecialchars($ads[0]['image']) ?>" alt="إعلان" />
        </a>
        <div class="ad-label">إعلان</div>
      </div>
      <?php endif; ?>
    </div>
    <div class="col-lg-8">
      <?php foreach(array_slice($articles,3) as $art): ?>
      <article class="article-row">
        <a href="details.php?id=<?= $art['id'] ?>">
            <img src="<?= htmlspecialchars($art['image']) ?>" alt="<?= htmlspecialchars($art['title']) ?>" class="article-row-image"/>
        </a>
        <div>
          <p class="article-meta">
              <?= htmlspecialchars($catName) ?>
          </p>
          <a href="details.php?id=<?= $art['id'] ?>" class="text-decoration-none">
              <h4 class="article-title">
                  <?= htmlspecialchars($art['title']) ?>
              </h4>
          </a>
        </div>
      </article>
      <?php endforeach; ?>      <!-- المفروض هو تاني اعلان -->
      <?php if(isset($ads[1])): ?>
      <div class="ad-container">
        <a href="<?= htmlspecialchars($ads[1]['link']) ?>" target="_blank">
          <img src="<?= htmlspecialchars($ads[1]['image']) ?>" alt="إعلان" />
        </a>
        <div class="ad-label">إعلان</div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>
<footer class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 text-start">
                <img src="logo.png" alt="Profile" class="end-logo mb-2">
                <p class="footer-text">تغطيّة إخبارية شاملة ومتعدّدة الوسائط للأحداث العربيّة والعالمية، ويتيح الوصول إلى شبكة منوعة من البرامج السياسة والاجتماعية.</p>            </div>            <div class="col-md-3">
                <h6>روابط</h6>
                <ul class="list-unstyled">
                    <?php
                    $footerCategories = $conn->query("SELECT id, name FROM category LIMIT 5");
                    while($footerCat = $footerCategories->fetch_assoc()): ?>
                        <li><a href="category.php?cat=<?= urlencode($footerCat['name']) ?>"><?= htmlspecialchars($footerCat['name']) ?></a></li>
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
        </div>    </div>
</footer>
<script>
// Weather functionality
async function loadWeather() {
    try {
        const response = await fetch('weather_api.php?city=Hebron');
        
        if (!response.ok) {
            throw new Error('Weather API request failed');
        }
        
        const data = await response.json();
        
        if (data.success) {
            updateWeatherDisplay(data.temperature, data.icon, data.city);
            
            // If it's fallback data, add a subtle indicator
            if (data.fallback) {
                console.log('Using fallback weather data. Configure WEATHER_API_KEY in config.php for live weather.');
            }
        } else {
            throw new Error('Weather data not available');
        }
    } catch (error) {
        console.log('Weather API error:', error);
        // Use fallback weather display
        updateWeatherDisplay('21°C', 'fa-cloud', 'الخليل');
    }
}

function updateWeatherDisplay(temperature, iconClass, cityName) {
    const tempElement = document.getElementById('temperature');
    const iconElement = document.getElementById('weather-icon');
    
    if (tempElement) tempElement.textContent = temperature;
    if (iconElement) iconElement.className = `fas ${iconClass} ms-2`;
    
    // Update city name if needed
    const weatherDiv = document.querySelector('.weather');
    if (weatherDiv) {
        const citySpan = weatherDiv.querySelector('.ms-1');
        if (citySpan) {
            citySpan.textContent = cityName;
        }
    }
}

// Initialize weather when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadWeather();
});
</script>

</body> 
</html>