<?php
header('Content-Type: application/json'); // 設定回應為 JSON 格式

// 包含資料庫連線檔案
require_once 'db_connect.php';

try {
    // 假設 db_connect.php 返回 $conn 物件
    if (!$conn) {
        throw new Exception("資料庫連線失敗");
    }

    // 獲取表單數據
    $school_num = $_POST['school_num'] ?? '';
    $password = $_POST['password'] ?? '';

    // 驗證輸入是否為空
    if (empty($school_num) || empty($password)) {
        echo json_encode([
            "status" => "error",
            "message" => "學號和密碼不能為空"
        ]);
        exit;
    }

    // 查詢資料庫，驗證學號和密碼，使用正確的大寫欄位名稱 SCHOOL_ID
    $stmt = $conn->prepare("SELECT PASSWORD FROM users WHERE SCHOOL_ID = ?");
    $stmt->bind_param("s", $school_num);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['PASSWORD'];

        // 這裡簡單比較密碼（建議在實際應用中使用密碼雜湊，如 password_verify()）
        if ($password === $stored_password) {
            echo json_encode([
                "status" => "success",
                "message" => "登入成功"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "密碼錯誤"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "學號不存在"
        ]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
?>
