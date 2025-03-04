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
  stopTimer();
  const userCode = document.getElementById("code").value;

  // 在這裡處理表單提交，這只是前端示例
  document.getElementById("user-code-display").textContent = userCode;

  // 模擬輸出結果
  const output = "4a6acd73f528662d13207cf429a42593";
  document.getElementById("user-output").textContent = output;

  // 模擬比對標準答案
  const expectedOutput = "4a6acd73f528662d13207cf429a42593";
  if (output === expectedOutput) {
    document.getElementById("comparison-result").innerHTML = `
          <div class='p-3 bg-green-50 rounded-md border border-green-200'>
            <p class='flex items-center text-green-700'><svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 mr-2' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg><strong>完全符合！</strong></p>
            <p class='mt-2 text-green-600'>你的輸出與標準答案完全一致，獲得滿分！</p>
          </div>
        `;
    document.getElementById("score-display").textContent = "100";
    document.getElementById("score-display").className =
      "text-3xl font-bold text-green-600";
  } else {
    document.getElementById("comparison-result").innerHTML = `
          <div class='p-3 bg-red-50 rounded-md border border-red-200'>
            <p class='flex items-center text-red-700'><svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5 mr-2' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg><strong>不符合！</strong></p>
            <p class='mt-2 text-red-600'>你的輸出與標準答案不一致。請檢查你的解密方法或密鑰是否正確。</p>
          </div>
        `;
    document.getElementById("score-display").textContent = "0";
    document.getElementById("score-display").className =
      "text-3xl font-bold text-red-500";
  }

  // 顯示比對結果區塊
  document.getElementById("result-section").classList.remove("hidden");

  return false; // 阻止表單實際提交
}
