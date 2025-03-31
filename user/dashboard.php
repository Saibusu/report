<?php
session_start();

// 確保 db_connect.php 包含資料庫連線函數，並呼叫它來建立連線
require '../LoginRegister/db_connect.php';
$conn = connectDB(); // 呼叫 connectDB() 函數並將結果賦值給 $conn

// 如果連線失敗，檢查並處理錯誤
if ($conn === false || $conn->connect_error) {
    die("資料庫連線失敗: " . ($conn->connect_error ?? "未知錯誤"));
}

// 如果未登入，跳轉回 login.php
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

// 假設 $_SESSION["user_id"] 是 school_num（根據圖片和數據）
$school_num = $_SESSION["user_id"];

// 取得使用者資訊（添加 name 欄位，排除 password），使用 school_num 查詢
$stmt = $conn->prepare("SELECT name, team, school_num, email, reg_time FROM users WHERE school_num = ?"); // 添加 name
if ($stmt === false) {
    die("準備語句失敗: " . $conn->error);
}

$stmt->bind_param("i", $school_num);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("未找到與學號 " . htmlspecialchars($school_num) . " 相關的用戶記錄");
}

$stmt->bind_result($name, $team, $school_num, $email, $reg_time); // 添加 name
if (!$stmt->fetch()) {
    die("獲取用戶數據失敗");
}

// 取得排行榜數據（使用 school_num 與 self_leaderboard 關聯）
$leader_stmt = $conn->prepare("SELECT total_solved, total_submissions, score, last_submit_time FROM self_leaderboard WHERE user_schoolnum = ?");
if ($leader_stmt === false) {
    die("準備排行榜語句失敗: " . $conn->error);
}

