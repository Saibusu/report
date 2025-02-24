<?php
$servername = "localhost";
$username = "root";
$password = "abc369"; // 請確保這是實際密碼
$dbname = "test";

function connectDB() {
    global $servername, $username, $password, $dbname;
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("資料庫連線失敗: " . $conn->connect_error);
        }
        $conn->set_charset("utf8mb4"); // 設定編碼
        return $conn; // 返回連線物件
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
        exit;
    }
}
?>