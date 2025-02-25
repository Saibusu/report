<?php
$db_host = 'localhost'; // 資料庫主機 (通常是 localhost 或 127.0.0.1)
$db_user = 'root'; // 你的 MySQL 使用者名稱
$db_pass = 'abc369';     // 你的 MySQL 密碼
$db_name = 'test'; // 你建立的資料庫名稱

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}
echo "測試成功";
?>