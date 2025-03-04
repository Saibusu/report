$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'abc369';
$db_name = 'test';

function connectDB() {
    global $db_host, $db_user, $db_pass, $db_name;
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        // 直接退出並顯示錯誤（在非JSON版本中避免使用JSON）
        die("資料庫連線失敗: " . $conn->connect_error);
    }

    // 設置 UTF-8 字符集
    $conn->set_charset("utf8mb4");
    
    return $conn;
}
?>