/**
 * Comment Actions JavaScript (Sửa và Xóa bình luận dành cho người dùng đã đăng nhập)
 * Theme: CMS Nhóm C
 */

(function() {
    'use strict';

    var config = window.cmsNhomcComment || {
        ajaxUrl: '/wp-admin/admin-ajax.php'
    };

    document.addEventListener('DOMContentLoaded', function() {
        var commentsSection = document.getElementById('comments');
        if (!commentsSection) return;

        // Xử lý sự kiện click bằng event delegation trên container bình luận
        commentsSection.addEventListener('click', function(e) {
            
            // 1. NÚT SỬA BÌNH LUẬN
            var editBtn = e.target.closest('.cms-comment-edit-btn');
            if (editBtn) {
                e.preventDefault();
                var commentId = editBtn.getAttribute('data-comment-id');
                var textEl = document.getElementById('cms-comment-text-' + commentId);
                var formEl = document.getElementById('cms-comment-edit-form-' + commentId);
                var textarea = document.getElementById('cms-comment-textarea-' + commentId);

                if (textEl && formEl) {
                    textEl.style.display = 'none';
                    formEl.style.display = 'block';
                    if (textarea) {
                        textarea.focus();
                        var len = textarea.value.length;
                        textarea.setSelectionRange(len, len);
                    }
                }
                return;
            }

            // 2. NÚT HỦY CHỈNH SỬA
            var cancelBtn = e.target.closest('.cms-btn-cancel-edit');
            if (cancelBtn) {
                e.preventDefault();
                var commentId = cancelBtn.getAttribute('data-comment-id');
                var textEl = document.getElementById('cms-comment-text-' + commentId);
                var formEl = document.getElementById('cms-comment-edit-form-' + commentId);

                if (textEl && formEl) {
                    formEl.style.display = 'none';
                    textEl.style.display = 'block';
                }
                return;
            }

            // 3. NÚT LƯU CHỈNH SỬA
            var saveBtn = e.target.closest('.cms-btn-save-edit');
            if (saveBtn) {
                e.preventDefault();
                var commentId = saveBtn.getAttribute('data-comment-id');
                var nonce = saveBtn.getAttribute('data-nonce');
                var textarea = document.getElementById('cms-comment-textarea-' + commentId);
                var textEl = document.getElementById('cms-comment-text-' + commentId);
                var formEl = document.getElementById('cms-comment-edit-form-' + commentId);

                if (!textarea) return;
                var newContent = textarea.value.trim();
                if (!newContent) {
                    alert('Nội dung bình luận không được để trống!');
                    textarea.focus();
                    return;
                }

                var originalBtnText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang lưu...';

                var formData = new FormData();
                formData.append('action', 'cms_nhomc_edit_comment');
                formData.append('comment_id', commentId);
                formData.append('nonce', nonce);
                formData.append('content', newContent);

                fetch(config.ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(response) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnText;

                    if (response && response.success) {
                        if (textEl) {
                            textEl.innerHTML = response.data.content;
                            formEl.style.display = 'none';
                            textEl.style.display = 'block';
                        }
                    } else {
                        alert((response && response.data && response.data.message) || 'Có lỗi xảy ra khi cập nhật bình luận.');
                    }
                })
                .catch(function(err) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnText;
                    console.error('Lỗi lưu bình luận:', err);
                    alert('Không thể kết nối tới máy chủ. Vui lòng thử lại!');
                });
                return;
            }

            // 4. NÚT XÓA BÌNH LUẬN
            var deleteBtn = e.target.closest('.cms-comment-delete-btn');
            if (deleteBtn) {
                e.preventDefault();
                var commentId = deleteBtn.getAttribute('data-comment-id');
                var nonce = deleteBtn.getAttribute('data-nonce');

                if (!confirm('Bạn có chắc chắn muốn xóa bình luận này? Thao tác này không thể hoàn tác.')) {
                    return;
                }

                var commentItem = document.getElementById('comment-' + commentId);
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

                var formData = new FormData();
                formData.append('action', 'cms_nhomc_delete_comment');
                formData.append('comment_id', commentId);
                formData.append('nonce', nonce);

                fetch(config.ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(response) {
                    if (response && response.success) {
                        // Cập nhật số lượng bình luận ở tiêu đề
                        var titleEl = document.querySelector('.cms-comments-title');
                        if (titleEl && response.data && response.data.count_label) {
                            titleEl.innerHTML = '<i class="fa fa-comments-o"></i> ' + response.data.count_label;
                        }

                        // Hiệu ứng mờ dần và xóa phần tử khỏi DOM
                        if (commentItem) {
                            commentItem.style.transition = 'all 0.3s ease';
                            commentItem.style.opacity = '0';
                            commentItem.style.transform = 'scale(0.95)';
                            setTimeout(function() {
                                commentItem.remove();
                            }, 300);
                        }
                    } else {
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = '<i class="fa fa-trash"></i> Xóa';
                        alert((response && response.data && response.data.message) || 'Có lỗi xảy ra khi xóa bình luận.');
                    }
                })
                .catch(function(err) {
                    deleteBtn.disabled = false;
                    deleteBtn.innerHTML = '<i class="fa fa-trash"></i> Xóa';
                    console.error('Lỗi xóa bình luận:', err);
                    alert('Không thể kết nối tới máy chủ. Vui lòng thử lại!');
                });
                return;
            }

        });
    });
})();
