document.addEventListener('DOMContentLoaded', function() {
    const createBtn = document.getElementById('createBtn');
    const createModal = document.getElementById('createModal');
    const closeCreateModalBtn = document.querySelector('#createModal .close');
    const createPostForm = document.getElementById('createPost');
    const loadingIndicator = document.getElementById('loadingIndicator');

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
                            <div class="comments">${data.post.Comments.map(comment => `<p><strong><a href="profile.php?userid=${comment.UserID}">${comment.FirstName} ${comment.LastName}</a>:</strong> ${comment.CommentText}</p>`).join('')}</div>
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

    // Infinite scroll implementation
    let page = 1;
    window.addEventListener('scroll', () => {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight) {
            page++;
            loadMorePosts(page);
        }
    });

    function loadMorePosts(page) {
        fetch('fetch_more_posts.php?page=' + page)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const grid = document.querySelector('.grid');
                    data.posts.forEach(post => {
                        const gridItem = document.createElement('div');
                        gridItem.className = 'grid-item';
                        gridItem.setAttribute('data-postid', post.PostID);
                        gridItem.innerHTML = `<img src="${post.MediaURL}" alt="Post Image"><p>${post.Caption}</p><p>by ${post.FirstName} ${post.LastName}</p>`;
                        grid.appendChild(gridItem);

                        // Attach click event listener to new grid item
                        gridItem.addEventListener('click', function() {
                            const postID = this.getAttribute('data-postid');
                            fetchPostDetails(postID);
                        });
                    });
                } else {
                    console.error('Error loading more posts:', data.message);
                    alert('Error loading more posts.');
                }
            })
            .catch(error => console.error('Error loading more posts:', error));
    }

    // Check for new post flag
    if (localStorage.getItem('newPost') === 'true') {
        localStorage.removeItem('newPost');
        location.reload();
    }
});
