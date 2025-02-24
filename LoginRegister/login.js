function toggleForm() {
  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");
  const formTitle = document.getElementById("formTitle");

  if (loginForm.style.display === "none") {
    loginForm.style.display = "block";
    registerForm.style.display = "none";
    formTitle.innerText = "登入";
  } else {
    loginForm.style.display = "none";
    registerForm.style.display = "block";
    formTitle.innerText = "註冊";
  }
}
