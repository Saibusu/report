let timerInterval;
let timerSeconds = 0;

function startTimer() {
  timerInterval = setInterval(function () {
    timerSeconds++;
    const minutes = Math.floor(timerSeconds / 60);
    const seconds = timerSeconds % 60;
    document.getElementById("timer").textContent = `${minutes
      .toString()
      .padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
  }, 1000);
}

function stopTimer() {
  clearInterval(timerInterval);
}

function submitAnswer() {
  stopTimer(); // 停止計時器
  const userAnswer = document.getElementById("code").value.trim(); // 獲取用戶輸入
  const levelId = document.getElementById("level_id").value; // 獲取關卡 ID

  // 清除之前的提交紀錄
  const existingMessages = document.querySelectorAll("form .bg-green-50, form .bg-red-50");
  existingMessages.forEach(message => message.remove());
  document.getElementById("score").innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    分數: <span class="ml-1">0</span>
  `;

  // 使用 AJAX 將答案和關卡 ID 發送到後端
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "submit_answer.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4 && xhr.status === 200) {
      const response = JSON.parse(xhr.responseText);

      if (response.status === "success") {
        // 答案正確，停止計時器（已在 submitAnswer 開頭執行）
        // 顯示這一題的分數（100 分）
        document.getElementById("score").innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          分數: <span class="ml-1">100</span>
        `;
        // 顯示「答題成功」訊息
        const successMessage = document.createElement("div");
        successMessage.className = "p-3 bg-green-50 rounded-md border border-green-200 mt-4";
        successMessage.innerHTML = `
          <p class="flex items-center text-green-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <strong>答題成功！</strong>
          </p>
          <p class="mt-2 text-green-600">已增加 100 分！</p>
        `;
        document.querySelector("form").appendChild(successMessage);
      } else {
        // 答案錯誤，計時器繼續運行（不重啟）
        // 顯示錯誤訊息
        const errorMessage = document.createElement("div");
        errorMessage.className = "p-3 bg-red-50 rounded-md border border-red-200 mt-4";
        errorMessage.innerHTML = `
          <p class="flex items-center text-red-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <strong>答案錯誤！</strong>
          </p>
          <p class="mt-2 text-red-600">${response.message}</p>
        `;
        document.querySelector("form").appendChild(errorMessage);
      }
    }
  };

  // 發送答案和關卡 ID 到後端
  const data = "answer=" + encodeURIComponent(userAnswer) + "&level_id=" + encodeURIComponent(levelId);
  xhr.send(data);

  return false; // 阻止表單實際提交
}