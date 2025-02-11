<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>註冊</title>
</head>
<body>
<h2>註冊頁面</h2>
<form action="" method="POST">
    <label for="username">帳號:</label>
    <input type="text" name="username" id="username" required><br>
    <label for="password">密碼:</label>
    <input type="password" name="password" id="password" required><br>
    <button type="submit">註冊</button>
</form>
<a href="index.php">返回登入頁面</a>

<?php
$conn = require_once("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    $check = "SELECT * FROM user WHERE username = ?";
    $stmt = $conn->prepare($check);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $sql = "INSERT INTO user (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>註冊成功!</p>";
            header("refresh:3;url=index.php");
        } else {
            echo "<p style='color: red;'>註冊失敗: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: red;'>該帳號已有人使用!</p>";
    }
}

$conn->close();
?>
</body>
</html>
