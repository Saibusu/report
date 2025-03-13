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

// 接收前端發來的答案
$answer = isset($_POST['answer']) ? trim($_POST['answer']) : '';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; // 假設 session 中存儲了用戶的 email

// 檢查用戶是否登入
if (empty($user_email)) {
    echo json_encode(['status' => 'error', 'message' => '請先登入']);
    exit;
}

// 定義正確答案（GCD(2740, 1760) = 20）
$correct_answer = 20;

// 檢查答案是否正確
$response = [];
if ($answer == $correct_answer) {
    // 答案正確，增加 100 分
    $sql = "UPDATE first_week SET score = score + 100 WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // 獲取更新後的分數
        $sql = "SELECT score FROM first_week WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $new_score = $row['score'];

        $response = [
            'status' => 'success',
            'message' => '答案正確！已增加 100 分。',
            'score' => $new_score
        ];
    } else {
        $response = ['status' => 'error', 'message' => '更新分數失敗'];
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