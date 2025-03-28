<?php
// 獲取表單資料
$level_id = $_POST['level_id'] ?? '';
$questionTitle = $_POST['questionTitle'] ?? '';
$questionDescription = $_POST['questionDescription'] ?? '';
$hints = $_POST['hints'] ?? '';
$instructions = $_POST['instructions'] ?? '';
$difficulty = $_POST['difficulty'] ?? '簡單';

// 驗證必填欄位
if (empty($level_id) || empty($questionTitle) || empty($questionDescription) || empty($instructions)) {
    header("Location: ../home/home.html?status=error&message=" . urlencode('請填寫所有必填欄位'));
    exit;
}

// 根據 level_id 生成檔案名稱（例如：level short3-1.html）
$filename = __DIR__ . '/level short' . $level_id . '.html';

// 檢查檔案是否已存在
if (file_exists($filename)) {
    header("Location: ../home/home.html?status=error&message=" . urlencode('檔案已存在，請選擇不同的 level_id 或刪除現有檔案'));
    exit;
}

// 確保 levels 目錄存在
$directory = __DIR__;
if (!is_dir($directory)) {
    if (!mkdir($directory, 0755, true)) {
        header("Location: ../home/home.html?status=error&message=" . urlencode('無法創建 levels 目錄'));
        exit;
    }
}

// 動態生成上一關和下一關的連結，並檢查檔案是否存在
$level_parts = explode('-', $level_id);
$chapter = $level_parts[0]; // 例如 "3"
$level_number = intval($level_parts[1]); // 例如 "1"

// 計算上一關和下一關的檔案名稱
$prev_level_file = $level_number > 1 ? __DIR__ . "/level short{$chapter}-" . ($level_number - 1) . ".html" : null;
$next_level_file = __DIR__ . "/level short{$chapter}-" . ($level_number + 1) . ".html";

// 檢查檔案是否存在，決定連結
$prev_level = $prev_level_file && file_exists($prev_level_file) ? "level short{$chapter}-" . ($level_number - 1) . ".html" : "levels.html";
$next_level = file_exists($next_level_file) ? "level short{$chapter}-" . ($level_number + 1) . ".html" : "levels.html";

// 根據難度設置標籤樣式
$difficulty_class = '';
switch ($difficulty) {
    case '簡單':
    case 'Easy':
        $difficulty_class = 'bg-indigo-100 text-indigo-800';
        break;
    case '中等':
    case 'Medium':
        $difficulty_class = 'bg-orange-100 text-orange-800';
        break;
    case '困難':
    case 'Hard':
        $difficulty_class = 'bg-red-100 text-red-800';
        break;
    default:
        $difficulty_class = 'bg-indigo-100 text-indigo-800'; // 預設為藍色
}

// 生成與 level short3-1.html 一致的 HTML 內容
$htmlContent = <<<EOD
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>密碼學闖關遊戲</title>
    <link
      href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css"
      rel="stylesheet"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&family=JetBrains+Mono:wght@400;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="level.css" />
  </head>
  <body
    onload="startTimer()"
    class="text-gray-800 min-h-screen flex flex-col items-center py-6"
  >
    <!-- 導覽列開始 -->
    <nav class="navbar">
      <div class="nav-container">
        <a href="../home/home.html" class="logo">密碼學闖關遊戲</a>
        <div class="nav-links">
          <a href="../user/dashboard.php">用戶</a>
          <a href="../home/home.html">首頁</a>
          <a href="./levels.html">關卡</a>
          <a href="../leaderboard/leaderboard.html">排行榜</a>
        </div>
      </div>
    </nav>

    <header class="w-full max-w-5xl flex flex-col items-center mb-6 mt-20">
      <h1 class="text-4xl font-bold text-indigo-700 mb-2">密碼學闖關遊戲</h1>
    </header>

    <!-- 題目區塊 -->
    <div
      class="w-full max-w-5xl bg-white shadow-lg rounded-lg p-6 mb-6 challenge-card"
    >
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-indigo-700">關卡 #{$level_id}：{$questionTitle}</h2>
        <span
          class="{$difficulty_class} px-3 py-1 rounded-full text-sm font-medium"
          >{$difficulty}</span
        >
      </div>
      <div class="prose max-w-none">
        <p class="text-lg mb-4">
          {$questionDescription}
        </p>

        <div class="bg-gray-50 p-4 rounded-md mb-4">
          <h3 class="text-md font-semibold mb-2">指示：</h3>
          <ul class="list-disc pl-5 space-y-1">
            <li>{$instructions}</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="w-full max-w-5xl bg-white shadow-lg rounded-lg p-6 mb-6">
      <div class="scoreboard mb-5 p-3 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-red-500 mr-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <div id="timer" class="text-red-600">00:00</div>
        </div>
        <div id="score" class="flex items-center text-green-600 font-bold">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-green-500 mr-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          分數: <span class="ml-1">0</span>
        </div>
      </div>

      <form onsubmit="return submitAnswer();" class="w-full">
        <input type="hidden" id="level_id" name="level_id" value="{$level_id}" />
        <label for="code" class="block text-gray-700 text-lg font-bold mb-2"
          >作答區</label
        >
        <textarea
          id="code"
          name="code"
          rows="10"
          class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 mb-4 font-mono"
          placeholder="在這裡輸入你的答案..."
        ></textarea>
        <button
          type="submit"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-200 flex items-center"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 mr-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z"
            />
          </svg>
          提交並驗證
        </button>
      </form>
    </div>

    <div class="w-full max-w-5xl flex justify-between items-center mb-8">
      <a
        href="./{$prev_level}"
        class="flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 mr-2"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>
        返回選關
      </a>
      <a
        href="./{$next_level}"
        class="flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition duration-200"
      >
        下一關
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5 ml-2"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5l7 7-7 7"
          />
        </svg>
      </a>
    </div>

    <script src="level.js"></script>
  </body>
</html>
EOD;

// 儲存檔案
if (file_put_contents($filename, $htmlContent) !== false) { // 修正：添加 $htmlContent 作為第二個參數
    header("Location: ../home/home.html?status=success&message=" . urlencode('關卡生成成功！檔案名稱：level short' . $level_id . '.html'));
} else {
    header("Location: ../home/home.html?status=error&message=" . urlencode('無法寫入檔案'));
}
exit;
?>