import { setActive } from "./navigation.js";

document.addEventListener("DOMContentLoaded", async function () {
  setActive();
  const usersGrid = document.querySelector(".users-grid");
  const searchInput = document.querySelector(".search-box input");
  const tabButtons = document.querySelectorAll(".tabs button");

  let currentTab = "new-users";
  let currentSearchTerm = "";
  let users = [];

  // Fetch users from PHP API
  async function fetchUsers() {
    try {
      const response = await fetch("php/fetch_users.php");
      const data = await response.json();

      if (data.error) {
        usersGrid.innerHTML = `<div class="error">${data.error}</div>`;
        return;
      }

      // Transform the data to match expected structure (if needed)
      users = data.map((user) => ({
        id: user.UserID,
        name: user.Username,
        avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(
          user.Username
        )}&background=random`,
        joinedDate: new Date(user.DateJoined),
        isAdmin: user.Role === "Admin",
        posts: user.PostCount,
      }));

      renderUsers();
    } catch (error) {
      console.error("Error fetching users:", error);
      usersGrid.innerHTML = `<div class="error">Unable to load users.</div>`;
    }
  }

  function getFilteredUsers() {
    let filteredUsers = [...users];

    if (currentSearchTerm) {
      filteredUsers = filteredUsers.filter(
        (user) =>
          user.name.toLowerCase().includes(currentSearchTerm.toLowerCase()) ||
          user.location.toLowerCase().includes(currentSearchTerm.toLowerCase())
      );
    }

    switch (currentTab) {
      case "users":
        return filteredUsers.sort((a, b) => b.joinedDate - a.joinedDate);
      case "admin":
        return filteredUsers.filter((user) => user.isAdmin);
      case "most-posts":
        return filteredUsers.sort((a, b) => b.posts - a.posts);
      default:
        return filteredUsers;
    }
  }

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
        <a href="/user.php?id=${encodeURIComponent(user.id)}" class="user-card">
          <img src="${user.avatar}" alt="${user.name}" />
          <div class="user-info">
            <h3>${user.name}</h3>
            <div class="location">Joined: ${new Date(
              user.joinedDate
            ).toLocaleDateString()}</div>
            <div class="score">
              ${`Posts: ${user.posts}`}
            </div>
            ${user.isAdmin ? '<div class="editor-badge">Admin</div>' : ""}
          </div>
        </a>
      `;
      usersGrid.innerHTML += userCard;
    });
  }

  // Tab click handlers
  tabButtons.forEach((button) => {
    button.addEventListener("click", () => {
      tabButtons.forEach((btn) => btn.classList.remove("active"));
      button.classList.add("active");

      currentTab = button.textContent.toLowerCase().replace(" ", "-");
      renderUsers();
    });
  });

  // Search handler
  searchInput.addEventListener("input", (e) => {
    currentSearchTerm = e.target.value;
    renderUsers();
  });

  // Initial fetch + render
  await fetchUsers();
});
