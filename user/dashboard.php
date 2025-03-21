<?php
session_start();

// 確保 db_connect.php 包含資料庫連線函數，並呼叫它來建立連線
require '../LoginRegister/db_connect.php';
$conn = connectDB(); // 呼叫 connectDB() 函數並將結果賦值給 $conn

// 如果連線失敗，檢查並處理錯誤
if ($conn === false || $conn->connect_error) {
    die("資料庫連線失敗: " . ($conn->connect_error ?? "未知錯誤"));
}

// 如果未登入，跳轉回 login.php
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// 假設 $_SESSION["user_id"] 是 school_num（根據圖片和數據）
$school_num = $_SESSION["user_id"];

// 取得使用者資訊（添加 name 欄位，排除 password），使用 school_num 查詢
$stmt = $conn->prepare("SELECT name, team, school_num, email, reg_time FROM users WHERE school_num = ?"); // 添加 name
if ($stmt === false) {
    die("準備語句失敗: " . $conn->error);
}

$stmt->bind_param("i", $school_num);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("未找到與學號 " . htmlspecialchars($school_num) . " 相關的用戶記錄");
}

$stmt->bind_result($name, $team, $school_num, $email, $reg_time); // 添加 name
if (!$stmt->fetch()) {
    die("獲取用戶數據失敗");
}

// 取得排行榜數據（使用 school_num 與 self_leaderboard 關聯）
$leader_stmt = $conn->prepare("SELECT total_solved, total_submissions, score, last_submit_time FROM self_leaderboard WHERE user_schoolnum = ?");
if ($leader_stmt === false) {
    die("準備排行榜語句失敗: " . $conn->error);
}

$leader_stmt->bind_param("i", $school_num); // 使用 school_num 查詢
$leader_stmt->execute();
$leader_stmt->store_result();
$leader_stmt->bind_result($total_solved, $total_submissions, $score, $last_submit_time);
$leader_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>個人主頁</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 20px; 
            background-color: white; 
            border-radius: 10px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
            text-align: left;
        }
        h1 { 
            color: #333; 
            margin-bottom: 20px;
            text-align: center;
        }
        p { 
            margin: 10px 0; 
            color: #666;
        }
        .info { 
            margin-top: 20px; 
            border-top: 1px solid #ddd; 
            padding-top: 20px;
        }
        .stats { 
            margin-top: 20px; 
            border-top: 1px solid #ddd; 
            padding-top: 20px;
        }
        .stats p { 
            margin: 5px 0; 
        }
        button { 
            padding: 10px 20px; 
            margin-top: 20px; 
            background-color: #4CAF50; 
            color: white; 
            border: none; 
            cursor: pointer; 
            border-radius: 5px;
        }
        button:hover { 
            background-color: #45a049; 
        }
    </style>
</head>
<body>

<div class="container">
    <h1>歡迎，<?php echo htmlspecialchars($name ?? '用戶名稱'); ?>！</h1> <!-- 更改為使用 name 欄位，預設為「用戶名稱」 -->
    
    <div class="info">
        <p>學號: <?php echo htmlspecialchars($school_num ?? '未設定'); ?></p>
        <p>小組名稱: <?php echo htmlspecialchars($team ?? '未設定'); ?></p>
        <p>Email: <?php echo htmlspecialchars($email ?? '未設定'); ?></p>
        <p>註冊時間: <?php echo htmlspecialchars($reg_time ?? '未設定'); ?></p>
    </div>

    <div class="stats">
        <h2>競賽統計</h2>
        <p>解題數: <?php echo $total_solved ?? 0; ?></p>
        <p>提交次數: <?php echo $total_submissions ?? 0; ?></p>
        <p>競賽積分: <?php echo $score ?? 1500; ?></p>
        <p>最後提交時間: <?php echo htmlspecialchars($last_submit_time ?? '無'); ?></p>
    </div>

    <!-- 返回按鈕 -->
    <button onclick="goBack()">返回首頁</button>
</div>

<script>
    function goBack() {
        window.location.href = "../home/home.html";
    }
</script>

</body>
</html>

<?php
$stmt->close();
$leader_stmt->close();
$conn->close();
?>