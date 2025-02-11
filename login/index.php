<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: welcome.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>登入</title>
</head>
<body>
<h2>登入頁面</h2>
<form action="login.php" method="post">
    <label for="username">帳號:</label>
    <input type="text" name="username" id="username" required><br>
    <label for="password">密碼:</label>
    <input type="password" name="password" id="password" required><br>
    <button type="submit">登入</button>
</form>
<a href="register.php">註冊</a>
</body>
</html>