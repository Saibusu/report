<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $school_num = $_POST['school_num'] ?? '';
    $team = $_POST['team'] ?? ''; // 新增小組名稱
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($school_num) || empty($team) || empty($email) || empty($password)) { // 包含 team 檢查
        header("Location: /report/LoginRegister/register.html?error=" . urlencode("所有欄位都必須填寫"));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /report/LoginRegister/register.html?error=" . urlencode("Email 格式不正確"));
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
        header("Location: /report/LoginRegister/register.html?error=" . urlencode($error));
        exit;
    }

    // 移除 password_hash，直接使用明文密碼
    $stmt = $conn->prepare("INSERT INTO users (name, school_num, team, email, password, reg_time) VALUES (?, ?, ?, ?, ?, NOW())"); // 增加 team 欄位
    $stmt->bind_param("sisss", $name, $school_num, $team, $email, $password); // 調整參數類型和順序

    if ($stmt->execute()) {
        header("Location: /report/LoginRegister/login.html?success=" . urlencode("註冊成功，請登入"));
    } else {
        header("Location: /report/LoginRegister/register.html?error=" . urlencode("註冊失敗: " . $stmt->error));
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: /report/LoginRegister/register.html?error=" . urlencode("請求方式錯誤"));
}
?>