$leader_stmt->bind_param("i", $school_num); // 使用 school_num 查詢
$leader_stmt->execute();
$leader_stmt->store_result();
$leader_stmt->bind_result($total_solved, $total_submissions, $score, $last_submit_time);
$leader_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用戶控制台</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- 引入 SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans TC', "Microsoft JhengHei", Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #a8edea 0%, #05568b 100%);
            color: #333;
        }

        /* 主要內容區域 - 創新設計 */
        .main-content {
            padding-top: 100px;
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-section {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .header-section h1 {
            color: #fff;
            font-weight: 700;
            font-size: 2.8rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
            letter-spacing: 1px;
        }

        .header-section .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* 玻璃擬態卡片設計 */
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.4s ease;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(90deg, #0a85c2, #3498db);
            color: white;
            padding: 20px;
            position: relative;
        }

        .card-header h2 {
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-header .icon {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .card-body {
            padding: 0;
        }

        /* 創新表格設計 */
        .custom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .custom-table tr:nth-child(even) {
            background-color: rgba(240, 247, 255, 0.5);
        }

        .custom-table tr:nth-child(odd) {
            background-color: rgba(255, 255, 255, 0.7);
        }

        .custom-table td {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .custom-table tr:last-child td {
            border-bottom: none;
        }

        .info-label {
            font-weight: 500;
            color: #05568b;
            width: 40%;
            position: relative;
        }

        .info-label::after {
            content: '';
            position: absolute;
            right: 20px;
            top: 50%;
            width: 1px;
            height: 20px;
            background: rgba(5, 86, 139, 0.2);
            transform: translateY(-50%);
        }

        .info-value {
            color: #444;
        }

        /* 統計數字卡片設計 */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2.5rem;
            color: #0a85c2;
            margin-bottom: 10px;
            background: #f0f7ff;
            width: 70px;
            height: 70px;
            line-height: 70px;
            border-radius: 50%;
            margin: 0 auto 15px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        /* 返回按鈕設計 */
        .action-button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(90deg, #0a85c2, #3498db);
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(10, 133, 194, 0.3);
            position: relative;
            overflow: hidden;
        }

        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(10, 133, 194, 0.4);
        }

        .action-button:active {
            transform: translateY(-1px);
        }

        .action-button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .action-button:hover::after {
            transform: translateX(0);
        }

        .button-container {
            text-align: center;
            margin-top: 40px;
        }

        /* 響應式設計 */
        @media (max-width: 992px) {
            .stats-cards {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }

            .header-section h1 {
                font-size: 2.2rem;
            }

            .header-section .subtitle {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .info-label::after {
                display: none;
            }
            
            .custom-table td {
                display: block;
                width: 100%;
            }
            
            .info-label {
                width: 100%;
                padding-bottom: 5px;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }
            
            .info-value {
                padding-top: 5px;
            }
            
            .stat-value {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <!-- 主要內容區域 -->
    <div class="main-content">
        <div class="dashboard-container">
            <!-- 頭部區域 -->
            <div class="header-section" data-aos="fade-down" data-aos-duration="800">
                <h1>用戶控制台</h1>
                <p class="subtitle">查看您的個人資料和遊戲進度</p>
            </div>
            
            <div class="row">
                <!-- 用戶資訊卡片 -->
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <div class="glass-card">
                        <div class="card-header">
                            <h2><i class="fas fa-user-circle icon"></i> 個人資料</h2>
                        </div>
                        <div class="card-body">
                            <table class="custom-table">
                                <tr>
                                    <td class="info-label">姓名</td>
                                    <td class="info-value"><?php echo htmlspecialchars($name ?? '未設定'); ?></td>
                                </tr>
                                <tr>
                                    <td class="info-label">學號</td>
                                    <td class="info-value"><?php echo htmlspecialchars($school_num ?? '未設定'); ?></td>
                                </tr>
                                <tr>
                                    <td class="info-label">小組名稱</td>
                                    <td class="info-value"><?php echo htmlspecialchars($team ?? '未設定'); ?></td>
                                </tr>
                                <tr>
                                    <td class="info-label">Email</td>
                                    <td class="info-value"><?php echo htmlspecialchars($email ?? '未設定'); ?></td>
                                </tr>
                                <tr>
                                    <td class="info-label">註冊時間</td>
                                    <td class="info-value"><?php echo htmlspecialchars($reg_time ?? '未設定'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- 競賽統計卡片 -->
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                    <div class="glass-card">
                        <div class="card-header">
                            <h2><i class="fas fa-chart-line icon"></i> 競賽統計</h2>
                        </div>
                        <div class="card-body">
                            <div class="stats-cards">
                                <div class="stat-card" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="300">
                                    <div class="stat-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stat-value"><?php echo $total_solved ?? 0; ?></div>
                                    <div class="stat-label">解題數</div>
                                </div>
                                <div class="stat-card" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="400">
                                    <div class="stat-icon">
                                        <i class="fas fa-paper-plane"></i>
                                    </div>
                                    <div class="stat-value"><?php echo $total_submissions ?? 0; ?></div>
                                    <div class="stat-label">提交次數</div>
                                </div>
                                <div class="stat-card" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="500">
                                    <div class="stat-icon">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <div class="stat-value"><?php echo $score ?? 1500; ?></div>
                                    <div class="stat-label">競賽積分</div>
                                </div>
                                <div class="stat-card" data-aos="zoom-in" data-aos-duration="600" data-aos-delay="600">
                                    <div class="stat-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stat-value" style="font-size: 1.2rem; margin-top: 10px;">
                                        <?php echo htmlspecialchars($last_submit_time ?? '尚無提交'); ?>
                                    </div>
                                    <div class="stat-label">最後提交時間</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 返回按鈕 -->
            <div class="button-container" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                <button class="action-button" onclick="goBack()">
                    <i class="fas fa-home me-2"></i> 返回首頁
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // 初始化 AOS 動畫
        AOS.init({
            once: true
        });
        
        function goBack() {
            window.location.href = "../home/home.html";
        }
    </script>

</body>
</html>

<?php
$stmt->close();
$leader_stmt->close();
$conn->close();
?>