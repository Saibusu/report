<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_num = $_POST['school_num'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($school_num) || empty($password)) {
        header("Location: ./login.html?error=" . urlencode("學號和密碼不得為空"));
        exit;
    }

    $conn = connectDB(); // 這裡調用統一的 connectDB()

    // 查詢用戶
    $stmt = $conn->prepare("SELECT * FROM users WHERE school_num = ?");
    $stmt->bind_param("i", $school_num);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // 驗證密碼（目前使用明文比較，建議改用 password_verify）
        if ($password === $user['password']) {
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $user['school_num'];
            // 檢查是否為 admin
            $_SESSION['is_admin'] = isset($user['is_admin']) && $user['is_admin'] == 1;
            header("Location: ../home/home.html");
        } else {
            header("Location: ./login.html?error=" . urlencode("密碼錯誤"));
        }
    } else {
        header("Location: ./login.html?error=" . urlencode("學號不存在"));
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ./login.html?error=" . urlencode("請求方式錯誤"));
}
?>