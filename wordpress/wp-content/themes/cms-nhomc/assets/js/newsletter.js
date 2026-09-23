/**
 * ==========================================================================
 * (20) NEWSLETTER SUBSCRIPTION JAVASCRIPT
 * Kiến trúc: Vanilla JS, Ponytail Standard, Không phụ thuộc thư viện ngoài
 * Xử lý: AJAX Form Submit, Anti-spam, Validation client-side & Feedback
 * ==========================================================================
 */

(function () {
  'use strict';

  // Khởi tạo sau khi DOM đã sẵn sàng
  document.addEventListener('DOMContentLoaded', initNewsletterForms);

  function initNewsletterForms() {
    var forms = document.querySelectorAll('.cms-newsletter-form');
    if (!forms || forms.length === 0) return;

    forms.forEach(function (form) {
      // Tránh gắn trùng listener nếu khởi tạo nhiều lần
      if (form.getAttribute('data-newsletter-initialized') === 'true') {
        return;
      }
      form.setAttribute('data-newsletter-initialized', 'true');
      setupFormHandler(form);
    });
  }

  function setupFormHandler(form) {
    var input = form.querySelector('.cms-newsletter-input');
    var button = form.querySelector('.cms-newsletter-btn');
    var btnText = form.querySelector('.cms-newsletter-btn-text');
    var btnSpinner = form.querySelector('.cms-newsletter-btn-spinner');
    var messageBox = form.querySelector('.cms-newsletter-message');

    if (!input || !button || !messageBox) return;

    var originalBtnText = btnText ? btnText.textContent : 'Đăng ký';
    var isSubmitting = false;

    // Reset thông báo lỗi khi người dùng bắt đầu gõ lại
    input.addEventListener('input', function () {
      if (messageBox.classList.contains('is-error')) {
        hideMessage(messageBox);
      }
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      if (isSubmitting) return;

      var rawValue = input.value;
      var email = rawValue ? rawValue.trim() : '';

      // 1. Kiểm tra rỗng
      if (!email) {
        showMessage(messageBox, 'Vui lòng nhập địa chỉ email.', 'error');
        input.focus();
        return;
      }

      // 2. Kiểm tra định dạng cơ bản client-side
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        showMessage(messageBox, 'Vui lòng nhập địa chỉ email hợp lệ.', 'error');
        input.focus();
        return;
      }

      // 3. Chuẩn bị gửi AJAX
      isSubmitting = true;
      button.disabled = true;
      button.classList.add('is-loading');
      if (btnText) btnText.textContent = 'Đang đăng ký...';
      if (btnSpinner) btnSpinner.style.display = 'inline-flex';
      hideMessage(messageBox);

      var ajaxUrl = (window.cmsNhomcNewsletter && window.cmsNhomcNewsletter.ajaxUrl) 
        ? window.cmsNhomcNewsletter.ajaxUrl 
        : (form.getAttribute('action') || '/wp-admin/admin-ajax.php');

      var nonce = (window.cmsNhomcNewsletter && window.cmsNhomcNewsletter.nonce)
        ? window.cmsNhomcNewsletter.nonce
        : (form.querySelector('input[name="nonce"]') ? form.querySelector('input[name="nonce"]').value : '');

      var formData = new URLSearchParams();
      formData.append('action', 'cms_nhomc_newsletter_subscribe');
      formData.append('nonce', nonce);
      formData.append('email', email);

      // Gửi request bằng Fetch API native
      fetch(ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData.toString()
      })
        .then(function (response) {
          return response.json().catch(function () {
            throw new Error('Invalid JSON response');
          });
        })
        .then(function (res) {
          if (res && res.success) {
            showMessage(messageBox, res.data.message || 'Đăng ký nhận bản tin thành công!', 'success');
            input.value = ''; // Reset ô nhập
          } else {
            var errorMsg = (res && res.data && res.data.message) 
              ? res.data.message 
              : 'Không thể hoàn tất đăng ký. Vui lòng thử lại sau.';
            showMessage(messageBox, errorMsg, 'error');
          }
        })
        .catch(function () {
          showMessage(messageBox, 'Không thể hoàn tất đăng ký. Vui lòng thử lại sau.', 'error');
        })
        .finally(function () {
          isSubmitting = false;
          button.disabled = false;
          button.classList.remove('is-loading');
          if (btnText) btnText.textContent = originalBtnText;
          if (btnSpinner) btnSpinner.style.display = 'none';
        });
    });
  }

  function showMessage(box, text, type) {
    if (!box) return;
    box.textContent = text;
    box.className = 'cms-newsletter-message is-' + type;
    box.style.display = 'block';
  }

  function hideMessage(box) {
    if (!box) return;
    box.style.display = 'none';
    box.textContent = '';
    box.className = 'cms-newsletter-message';
  }
})();
