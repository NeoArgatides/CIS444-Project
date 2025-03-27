import { setActive } from "./navigation.js";

document.addEventListener("DOMContentLoaded", function () {
  setActive();
  const postsList = document.querySelector(".posts-list");

  const posts = [
    {
      type: "answer",
      score: 72,
      title:
        "VS Code 1.86 line of code started following me in split at top of screen as I scroll. Why?",
      date: "Jan 6, 2024",
      timestamp: new Date("2024-01-06").getTime(),
      tags: ["visual-studio-code", "user-interface", "scrolling"],
      views: "15k",
    },
    {
      type: "question",
      score: 49,
      title:
        "How can I change when Git commit message length warning appears in VS Code?",
      date: "Feb 9, 2023",
      timestamp: new Date("2023-02-09").getTime(),
      tags: ["git", "visual-studio-code", "git-commit"],
      views: "8.2k",
    },
    {
      type: "answer",
      score: 39,
      title:
        "How to prevent VS Code from deleting the next word characters on IntelliSense auto-completion?",
      date: "Feb 14, 2023",
      timestamp: new Date("2023-02-14").getTime(),
      tags: ["visual-studio-code", "intellisense", "autocomplete"],
      views: "12k",
    },
    {
      type: "article",
      score: 31,
      title:
        "How do I force Visual Studio Code to always use my workspace's version of TypeScript for all projects?",
      date: "Feb 6, 2023",
      timestamp: new Date("2023-02-06").getTime(),
      tags: ["typescript", "visual-studio-code", "workspace"],
      views: "9.5k",
    },
    {
      type: "answer",
      score: 28,
      title: "CMakePresets.json vs CMakeSettings.json vs CMakeLists.txt",
      date: "Jan 18, 2023",
      timestamp: new Date("2023-01-18").getTime(),
      tags: ["cmake", "visual-studio-code", "build-system"],
      views: "7.3k",
    },
    {
      type: "question",
      score: 26,
      title:
        "Some of my workspace Git repositories no longer show up in my VS Code Source Control View. Why?",
      date: "Aug 22, 2023",
      timestamp: new Date("2023-08-22").getTime(),
      tags: ["git", "visual-studio-code", "source-control"],
      views: "5.8k",
    },
  ];

  function getTypeIcon(type) {
    switch (type) {
      case "answer":
        return '<svg width="14" height="14" viewBox="0 0 24 24" fill="#0074cc"><path d="M12 2L2 19h20L12 2zm0 3.8L18.5 17H5.5L12 5.8z"/></svg>';
      case "question":
        return '<svg width="14" height="14" viewBox="0 0 24 24" fill="#8a6d3b"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg>';
      case "article":
        return '<svg width="14" height="14" viewBox="0 0 24 24" fill="#2e7d32"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>';
    }
  }

  let currentFilter = "all";
  let currentSort = "score";

  function filterAndSortPosts() {
    let filteredPosts = [...posts];

    // Apply type filter
    if (currentFilter !== "all") {
      const filterType = currentFilter.replace(/s$/, ""); // Remove trailing 's'
      filteredPosts = filteredPosts.filter((post) => post.type === filterType);
    }

    // Apply sorting
    filteredPosts.sort((a, b) => {
      if (currentSort === "score") {
        return b.score - a.score;
      } else if (currentSort === "newest") {
        return b.timestamp - a.timestamp;
      }
      return 0;
    });

    return filteredPosts;
  }

  function renderPosts() {
    const filteredPosts = filterAndSortPosts();

    if (filteredPosts.length === 0) {
      postsList.innerHTML = `
        <div class="no-posts">
          <p>No ${currentFilter === "all" ? "" : currentFilter} found</p>
        </div>
      `;
      return;
    }

    postsList.innerHTML = filteredPosts
      .map(
        (post) => `
      <div class="post">
        <div class="post-score">
          <div class="number">${post.score}</div>
          <div class="label">votes</div>
        </div>
        <div class="post-content">
          <div class="post-type ${post.type}">
            ${getTypeIcon(post.type)}
            ${post.type.charAt(0).toUpperCase() + post.type.slice(1)}
          </div>
          <a href="#" class="post-title">${post.title}</a>
          <div class="post-meta">
            <span>Posted ${post.date}</span>
            <span>${post.views} views</span>
          </div>
          <div class="post-tags">
            ${post.tags
              .map(
                (tag) => `
              <a href="#" class="post-tag">${tag}</a>
            `
              )
              .join("")}
          </div>
        </div>
      </div>
    `
      )
      .join("");

    if (filteredPosts.length >= 5) {
      postsList.innerHTML += `
        <button class="load-more">Load more posts</button>
      `;
    }
  }

  // Handle tab filter clicks
  const tabButtons = document.querySelectorAll(".tabs button");
  tabButtons.forEach((button) => {
    button.addEventListener("click", function () {
      tabButtons.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      // Update current filter based on button text
      currentFilter = this.textContent.toLowerCase();
      renderPosts();
    });
  });

  // Handle sort clicks
  const sortButtons = document.querySelectorAll(".sort button");
  sortButtons.forEach((button) => {
    button.addEventListener("click", function () {
      sortButtons.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      // Update current sort based on button text
      currentSort = this.textContent.toLowerCase();
      renderPosts();
    });
  });

  // Initial render
  renderPosts();
});
