<?php
session_start();
header("Content-Type: application/json"); // 設定回應類型為 JSON
require "db_connect.php"; // 連接資料庫

$response = ["status" => "error", "message" => "請求失敗"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得前端傳來的資料
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // 檢查是否有空值
    if (empty($email) || empty($password)) {
        $response["message"] = "請填寫 Email 和密碼";
        echo json_encode($response);
        exit;
    }

    // 從資料庫查詢該 Email
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    // 如果找到該 Email
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $name, $hashed_password);
        $stmt->fetch();

        // 驗證密碼
        if (password_verify($password, $hashed_password)) {
            // 設定 Session
            $_SESSION["user_id"] = $user_id;
            $_SESSION["name"] = $name;
            $_SESSION["email"] = $email;

            $response["status"] = "success";
            $response["message"] = "登入成功";
            $response["user"] = ["id" => $user_id, "name" => $name, "email" => $email];
        } else {
            $response["message"] = "密碼錯誤";
        }
    } else {
        $response["message"] = "此 Email 未註冊";
    }
    
    $stmt->close();
}

echo json_encode($response);
?>
