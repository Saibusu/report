<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

// ✅ 確保 `username` 來自 `name` 欄位
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "訪客";
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>歡迎頁面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            text-align: center;
            padding: 50px;
        }
        h2 {
            color: #4CAF50;
            font-size: 2em;
        }
        a {
            color: #ffffff;
            background-color: #007BFF;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.2em;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>歡迎, <strong><?php echo $username; ?></strong>!</h2>
    <p>很高興見到你！你現在已經登入。</p>
    <a href="logout.php">登出</a>
</body>
</html>
