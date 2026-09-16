/**
 * Smart Search & Autocomplete JavaScript
 * Theme: CMS Nhóm C
 * 
 * Bám sát layout đặc tả: Bootsnipp 35V6b + FIT-TDC (http://fit.tdc.edu.vn/tin-tuc)
 * 
 * Tính năng:
 * - Debounce 300ms.
 * - Request cancellation với AbortController (chống race condition khi gõ nhanh).
 * - Request sequence tracking (chống stale response).
 * - Cache kết quả ở Client (Map).
 * - Điều hướng bàn phím (Arrow Up, Arrow Down, Enter, Escape).
 * - Cập nhật Search Result realtime trên trang search.php không reload trang.
 * - Gợi ý sửa lỗi chính tả từ từ điển Viet74K ("Có phải bạn muốn tìm...").
 * - Accessibility ARIA đầy đủ.
 */

(function() {
    'use strict';

    // Cấu hình từ WordPress wp_localize_script
    var config = window.cmsNhomcSearch || {
        ajaxUrl: '/wp-admin/admin-ajax.php',
        restUrl: '/wp-json/cms-nhomc/v1',
        nonce: '',
        homeUrl: '/',
    };

    // Client cache lưu trữ kết quả gợi ý
    var suggestionCache = new Map();
    var searchResultsCache = new Map();

    // Biến điều khiển request
    var activeAbortController = null;
    var currentRequestId = 0;
    var debounceTimer = null;
    var activeDropdownIndex = -1;

    /**
     * Khởi tạo khi DOM sẵn sàng
     */
    document.addEventListener('DOMContentLoaded', function() {
        initHeaderSmartSearch();
        initSearchResultsPageRealtime();
    });

    /**
     * 1. Khởi tạo Smart Search trên Header
     */
    function initHeaderSmartSearch() {
        var form = document.getElementById('headerSearchForm');
        var input = document.getElementById('headerSearchInput');
        if (!form || !input) return;

        // Tạo container cho Autocomplete dropdown nếu chưa có
        var wrapper = form.querySelector('.search-input-wrapper');
        if (!wrapper) {
            wrapper = document.createElement('div');
            wrapper.className = 'search-input-wrapper';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
        }

        var dropdown = document.createElement('div');
        dropdown.className = 'smart-search-dropdown';
        dropdown.id = 'headerSearchDropdown';
        dropdown.setAttribute('role', 'listbox');
        dropdown.setAttribute('aria-label', 'Gợi ý tìm kiếm');
        wrapper.appendChild(dropdown);

        // Indicator loading spinner
        var spinner = document.createElement('span');
        spinner.className = 'search-spinner';
        spinner.innerHTML = '<svg class="spinner-svg" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg>';
        wrapper.appendChild(spinner);

        // Accessibility attributes
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-controls', dropdown.id);
        input.setAttribute('aria-expanded', 'false');

        // Sự kiện gõ phím (input)
        input.addEventListener('input', function() {
            var query = input.value.trim();
            activeDropdownIndex = -1;

            if (query.length < 2) {
                closeDropdown(dropdown, input);
                spinner.classList.remove('is-active');
                if (activeAbortController) {
                    activeAbortController.abort();
                    activeAbortController = null;
                }
                return;
            }

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                fetchAutocompleteSuggestions(query, dropdown, input, spinner);
            }, 300);
        });

        // Sự kiện bàn phím (Keyboard Navigation)
        input.addEventListener('keydown', function(e) {
            handleKeyboardNavigation(e, dropdown, input, form);
        });

        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                closeDropdown(dropdown, input);
            }
        });

        // Mở lại dropdown nếu input đã có từ khóa khi focus
        input.addEventListener('focus', function() {
            var query = input.value.trim();
            if (query.length >= 2 && dropdown.innerHTML.trim() !== '') {
                openDropdown(dropdown, input);
            }
        });
    }

    /**
     * Gửi request lấy gợi ý Autocomplete với AbortController
     */
    function fetchAutocompleteSuggestions(query, dropdown, input, spinner) {
        // Kiểm tra client cache
        var cacheKey = query.toLowerCase();
        if (suggestionCache.has(cacheKey)) {
            renderDropdownResults(suggestionCache.get(cacheKey), query, dropdown, input);
            spinner.classList.remove('is-active');
            return;
        }

        // Hủy request cũ đang bay (Request Cancellation)
        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();
        var signal = activeAbortController.signal;

        // Tăng sequence id để chống stale response ghi đè
        var requestId = ++currentRequestId;
        spinner.classList.add('is-active');

        var url = config.ajaxUrl + '?action=cms_nhomc_autocomplete&q=' + encodeURIComponent(query) + '&nonce=' + encodeURIComponent(config.nonce);

        fetch(url, { signal: signal })
            .then(function(res) {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(function(data) {
                // Nếu không phải request mới nhất, bỏ qua
                if (requestId !== currentRequestId) return;

                spinner.classList.remove('is-active');

                if (data && data.success && data.data) {
                    suggestionCache.set(cacheKey, data.data);
                    renderDropdownResults(data.data, query, dropdown, input);
                } else {
                    renderDropdownEmpty(query, dropdown, input);
                }
            })
            .catch(function(err) {
                if (err.name === 'AbortError') {
                    // Request bị hủy do người dùng gõ tiếp, đây là hành vi mong muốn
                    return;
                }
                spinner.classList.remove('is-active');
                console.error('Search Autocomplete Error:', err);
            });
    }

    /**
     * Render nội dung gợi ý vào Dropdown (bám sát cấu trúc tài liệu)
     */
    function renderDropdownResults(data, query, dropdown, input) {
        var posts = data.posts || [];
        var categories = data.categories || [];
        var suggestion = data.suggestion;

        if (posts.length === 0 && categories.length === 0 && !suggestion) {
            renderDropdownEmpty(query, dropdown, input);
            return;
        }

        var html = '';

        // 1. Banner gợi ý sửa lỗi chính tả từ từ điển Viet74K nếu phát hiện typo
        if (suggestion && suggestion.suggested) {
            html += '<div class="dropdown-typo-suggestion">';
                html += '<span class="typo-icon">💡</span> ';
                html += '<span>Có phải bạn muốn tìm: </span>';
                html += '<a href="' + escapeHtml(config.homeUrl + '?s=' + encodeURIComponent(suggestion.suggested)) + '" class="typo-suggested-link">' + escapeHtml(suggestion.suggested) + '</a>';
            html += '</div>';
        }

        // 2. Chuyên mục phù hợp (Categories)
        if (categories.length > 0) {
            html += '<div class="dropdown-section-title">Chuyên mục</div>';
            html += '<div class="dropdown-categories-list">';
            categories.forEach(function(cat) {
                html += '<a href="' + escapeHtml(cat.link) + '" class="dropdown-category-chip" role="option">';
                    html += '<span class="chip-name">' + escapeHtml(cat.name) + '</span>';
                    html += '<span class="chip-count">(' + cat.count + ')</span>';
                html += '</a>';
            });
            html += '</div>';
        }

        // 3. Danh sách bài viết gợi ý (theo phong cách FIT-TDC)
        if (posts.length > 0) {
            html += '<div class="dropdown-section-title">Bài viết liên quan (' + data.total + ')</div>';
            html += '<div class="dropdown-posts-list">';
            posts.forEach(function(p, idx) {
                html += '<a href="' + escapeHtml(p.permalink) + '" class="dropdown-post-item" role="option" data-index="' + idx + '">';
                    // Thumbnail
                    html += '<div class="dropdown-post-thumb">';
                    if (p.thumbnail_url) {
                        html += '<img src="' + escapeHtml(p.thumbnail_url) + '" alt="' + escapeHtml(p.title) + '" loading="lazy" />';
                    } else {
                        html += '<div class="thumb-placeholder-mini">TDC</div>';
                    }
                    html += '</div>';

                    // Info
                    html += '<div class="dropdown-post-info">';
                        // Title
                        html += '<h4 class="dropdown-post-title">' + escapeHtml(p.title) + '</h4>';
                        // Meta: Date & Category
                        html += '<div class="dropdown-post-meta">';
                            html += '<span class="meta-date">' + p.date.day + '/' + p.date.month + '/' + p.date.year + '</span>';
                            if (p.categories && p.categories.length > 0) {
                                html += '<span class="meta-dot">&bull;</span>';
                                html += '<span class="meta-cat">' + escapeHtml(p.categories[0].name) + '</span>';
                            }
                        html += '</div>';
                    html += '</div>';
                html += '</a>';
            });
            html += '</div>';
        }

        // 4. Nút xem tất cả kết quả
        html += '<div class="dropdown-footer">';
            html += '<a href="' + escapeHtml(config.homeUrl + '?s=' + encodeURIComponent(query)) + '" class="dropdown-view-all-btn">';
                html += 'Xem tất cả kết quả cho &ldquo;<strong>' + escapeHtml(query) + '</strong>&rdquo;';
                html += ' <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>';
            html += '</a>';
        html += '</div>';

        dropdown.innerHTML = html;
        openDropdown(dropdown, input);
    }

    /**
     * Render trạng thái rỗng trong Dropdown
     */
    function renderDropdownEmpty(query, dropdown, input) {
        var html = '<div class="dropdown-empty-state">';
            html += '<div class="empty-icon">🔍</div>';
            html += '<p class="empty-text">Không tìm thấy bài viết nào cho &ldquo;<strong>' + escapeHtml(query) + '</strong>&rdquo;</p>';
            html += '<p class="empty-sub">Hãy thử với từ khóa: <em>Tuyển sinh, Đào tạo, Tin tức, CNTT</em></p>';
        html += '</div>';

        dropdown.innerHTML = html;
        openDropdown(dropdown, input);
    }

    /**
     * Điều hướng bằng bàn phím (Arrow Up, Down, Enter, Esc)
     */
    function handleKeyboardNavigation(e, dropdown, input, form) {
        if (!dropdown.classList.contains('is-open')) return;

        var items = dropdown.querySelectorAll('.dropdown-post-item, .dropdown-category-chip, .dropdown-view-all-btn');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeDropdownIndex++;
            if (activeDropdownIndex >= items.length) {
                activeDropdownIndex = 0;
            }
            updateActiveItem(items, activeDropdownIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeDropdownIndex--;
            if (activeDropdownIndex < 0) {
                activeDropdownIndex = items.length - 1;
            }
            updateActiveItem(items, activeDropdownIndex);
        } else if (e.key === 'Enter') {
            if (activeDropdownIndex >= 0 && items[activeDropdownIndex]) {
                e.preventDefault();
                items[activeDropdownIndex].click();
            }
        } else if (e.key === 'Escape') {
            closeDropdown(dropdown, input);
        }
    }

    function updateActiveItem(items, index) {
        items.forEach(function(el, i) {
            if (i === index) {
                el.classList.add('is-focused');
                el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                el.classList.remove('is-focused');
            }
        });
    }

    function openDropdown(dropdown, input) {
        dropdown.classList.add('is-open');
        input.setAttribute('aria-expanded', 'true');
    }

    function closeDropdown(dropdown, input) {
        dropdown.classList.remove('is-open');
        input.setAttribute('aria-expanded', 'false');
        activeDropdownIndex = -1;
    }

    /**
     * 2. Khởi tạo chức năng Realtime Search trên trang search.php
     * Cập nhật danh sách kết quả trực tiếp khi gõ trong retry-box mà không reload toàn bộ trang!
     */
    function initSearchResultsPageRealtime() {
        var searchPage = document.querySelector('.search-results-page');
        if (!searchPage) return;

        var retryInput = searchPage.querySelector('.search-retry-input');
        var resultsContainer = searchPage.querySelector('.search-results-list');
        var headerBar = searchPage.querySelector('.search-header-bar');
        var paginationNav = searchPage.querySelector('.search-pagination-wrapper');

        if (!retryInput) return;

        var pageAbortController = null;
        var pageDebounceTimer = null;
        var pageReqId = 0;

        retryInput.addEventListener('input', function() {
            var q = retryInput.value.trim();
            if (q.length < 2) return;

            clearTimeout(pageDebounceTimer);
            pageDebounceTimer = setTimeout(function() {
                if (pageAbortController) {
                    pageAbortController.abort();
                }
                pageAbortController = new AbortController();
                var signal = pageAbortController.signal;
                var currentId = ++pageReqId;

                // Thêm lớp loading nhẹ nhàng trên danh sách hiện tại
                if (resultsContainer) {
                    resultsContainer.classList.add('is-loading-overlay');
                }

                var url = config.ajaxUrl + '?action=cms_nhomc_smart_search&q=' + encodeURIComponent(q) + '&nonce=' + encodeURIComponent(config.nonce);

                fetch(url, { signal: signal })
                    .then(function(res) { return res.json(); })
                    .then(function(res) {
                        if (currentId !== pageReqId) return;
                        if (resultsContainer) {
                            resultsContainer.classList.remove('is-loading-overlay');
                        }

                        if (res.success && res.data) {
                            renderRealtimeSearchResults(res.data, q, searchPage);
                            // Cập nhật URL mà không reload trang (History API)
                            if (window.history && window.history.pushState) {
                                var newUrl = window.location.pathname + '?s=' + encodeURIComponent(q);
                                window.history.pushState({ path: newUrl }, '', newUrl);
                            }
                        }
                    })
                    .catch(function(err) {
                        if (err.name === 'AbortError') return;
                        if (resultsContainer) {
                            resultsContainer.classList.remove('is-loading-overlay');
                        }
                    });
            }, 350);
        });
    }

    /**
     * Render kết quả realtime trên trang search.php theo chuẩn cấu trúc FIT-TDC
     */
    function renderRealtimeSearchResults(data, query, container) {
        var posts = data.posts || [];
        var total = data.total || 0;
        var suggestion = data.suggestion;

        var headerTitle = container.querySelector('.search-title');
        var searchCount = container.querySelector('.search-count');
        var resultsList = container.querySelector('.search-results-list');
        var typoNotice = container.querySelector('.search-typo-notice');

        // Cập nhật Header bar
        if (headerTitle) {
            headerTitle.innerHTML = 'Kết quả tìm kiếm cho: <span class="search-keyword">&ldquo;' + escapeHtml(query) + '&rdquo;</span>';
        }
        if (searchCount) {
            if (total > 0) {
                searchCount.innerHTML = 'Tìm thấy <strong>' + total + '</strong> bài viết phù hợp';
            } else {
                searchCount.innerHTML = 'Không tìm thấy bài viết nào phù hợp';
            }
        }

        // Cập nhật Typo Suggestion banner
        if (suggestion && suggestion.suggested) {
            if (!typoNotice) {
                typoNotice = document.createElement('div');
                typoNotice.className = 'search-typo-notice';
                var headerBar = container.querySelector('.search-header-bar');
                if (headerBar) {
                    headerBar.parentNode.insertBefore(typoNotice, headerBar.nextSibling);
                }
            }
            typoNotice.innerHTML = '<div class="typo-box">' +
                '<span class="typo-lamp">💡</span> Có phải bạn muốn tìm: ' +
                '<a href="' + escapeHtml(config.homeUrl + '?s=' + encodeURIComponent(suggestion.suggested)) + '" class="typo-link">' +
                '<strong>' + escapeHtml(suggestion.suggested) + '</strong></a>?' +
                '</div>';
            typoNotice.style.display = 'block';
        } else if (typoNotice) {
            typoNotice.style.display = 'none';
        }

        // Cập nhật danh sách Card bài viết theo phong cách FIT-TDC
        if (resultsList && posts.length > 0) {
            var html = '';
            posts.forEach(function(p) {
                html += '<article id="post-' + p.id + '" class="search-post-card">';
                    // Cột trái: Thumbnail
                    html += '<div class="search-post-thumb">';
                        html += '<a href="' + escapeHtml(p.permalink) + '" title="' + escapeHtml(p.title) + '">';
                        if (p.thumbnail_url) {
                            html += '<img src="' + escapeHtml(p.thumbnail_url) + '" alt="' + escapeHtml(p.title) + '" class="search-thumb-img" loading="lazy" />';
                        } else {
                            html += '<div class="search-thumb-placeholder"><span>TDC News</span></div>';
                        }
                        html += '</a>';
                    html += '</div>';

                    // Cột phải: Thông tin
                    html += '<div class="search-post-body">';
                        html += '<div class="search-post-header">';
                            // Date Badge FIT-TDC
                            html += '<div class="search-date-badge">';
                                html += '<div class="date-day">' + p.date.day + '</div>';
                                html += '<div class="date-month-year">';
                                    html += '<span class="date-month">Tháng ' + p.date.month + '</span>';
                                    html += '<span class="date-year">' + p.date.year + '</span>';
                                html += '</div>';
                            html += '</div>';

                            // Tiêu đề & Categories
                            html += '<div class="search-title-section">';
                                html += '<h2 class="search-post-title">';
                                    html += '<a href="' + escapeHtml(p.permalink) + '">' + escapeHtml(p.title) + '</a>';
                                html += '</h2>';
                                html += '<div class="search-post-categories">';
                                    html += '<span class="cat-label">Chuyên mục</span>';
                                    html += '<div class="cat-links">';
                                    if (p.categories && p.categories.length > 0) {
                                        p.categories.forEach(function(c, ci) {
                                            if (ci > 0) html += ', ';
                                            html += '<a href="' + escapeHtml(c.link) + '">' + escapeHtml(c.name) + '</a>';
                                        });
                                    } else {
                                        html += '<span>Tin tức</span>';
                                    }
                                    html += '</div>';
                                html += '</div>';
                            html += '</div>';
                        html += '</div>';

                        // Excerpt
                        html += '<div class="search-post-excerpt">';
                            html += '<p>' + escapeHtml(p.excerpt) + '</p>';
                        html += '</div>';

                        // Read more
                        html += '<div class="search-post-footer">';
                            html += '<a href="' + escapeHtml(p.permalink) + '" class="search-readmore-link">';
                                html += 'Xem chi tiết ';
                                html += '<svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>';
                            html += '</a>';
                        html += '</div>';
                    html += '</div>';
                html += '</article>';
            });
            resultsList.innerHTML = html;
        }
    }

    /**
     * Escape chuỗi HTML chống XSS
     */
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

})();
