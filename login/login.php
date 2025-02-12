<?php
session_start();
require_once("config.php");  // 確保資料庫連線

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST["student_id"]);
    $password = trim($_POST["password"]);

    // 查詢學號對應的名稱與密碼
    $sql = "SELECT student_id, name, password FROM users WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ⚠️ 使用 password_verify() 來比對加密密碼（如果密碼有加密）
        if ($password === $row["password"]) {  
            $_SESSION["student_id"] = $row["student_id"];
            $_SESSION["username"] = $row["name"];  // ✅ 使用 name 存入 session

            header("Location: welcome.php");
            exit;
        } else {
            $_SESSION["error"] = "學號或密碼錯誤！";
        }
    } else {
        $_SESSION["error"] = "學號或密碼錯誤！";
    }

    // 重新導向回登入頁面，讓錯誤訊息顯示
    header("Location: index.php");
    exit;
}
$conn->close();
?>
