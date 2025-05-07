<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Q&A Page</title>
    <link rel="stylesheet" href="./assets/css/qa.css" />
  </head>
  <body>
    <?php include_once 'header.php'; ?>
    <main>
      <div class="sidebar">
        <ul>
          <li>
            <a href="index.php">
              <img
                src="https://img.icons8.com/?size=100&id=67881&format=png&color=000000"
                alt="home"
              />
              <span>Home</span>
            </a>
          </li>
          <li>
            <a href="users.html">
              <img
                src="https://img.icons8.com/?size=100&id=98957&format=png&color=000000"
                alt="users"
              />
              <span>Users</span>
            </a>
          </li>
          <li>
            <a href="questionanswer.html">
              <img
                src="https://img.icons8.com/?size=100&id=2908&format=png&color=000000"
                alt="q&a"
              />
              <span>Q&A</span>
            </a>
          </li>
        </ul>
        <div class="links">
          <a href="#">Privacy Policy</a>
          <a href="#">User agreement</a>
        </div>
      </div>
      <div class="main-content">
        <div class="content">
          <div class="page-header">
            <h1>Newest Questions</h1>
            <div class="stats-nav">
              <span class="question-count">24,254,999 questions</span>
              <div class="nav-tabs">
                <button class="active">Newest</button>
                <button>Active</button>
                <button>Bountied</button>
                <button>Unanswered</button>
              </div>
            </div>
            <div class="filter-section">
              <span class="filter-label">Tags:</span>
              <div class="filter-tags">
                <span class="filter-tag">javascript</span>
                <span class="filter-tag">python</span>
              </div>
              <span class="filter-label">User:</span>
              <span class="filter-value">anyone</span>
              <span class="filter-label">Sorted by:</span>
              <span class="filter-value">Newest</span>
            </div>
            <button class="ask-button">Ask Question</button>
          </div>

          <div class="questions-list">
            <!-- Questions will be populated by JavaScript -->
          </div>

          <div class="pagination">
            <a href="#">Previous</a>
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <span>...</span>
            <a href="#">50</a>
            <a href="#">Next</a>
          </div>
        </div>

        <aside class="side-widgets">
          <div class="widget custom-filters">
            <h2>Custom Filters</h2>
            <a href="#" class="create-filter">Create a custom filter</a>
          </div>

          <div class="widget watched-tags">
            <div class="widget-header">
              <h2>Watched Tags</h2>
              <a href="#" class="edit-link">edit</a>
            </div>
            <div class="tags-list">
              <a href="#" class="tag">android-studio</a>
              <a href="#" class="tag">c++</a>
              <a href="#" class="tag">java</a>
              <a href="#" class="tag">python</a>
            </div>
          </div>

          <div class="widget ignored-tags">
            <h2>Ignored Tags</h2>
            <button class="add-tag-button">Add an ignored tag</button>
          </div>
        </aside>
      </div>
    </main>
    <script type="module" src="./assets/js/questionanswer.js"></script>
  </body>
</html>
