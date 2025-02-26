<?php
session_start();
require "db_connect.php"; // 連接資料庫

// 如果未登入，跳轉回 login.php
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// 取得使用者資訊
$user_id = $_SESSION["user_id"];
$stmt = $conn->prepare("SELECT id, name, email, reg_time FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id, $name, $email, $reg_time);
$stmt->fetch();

// 取得排行榜數據
$leader_stmt = $conn->prepare("SELECT total_solved, total_submissions, score FROM leaderboard WHERE user_id = ?");
$leader_stmt->bind_param("i", $user_id);
$leader_stmt->execute();
$leader_stmt->store_result();
$leader_stmt->bind_result($total_solved, $total_submissions, $score);
$leader_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>個人主頁</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; box-shadow: 2px 2px 10px #aaa; }
        h1 { color: #333; }
        .info { text-align: left; margin-top: 20px; }
        button { padding: 10px 20px; margin-top: 20px; background-color: red; color: white; border: none; cursor: pointer; }
        button:hover { background-color: darkred; }
    </style>
</head>
<body>

<div class="container">
    <h1>歡迎, <?php echo htmlspecialchars($name); ?>！</h1>
    <p>Email: <?php echo htmlspecialchars($email); ?></p>
    <p>註冊時間: <?php echo htmlspecialchars($reg_time); ?></p>

    <div class="info">
        <h2>競賽統計</h2>
        <p>解題數: <?php echo $total_solved ?? 0; ?></p>
        <p>提交次數: <?php echo $total_submissions ?? 0; ?></p>
        <p>競賽積分: <?php echo $score ?? 1500; ?></p>
    </div>

    <button onclick="logout()">登出</button>
</div>

<script>
    function logout() {
        fetch("logout.php", { method: "POST" })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("登出成功！");
                    window.location.href = "login.php";
                }
            });
    }
</script>

</body>
</html>

<?php
$stmt->close();
$leader_stmt->close();
$conn->close();
?>
