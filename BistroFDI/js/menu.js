document.addEventListener("DOMContentLoaded", function () {
  var btn = document.getElementById("btnMenu");
  var menu = document.getElementById("desplegable");

  btn.addEventListener("click", function () {
    if (menu.style.display === "block") menu.style.display = "none";
    else menu.style.display = "block";
  });
});