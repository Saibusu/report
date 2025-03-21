<?php
// 確保 db_connect.php 包含資料庫連線函數，並呼叫它來建立連線
require '../LoginRegister/db_connect.php';
$conn = connectDB(); // 呼叫 connectDB() 函數並將結果賦值給 $conn

// 如果連線失敗，檢查並處理錯誤
if ($conn === false || $conn->connect_error) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "資料庫連線失敗: " . ($conn->connect_error ?? "未知錯誤")]);
    exit;
}

// 從 URL 獲取 limit 參數，預設為 10
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// 查詢 self_leaderboard 表，根據 score 從高到低排序，限制前 $limit 條記錄
// 假設 self_leaderboard 表有 user_schoolnum、user_name、score 等欄位
$stmt = $conn->prepare("
    SELECT user_schoolnum, user_name, score, total_solved AS levels_cleared 
    FROM self_leaderboard 
    ORDER BY score DESC 
    LIMIT ?
");
if ($stmt === false) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(["error" => "準備語句失敗: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

// 準備返回的數據陣列
$players = [];
$rank = 1;
while ($row = $result->fetch_assoc()) {
    $players[] = [
        "rank" => $rank++,
        "player_name" => htmlspecialchars($row['user_name']),
        "score" => (int)$row['score'],
        "levels_cleared" => (int)$row['levels_cleared']
    ];
}

// 關閉語句和連線
$stmt->close();
$conn->close();

// 返回 JSON 格式數據
header("Content-Type: application/json");
echo json_encode($players);
?>