<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'abc369';
$db_name = 'test';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(["status" => "error", "message" => "資料庫連線失敗: " . $conn->connect_error]);
    exit;
}

// 設置 UTF-8 字符集
$conn->set_charset("utf8mb4");
?>