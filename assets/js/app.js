document.addEventListener('click', async (e) => {
    const voteBtn = e.target.closest('[data-comment-vote]');
    if (voteBtn) {
        e.preventDefault();
        const commentId = voteBtn.dataset.commentId;
        const voteType = voteBtn.dataset.commentVote;

        const formData = new FormData();
        formData.append('csrf_token', window.VideoShare.csrfToken);
        formData.append('comment_id', commentId);
        formData.append('vote', voteType);

        const res = await fetch(window.VideoShare.baseUrl + 'ajax/vote_comment.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            const wrapper = voteBtn.closest('.comment-votes');
            wrapper.querySelector('.upvotes').textContent = data.upvotes;
            wrapper.querySelector('.downvotes').textContent = data.downvotes;
        } else {
            alert(data.message || 'Vote failed');
        }
    }

    const replyBtn = e.target.closest('[data-reply-toggle]');
    if (replyBtn) {
        const target = document.getElementById(replyBtn.dataset.replyToggle);
        if (target) {
            target.classList.toggle('d-none');
        }
    }
});

document.addEventListener('submit', async (e) => {
    const form = e.target.closest('[data-ajax-comment-form]');
    if (!form) return;

    e.preventDefault();
    const formData = new FormData(form);
    formData.append('csrf_token', window.VideoShare.csrfToken);

    const res = await fetch(window.VideoShare.baseUrl + 'ajax/comment.php', {
        method: 'POST',
        body: formData
    });

    const data = await res.json();
    if (data.success) {
        window.location.reload();
    } else {
        alert(data.message || 'Could not save comment');
    }
});
