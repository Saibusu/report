<?php
session_start();
session_unset();  // 清除所有 session 變數
session_destroy();  // 銷毀 session
header("Location: index.php");
exit;
?>
