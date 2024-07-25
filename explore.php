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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('postModal');
            const modalContent = document.getElementById('postDetails');
            const closeModal = document.querySelector('.modal .close');

            document.querySelectorAll('.grid-item').forEach(item => {
                item.addEventListener('click', function() {
                    let postID = this.dataset.postid;
                    fetchPostDetails(postID);
                });
            });

            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });

            function fetchPostDetails(postID) {
                fetch('fetch_post_details.php?postID=' + postID)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            let mediaTag = data.MediaURL.endsWith('.mp4') ? `<video controls src="${data.MediaURL}"></video>` : `<img src="${data.MediaURL}" alt="Post Image">`;

                            modalContent.innerHTML = `
                                <div class="post-details">
                                    ${mediaTag}
                                    <div class="caption"><strong>${data.FirstName} ${data.LastName}:</strong> ${data.Caption}</div>
                                    <div class="likes">Likes: ${data.Likes} <button class="like-button" data-postid="${data.PostID}">Like</button></div>
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
                                </div>
                            `;

                            document.getElementById('addCommentForm').addEventListener('submit', function(e) {
                                e.preventDefault();
                                let commentText = this.commentText.value;
                                addComment(postID, commentText);
                            });

                            document.querySelector('.like-button').addEventListener('click', function() {
                                likePost(postID);
                            });

                            modal.style.display = 'block';
                        } else {
                            alert('Error fetching post details.');
                        }
                    })
                    .catch(error => console.error('Error fetching post details:', error));
            }

            function addComment(postID, commentText) {
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
                        fetchPostDetails(postID); // Refresh post details to show the new comment
                    } else {
                        console.error('Error adding comment:', data.message);
                        alert('Error adding comment.');
                    }
                })
                .catch(error => console.error('Error adding comment:', error));
            }

            function likePost(postID) {
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
                        fetchPostDetails(postID); // Refresh post details to show the updated like count
                        showTemporaryPopup('Post liked successfully!');
                    } else {
                        console.error('Error liking post:', data.message);
                        alert('Error liking post.');
                    }
                })
                .catch(error => console.error('Error liking post:', error));
            }

            function showTemporaryPopup(message) {
                const popup = document.createElement('div');
                popup.className = 'temporary-popup';
                popup.textContent = message;
                document.body.appendChild(popup);

                setTimeout(() => {
                    popup.remove();
                }, 2000); // Remove the popup after 2 seconds
            }
        });
    </script>
</body>
</html>
