<?php
require_once 'db_connect.php';

header('Content-Type: application/json; charset=UTF-8');

// 使用 db_connect.php 中的資料庫連接
function connectDB() {
    $db_host = 'localhost'; // 資料庫主機
    $db_user = 'root';      // MySQL 使用者名稱
    $db_pass = 'abc369';    // MySQL 密碼
    $db_name = 'test';      // 資料庫名稱

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        // 改進錯誤處理，返回 JSON 格式的錯誤訊息
        echo json_encode(["status" => "error", "message" => "資料庫連線失敗: " . $conn->connect_error]);
        exit;
    }
    return $conn;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_num = $_POST['school_num'] ?? '';
    $password = $_POST['password'] ?? '';

    // 檢查輸入是否為空
    if (empty($school_num) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "學號和密碼不得為空"]);
        exit;
    }

    $conn = connectDB();
    if (!$conn) {
        exit; // connectDB() 已經處理了錯誤回應
    }

    // 準備 SQL 語句，檢查學號是否存在
    $stmt = $conn->prepare("SELECT * FROM users WHERE school_num = ?");
    $stmt->bind_param("i", $school_num); // school_num 為整數類型
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // **安全性改進**：
        // 假設密碼在註冊時已使用 password_hash() 加密，這裡使用 password_verify() 驗證
        // 註：如果您的資料庫中密碼仍是明文，這段程式碼需要調整
        if (password_verify($password, $user['password'])) {
            echo json_encode(["status" => "success", "message" => "登入成功"]);
        } else {
            echo json_encode(["status" => "error", "message" => "密碼錯誤"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "學號不存在"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "請求方式錯誤"]);
}
?>