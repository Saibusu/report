<?php
$servername = "localhost"; // 確保這是正確的伺服器地址
$username = "root"; // 確保這是正確的 MySQL 使用者名稱
$password = "abc369"; // 將 "abc369" 替換為實際的 MySQL 密碼
$dbname = "test"; // 確保資料庫名稱正確

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "message" => "資料庫連接失敗: " . $conn->connect_error
    ]));
}

// 連線成功後，可以進行其他操作
echo json_encode(["status" => "success", "message" => "資料庫連接成功"]);
?>