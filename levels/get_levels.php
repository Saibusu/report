<?php
// 資料庫連線
$db_host = '140.129.25.130';
$db_user = 'Jay';
$db_pass = 'jay0804';
$db_name = 'test';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => '資料庫連線失敗: ' . $conn->connect_error]);
    exit;
}

$conn->set_charset("utf8mb4");

// 啟用輸出緩衝並抑制錯誤顯示
ob_start();

// 查詢所有關卡
$sql = "SELECT level_id, correct_answer, type FROM answers";
$result = $conn->query($sql);

$levels = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $level_parts = explode('-', $row['level_id']);
        $chapter = $level_parts[0];
        $level_num = $level_parts[1];

        if (!isset($levels[$chapter])) {
            $levels[$chapter] = [];
        }

        $levels[$chapter][] = [
            'level' => $level_num,
            'text' => "第{$level_num}關",
            'type' => $row['type'],
            'url' => ($row['type'] === '選擇題' ? "level choice{$row['level_id']}.html" : "level{$row['level_id']}.html")
        ];
    }

    // 按 level 字段排序每個章節的關卡
    foreach ($levels as $chapter => &$chapter_levels) {
        usort($chapter_levels, function($a, $b) {
            return $a['level'] - $b['level'];
        });
    }
    unset($chapter_levels); // 解除引用
} else {
    error_log("Query failed: " . $conn->error);
    $levels = []; // 確保 $levels 為有效陣列，即使查詢失敗
}

ob_end_clean(); // 清除任何可能的輸出緩衝

// 定義章節
$chapters = [
    ['chapter' => '1', 'text' => '第一章'],
    ['chapter' => '2', 'text' => '第二章'],
    ['chapter' => '3', 'text' => '第三章'],
    ['chapter' => '4', 'text' => '第四章'],
    ['chapter' => '5', 'text' => '第五章'],
    ['chapter' => '6', 'text' => '第六章'],
    ['chapter' => '7', 'text' => '第七章'],
    ['chapter' => '8', 'text' => '第八章'],
    ['chapter' => '9', 'text' => '第九章'],
];

header('Content-Type: application/json');
echo json_encode(['chapters' => $chapters, 'levels' => $levels]);

$conn->close();
?>