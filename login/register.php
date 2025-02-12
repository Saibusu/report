<?php
session_start();
$conn = require_once("config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $group_name = $_POST["group_name"];
    $name = $_POST["name"];
    $student_id = $_POST["student_id"];
    $password = $_POST["password"];

    // 檢查學號是否已經存在
    $check = "SELECT * FROM users WHERE student_id = ?";
    $stmt = $conn->prepare($check);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // 學號不存在，進行註冊
        $sql = "INSERT INTO users (group_name, name, student_id, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $group_name, $name, $student_id, $password);

        if ($stmt->execute()) {
            $success = "註冊成功! 將在3秒後跳轉至登入頁面";
            header("refresh:3;url=index.php");
        } else {
            $error = "註冊失敗: " . $conn->error;
        }
    } else {
        $error = "該學號已有人註冊!";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>註冊</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .register-container {
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
    <div class="register-container">
        <h2 class="text-center">註冊頁面</h2>
        <?php if (isset($success)) echo "<p class='text-success text-center'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='text-danger text-center'>$error</p>"; ?>
        <form action="register.php" method="post">
            <div class="mb-3">
                <label for="group_name" class="form-label">小組名稱:</label>
                <input type="text" class="form-control" name="group_name" id="group_name" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">姓名:</label>
                <input type="text" class="form-control" name="name" id="name" required>
            </div>
            <div class="mb-3">
                <label for="student_id" class="form-label">學號:</label>
                <input type="text" class="form-control" name="student_id" id="student_id" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">密碼:</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">註冊</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php">返回登入頁面</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
