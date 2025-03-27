import { users } from "./UserList.js";

document.addEventListener("DOMContentLoaded", function () {
  const currentPath = window.location.pathname;
  const navLinks = document.querySelectorAll(".sidebar ul li a");

  navLinks.forEach((link) => {
    const linkPath = new URL(link.href, window.location.origin).pathname;
    console.log("linkPath", linkPath);
    console.log("currentPath", currentPath.split(".html")[0]);

    if (currentPath === linkPath) {
      link.classList.add("active");
      link.parentElement.classList.add("active");
    }
  });

  const usersGrid = document.querySelector(".users-grid");
  const searchInput = document.querySelector(".search-box input");
  const tabButtons = document.querySelectorAll(".tabs button");

  let currentTab = "new-users";
  let currentSearchTerm = "";

  // Function to sort and filter users based on current tab and search term
  function getFilteredUsers() {
    let filteredUsers = [...users];

    // Apply search filter if there's a search term
    if (currentSearchTerm) {
      filteredUsers = filteredUsers.filter(
        (user) =>
          user.name.toLowerCase().includes(currentSearchTerm.toLowerCase()) ||
          user.location.toLowerCase().includes(currentSearchTerm.toLowerCase())
      );
    }

    // Apply tab-specific filtering and sorting
    switch (currentTab) {
      case "new-users":
        return filteredUsers.sort((a, b) => b.joinedDate - a.joinedDate);
      case "editors":
        return filteredUsers.filter((user) => user.isEditor);
      case "reputation":
        return filteredUsers.sort((a, b) => b.reputation - a.reputation);
      default:
        return filteredUsers;
    }
  }

  // Function to render users
  function renderUsers() {
    const filteredUsers = getFilteredUsers();
    usersGrid.innerHTML = "";

    if (filteredUsers.length === 0) {
      usersGrid.innerHTML = `
        <div class="no-results">
          <p>No users found</p>
        </div>
      `;
      return;
    }

    filteredUsers.forEach((user) => {
      const userCard = `
        <a href="/user.html?name=${encodeURIComponent(
          user.name
        )}" class="user-card">
          <img src="${user.avatar}" alt="${user.name}" />
          <div class="user-info">
            <h3>${user.name}</h3>
            <div class="location">${user.location}</div>
            <div class="score">
              ${
                currentTab === "reputation"
                  ? `Reputation: ${user.reputation}`
                  : `Score: ${user.score}`
              }
            </div>
            ${user.isEditor ? '<div class="editor-badge">Editor</div>' : ""}
          </div>
        </a>
      `;
      usersGrid.innerHTML += userCard;
    });
  }

  // Tab click handlers
  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      // Remove active class from all buttons
      tabButtons.forEach((btn) => btn.classList.remove("active"));
      // Add active class to clicked button
      button.classList.add("active");

      // Update current tab
      currentTab = button.textContent.toLowerCase().replace(" ", "-");
      renderUsers();
    });
  });

  // Search handler
  searchInput.addEventListener("input", (e) => {
    currentSearchTerm = e.target.value;
    renderUsers();
  });

  // Initial render
  renderUsers();
});
