<?php
session_start();
if (isset($_SESSION['student_id'])) {  
    header("Location: welcome.php");
    exit;
}

// 取得錯誤訊息，顯示後刪除
$error = "";
if (isset($_SESSION["error"])) {
    $error = $_SESSION["error"];
    unset($_SESSION["error"]);  // 顯示後刪除 session
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登入</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container {
            max-width: 400px;
            margin: auto;
            margin-top: 10vh;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <h2 class="text-center">登入頁面</h2>
        <?php if (!empty($error)) { ?>
            <p class="text-danger text-center"><?php echo $error; ?></p>
        <?php } ?>
        <form action="login.php" method="post">
            <div class="mb-3">
                <label for="student_id" class="form-label">學號:</label>
                <input type="text" class="form-control" name="student_id" id="student_id" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">密碼:</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">登入</button>
        </form>
        <div class="text-center mt-3">
            <a href="register.php">註冊</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
