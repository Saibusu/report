<?php
require_once 'db_connect.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $school_num = $_POST['school_num'];
    $password = $_POST['password'];

    if (empty($school_num) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "學號和密碼不得為空"]);
        exit;
    }

    $conn = connectDB();
    if (!$conn) {
        exit; // connectDB() already handles error response
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE school_id = ?");
    // 將 "s" 改為 "i"，因為 school_id 在資料庫中是 INT
    $stmt->bind_param("i", $school_num);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // **Important Security Note:**
        // In a real application, you should NEVER store plain text passwords.
        // Use password_hash() when registering and password_verify() here.
        if ($password === $user['password']) { // WARNING: Plain text password comparison!
            echo json_encode(["status" => "success", "message" => "登入成功"]);
        } else {
            echo json_encode(["status" => "error", "message" => "密碼錯誤"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "學號不存在"]);
    }

    $stmt->close();
    $conn->close();

} else {
    echo json_encode(["status" => "error", "message" => "請求方式錯誤"]);
}
?>
