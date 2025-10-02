/*common or same used for login and registration pages */

function togglePage() {
  document.getElementById("login-page")?.classList.toggle("hidden");
  document.getElementById("signup-page")?.classList.toggle("hidden");
}



window.togglePassword = function(id, btn) {
    const input = document.getElementById(id);
    if (input) {
        if (input.type === "password") {
            input.type = "text";
            btn.textContent = "🙈"; // 👀 close eye
        } else {
            input.type = "password";
            btn.textContent = "👁"; // 👁 open eye
        }
    }
};