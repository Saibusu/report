<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "不允許的請求方法，請使用 POST"
    ]);
    exit;
}
var_dump($_POST); // 檢查接收到的 POST 資料
require_once 'db_connect.php';
// 後續代碼...

$conn = null;
try {
    $conn = require 'db_connect.php'; // 使用 require 返回 $conn
    if (!is_object($conn) || !$conn instanceof mysqli) {
        throw new Exception("無法獲取資料庫連線");
    }

    $name = $_POST['name'] ?? '';
    $school_id = $_POST['school_id'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($school_id) || empty($email) || empty($password)) {
        throw new Exception("所有欄位都必須填寫");
    }

    // 檢查學號是否已存在
    $stmt = $conn->prepare("SELECT SCHOOL_ID FROM users WHERE SCHOOL_ID = ?");
    if ($stmt === false) {
        throw new Exception("準備語句失敗: " . $conn->error);
    }
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("學號已存在");
    }

    // 檢查Email是否已存在
    $stmt = $conn->prepare("SELECT EMAIL FROM users WHERE EMAIL = ?");
    if ($stmt === false) {
        throw new Exception("準備語句失敗: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email已存在");
    }

    // 加密密碼
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 插入新用戶
    $stmt = $conn->prepare("INSERT INTO users (SCHOOL_ID, NAME, EMAIL, PASSWORD, REG_TIME) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt === false) {
        throw new Exception("準備語句失敗: " . $conn->error);
    }
    $stmt->bind_param("isss", $school_id, $name, $email, $hashed_password);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "註冊成功"
        ]);
    } else {
        throw new Exception("註冊失敗: " . $conn->error);
    }
} catch (Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>