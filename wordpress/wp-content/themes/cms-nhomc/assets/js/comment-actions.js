/**
 * Comment Actions JavaScript (Sửa và Xóa bình luận dành cho người dùng đã đăng nhập)
 * Theme: CMS Nhóm C
 */

(function() {
    'use strict';

    var config = window.cmsNhomcComment || {
        ajaxUrl: '/wp-admin/admin-ajax.php'
    };

    // Biến lưu trạng thái xóa bình luận
    var pendingCommentId = null;
    var pendingNonce = null;
    var pendingDeleteBtn = null;

    /**
     * Tạo hoặc lấy modal xác nhận xóa ở giữa màn hình
     */
    function getOrCreateDeleteModal() {
        var modal = document.getElementById('cms-delete-confirm-modal');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'cms-delete-confirm-modal';
        modal.className = 'cms-modal-backdrop';
        modal.innerHTML = 
            '<div class="cms-modal-dialog">' +
                '<div class="cms-modal-icon"><i class="fa fa-exclamation-triangle"></i></div>' +
                '<h5 class="cms-modal-title">Xác nhận xóa bình luận</h5>' +
                '<div class="cms-modal-body">' +
                    '<p>Bạn có chắc chắn muốn xóa bình luận này không?<br>Thao tác này không thể hoàn tác.</p>' +
                '</div>' +
                '<div class="cms-modal-footer">' +
                    '<button type="button" class="btn btn-secondary cms-modal-btn-cancel">Hủy</button>' +
                    '<button type="button" class="btn btn-danger cms-modal-btn-confirm"><i class="fa fa-trash"></i> Xóa vĩnh viễn</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);

        // Đóng modal khi bấm Hủy hoặc bấm ra ngoài màn che
        modal.querySelector('.cms-modal-btn-cancel').addEventListener('click', function() {
            closeDeleteModal();
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeDeleteModal();
            }
        });

        // Xử lý khi bấm nút "Xóa vĩnh viễn" trong modal
        modal.querySelector('.cms-modal-btn-confirm').addEventListener('click', function() {
            if (!pendingCommentId || !pendingNonce) return;

            var confirmBtn = this;
            var originalBtnHtml = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xóa...';

            var commentItem = document.getElementById('comment-' + pendingCommentId);

            var formData = new FormData();
            formData.append('action', 'cms_nhomc_delete_comment');
            formData.append('comment_id', pendingCommentId);
            formData.append('nonce', pendingNonce);

            fetch(config.ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(response) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalBtnHtml;
                closeDeleteModal();

                if (response && response.success) {
                    // Cập nhật số lượng bình luận ở tiêu đề
                    var titleEl = document.querySelector('.cms-comments-title');
                    if (titleEl && response.data && response.data.count_label) {
                        titleEl.innerHTML = '<i class="fa fa-comments-o"></i> ' + response.data.count_label;
                    }

                    // Hiệu ứng mờ dần và xóa khỏi DOM
                    if (commentItem) {
                        commentItem.style.transition = 'all 0.3s ease';
                        commentItem.style.opacity = '0';
                        commentItem.style.transform = 'scale(0.95)';
                        setTimeout(function() {
                            commentItem.remove();
                        }, 300);
                    }
                } else {
                    alert((response && response.data && response.data.message) || 'Có lỗi xảy ra khi xóa bình luận.');
                }
            })
            .catch(function(err) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalBtnHtml;
                closeDeleteModal();
                console.error('Lỗi xóa bình luận:', err);
                alert('Không thể kết nối tới máy chủ. Vui lòng thử lại!');
            });
        });

        return modal;
    }

    function openDeleteModal(commentId, nonce, deleteBtn) {
        pendingCommentId = commentId;
        pendingNonce = nonce;
        pendingDeleteBtn = deleteBtn;

        var modal = getOrCreateDeleteModal();
        modal.style.display = 'flex';
        // Buộc trình duyệt reflow để chạy transition CSS mượt mà
        void modal.offsetWidth;
        modal.classList.add('active');
    }

    function closeDeleteModal() {
        var modal = document.getElementById('cms-delete-confirm-modal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(function() {
                modal.style.display = 'none';
            }, 200);
        }
        pendingCommentId = null;
        pendingNonce = null;
        pendingDeleteBtn = null;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var commentsSection = document.getElementById('comments');
        if (!commentsSection) return;

        // Xử lý sự kiện click bằng event delegation trên container bình luận
        commentsSection.addEventListener('click', function(e) {
            
            // 1. NÚT SỬA BÌNH LUẬN -> MỞ Ô NHẬP TRỰC TIẾP (CHO PHÉP MỞ CÙNG LÚC)
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

            // 3. NÚT LƯU CHỈNH SỬA -> BÊN NÀO BẤM SAU SẼ BÁO LỖI
            var saveBtn = e.target.closest('.cms-btn-save-edit');
            if (saveBtn) {
                e.preventDefault();
                var commentId = saveBtn.getAttribute('data-comment-id');
                var nonce = saveBtn.getAttribute('data-nonce');
                var versionHash = saveBtn.getAttribute('data-version-hash') || '';
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
                formData.append('version_hash', versionHash);

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
                        if (response.data && response.data.new_version_hash) {
                            saveBtn.setAttribute('data-version-hash', response.data.new_version_hash);
                        }
                    } else {
                        // NẾU BÊN KHÁC ĐÃ LƯU TRƯỚC ĐÓ -> HIỆN THÔNG BÁO LỖI NGAY TẠI ĐÂY
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

            // 4. NÚT XÓA BÌNH LUẬN -> MỞ MODAL XÁC NHẬN Ở GIỮA MÀN HÌNH
            var deleteBtn = e.target.closest('.cms-comment-delete-btn');
            if (deleteBtn) {
                e.preventDefault();
                var commentId = deleteBtn.getAttribute('data-comment-id');
                var nonce = deleteBtn.getAttribute('data-nonce');

                // Mở modal xác nhận giữa màn hình thay cho popup confirm() mặc định của trình duyệt
                openDeleteModal(commentId, nonce, deleteBtn);
                return;
            }

        });
    });
})();
