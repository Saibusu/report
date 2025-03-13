<?php
session_start(); // 啟用 session 用來識別用戶

// 引入資料庫連線
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

// 連接到資料庫
$conn = connectDB();

// 接收前端發來的資料
$answer = isset($_POST['answer']) ? trim($_POST['answer']) : '';
$level_id = isset($_POST['level_id']) ? intval($_POST['level_id']) : 0; // 獲取關卡 ID
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; // 使用 school_num 作為用戶 ID

// 檢查用戶是否登入
if (empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => '請先登入']);
    exit;
}

// 檢查關卡 ID 是否有效
if ($level_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => '無效的關卡 ID']);
    exit;
}

// 從 answers 表格中獲取該關卡的正確答案（假設你已經創建了 answers 表格）
$sql = "SELECT correct_answer FROM answers WHERE level_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $level_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => '找不到該關卡的答案']);
    exit;
}

$row = $result->fetch_assoc();
$correct_answer = $row['correct_answer'];

// 檢查答案是否正確
$response = [];
if ($answer === $correct_answer) {
    // 答案正確，增加 100 分到 self_leaderboard
    $sql = "UPDATE self_leaderboard SET score = score + 100 WHERE user_schoolnum = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // user_schoolnum 是 INT 類型
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // 不再查詢總分，直接返回這一題的分數
        $response = [
            'status' => 'success',
            'message' => '答題成功！已增加 100 分。',
            'score' => 100 // 固定返回這一題的分數
        ];
    } else {
        // 如果沒有更新，可能是用戶記錄不存在，插入一條新記錄
        $sql = "INSERT INTO self_leaderboard (user_schoolnum, score) VALUES (?, 100)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response = [
                'status' => 'success',
                'message' => '答題成功！已增加 100 分。',
                'score' => 100 // 固定返回這一題的分數
            ];
        } else {
            $response = ['status' => 'error', 'message' => '更新分數失敗'];
        }
    }
} else {
    // 答案錯誤
    $response = ['status' => 'error', 'message' => '答案錯誤'];
}

// 返回 JSON 結果給前端
header('Content-Type: application/json');
echo json_encode($response);

// 關閉資料庫連線
$conn->close();
?>