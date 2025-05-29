<?php
require_once __DIR__ . '/init.php';
$categories = $conn->query("SELECT id, name FROM category")->fetch_all(MYSQLI_ASSOC);
$latestNews = $conn->query("SELECT n.id, n.title, n.body, n.image, c.name AS category FROM news n JOIN category c ON n.category_id = c.id WHERE n.status = 'approved' ORDER BY n.dateposted DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);


$newsByCategory = [];
foreach ($categories as $cat) {
    $stmt = $conn->prepare("SELECT id, title, body, image FROM news WHERE status = 'approved' AND category_id = ? ORDER BY dateposted DESC LIMIT 5");
    $stmt->bind_param("i", $cat['id']);
    $stmt->execute();
    $newsByCategory[$cat['name']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}


$mostRead = $conn->query("SELECT id, title, views FROM news WHERE status = 'approved' ORDER BY views DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$mostCommented = $conn->query("SELECT n.id, n.title, COUNT(c.id) AS comment_count FROM news n JOIN comments c ON n.id = c.news_id WHERE n.status = 'approved' GROUP BY n.id ORDER BY comment_count DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الصفحة الرئيسية</title>
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

        .card {
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .news-img {
            height: 150px;
            object-fit: cover;
        }

        .big-news-img {
            height: 200px;
            object-fit: cover;
        }

        .row.g-3 > * {
            margin-bottom: 1rem;
        }

        .most-read-number {
            font-weight: 700;
            font-size: 24px;
            color: #d1d5db;
            width: 32px;
            flex-shrink: 0;
        }        .most-read-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            direction: rtl;
            padding: 8px;
            border-radius: 6px;
            transition: background-color 0.2s ease;
        }

        .most-read-item:hover {
            background-color: #f8f9fa;
        }

        .most-read-content {
            flex: 1;
        }

        .most-read-link {
            font-size: 14px;
            line-height: 20px;
            text-decoration: none;
            color: #1f2937;
            font-weight: 500;
        }

        .most-read-number {
            font-weight: 700;
            font-size: 24px;
            color: #d1d5db;
            width: 32px;
            flex-shrink: 0;
            text-align: center;
        }

        .most-read-stats {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .most-read-link:hover {
            text-decoration: underline;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #bbbbbb;
            padding-bottom: 8px;
            direction: rtl;
        }

        .section-title {
            font-weight: 600;
            font-size: 16px;
            margin: 0;
            padding-bottom: 6px;
            border-bottom: 3px solid #0a1f95b6;
            display: inline-block;
            line-height: 1.2;
        }

        .more-link {
            font-weight: 600;
            font-size: 14px;
            color: #0c2bc6;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .more-link:hover {
            text-decoration: underline;
        }

        .news-small-img {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .news-large-img {
            width: 100%;
            max-width: 300px;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>




<?php include 'includes/navbar.php'; ?>
</nav>

<main class="container mt-4">
    
    <section class="row">
        <?php if (count($latestNews) > 0): ?>
            <div class="col-md-4">
                <div class="card bg-dark text-white">
                    <a href="details.php?id=<?= $latestNews[0]['id'] ?>">
                        <img src="<?= htmlspecialchars($latestNews[0]['image']) ?>" class="card-img-top" alt="news01">
                    </a>
                    <div class="card-body">
                        <h6 class="card-title text-secondary">
                            <a href="details.php?id=<?= $latestNews[0]['id'] ?>" class="text-white text-decoration-none">
                                <?= htmlspecialchars($latestNews[0]['category']) ?>
                            </a>
                        </h6>
                        <p class="card-text fw-bold">
                            <a href="details.php?id=<?= $latestNews[0]['id'] ?>" class="text-white text-decoration-none">
                                <?= htmlspecialchars($latestNews[0]['title']) ?>
                            </a>
                        </p>
                        <p class="card-text">
                            <a href="details.php?id=<?= $latestNews[0]['id'] ?>" class="text-white text-decoration-none">
                                <?= mb_substr(strip_tags($latestNews[0]['body']), 0, 150) ?>...
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-3">
            <?php for ($i = 1; $i < count($latestNews) && $i < 3; $i++): ?>
                <div class="card border-0">
                    <a href="details.php?id=<?= $latestNews[$i]['id'] ?>">
                        <img src="<?= htmlspecialchars($latestNews[$i]['image']) ?>" class="card-img-top" alt="news">
                    </a>
                    <div class="card-body">
                        <h6 class="card-title text-secondary">
                            <a href="details.php?id=<?= $latestNews[$i]['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($latestNews[$i]['category']) ?>
                            </a>
                        </h6>
                        <p class="card-text">
                            <a href="details.php?id=<?= $latestNews[$i]['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($latestNews[$i]['title']) ?>
                            </a>
                        </p>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <div class="col-md-3">
            <?php for ($i = 3; $i < count($latestNews) && $i < 5; $i++): ?>
                <div class="card border-0">
                    <a href="details.php?id=<?= $latestNews[$i]['id'] ?>">
                        <img src="<?= htmlspecialchars($latestNews[$i]['image']) ?>" class="card-img-top" alt="news">
                    </a>
                    <div class="card-body">
                        <h6 class="card-title text-secondary">
                            <a href="details.php?id=<?= $latestNews[$i]['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($latestNews[$i]['category']) ?>
                            </a>
                        </h6>
                        <p class="card-text">
                            <a href="details.php?id=<?= $latestNews[$i]['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($latestNews[$i]['title']) ?>
                            </a>
                        </p>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </section>

   
    <div class="container py-4">
        <div class="row gx-4">
            <aside class="col-12 col-md-3 mb-4">
                <div class="section-header">
                    <h2 class="section-title">الأكثر قراءة</h2>
                </div>                <ol class="list-unstyled">
                    <?php foreach ($mostRead as $i => $item): ?>
                        <li class="most-read-item mb-3">
                            <span class="most-read-number">
                                <?php if ($i < 3): ?>
                                    <i class="fas fa-fire text-danger"></i>
                                <?php else: ?>
                                    <?= $i + 1 ?>
                                <?php endif; ?>
                            </span>
                            <div class="most-read-content">
                                <a href="details.php?id=<?= $item['id'] ?>" class="most-read-link">
                                    <?= htmlspecialchars($item['title']) ?>
                                </a>
                                <div class="most-read-stats">
                                    <i class="fas fa-eye"></i> <?= number_format($item['views']) ?> قراءة
                                    <?php if ($i == 0): ?>
                                        <span class="badge bg-danger ms-2">الأكثر قراءة</span>
                                    <?php elseif ($i < 3): ?>
                                        <span class="badge bg-warning ms-2">رائج</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </aside>

           
            <section class="col-12 col-md-9">
                <div class="section-header">
                    <h2 class="section-title">المزيد من الأخبار</h2>
                    <a href="#" class="more-link">المزيد</a>
                </div>
                <div class="row gx-4">
                    <div class="col-12 col-md-8 d-flex flex-column mb-4 mb-md-0" style="direction: rtl;">
                        <?php if (isset($latestNews[0])): ?>
                            <article class="d-flex flex-column flex-md-row align-items-start gap-4">
                                <img src="<?= htmlspecialchars($latestNews[0]['image']) ?>" class="news-large-img flex-shrink-0" />
                                <div class="d-flex flex-column justify-content-between">
                                    <a href="details.php?id=<?= $latestNews[0]['id'] ?>" class="news-title fs-5 fw-semibold">
                                        <?= htmlspecialchars($latestNews[0]['title']) ?>
                                    </a>
                                    <p class="card-text">
                                        <?= mb_substr(strip_tags($latestNews[0]['body']), 0, 150) ?>...
                                    </p>
                                </div>
                            </article>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 col-md-4 d-flex flex-column gap-4" style="direction: rtl;">
                        <?php for ($i = 1; $i < count($latestNews) && $i < 3; $i++): ?>
                            <article class="d-flex">
                                <img src="<?= htmlspecialchars($latestNews[$i]['image']) ?>" class="news-small-img" />
                                <div class="d-flex flex-column justify-content-between me-3">
                                    <a href="details.php?id=<?= $latestNews[$i]['id'] ?>" class="news-title">
                                        <?= htmlspecialchars($latestNews[$i]['title']) ?>
                                    </a>
                                    <p class="news-subtitle">
                                        <?= mb_substr(strip_tags($latestNews[$i]['body']), 0, 100) ?>...
                                    </p>
                                </div>
                            </article>
                        <?php endfor; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>

   
    <?php foreach ($newsByCategory as $catName => $articles): ?>
        <?php if (count($articles) > 0): ?>
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title"><?= htmlspecialchars($catName) ?></h2>
                    <a href="category.php?cat=<?= urlencode($catName) ?>" class="more-link">المزيد</a>
                </div>
                <div class="row g-3">
                    <div class="col-lg-6 d-flex flex-column justify-content-center order-lg-1 order-2">
                        <a href="details.php?id=<?= $articles[0]['id'] ?>">
                            <img class="big-news-img rounded mb-2" src="<?= htmlspecialchars($articles[0]['image']) ?>" />
                        </a>
                        <h6 class="card-title">
                            <a href="details.php?id=<?= $articles[0]['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($articles[0]['title']) ?>
                            </a>
                        </h6>
                        <p class="card-text">
                            <a href="details.php?id=<?= $articles[0]['id'] ?>" class="text-dark text-decoration-none">
                                <?= mb_substr(strip_tags($articles[0]['body']), 0, 150) ?>...
                            </a>
                        </p>
                    </div>
                    <div class="col-lg-6 d-flex flex-column justify-content-between order-lg-2 order-1">
                        <div class="row g-3 mb-3">
                            <?php for ($i = 1; $i < count($articles) && $i < 5; $i++): ?>
                                <div class="col-6 news-item">
                                    <a href="details.php?id=<?= $articles[$i]['id'] ?>">
                                        <img class="news-img rounded" src="<?= htmlspecialchars($articles[$i]['image']) ?>" />
                                    </a>
                                    <p class="mt-2 small fw-semibold">
                                        <a href="details.php?id=<?= $articles[$i]['id'] ?>" class="text-dark text-decoration-none">
                                            <?= htmlspecialchars($catName) ?><br>
                                            <span class="fw-bold">
                                                <?= htmlspecialchars($articles[$i]['title']) ?>
                                            </span>
                                        </a>
                                    </p>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</main>

<footer class="bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-3 text-start">
                <img src="logo.png" alt="Profile" class="end-logo mb-2">
                <p class="footer-text">تغطيّة إخبارية شاملة ومتعدّدة الوسائط للأحداث العربيّة والعالمية، ويتيح الوصول إلى شبكة منوعة من البرامج السياسة والاجتماعية.</p>
            </div>            <div class="col-md-3">
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
