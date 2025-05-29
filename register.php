<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/init.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'reader';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'الرجاء تعبئة جميع الحقول.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صحيح.';
    } else {
        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'البريد الإلكتروني مسجل بالفعل.';
            $checkStmt->close();
        } else {
            $checkStmt->close();
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO user (name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
                if ($stmt->execute()) {
                    // Set session for automatic login
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = $role;
                    
                    $stmt->close();
                    
                    // Redirect based on role
                    switch ($role) {
                        case 'writer':
                            header('Location: add_news.php');
                            break;
                        case 'editor':
                            header('Location: editor_dashboard.php');
                            break;
                        case 'reader':
                        default:
                            header('Location: index.php');
                            break;
                    }
                    exit;
                } else {
                    $error = 'حدث خطأ أثناء إنشاء الحساب: ' . $stmt->error;
                    $stmt->close();
                }
            } else {
                $error = 'حدث خطأ في إعداد قاعدة البيانات.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>إنشاء حساب جديد | News Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .main-content {
      background: #f0f2f5;
      font-family: 'Segoe UI', Tahoma, sans-serif;
      padding: 40px;
    }
    .panel {
      max-width: 500px;
      margin: auto;
      background: #fff;
      border-radius: 10px;
      padding: 40px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #333;
    }
    .btn-custom {
      background: #0e2147;
      color: #fff;
      transition: background 0.3s;
    }
    .btn-custom:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
  
  
  <div class="main-content">
  <div class="panel">
    <h2>إنشاء حساب جديد</h2>

    <?php if ($error): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label>الاسم</label>
        <input type="text" name="name" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>كلمة المرور</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>الهوية</label>
        <select name="role" class="form-control" required>
          <option value="reader">قارئ</option>
          <option value="writer">كاتب</option>
          <option value="editor">محرر</option>
        </select>
      </div>

      <button type="submit" class="btn btn-custom w-100">تسجيل الحساب</button>
    </form>    <div class="text-center mt-3">
      لديك حساب بالفعل؟ <a href="login.php">سجّل الدخول</a>
    </div>
  </div>
  </div>
</body>
</html>
