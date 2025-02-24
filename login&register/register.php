<?php
require_once 'db_connect.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $school_id = $_POST['school_id'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($name) || empty($school_id) || empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "所有欄位都必須填寫"]);
        exit;
    }

    // Basic email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Email 格式不正確"]);
        exit;
    }

    $conn = connectDB();
    if (!$conn) {
        exit; // connectDB() already handles error response
    }

    // Check if school_id or email already exists
    $stmt = $conn->prepare("SELECT school_id, email FROM users WHERE school_id = ? OR email = ?");
    // 將 "s" 改為 "i"，因為 school_id 在資料庫中是 INT
    $stmt->bind_param("i", $school_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing_user = $result->fetch_assoc();
        if ($existing_user['school_id'] === $school_id) {
            echo json_encode(["status" => "error", "message" => "學號已被註冊"]);
        } else if ($existing_user['email'] === $email) {
            echo json_encode(["status" => "error", "message" => "Email 已被註冊"]);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    // **Important Security Note:**
    // In a real application, you should ALWAYS hash the password before storing it.
    // Use password_hash() here.
    // Example:
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    // Then store $hashed_password in the database instead of $password.

    // 調整 bind_param 為 "ssis" (name-string, school_id-integer, email-string, password-string)
    // 並加入 reg_time 欄位和使用 $hashed_password
    $stmt = $conn->prepare("INSERT INTO users (name, school_id, email, password, reg_time) VALUES (?, ?, ?, ?, NOW())");
    //  使用 $hashed_password 取代 $password，並將 "ssss" 改為 "ssis"
    $stmt->bind_param("ssis", $name, $school_id, $email, $hashed_password); //  使用 $hashed_password

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "註冊成功"]);
    } else {
        echo json_encode(["status" => "error", "message" => "註冊失敗: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

} else {
    echo json_encode(["status" => "error", "message" => "請求方式錯誤"]);
}
?>