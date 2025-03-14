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
$level_id = isset($_POST['level_id']) ? $_POST['level_id'] : ''; // VARCHAR 不需要轉成 int
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; // 使用 school_num 作為用戶 ID

// 檢查用戶是否登入
if (empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => '請先登入']);
    exit;
}

// 檢查關卡 ID 是否有效
if (empty($level_id)) {
    echo json_encode(['status' => 'error', 'message' => '無效的關卡 ID']);
    exit;
}

// 從 answers 表格中獲取該關卡的正確答案
$sql = "SELECT correct_answer FROM answers WHERE level_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $level_id); // level_id 是 VARCHAR，使用 "s"
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => '找不到該關卡的答案']);
    exit;
}

$row = $result->fetch_assoc();
$correct_answer = $row['correct_answer'];

// 查詢當前用戶的統計數據
$sql = "SELECT completed_levels, total_solved, total_submissions FROM self_leaderboard WHERE user_schoolnum = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // user_id 是 INT，使用 "i"
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$completed_levels = $row['completed_levels'] ?? '';
$total_solved = $row['total_solved'] ?? 0;
$total_submissions = $row['total_submissions'] ?? 0;

// 每次提交都增加 total_submissions
$new_total_submissions = $total_submissions + 1;

// 檢查答案是否正確
$response = [];
if ($answer === $correct_answer) {
    if (strpos($completed_levels, "level_$level_id") !== false) {
        $response = ['status' => 'error', 'message' => '你已經完成該關卡，不能重複加分'];
    } else {
        // 更新分數、完成狀態、總答對題數和總提交次數
        $sql = "UPDATE self_leaderboard SET score = score + 100, completed_levels = CONCAT(completed_levels, ',level_$level_id'), total_solved = ?, total_submissions = ? WHERE user_schoolnum = ?";
        $stmt = $conn->prepare($sql);
        $new_total_solved = $total_solved + 1;
        $stmt->bind_param("iii", $new_total_solved, $new_total_submissions, $user_id); // 三個參數都是 INT，使用 "iii"
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $response = [
                'status' => 'success',
                'message' => '答題成功！已增加 100 分。',
                'score' => 100 // 固定返回這一題的分數
            ];
        } else {
            // 如果沒有更新，可能是用戶記錄不存在，插入一條新記錄
            $sql = "INSERT INTO self_leaderboard (user_schoolnum, score, completed_levels, total_solved, total_submissions) VALUES (?, 100, ?, 1, ?)";
            $initial_completed_levels = "level_$level_id";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isi", $user_id, $initial_completed_levels, $new_total_submissions); // user_schoolnum 是 INT ("i")，completed_levels 是字串 ("s")，total_submissions 是 INT ("i")
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
    }
} else {
    // 答案錯誤，只更新 total_submissions
    $sql = "UPDATE self_leaderboard SET total_submissions = ? WHERE user_schoolnum = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_total_submissions, $user_id); // 兩個參數都是 INT，使用 "ii"
    $stmt->execute();

    $response = ['status' => 'error', 'message' => '答案錯誤'];
}

// 返回 JSON 結果給前端
header('Content-Type: application/json');
echo json_encode($response);

// 關閉資料庫連線
$conn->close();
?>