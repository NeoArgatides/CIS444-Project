<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Profile</title>
    <link rel="stylesheet" href="./assets/css/user.css" />
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
      <div class="page">
        <div class="user-profile">
          <div class="profile-header">
            <img
              src="https://github.com/identicons/starball.png"
              alt="starball"
              class="avatar"
            />
            <div class="profile-info">
              <h1>starball</h1>
              <div class="member-info">
                <span>Member for 6 years</span>
                <span>Last seen this week</span>
              </div>
              <div class="stats">
                <div class="stat">
                  <div class="number">~5.7m</div>
                  <div class="label">people reached</div>
                </div>
                <div class="stat">
                  <div class="number">9,824</div>
                  <div class="label">posts edited</div>
                </div>
                <div class="stat">
                  <div class="number">89,503</div>
                  <div class="label">helpful flags</div>
                </div>
                <div class="stat">
                  <div class="number">28,651</div>
                  <div class="label">votes cast</div>
                </div>
              </div>
            </div>
          </div>

          <div class="about-section">
            <h2>About</h2>
            <p>
              Generally happy to chat. Feel free to ping <a href="#">me in</a> /
              <a href="#">invite me</a> to <a href="#">The Meta Room</a>.
            </p>

            <div class="vote-info">
              <h3>Remember to vote!</h3>
              <p><a href="#">Voting is a pillar of this system</a>.</p>
              <p>Did you find something useful* to you? Upvote it!</p>
              <p>Was something actively not useful to you? Downvote it.</p>
              <p class="note">
                Even if you haven't unlocked full voting
                <a href="#">privileges</a>, still, cast your votes (the system
                keeps track of them).
              </p>
            </div>
          </div>

          <div class="posts-section">
            <div class="posts-header">
              <h2>Top posts</h2>
              <div class="view-links">
                View all <a href="#">questions</a>, <a href="#">answers</a>, and
                <a href="#">articles</a>
              </div>
              <div class="filters">
                <div class="tabs">
                  <button class="active">All</button>
                  <button>Questions</button>
                  <button>Answers</button>
                  <button>Articles</button>
                </div>
                <div class="sort">
                  <button class="active">Score</button>
                  <button>Newest</button>
                </div>
              </div>
            </div>
            <div class="posts-list">
              <!-- Posts will be populated by JavaScript -->
            </div>
          </div>
        </div>
      </div>
    </main>
    <script type="module" src="./assets/js/user.js"></script>
  </body>
</html>
