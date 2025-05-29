<?php
include 'db.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 1;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'newest';

$order_by = $filter === 'most_read' ? 'views DESC' : 'created_at DESC';
$news_query = "SELECT * FROM news WHERE category_id = $category_id ORDER BY $order_by LIMIT 10 OFFSET $offset";
$news_result = mysqli_query($conn, $news_query);

while ($row = mysqli_fetch_assoc($news_result)) {
    echo '<article>';
    echo '<img src="' . $row['image_url'] . '" alt="' . $row['title'] . '" class="w-full object-cover">';
    echo '<p class="text-sm text-gray-500 mt-2">' . $row['category'] . '</p>';
    echo '<h2 class="font-extrabold text-lg leading-tight mt-1">' . $row['title'] . '</h2>';
    echo '<p class="text-sm text-gray-700 mt-1 leading-tight">' . $row['excerpt'] . '</p>';
    echo '</article>';
}
?>
