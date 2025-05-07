document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("user-search");
  const resultsContainer = document.getElementById("search-results");

  searchInput.addEventListener("input", async () => {
    const query = searchInput.value.trim();

    if (query.length === 0) {
      resultsContainer.innerHTML = "";
      resultsContainer.style.display = "none";
      return;
    }

    try {
      const response = await fetch(
        `/php/header.php?q=${encodeURIComponent(query)}`
      );
      const users = await response.json();

      if (!Array.isArray(users)) {
        resultsContainer.innerHTML = "<div>No results</div>";
        return;
      }

      if (users.length === 0) {
        resultsContainer.innerHTML = "<div>No users found</div>";
        return;
      }

      resultsContainer.innerHTML = users
        .map(
          (user) =>
            `<div onclick="location.href='/user.php?id=${user.UserID}'">${user.Username}</div>`
        )
        .join("");
      resultsContainer.style.display = "block";
    } catch (err) {
      resultsContainer.innerHTML = "<div>Error loading users</div>";
    }
  });
});
