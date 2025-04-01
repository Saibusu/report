<?php
session_start();

// 資料庫連線
$db_host = '140.129.25.130';
$db_user = 'Jay';
$db_pass = 'jay0804';
$db_name = 'test';

function connectDB() {
    global $db_host, $db_user, $db_pass, $db_name;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("資料庫連線失敗: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

$conn = connectDB();

// 檢查用戶是否為 admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !$_SESSION['is_admin']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '只有管理員才能刪除關卡']);
    exit;
}

// 獲取 POST 數據
$level_id = $_POST['level_id'] ?? '';

if (empty($level_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '關卡 ID 為空']);
    exit;
}

// 從資料庫中刪除記錄
$sql = "DELETE FROM answers WHERE level_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $level_id);

if ($stmt->execute()) {
    // 刪除對應的 HTML 檔案
    $filename = __DIR__ . '/level' . $level_id . '.html';
    if (file_exists($filename) && unlink($filename)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => '關卡及檔案刪除成功']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => '關卡從資料庫刪除成功，但檔案刪除失敗']);
    }
} else {
    error_log("Delete failed: " . $stmt->error);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '刪除失敗: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>