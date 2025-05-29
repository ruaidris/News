<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/init.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'الرجاء تعبئة الحقلين أولاً';
    } else 
       
        $stmt = $conn->prepare("SELECT id, password, role FROM `user` WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

           
            if ($user && password_verify($password, $user['password'])) {
               
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $user['role'];

                
                switch ($user['role']) {
                case 'author':  header('Location: author_dashboard.php'); break;
                case 'editor':  header('Location: editor_dashboard.php'); break;
                case 'admin':   header('Location: admin_dashboard.php');  break;
                default:        header('Location: index.php');           break;
                
                }
                exit;
            } else {
                $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
            }
        } else {
            $error = 'حدث خطأ داخلي، الرجاء المحاولة لاحقاً';
        }
    }
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <title>تسجيل الدخول | News Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .main-content {
      background: #f0f2f5;
      display: flex; 
      align-items: center; 
      justify-content: center;
      min-height: 80vh;
      padding: 40px 0;
    }    .login-container {
      background: #fff;
      padding: 30px 40px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      width: 350px;
    }
    .login-container h1 {
      margin-bottom: 20px;
      text-align: center;
      color: #333;
      font-size: 24px;
    }
    .login-container .error {
      background: #ffe6e6;
      color: #cc0000;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 15px;
      text-align: center;
    }
    .login-container form label {
      display: block;
      margin-bottom: 5px;
      color: #555;
      font-size: 14px;
    }
    .login-container form input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
    }
    .login-container form button {
      width: 100%;
      padding: 10px;
      background:  #0e2147;
      color: #fff;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.2s ease;
    }
    .login-container form button:hover {
      background: #0056b3;
    }
    .login-container .footer {
      margin-top: 20px;
      text-align: center;
      font-size: 13px;
      color: #777;
    }  </style>
</head>
<body>
  <div class="main-content">
    <div class="login-container">
    <h1>تسجيل الدخول</h1>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="">
      <label for="email">البريد الإلكتروني</label>
      <input type="email" id="email" name="email"  required autofocus>

      <label for="password">كلمة المرور</label>
      <input type="password" id="password" name="password" required>      <button type="submit">دخول</button>
    </form>
    
    </div>
  </div>
</body>
</html>
</html>
