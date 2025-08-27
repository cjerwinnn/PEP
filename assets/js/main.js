// add hovered class to selected list item
let list = document.querySelectorAll(".navigation li");

function activeLink() {
  list.forEach((item) => {
    item.classList.remove("hovered");
  });
  this.classList.add("hovered");
}

list.forEach((item) => item.addEventListener("mouseover", activeLink));

// Menu Toggle
let toggle = document.querySelector(".toggle");
let navigation = document.querySelector(".navigation");
let main = document.querySelector(".main");

// Restore state from localStorage on page load
if (localStorage.getItem("menuActive") === "true") {
  navigation.classList.add("active");
  main.classList.add("active");
}

// Toggle and save state
toggle.onclick = function () {
  navigation.classList.toggle("active");
  main.classList.toggle("active");

  // Save state
  localStorage.setItem("menuActive", navigation.classList.contains("active"));
};


// Handle logout to clear chat interval
const logoutLink = document.getElementById('logout-link');
if (logoutLink) {
  logoutLink.addEventListener('click', function (e) {
    e.preventDefault(); // Stop the link from redirecting immediately

    // Clear the chat refresh interval if it exists
    if (typeof inboxRefreshInterval !== 'undefined' && inboxRefreshInterval) {
      clearInterval(inboxRefreshInterval);
      inboxRefreshInterval = null; // Clean up the variable
    }

    // Now, proceed to the logout page
    window.location.href = this.href;
  });
}
