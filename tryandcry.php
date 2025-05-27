<?php
require_once 'db.php';  

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = 'author'; 
    
    if ($name === '' || $email === '' || $password === '') {
        $error = 'رجاءً عبّي جميع الحقول.';
    }
   
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'الإيميل غير صالح.';
    }
    else {
       
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        
        $stmt = $conn->prepare("
            INSERT INTO `user` (name, email, password, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('ssss', $name, $email, $hashed, $role);

        if ($stmt->execute()) {
            
            header('Location: login.php');
            exit;
        } else {
            
            $error = 'تعذّر إنشاء الحساب. إذا الإيميل مستخدم سابقاً جرّب تسجيل الدخول.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تسجيل مستخدم جديد</title>
</head>
<body>
    <h2>إنشاء حساب جديد</h2>
    <?php if ($error): ?>
      <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>الاسم:</label><br>
        <input type="text" name="name" required><br><br>

        <label>الإيميل:</label><br>
        <input type="email" name="email" required><br><br>

        <label>كلمة المرور:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">سجّل</button>
    </form>
</body>
</html>
