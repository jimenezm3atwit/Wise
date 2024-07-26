document.addEventListener('DOMContentLoaded', function() {
    const createBtn = document.getElementById('createBtn');
    const createModal = document.getElementById('createModal');
    const closeCreateModalBtn = document.querySelector('#createModal .close');
    const createPostForm = document.getElementById('createPost');

    if (createBtn) {
        createBtn.addEventListener('click', function(event) {
            event.preventDefault();
            createModal.style.display = 'block';
        });
    }

    if (closeCreateModalBtn) {
        closeCreateModalBtn.addEventListener('click', function() {
            createModal.style.display = 'none';
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === createModal) {
            createModal.style.display = 'none';
        }
    });

    createPostForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(createPostForm);

        fetch('upload_post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Post created successfully!');
                createModal.style.display = 'none';
                location.reload();
            } else {
                alert(`Error creating post: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error creating post:', error);
            alert('Error creating post.');
        });
    });

    const modal = document.getElementById('postModal');
    const modalContent = document.getElementById('postDetails');
    const closeModal = document.querySelector('#postModal .close');
    const loadingIndicator = document.getElementById('loadingIndicator');

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
        loadingIndicator.style.display = 'block';
        fetch('fetch_post_details.php?postID=' + postID)
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none';
                if (data.status === 'error') {
                    console.error('Error fetching post details:', data);
                    alert(`Error: ${data.message}`);
                } else {
                    let mediaTag = data.post.MediaURL.endsWith('.mp4') ? 
                        `<video controls src="${data.post.MediaURL}"></video>` : 
                        `<img src="${data.post.MediaURL}" alt="Post Image" class="modal-image">`;

                    modalContent.innerHTML = `
                        <div class="post-details">
                            ${mediaTag}
                            <div class="caption"><strong><a href="profile.php?userid=${data.post.UserID}">${data.post.FirstName} ${data.post.LastName}</a>:</strong> ${data.post.Caption}</div>
                            <div class="likes">Likes: <span id="likeCount">${data.post.Likes}</span> <button class="like-button" id="likeButton">Like</button></div>
                            <div class="comments">${data.comments.map(comment => `<p><strong><a href="profile.php?userid=${comment.UserID}">${comment.FirstName} ${comment.LastName}</a>:</strong> ${comment.CommentText}</p>`).join('')}</div>
                            <div class="add-comment">
                                <input type="text" placeholder="Add a comment..." id="commentText">
                                <button class="btn" id="postCommentBtn">Post</button>
                            </div>
                        </div>
                    `;
                    document.getElementById('postCommentBtn').addEventListener('click', function() {
                        addComment(data.post.PostID, document.getElementById('commentText').value);
                    });
                    document.getElementById('likeButton').addEventListener('click', function() {
                        likePost(data.post.PostID);
                    });
                    modal.style.display = 'block';
                }
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
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
                alert(`Error adding comment: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error adding comment:', error);
            alert('Error adding comment.');
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
                let likeCount = document.getElementById('likeCount');
                if (data.message === 'Like added') {
                    likeCount.textContent = parseInt(likeCount.textContent) + 1;
                    showTemporaryPopup('Post liked successfully!');
                }
            } else {
                console.error('Error liking post:', data.message);
                alert(`Error liking post: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Error liking post:', error);
            alert('Error liking post.');
        });
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

    // Functions to show and close the edit profile modals
    window.showEditModal = function() {
        document.getElementById('edit-modal').style.display = 'block';
    }

    window.closeEditModal = function() {
        document.getElementById('edit-modal').style.display = 'none';
    }

    window.showPhotoUpload = function() {
        closeEditModal();
        document.getElementById('photo-upload-modal').style.display = 'block';
    }

    window.closePhotoUploadModal = function() {
        document.getElementById('photo-upload-modal').style.display = 'none';
    }

    window.showEditAboutMe = function() {
        closeEditModal();
        document.getElementById('about-me-modal').style.display = 'block';
    }

    window.closeAboutMeModal = function() {
        document.getElementById('about-me-modal').style.display = 'none';
    }

    window.submitProfilePhotoForm = function() {
        const fileInput = document.getElementById('profilePhotoUpload');
        if (fileInput.files.length > 0) {
            document.getElementById('uploadProfilePhotoForm').submit();
        } else {
            alert('Please select a file to upload.');
        }
    }

    const followBtn = document.getElementById('followBtn');
    if (followBtn) {
        followBtn.addEventListener('click', function() {
            const followingID = followBtn.getAttribute('data-userid');
            const action = followBtn.textContent.toLowerCase();
            const url = action === 'follow' ? 'follow.php' : 'unfollow.php';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'followingID=' + followingID
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    followBtn.textContent = action === 'follow' ? 'Unfollow' : 'Follow';
                    const followersCount = document.getElementById('followersCount');
                    followersCount.textContent = parseInt(followersCount.textContent) + (action === 'follow' ? 1 : -1);
                } else {
                    alert(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing request.');
            });
        });
    }
});
