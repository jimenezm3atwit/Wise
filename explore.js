document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('postModal');
    const modalContent = document.getElementById('postDetails');
    const closeModal = document.querySelector('.modal .close');

    document.querySelectorAll('.grid-item').forEach(item => {
        item.addEventListener('click', function() {
            const postID = this.getAttribute('data-postid');
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
                let mediaTag = '';
                if (data.MediaURL.endsWith('.mp4')) {
                    mediaTag = `<video controls src="${data.MediaURL}"></video>`;
                } else {
                    mediaTag = `<img src="${data.MediaURL}" alt="Post Image">`;
                }

                modalContent.innerHTML = `
                    <div class="post-details">
                        ${mediaTag}
                        <div class="caption"><strong>${data.FirstName} ${data.LastName}:</strong> ${data.Caption}</div>
                        <div class="likes">Likes: ${data.Likes} <button class="like-button" data-postid="${data.PostID}">Like</button></div>
                        <div class="comments">
                            <h4>Comments</h4>
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

                modal.style.display = 'block';

                document.querySelector('.like-button').addEventListener('click', function() {
                    likePost(postID);
                });
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
                showPopupMessage('Error adding comment.', 'error');
            }
        })
        .catch(error => {
            console.error('Error adding comment:', error);
            showPopupMessage('Error adding comment.', 'error');
        });
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
                showPopupMessage('Post liked successfully!', 'success');
            } else {
                console.error('Error liking post:', data.message);
                showPopupMessage('Error liking post.', 'error');
            }
        })
        .catch(error => {
            console.error('Error liking post:', error);
            showPopupMessage('Error liking post.', 'error');
        });
    }

    function showPopupMessage(message, type) {
        const popup = document.createElement('div');
        popup.className = `popup-message ${type}`;
        popup.innerText = message;
        document.body.appendChild(popup);

        setTimeout(() => {
            popup.remove();
        }, 3000);
    }
});
