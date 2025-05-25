<?php
// register.php
error_reporting(E_ALL);
ini_set('display_errors',1);
require_once __DIR__ . '/init.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // نظّفي المدخلات
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $pass1    = $_POST['password'] ?? '';
    $pass2    = $_POST['confirm_password'] ?? '';
    
    // تحقق من صحة المدخلات
    if (!$name || !$email || !$pass1) {
        $error = 'رجاءً عبّئي كل الحقول.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'الإيميل غير صحيح.';
    } elseif ($pass1 !== $pass2) {
        $error = 'كلمتا المرور غير متطابقتين.';
    } else {
        // جهّز الهاش
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        // دور المستخدم العادي
        $role = 'user';

        // أدخل السجل
        $stmt = $conn->prepare("
          INSERT INTO `user` (name, email, password, role)
          VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $name, $email, $hash, $role);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'حدث خطأ: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>تسجيل مستخدم جديد</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
</head>
<body class="p-4">
  <div class="container" style="max-width:400px">
    <h2 class="mb-4">إنشاء حساب</h2>
    <?php if($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif;?>
    <form method="post">
      <div class="mb-3">
        <label>الاسم</label>
        <input type="text" name="name" class="form-control" required value="<?=htmlspecialchars($name ?? '')?>">
      </div>
      <div class="mb-3">
        <label>الإيميل</label>
        <input type="email" name="email" class="form-control" required value="<?=htmlspecialchars($email ?? '')?>">
      </div>
      <div class="mb-3">
        <label>كلمة المرور</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>تأكيد كلمة المرور</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button class="btn btn-primary w-100">سجّل</button>
      <p class="mt-2 text-center">
        لديك حساب؟ <a href="login.php">تسجيل دخول</a>
      </p>
    </form>
  </div>
</body>
</html>
