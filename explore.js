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
                        <div class="likes">Likes: ${data.Likes}</div>
                        <div class="comments">
                            <h4>Comments</h4>
                            ${data.Comments.map(comment => `
                                <p><strong>${comment.FirstName} ${comment.LastName}:</strong> ${comment.Comment}</p>
                            `).join('')}
                        </div>
                    </div>
                `;

                modal.style.display = 'block';
            })
            .catch(error => console.error('Error fetching post details:', error));
    }
});
