<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$userID = $_SESSION['userid'];
$sql = "SELECT FirstName, LastName FROM Users WHERE UserID = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $userID);

if ($stmt->execute() === false) {
    die('Execute failed: ' . htmlspecialchars($stmt->error));
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $firstName = htmlspecialchars($row['FirstName']);
    $lastName = htmlspecialchars($row['LastName']);
} else {
    $firstName = "User";
    $lastName = "";
}

$stmt->close();

// Fetch all posts
$sql = "SELECT P.PostID, P.MediaURL, P.Caption, U.FirstName, U.LastName 
        FROM Posts P 
        JOIN Users U ON P.UserID = U.UserID 
        ORDER BY P.CreatedAt DESC";
$result = $conn->query($sql);

if ($result === false) {
    die('Query failed: ' . htmlspecialchars($conn->error));
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore - WeatherWise</title>
    <link rel="stylesheet" href="explore.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="explore.php">Explore</a></li>
                <li><a href="notifications.php">Notifications</a></li>
                <li><a href="create.php">Create</a></li>
                <li><a href="profile.php">Profile</a></li>
            </ul>
        </aside>
        <div class="main-content">
            <header>
                <h2>Explore</h2>
            </header>
            <div class="grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='grid-item' data-postid='" . $row['PostID'] . "'>";
                        echo "<img src='" . htmlspecialchars($row['MediaURL']) . "' alt='Post Image'>";
                        echo "<div class='post-info'>";
                        echo "<p><strong>" . htmlspecialchars($row['FirstName']) . " " . htmlspecialchars($row['LastName']) . ":</strong> " . htmlspecialchars($row['Caption']) . "</p>";
                        echo "<button class='like-button' data-postid='" . $row['PostID'] . "'>Like</button>";
                        echo "<button class='comment-button' data-postid='" . $row['PostID'] . "'>Comment</button>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No posts to show.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <div id="postModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCreateModal()">&times;</span>
            <div id="postDetails"></div>
        </div>
    </div>

    <script src="explore.js"></script>
    <script>
        function openCreateModal() {
            document.getElementById('postModal').style.display = 'block';
        }

        function closeCreateModal() {
            document.getElementById('postModal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.grid-item').forEach(item => {
                item.addEventListener('click', function() {
                    let postID = this.dataset.postid;
                    fetch('fetch_post_details.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'postID=' + postID
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            let postDetails = document.getElementById('postDetails');
                            postDetails.innerHTML = `
                                <img src="${data.MediaURL}" alt="Post Image">
                                <p><strong>${data.FirstName} ${data.LastName}:</strong> ${data.Caption}</p>
                                <p>Likes: ${data.Likes}</p>
                                <div class="comments">
                                    <h3>Comments</h3>
                                    ${data.Comments.map(comment => `
                                        <p><strong>${comment.FirstName} ${comment.LastName}:</strong> ${comment.CommentText}</p>
                                    `).join('')}
                                </div>
                                <form id="addCommentForm">
                                    <textarea name="commentText" placeholder="Add a comment..." required></textarea>
                                    <button type="submit" class="btn">Submit</button>
                                </form>
                            `;
                            document.getElementById('addCommentForm').addEventListener('submit', function(e) {
                                e.preventDefault();
                                let commentText = this.commentText.value;
                                fetch('add_comment.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: 'postID=' + postID + '&commentText=' + encodeURIComponent(commentText)
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        alert('Comment added successfully!');
                                        // Refresh comments section
                                        fetch('fetch_post_details.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded'
                                            },
                                            body: 'postID=' + postID
                                        })
                                        .then(response => response.json())
                                        .then(updatedData => {
                                            let commentsSection = document.querySelector('.comments');
                                            commentsSection.innerHTML = `
                                                <h3>Comments</h3>
                                                ${updatedData.Comments.map(comment => `
                                                    <p><strong>${comment.FirstName} ${comment.LastName}:</strong> ${comment.CommentText}</p>
                                                `).join('')}
                                            `;
                                        });
                                    } else {
                                        alert('Error adding comment.');
                                    }
                                });
                            });
                            openCreateModal();
                        } else {
                            alert('Error fetching post details.');
                        }
                    });
                });
            });

            document.querySelectorAll('.like-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    let postID = this.dataset.postid;
                    fetch('like_post.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'postID=' + postID
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Post liked successfully!');
                        } else {
                            alert('Error liking post.');
                        }
                    });
                });
            });

            document.querySelectorAll('.comment-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    let postID = this.dataset.postid;
                    let commentText = prompt('Enter your comment:');
                    if (commentText) {
                        fetch('add_comment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'postID=' + postID + '&commentText=' + encodeURIComponent(commentText)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                alert('Comment added successfully!');
                            } else {
                                alert('Error adding comment.');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
