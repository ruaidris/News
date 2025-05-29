<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/init.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'author') {
    header('Location: login.php');
    exit;
}

$uploadsDir = __DIR__ . '/uploads/';

$edit_mode   = false;
$id          = null;
$title       = '';
$summary     = '';
$body        = '';
$category_id = '';
$imageSource = '';  
$keywords    = '';
$error       = '';

if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $id        = intval($_GET['edit_id']);
    $stmt = $conn->prepare("
        SELECT title, summary, body, image, category_id, keywords
        FROM news
        WHERE id = ? AND author_id = ?
    ");
    $stmt->bind_param('ii', $id, $_SESSION['user_id']);
    $stmt->execute();
    $news = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$news) {
        header('Location: author_dashboard.php');
        exit;
    }

    $title       = $news['title'];
    $summary     = $news['summary'];
    $body        = $news['body'];
    $imageSource = $news['image'];  
    $category_id = $news['category_id'];
    $keywords    = $news['keywords'];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $summary     = trim($_POST['summary']);
    $body        = trim($_POST['body']);
    $category_id = intval($_POST['category_id']);
    $keywords    = trim($_POST['keywords'] ?? '');
    $imageUrl    = trim($_POST['image_url'] ?? '');
    $date        = date('Y-m-d');
    $author_id   = $_SESSION['user_id'];
    $oldImage    = $imageSource;    // Handle image upload or URL
    if (!empty($_FILES['image']['name'])) {
        // Check for upload errors first
        switch ($_FILES['image']['error']) {
            case UPLOAD_ERR_OK:
                // Continue with processing
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'حجم الملف كبير جداً.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'تم رفع جزء من الملف فقط.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'لم يتم رفع أي ملف.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error = 'مجلد مؤقت مفقود.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error = 'فشل في كتابة الملف.';
                break;
            default:
                $error = 'خطأ غير معروف في رفع الملف.';
                break;
        }
        
        if (empty($error)) {
            // Create uploads directory if it doesn't exist
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = $_FILES['image']['type'];
            
            // Use finfo for better file type detection
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedType = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);
                if ($detectedType) {
                    $fileType = $detectedType;
                }
            }
            
            // Fallback: check by extension if type detection fails
            if (!in_array($fileType, $allowedTypes)) {
                $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($extension, $allowedExtensions)) {
                    $error = 'نوع الملف غير مدعوم. الأنواع المدعومة: JPG, PNG, GIF, WEBP';
                }
            }
            
            // Check file size (max 5MB)
            if (empty($error) && $_FILES['image']['size'] > 5 * 1024 * 1024) {
                $error = 'حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت';
            }
            
            if (empty($error)) {
                // Generate unique filename to avoid conflicts
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $target = $uploadsDir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $imageSource = 'uploads/' . $filename;
                } else {
                    $error = 'فشل رفع الصورة. تأكد من صلاحيات مجلد uploads.';
                }
            }
        }
    }
   
    elseif (!empty($imageUrl)) {

        if (preg_match('#^https?://#i', $imageUrl)) {
            $imageSource = $imageUrl;
        } else {
            $error = 'رابط الصورة غير صالح.';
        }
    }
    // 4.c) إذا وضع تعديل ولم يُغيّر الصورة أو الرابط
    elseif ($edit_mode) {
        $imageSource = $oldImage;
    }
    // 4.d) وإلا خطأ
    else {
        $error = 'يجب رفع ملف أو لصق رابط للصورة.';
    }

    // 5) إذا لا أخطاء، أدخل أو حدّث
    if (empty($error)) {
        if ($edit_mode) {
            $stmt = $conn->prepare("
                UPDATE news
                SET title       = ?,
                    summary     = ?,
                    body        = ?,
                    image       = ?,
                    category_id = ?,
                    keywords    = ?,
                    status      = 'pending'
                WHERE id = ? AND author_id = ?
            ");
            $stmt->bind_param(
                'ssssisii',
                $title,
                $summary,
                $body,
                $imageSource,
                $category_id,
                $keywords,
                $id,
                $author_id
            );
        } else {
            $stmt = $conn->prepare("
                INSERT INTO news
                  (title, summary, body, image, dateposted, category_id, author_id, keywords)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'sssssiis',
                $title,
                $summary,
                $body,
                $imageSource,
                $date,
                $category_id,
                $author_id,
                $keywords
            );
        }

        if ($stmt->execute()) {
            header('Location: author_dashboard.php');
            exit;
        } else {
            $error = 'خطأ في قاعدة البيانات: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $edit_mode ? 'تعديل خبر' : 'إضافة خبر' ?></title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; font-family:Arial,sans-serif; padding:30px; }
    .container { max-width:700px; margin:auto; background:#fff; padding:30px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    .btn-custom { background:#0069d9; color:#fff; border:none; }
    .btn-custom:hover { background:#0056b3; }
    img.preview { max-width:200px; margin-top:10px; display:block; }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="mb-4 text-center">
      <?= $edit_mode ? '✏️ تعديل الخبر' : '📰 إضافة خبر جديد' ?>
    </h2>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <!-- العنوان والموجز والمحتوى -->
      <div class="mb-3">
        <label class="form-label">العنوان</label>
        <input name="title" class="form-control" required value="<?= htmlspecialchars($title) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">الموجز</label>
        <textarea name="summary" class="form-control" rows="2" required><?= htmlspecialchars($summary) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">المحتوى</label>
        <textarea name="body" class="form-control" rows="5" required><?= htmlspecialchars($body) ?></textarea>
      </div>

      <!-- القسم والكلمات المفتاحية -->
      <div class="mb-3">
        <label class="form-label">القسم</label>
        <select name="category_id" class="form-select" required>
          <option value="">اختر تصنيفًا</option>
          <?php
            $cats = $conn->query("SELECT id,name FROM category");
            while ($cat = $cats->fetch_assoc()):
          ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id']==$category_id?'selected':''?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">الكلمات المفتاحية</label>
        <input name="keywords" class="form-control" placeholder="مثال: سياسة, اقتصاد" value="<?= htmlspecialchars($keywords) ?>">
      </div>      <!-- رفع ملف أو لصق رابط -->
      <div class="mb-3">
        <label class="form-label">رفع صورة من جهازك</label>
        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this)">
        <small class="form-text text-muted">أنواع الملفات المدعومة: JPG, PNG, GIF, WEBP (حد أقصى 5 ميجابايت)</small>
        <div id="image-preview" style="margin-top: 10px;"></div>
      </div>
      <div class="mb-3">
        <label class="form-label">أو لصق رابط الصورة</label>
        <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg" value="<?= isset($imageUrl)?htmlspecialchars($imageUrl):'' ?>" onchange="previewImageUrl(this.value)">
        <small class="form-text text-muted">يمكنك لصق رابط صورة من الإنترنت بدلاً من رفع ملف</small>
      </div>

      <!-- معاينة الصورة الحالية -->
      <?php if ($imageSource): ?>
        <div class="mb-3">
          <label class="form-label">معاينة الصورة الحالية</label>
          <div style="text-align: center;">
            <img src="<?= htmlspecialchars($imageSource) ?>" alt="صورة الخبر" class="preview" style="max-width: 300px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
          </div>
          <small class="form-text text-muted">الصورة الحالية: <?= htmlspecialchars($imageSource) ?></small>
        </div>
      <?php endif; ?>

      <button type="submit" class="btn btn-custom w-100">
        <?= $edit_mode ? 'تحديث الخبر' : 'إضافة الخبر' ?>
      </button>
    </form>
  </div>
</body>
</html>
