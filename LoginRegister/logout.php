<?php
session_start();
header("Content-Type: application/json");

// 清除所有 session 變數
$_SESSION = array();

// 若有使用 session cookies，則銷毀它
if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 42000, "/");
}

// 銷毀 session
session_destroy();

// 回傳登出成功的訊息
echo json_encode(["status" => "success", "message" => "已成功登出"]);
?>
