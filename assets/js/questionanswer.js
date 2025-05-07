import { setActive } from "./navigation.js";
import { questions } from "./QuestionList.js";

document.addEventListener("DOMContentLoaded", function () {
  setActive();
  const questionsList = document.querySelector(".questions-list");
  const navTabs = document.querySelectorAll(".nav-tabs button");
  const filterTags = document.querySelector(".filter-tags");

  // Filter state
  let currentFilters = {
    tab: "newest",
    tags: [],
    user: "anyone",
    sort: "newest",
  };

  function filterQuestions() {
    let filteredQuestions = [...questions];

    // Apply tab filter
    switch (currentFilters.tab.toLowerCase()) {
      case "active":
        filteredQuestions.sort(
          (a, b) => b.lastActivityDate - a.lastActivityDate
        );
        break;
      case "bountied":
        filteredQuestions = filteredQuestions.filter((q) => q.bounty);
        break;
      case "unanswered":
        filteredQuestions = filteredQuestions.filter((q) => q.answers === 0);
        break;
      case "newest":
      default:
        filteredQuestions.sort(
          (a, b) => new Date(b.timeAgo) - new Date(a.timeAgo)
        );
    }

    // Apply tag filters
    if (currentFilters.tags.length > 0) {
      filteredQuestions = filteredQuestions.filter((question) =>
        currentFilters.tags.some((tag) => question.tags.includes(tag))
      );
    }

    return filteredQuestions;
  }

  function renderQuestions() {
    const filteredQuestions = filterQuestions();

    if (filteredQuestions.length === 0) {
      questionsList.innerHTML = `
        <div class="no-results">
          <p>No questions found matching your criteria</p>
        </div>
      `;
      return;
    }

    questionsList.innerHTML = filteredQuestions
      .map(
        (question) => `
            <div class="question-item">
                <div class="question-stats">
                    <div class="stat-group">
                        <span>${question.votes} votes</span>
                    </div>
                    <div class="stat-group">
                        <span>${question.answers} answers</span>
                    </div>
                    <div class="stat-group">
                        <span>${question.views} views</span>
                    </div>
                </div>
                <div class="question-content">
                    <a href="question.php?id=${
                      question.id
                    }" class="question-title">${question.title}</a>
                    <p class="question-excerpt">${question.content}</p>
                    <div class="question-tags">
                        ${question.tags
                          .map((tag) => `<a href="#" class="tag">${tag}</a>`)
                          .join("")}
                    </div>
                    <div class="question-meta">
                        <img src="${
                          question.author.avatar
                        }" alt="" class="user-avatar">
                        <a href="#" class="user-name">${
                          question.author.name
                        }</a>
                        <span class="user-reputation">${
                          question.author.reputation
                        }</span>
                        <span class="timestamp">asked ${question.timeAgo}</span>
                    </div>
                </div>
            </div>
        `
      )
      .join("");

    // Make entire question item clickable while preserving tag clicks
    const questionItems = document.querySelectorAll(".question-item");
    questionItems.forEach((item) => {
      const questionLink = item.querySelector(".question-title").href;

      item.addEventListener("click", (e) => {
        // Don't redirect if clicking on a tag or user link
        if (
          !e.target.classList.contains("tag") &&
          !e.target.classList.contains("user-name") &&
          !e.target.classList.contains("question-tags")
        ) {
          window.location.href = questionLink;
        }
      });
    });
  }

  // Handle tab clicks
  navTabs.forEach((button) => {
    button.addEventListener("click", () => {
      navTabs.forEach((btn) => btn.classList.remove("active"));
      button.classList.add("active");
      currentFilters.tab = button.textContent;
      renderQuestions();
    });
  });

  // Handle tag clicks and removal
  function updateTagsFilter(tag) {
    if (currentFilters.tags.includes(tag)) {
      currentFilters.tags = currentFilters.tags.filter((t) => t !== tag);
    } else {
      currentFilters.tags.push(tag);
    }
    renderFilterTags();
    renderQuestions();
  }

  function renderFilterTags() {
    filterTags.innerHTML = currentFilters.tags
      .map(
        (tag) => `
        <span class="filter-tag">
          ${tag}
          <button class="remove-tag" data-tag="${tag}">×</button>
        </span>
      `
      )
      .join("");

    // Add event listeners to remove buttons
    document.querySelectorAll(".remove-tag").forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();
        updateTagsFilter(button.dataset.tag);
      });
    });
  }

  // Handle tag clicks in questions
  questionsList.addEventListener("click", (e) => {
    if (e.target.classList.contains("tag")) {
      e.preventDefault();
      updateTagsFilter(e.target.textContent);
    }
  });

  // Initial render
  renderQuestions();
});
