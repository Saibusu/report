<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_num = $_POST['school_num'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($school_num) || empty($password)) {
        header("Location: /LoginRegister/login.html?error=" . urlencode("學號和密碼不得為空"));
        exit;
    }

    $conn = connectDB(); // 這裡調用統一的 connectDB()
    
    // 如果 connectDB() 失敗，會直接 die()，所以這裡不需要額外檢查
    $stmt = $conn->prepare("SELECT * FROM users WHERE school_num = ?");
    $stmt->bind_param("i", $school_num);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) { // 改為明文比較
            session_start();
            $_SESSION['user_id'] = $user['school_num'];
            header("Location: /home/home.html");
        } else {
            header("Location: /LoginRegister/login.html?error=" . urlencode("密碼錯誤"));
        }
    } else {
        header("Location: /LoginRegister/login.html?error=" . urlencode("學號不存在"));
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: /LoginRegister/login.html?error=" . urlencode("請求方式錯誤"));
}
?>