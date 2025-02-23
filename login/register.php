<?php
header("Content-Type: application/json");
require "db_connect.php";

$response = ["status" => "error", "message" => "請求失敗"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    var_dump($_POST); // 檢查 POST 數據
    $response["message"] = "收到 POST 請求，但未處理資料庫";
    if ($conn) {
        $response["message"] = "資料庫連線成功";
    } else {
        $response["message"] = "資料庫連線失敗";
    }
}

echo json_encode($response);
?>