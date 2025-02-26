<?php
require 'db_connect.php';

// 設置 JSON 頭部
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $school_num = $_POST['school_num'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($school_num) || empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "所有欄位都必須填寫"]);
        exit;
    }

    // 基本 email 驗證
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Email 格式不正確"]);
        exit;
    }

    $conn = connectDB();
    if (!$conn) {
        echo json_encode(["status" => "error", "message" => "資料庫連線失敗"]);
        exit;
    }

    // 檢查學號或 email 是否已存在
    $stmt = $conn->prepare("SELECT school_num, email FROM users WHERE school_num = ? OR email = ?");
    $stmt->bind_param("is", $school_num, $email); // school_num 為整數，email 為字符串
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing_user = $result->fetch_assoc();
        if ($existing_user['school_num'] == $school_num) {
            echo json_encode(["status" => "error", "message" => "學號已被註冊"]);
        } else if ($existing_user['email'] == $email) {
            echo json_encode(["status" => "error", "message" => "Email 已被註冊"]);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    // 加密密碼
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 插入新用戶
    $stmt = $conn->prepare("INSERT INTO users (name, school_num, email, password, reg_time) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("siss", $name, $school_num, $email, $hashed_password); // 確保參數類型正確

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "註冊成功"]);
    } else {
        echo json_encode(["status" => "error", "message" => "註冊失敗: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "請求方式錯誤"]);
}
?>