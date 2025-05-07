// Check login status when page loads
document.addEventListener("DOMContentLoaded", () => {
  checkLoginStatus();
  loadPosts();
  setupEventListeners();
});

function checkLoginStatus() {
  // Check if user is logged in
  const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
  const createPostBtn = document.getElementById("create-post-btn");

  if (isLoggedIn) {
    createPostBtn.classList.remove("hidden");
  } else {
    createPostBtn.classList.add("hidden");
  }
}

function setupEventListeners() {
  // Search functionality
  const searchButton = document.getElementById("search-button");
  const searchInput = document.getElementById("search-input");

  searchButton.addEventListener("click", () => {
    const searchTerm = searchInput.value.trim().toLowerCase();
    searchPosts(searchTerm);
  });

  // Search on Enter key
  searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      const searchTerm = searchInput.value.trim().toLowerCase();
      searchPosts(searchTerm);
    }
  });

  // Create post button
  const createPostBtn = document.getElementById("create-post-btn");
  createPostBtn.addEventListener("click", () => {
    window.location.href = "create-post.php";
  });
}

async function loadPosts() {
  try {
    //const response = await fetch('/api/posts');
    //const posts = await response.json();
    //displayPosts(posts);
  } catch (error) {
    //console.error('Error loading posts:', error);
    //showError('Failed to load posts. Please try again later.');
  }
}

function displayPosts(posts) {
  const postsContainer = document.getElementById("posts-container");
  postsContainer.innerHTML = ""; // Clear existing posts

  posts.forEach((post) => {
    const postElement = createPostElement(post);
    postsContainer.appendChild(postElement);
  });
}

function createPostElement(post) {
  const article = document.createElement("article");
  article.className = "post";

  article.innerHTML = `
        <h3>${post.title}</h3>
        <p class="post-meta">Posted by ${post.author} on ${new Date(
    post.date
  ).toLocaleDateString()}</p>
        <p class="post-excerpt">${
          post.excerpt || post.content.substring(0, 150)
        }...</p>
        <a href="/post.php?id=${post.id}" class="read-more">Read More</a>
    `;

  return article;
}

async function searchPosts(searchTerm) {
  try {
    //const response = await fetch(`/api/posts/search?q=${encodeURIComponent(searchTerm)}`);
    //const posts = await response.json();
    //displayPosts(posts);
  } catch (error) {
    //console.error('Error searching posts:', error);
    //showError('Failed to search posts. Please try again later.');
  }
}

function showError(message) {
  alert(message);
}
