document.addEventListener("DOMContentLoaded", () => {
  fetchLeaderboard();
  setInterval(fetchLeaderboard, 10000); // 每 10 秒刷新
});

function fetchLeaderboard() {
  const tbody = document.getElementById("leaderboard-body");
  tbody.classList.add("loading"); // 添加載入狀態
  tbody.innerHTML =
    '<div class="row"><span>載入中...</span><span></span><span></span><span></span></div>';

  fetch("leaderboard.php?limit=10")
    .then((response) => response.json())
    .then((data) => {
      tbody.classList.remove("loading");
      tbody.innerHTML = ""; // 清空
      data.forEach((player) => {
        const row = `
                    <div class="row">
                        <span>${player.rank}</span>
                        <span>${player.player_name}</span>
                        <span>${player.score}</span>
                        <span>${player.levels_cleared}</span>
                    </div>
                `;
        tbody.innerHTML += row;
      });
    })
    .catch((error) => {
      console.error("獲取排行榜失敗:", error);
      tbody.innerHTML =
        '<div class="row"><span>載入失敗</span><span></span><span></span><span></span></div>';
    });
}
