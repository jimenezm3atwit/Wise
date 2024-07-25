document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('postModal');
    const modalContent = document.getElementById('postDetails');
    const closeModal = document.querySelector('.modal .close');

    document.querySelectorAll('.grid-item').forEach(item => {
        item.addEventListener('click', function() {
            const postID = this.getAttribute('data-postid');
            console.log('Click detected on post ID:', postID); // Debug line
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
        console.log('Fetching details for post ID:', postID); // Debug line
        fetch('fetch_post_details.php?postID=' + postID)
            .then(response => {
                console.log('Response status:', response.status); // Debug line
                return response.json();
            })
            .then(data => {
                console.log('Fetch response data:', data); // Debug line
                if (data.status === 'error') {
                    alert(data.message + ' (Error Code: ' + data.code + ')');
                } else {
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
                }
            })
            .catch(error => {
                console.error('Error fetching post details:', error);
                alert('Error fetching post details.');
            });
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
