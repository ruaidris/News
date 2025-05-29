<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/config.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) header('Location: index.php');

// Get categories for navigation
$categories = $conn->query("SELECT id, name FROM category")->fetch_all(MYSQLI_ASSOC);

$conn->query("UPDATE news SET views = views + 1 WHERE id = $id");
$stmt = $conn->prepare("
    SELECT n.*, c.name as category_name 
    FROM news n 
    LEFT JOIN category c ON n.category_id = c.id 
    WHERE n.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$article) {
    die("News article not found");
}

// Get dynamic ads
$ads = $conn->query(
    "SELECT image, link FROM ads WHERE status = 'active' ORDER BY id LIMIT 3"
)->fetch_all(MYSQLI_ASSOC);

// Get related news based on similar keywords and category
function getRelatedNews($conn, $article, $current_id) {
    $related_news = [];
    
    // Extract keywords from title and body
    $text = $article['title'] . ' ' . $article['body'];
    $text = preg_replace('/[^\p{Arabic}\p{L}\s]/u', ' ', $text); // Keep only Arabic letters and spaces
    $words = array_filter(array_unique(preg_split('/\s+/', $text)), function($word) {
        return mb_strlen(trim($word)) >= 3; // Only words with 3+ characters
    });
    
    // Limit to most relevant words
    $keywords = array_slice($words, 0, 10);
    
    if (!empty($keywords)) {
        // Build search query for similar content
        $searchConditions = [];
        $searchParams = [];
        $paramTypes = '';
        
        foreach ($keywords as $keyword) {
            $searchConditions[] = "(n.title LIKE ? OR n.body LIKE ?)";
            $searchParams[] = '%' . $keyword . '%';
            $searchParams[] = '%' . $keyword . '%';
            $paramTypes .= 'ss';
        }
        
        $searchQuery = "
            SELECT n.id, n.title, n.image, n.summary, n.dateposted,
                   c.name as category_name
            FROM news n 
            LEFT JOIN category c ON n.category_id = c.id 
            WHERE n.id != ? AND n.status = 'approved' AND (" . implode(' OR ', $searchConditions) . ")
            ORDER BY n.dateposted DESC 
            LIMIT 6
        ";
        
        $stmt = $conn->prepare($searchQuery);
        if ($stmt) {
            $allParams = array_merge([$current_id], $searchParams);
            $allTypes = 'i' . $paramTypes;
            $stmt->bind_param($allTypes, ...$allParams);
            $stmt->execute();
            $related_news = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    }
    
    // If we don't have enough related news, fill with news from same category
    if (count($related_news) < 3) {
        $categoryQuery = "
            SELECT n.id, n.title, n.image, n.summary, n.dateposted,
                   c.name as category_name
            FROM news n 
            LEFT JOIN category c ON n.category_id = c.id 
            WHERE n.category_id = ? AND n.id != ? AND n.status = 'approved' 
            ORDER BY n.dateposted DESC 
            LIMIT ?
        ";
        
        $needed = 6 - count($related_news);
        $stmt = $conn->prepare($categoryQuery);
        if ($stmt) {
            $stmt->bind_param('iii', $article['category_id'], $current_id, $needed);
            $stmt->execute();
            $categoryNews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Merge and remove duplicates
            $existingIds = array_column($related_news, 'id');
            foreach ($categoryNews as $news) {
                if (!in_array($news['id'], $existingIds)) {
                    $related_news[] = $news;
                }
            }
        }
    }
    
    return array_slice($related_news, 0, 6);
}

$related_news = getRelatedNews($conn, $article, $id);
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1" name="viewport"/>    <title><?= htmlspecialchars($article['title']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css" rel="stylesheet"/>
    <link href="style.css" rel="stylesheet"/>
    <style>
        /* Tailwind CSS Utility Classes */
        .text-sm { font-size: 0.875rem; }
        .text-3xl { font-size: 1.875rem; }
        .text-4xl { font-size: 2.25rem; }
        .text-xl { font-size: 1.25rem; }
        .text-lg { font-size: 1.125rem; }
        .text-gray-600 { color: #4b5563; }
        .text-gray-700 { color: #374151; }
        .text-gray-500 { color: #6b7280; }
        .text-blue-900 { color: #1e3a8a; }
        .text-center { text-align: center; }
        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }
        .font-extrabold { font-weight: 800; }
        .leading-tight { line-height: 1.25; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-5 { margin-bottom: 1.25rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-6 { margin-top: 1.5rem; }
        .ms-1 { margin-inline-start: 0.25rem; }
        .ms-2 { margin-inline-start: 0.5rem; }
        .mx-2 { margin-left: 0.5rem; margin-right: 0.5rem; }
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .justify-end { justify-content: flex-end; }
        .justify-start { justify-content: flex-start; }
        .items-center { align-items: center; }
        .items-start { align-items: flex-start; }
        .space-x-2 > * + * { margin-left: 0.5rem; }
        .space-x-3 > * + * { margin-left: 0.75rem; }
        .space-x-reverse > * + * { margin-left: 0; margin-right: 0.75rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-3 { gap: 0.75rem; }
        .gap-4 { gap: 1rem; }
        .w-4 { width: 1rem; }
        .h-4 { height: 1rem; }
        .w-10 { width: 2.5rem; }
        .h-10 { height: 2.5rem; }
        .max-w-full { max-width: 100%; }
        .flex-shrink-0 { flex-shrink: 0; }
        .rounded-full { border-radius: 9999px; }
        .rounded { border-radius: 0.25rem; }
        .bg-gray-900 { background-color: #111827; }
        .bg-gray-100 { background-color: #f3f4f6; }
        .bg-gray-50 { background-color: #f9fafb; }
        .text-white { color: #ffffff; }
        .text-dark { color: #212529; }
        .border { border-width: 1px; }
        .border-gray-400 { border-color: #9ca3af; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .p-3 { padding: 0.75rem; }
        .hover\:bg-gray-100:hover { background-color: #f3f4f6; }
        .hover\:bg-gray-700:hover { background-color: #374151; }
        .hover\:underline:hover { text-decoration: underline; }
        .select-none { user-select: none; }
        .order-1 { order: 1; }
        .order-2 { order: 2; }
        
        .comment-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .comment-item {
            background: white;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 6px;
            border-left: 3px solid #0e2147;
        }
        .comment-author {
            font-weight: bold;
            color: #0e2147;
            margin-bottom: 0.5rem;
        }
        .comment-date {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        .comment-form {
            background: white;
            padding: 1.5rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        .login-prompt {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 6px;
            color: #666;
        }
        .social-share {
            position: fixed;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 1000;
        }
        .social-share button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .social-share button:hover {
            transform: scale(1.1);        }
        .facebook { background-color: #3b5998; }
        .twitter { background-color: #1da1f2; }
        .whatsapp { background-color: #25d366; }
        .copy-link { background-color: #6c757d; }
        
        /* Related News Styles */
        .related-news-container {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .related-news-item {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 1rem;
        }
        
        .related-news-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            flex-shrink: 0;
        }
        
        .related-news-title a {
            color: #1f2937;
            text-decoration: none;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .related-news-title a:hover {
            color: #1e40af;
            text-decoration: underline;
        }
        
        .related-news-category {
            color: #6b7280;
            font-weight: 600;
        }
        
        .related-news-summary {
            line-height: 1.4;
        }
        
        .related-news-date {
            color: #9ca3af;
        }
          /* Ads Styles */
        .ads-section {
            border-top: 1px solid #e5e7eb;
            padding-top: 1rem;
        }
        
        .ad-item {
            transition: transform 0.2s ease;
            max-width: 280px; /* تحديد عرض أقصى للإعلان */
            margin: 0 auto 1rem auto; /* توسيط الإعلان */
        }
        
        .ad-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .ad-image {
            transition: opacity 0.2s ease;
            max-width: 100%;
            height: 150px; /* تحديد ارتفاع ثابت */
            object-fit: cover; /* قص الصورة بشكل متناسق */
            border-radius: 6px;
        }
        
        .ad-item:hover .ad-image {
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .social-share {
                position: static;
                flex-direction: row;
                justify-content: center;
                margin: 1rem 0;
                transform: none;
            }
            
            .related-news-image {
                width: 60px;
                height: 45px;
            }
            
            .related-news-title a {
                font-size: 13px;
            }
        }
    </style>
</head>
<body id="body">
<?php include 'includes/navbar.php'; ?>

    <!-- Social Share Buttons -->
    <div class="social-share">
        <button class="facebook" onclick="shareOnFacebook()" title="شارك على فيسبوك">
            <i class="fab fa-facebook-f"></i>
        </button>
        <button class="twitter" onclick="shareOnTwitter()" title="شارك على تويتر">
            <i class="fab fa-twitter"></i>
        </button>
        <button class="whatsapp" onclick="shareOnWhatsApp()" title="شارك على واتساب">
            <i class="fab fa-whatsapp"></i>
        </button>
        <button class="copy-link" onclick="copyNewsLink()" title="نسخ الرابط">
            <i class="fas fa-link"></i>
        </button>
    </div>

    <header>
        <div class="text-sm text-gray-600 mb-1 font-semibold">
            <?= htmlspecialchars($article['category_name'] ?? 'عام') ?>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold leading-tight mb-3">
            <?= htmlspecialchars($article['title']) ?>
        </h1>
        <div class="flex justify-between items-center text-gray-600 text-sm sm:text-base mb-5 max-w-full">
            <div class="font-semibold cursor-pointer order-2 md:order-1">
                شارك القصة
            </div>
            <div class="flex items-center space-x-2 space-x-reverse order-1 md:order-2">
                <i class="far fa-calendar-alt"></i>
                <span><?= date('j F Y', strtotime($article['dateposted'])) ?></span>
                <span class="mx-2">|</span>
                <i class="far fa-eye"></i>
                <span><?= $article['views'] ?> مشاهدة</span>
            </div>
        </div>
        <div class="flex space-x-3 space-x-reverse mb-6 justify-start max-w-full">
            <button aria-label="Facebook" class="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center text-lg hover:bg-gray-700" onclick="shareOnFacebook()">
                <i class="fab fa-facebook-f"></i>
            </button>
            <button aria-label="Twitter" class="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center text-lg hover:bg-gray-700" onclick="shareOnTwitter()">
                <i class="fab fa-twitter"></i>
            </button>
            <button aria-label="Email" class="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center text-lg hover:bg-gray-700" onclick="shareByEmail()">
                <i class="far fa-envelope"></i>
            </button>
            <button aria-label="Share" class="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center text-lg hover:bg-gray-700" onclick="copyNewsLink()">
                <i class="fas fa-share-alt"></i>
            </button>
        </div>
    </header>    <main id="main">
        <aside>
            <section>
                <h2>أخبار ذات صلة</h2>
                <div class="related-news-container">
                    <?php if (!empty($related_news)): ?>
                        <?php foreach ($related_news as $news): ?>
                        <article class="related-news-item mb-4">
                            <div class="d-flex gap-3">
                                <?php if ($news['image']): ?>
                                <img src="<?= htmlspecialchars($news['image']) ?>" 
                                     alt="<?= htmlspecialchars($news['title']) ?>" 
                                     class="related-news-image">
                                <?php endif; ?>
                                <div class="flex-1">
                                    <p class="related-news-category text-sm text-gray-600 mb-1">
                                        <?= htmlspecialchars($news['category_name'] ?? 'عام') ?>
                                    </p>
                                    <h4 class="related-news-title">
                                        <a href="details.php?id=<?= $news['id'] ?>" class="font-bold hover:underline">
                                            <?= htmlspecialchars($news['title']) ?>
                                        </a>
                                    </h4>
                                    <?php if ($news['summary']): ?>
                                    <p class="related-news-summary text-sm text-gray-600 mt-2">
                                        <?= mb_substr(htmlspecialchars($news['summary']), 0, 80) ?>...
                                    </p>
                                    <?php endif; ?>
                                    <p class="related-news-date text-xs text-gray-500 mt-1">
                                        <?= date('Y-m-d', strtotime($news['dateposted'])) ?>
                                    </p>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">لا توجد أخبار ذات صلة متاحة حالياً</p>
                    <?php endif; ?>
                </div>
            </section>
              <!-- Dynamic Ads Section -->
            <?php if (!empty($ads)): ?>
            <section class="ads-section mt-4">
                <div class="ads-container">
                    <?php foreach ($ads as $index => $ad): ?>
                    <div class="ad-item bg-gray-50 p-3 mb-3 rounded">
                        <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" rel="noopener">
                            <img src="<?= htmlspecialchars($ad['image']) ?>" 
                                 alt="إعلان <?= $index + 1 ?>" 
                                 class="ad-image"/>
                        </a>
                        <div class="text-center text-sm text-gray-600 mt-2">إعلان</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </aside>

        <article id="art">
            <figure>
                <?php if ($article['image']): ?>
                <img alt="<?= htmlspecialchars($article['title']) ?>" src="<?= htmlspecialchars($article['image']) ?>" style="border-radius:4px; object-fit:cover; width:100%; max-width:600px; height:350px; display:block; margin-left:auto; margin-right:auto;"/>
                <?php endif; ?>
            </figure>
            
            <div class="flex justify-end gap-3 text-gray-700 text-base font-semibold select-none mt-6">
                <button id="decrease-font" aria-label="تصغير الخط" class="flex items-center gap-2 border border-gray-400 rounded px-3 py-1 hover:bg-gray-100">
                    <span class="text-xl">−</span>
                    <span>تصغير</span>
                </button>
                <button id="increase-font" aria-label="تكبير الخط" class="flex items-center gap-2 border border-gray-400 rounded px-3 py-1 hover:bg-gray-100">
                    <span class="text-xl">+</span>
                    <span>تكبير</span>
                </button>
            </div>
            
            <div id="news-content" class="mt-6">
                <?= nl2br(htmlspecialchars($article['body'])) ?>
            </div>

            <!-- Comments Section -->
            <div class="comment-section">
                <h3 class="font-bold text-xl mb-4">التعليقات</h3>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="comment-form">
                        <h4 class="font-semibold mb-3">أضف تعليقاً</h4>
                        <form id="comment-form">
                            <input type="hidden" name="news_id" value="<?= $id ?>">
                            <textarea id="comment-text" name="content" class="form-control mb-3" rows="4" placeholder="اكتب تعليقك هنا..." required></textarea>
                            <button type="submit" class="btn btn-primary">إضافة تعليق</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="login-prompt">
                        <p>يجب <a href="login.php">تسجيل الدخول</a> لإضافة تعليق</p>
                    </div>
                <?php endif; ?>
                
                <div id="comments-container">
                    <!-- Comments will be loaded here via AJAX -->
                </div>
            </div>
        </article>
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
                    $footerCats = $conn->query("SELECT id, name FROM category LIMIT 5");
                    while($footerCat = $footerCats->fetch_assoc()): ?>
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
        </div>
    </div>
</footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        const newsId = <?= $id ?>;
        const newsTitle = <?= json_encode($article['title']) ?>;
        const newsUrl = window.location.href;
        let currentFontSize = 16;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadWeather();
            loadComments();
            initializeFontControls();
        });        // Weather functionality
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
            document.getElementById('temperature').textContent = temperature;
            document.getElementById('weather-icon').className = `fas ${iconClass} ms-2`;
            // Update city name if needed
            const weatherDiv = document.querySelector('.weather');
            const citySpan = weatherDiv.querySelector('.ms-1');
            if (citySpan) {
                citySpan.textContent = cityName;
            }        }

        // Font size controls
        function initializeFontControls() {
            const newsContent = document.getElementById('news-content');
            const increaseBtn = document.getElementById('increase-font');
            const decreaseBtn = document.getElementById('decrease-font');

            increaseBtn.addEventListener('click', function() {
                if (currentFontSize < 24) {
                    currentFontSize += 2;
                    newsContent.style.fontSize = currentFontSize + 'px';
                }
            });

            decreaseBtn.addEventListener('click', function() {
                if (currentFontSize > 12) {
                    currentFontSize -= 2;
                    newsContent.style.fontSize = currentFontSize + 'px';
                }
            });
        }

        // Social sharing functions
        function shareOnFacebook() {
            const url = encodeURIComponent(newsUrl);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
        }

        function shareOnTwitter() {
            const text = encodeURIComponent(newsTitle);
            const url = encodeURIComponent(newsUrl);
            window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank', 'width=600,height=400');
        }

        function shareOnWhatsApp() {
            const text = encodeURIComponent(`${newsTitle} - ${newsUrl}`);
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }

        function shareByEmail() {
            const subject = encodeURIComponent(newsTitle);
            const body = encodeURIComponent(`اقرأ هذا الخبر: ${newsUrl}`);
            window.location.href = `mailto:?subject=${subject}&body=${body}`;
        }

        function copyNewsLink() {
            navigator.clipboard.writeText(newsUrl).then(function() {
                alert('تم نسخ الرابط بنجاح!');
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = newsUrl;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('تم نسخ الرابط بنجاح!');
            });
        }        // Comments functionality
        function loadComments() {
            fetch(`ajax_get_comment.php?news_id=${newsId}`)
                .then(response => response.text())
                .then(text => {
                    console.log('Comments API Raw Response:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            displayComments(data.comments);
                        } else if (Array.isArray(data)) {
                            displayComments(data);
                        } else {
                            console.error('Comments API Error:', data.error || 'Unknown error');
                            displayComments([]);
                        }
                    } catch (jsonError) {
                        console.error('JSON Parse Error:', jsonError, 'Raw response:', text);
                        displayComments([]);
                    }
                })
                .catch(error => {
                    console.error('Error loading comments:', error);
                    displayComments([]);
                });
        }        function displayComments(comments) {
            const container = document.getElementById('comments-container');
            
            if (!comments || comments.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-500">لا توجد تعليقات بعد. كن أول من يعلق!</p>';
                return;
            }

            container.innerHTML = comments.map(comment => `
                <div class="comment-item">
                    <div class="comment-author">${comment.username || comment.email || 'مستخدم'}</div>
                    <div class="comment-date">${comment.dateposted || comment.created_at}</div>
                    <div class="comment-content">${comment.content}</div>
                </div>
            `).join('');
        }        // Handle comment form submission
        document.getElementById('comment-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const commentText = document.getElementById('comment-text').value.trim();
            if (!commentText) {
                alert('يرجى كتابة تعليق قبل الإرسال');
                return;
            }

            const formData = new FormData();
            formData.append('news_id', newsId);
            formData.append('content', commentText);

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'جاري الإرسال...';
            submitBtn.disabled = true;

            fetch('ajax_add_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                console.log('Add Comment Raw Response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        document.getElementById('comment-text').value = '';
                        loadComments(); // Reload comments
                        alert('تم إضافة التعليق بنجاح!');
                    } else {
                        alert(data.error || data.message || 'حدث خطأ أثناء إضافة التعليق');
                    }
                } catch (jsonError) {
                    console.error('JSON Parse Error:', jsonError, 'Raw response:', text);
                    alert('حدث خطأ في معالجة الاستجابة');
                }
            })
            .catch(error => {
                console.error('Error adding comment:', error);
                alert('حدث خطأ أثناء إضافة التعليق');
            })
            .finally(() => {
                // Restore button state
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    </script>
</body>
</html>
