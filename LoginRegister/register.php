<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $school_num = $_POST['school_num'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($school_num) || empty($email) || empty($password)) {
        header("Location: /LoginRegister/register.html?error=" . urlencode("所有欄位都必須填寫"));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /LoginRegister/register.html?error=" . urlencode("Email 格式不正確"));
        exit;
    }

    $conn = connectDB(); // 這裡調用統一的 connectDB()

    $stmt = $conn->prepare("SELECT school_num, email FROM users WHERE school_num = ? OR email = ?");
    $stmt->bind_param("is", $school_num, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing_user = $result->fetch_assoc();
        if ($existing_user['school_num'] == $school_num) {
            $error = "學號已被註冊";
        } else {
            $error = "Email 已被註冊";
        }
        $stmt->close();
        $conn->close();
        header("Location: /LoginRegister/register.html?error=" . urlencode($error));
        exit;
    }

    // 移除 password_hash，直接使用明文密碼
    $stmt = $conn->prepare("INSERT INTO users (name, school_num, email, password, reg_time) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("siss", $name, $school_num, $email, $password);

    if ($stmt->execute()) {
        header("Location: /LoginRegister/login.html?success=" . urlencode("註冊成功，請登入"));
    } else {
        header("Location: /LoginRegister/register.html?error=" . urlencode("註冊失敗: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: /LoginRegister/register.html?error=" . urlencode("請求方式錯誤"));
}
?>