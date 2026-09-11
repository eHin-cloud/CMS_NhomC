-- ==============================================================================
-- CƠ SỞ DỮ LIỆU ĐỒ ÁN CMS - NHÓM C (GROUP C)
-- CHUYÊN ĐỀ: THỂ THAO, KHOA HỌC, TIN TỨC, LỊCH HỌC
-- Tương thích 100% với 15 module của đề tài (Header, Footer, Search Result FIT-TDC...)
-- Bảng mã: utf8mb4_unicode_ci (Hỗ trợ chuẩn xác tiếng Việt có dấu)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `cms_nhomc` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cms_nhomc`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `schedules`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `post_tag`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `post_category`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `site_settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- 1. BẢNG USERS (Tài khoản Người dùng, Tác giả & Giảng viên)
-- Phục vụ: Module (1) Header Account login/logout, Module (8) Comment đã login
-- ==============================================================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `fullname` VARCHAR(100) NOT NULL,
    `avatar` VARCHAR(255) DEFAULT 'assets/images/default-avatar.png',
    `role` ENUM('admin', 'editor', 'lecturer', 'subscriber') DEFAULT 'subscriber',
    `bio` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 2. BẢNG CATEGORIES (4 Danh mục chủ đạo: Thể thao, Khoa học, Tin tức, Lịch học)
-- Phục vụ: Module (1) Menu Header, Module (3) Footer, Module (9) Categories Widget
-- ==============================================================================
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `parent_id` INT DEFAULT 0,
    `display_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 3. BẢNG POSTS (Bài viết: Thể thao, Khoa học, Tin tức, Lịch học)
-- Phục vụ: Module (4) Search, (5) Search Result (FIT-TDC), (6) Detail, (7) Prev/Next, (10) Recent, (11) Archive, (15) Last Posts
-- ==============================================================================
CREATE TABLE `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `summary` TEXT DEFAULT NULL,                       -- Tóm tắt hiển thị danh sách & trang tìm kiếm
    `content` LONGTEXT NOT NULL,                       -- Nội dung chi tiết bài viết (Module 6 Detail)
    `thumbnail` VARCHAR(255) DEFAULT NULL,             -- Ảnh đại diện bài viết
    `views` INT DEFAULT 0,                             -- Lượt xem bài viết
    `is_featured` TINYINT(1) DEFAULT 0,                -- Đánh dấu bài viết tiêu điểm (cho Banner / Top)
    `status` ENUM('publish', 'draft', 'trash') DEFAULT 'publish',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,   -- Ngày đăng (dùng cho Date Badge & Archive)
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Tự động trích xuất Ngày (d), Tháng (m), Năm (Y) cho Date Badge chuẩn mẫu FIT-TDC (Module 5)
    `post_day` TINYINT GENERATED ALWAYS AS (DAY(`created_at`)) VIRTUAL,
    `post_month` TINYINT GENERATED ALWAYS AS (MONTH(`created_at`)) VIRTUAL,
    `post_year` SMALLINT GENERATED ALWAYS AS (YEAR(`created_at`)) VIRTUAL,

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    INDEX `idx_post_created` (`created_at`),
    INDEX `idx_post_views` (`views`),
    INDEX `idx_post_status` (`status`),
    INDEX `idx_post_date` (`post_year`, `post_month`, `post_day`),
    FULLTEXT KEY `ft_post_search` (`title`, `summary`, `content`) -- Tối ưu tìm kiếm văn bản Module (4, 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 4. BẢNG LIÊN KẾT POST - CATEGORY (Quan hệ nhiều - nhiều giữa bài viết và danh mục)
-- ==============================================================================
CREATE TABLE `post_category` (
    `post_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    PRIMARY KEY (`post_id`, `category_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 5. BẢNG SCHEDULES (Chuyên đề LỊCH HỌC & THỜI KHÓA BIỂU SINH VIÊN)
-- Phục vụ: Tra cứu thời khóa biểu, lịch học thực hành, lịch thi theo lớp / phòng / thứ
-- ==============================================================================
CREATE TABLE `schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `class_code` VARCHAR(50) NOT NULL,                 -- Mã lớp (VD: CD-CNTT23A, CD-TKDH24B)
    `subject_name` VARCHAR(150) NOT NULL,              -- Tên học phần / Môn học
    `lecturer_name` VARCHAR(100) NOT NULL,             -- Giảng viên phụ trách
    `room` VARCHAR(50) NOT NULL,                       -- Phòng học / Lab máy (VD: Lab 302, C204)
    `day_of_week` ENUM('Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy', 'Chủ Nhật') NOT NULL,
    `session` ENUM('Sáng', 'Chiều', 'Tối') DEFAULT 'Sáng',
    `time_range` VARCHAR(50) NOT NULL,                 -- Khung giờ học (VD: 07:30 - 11:30)
    `semester` VARCHAR(50) NOT NULL,                   -- Học kỳ (VD: Học kỳ 1 (2026-2027))
    `schedule_type` ENUM('lich_hoc', 'lich_thi', 'lich_bu') DEFAULT 'lich_hoc',
    `notes` VARCHAR(255) DEFAULT NULL,                 -- Ghi chú (VD: Mang laptop, Thi trực tiếp...)
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_schedule_class` (`class_code`),
    INDEX `idx_schedule_day` (`day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 6. BẢNG TAGS & POST_TAG (Từ khóa bài viết: bóng đá, AI, tuyển sinh, thời khóa biểu...)
-- Phục vụ: Module (6) Detail
-- ==============================================================================
CREATE TABLE `tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `post_tag` (
    `post_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    PRIMARY KEY (`post_id`, `tag_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 7. BẢNG PAGES (Trang tĩnh độc lập: Giới thiệu, Quy chế đào tạo, Liên hệ...)
-- Phục vụ: Module (13) Pages
-- ==============================================================================
CREATE TABLE `pages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `content` LONGTEXT NOT NULL,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `display_order` INT DEFAULT 0,
    `status` ENUM('publish', 'draft') DEFAULT 'publish',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 8. BẢNG COMMENTS (Bình luận lồng nhau phân cấp parent_id)
-- Phục vụ: Module (8) Comment đã login, Module (12) & (14) Comment widget
-- ==============================================================================
CREATE TABLE `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT DEFAULT NULL,                      -- Khách vãng lai = NULL, thành viên = ID user
    `author_name` VARCHAR(100) DEFAULT NULL,
    `author_email` VARCHAR(100) DEFAULT NULL,
    `author_avatar` VARCHAR(255) DEFAULT 'assets/images/default-avatar.png',
    `content` TEXT NOT NULL,
    `parent_id` INT DEFAULT 0,                       -- 0: Gốc, >0: Trả lời comment khác
    `status` ENUM('approved', 'pending', 'spam') DEFAULT 'approved',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_comment_post` (`post_id`),
    INDEX `idx_comment_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 9. BẢNG SITE_SETTINGS (Cấu hình Header, Footer, Hotline, Slogan Nhóm C)
-- ==============================================================================
CREATE TABLE `site_settings` (
    `setting_key` VARCHAR(50) PRIMARY KEY,
    `setting_value` TEXT DEFAULT NULL,
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- DỮ LIỆU MẪU BAN ĐẦU (SEED DATA ĐÚNG 4 CHỦ ĐỀ YÊU CẦU)
-- ==============================================================================

-- 1. USERS MẪU
INSERT INTO `users` (`id`, `username`, `email`, `password`, `fullname`, `role`, `bio`) VALUES
(1, 'admin', 'admin@nhomc.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Quản trị viên Group C', 'admin', 'Quản trị hệ thống CMS Nhóm C'),
(2, 'dangnguyen', 'dangnguyen@nhomc.com', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Đặng Đăng Nguyên', 'editor', 'Biên tập viên chuyên mục Thể thao & Khoa học'),
(3, 'giangvien_cntt', 'gv.cntt@tdc.edu.vn', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'ThS. Nguyễn Văn Toàn', 'lecturer', 'Phụ trách học phần Lập trình CMS & Công nghệ Web'),
(4, 'sinhvien_tdc', 'sinhvien@tdc.edu.vn', '$2y$10$abcdefghijklmnopqrstuvwxyz123456', 'Trần Bảo Anh', 'subscriber', 'Sinh viên Lớp CD-CNTT23A');

-- 2. CATEGORIES MẪU (4 Chuyên đề trọng tâm)
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `display_order`) VALUES
(1, 'Thể thao', 'the-thao', 'Tin tức bóng đá, quần vợt, pickleball và các hoạt động thể thao sinh viên', 1),
(2, 'Khoa học', 'khoa-hoc', 'Khám phá công nghệ số, trí tuệ nhân tạo (AI), điện toán đám mây và kỹ thuật', 2),
(3, 'Tin tức', 'tin-tuc', 'Thông tin hoạt động của trường, sự kiện khoa học và tin tức nổi bật', 3),
(4, 'Lịch học', 'lich-hoc', 'Thông báo kế hoạch đào tạo, thời khóa biểu và lịch thi các học kỳ', 4);

-- 3. POSTS MẪU (Đầy đủ 12 bài viết chi tiết phủ khắp 4 chuyên đề)
INSERT INTO `posts` (`id`, `user_id`, `title`, `slug`, `summary`, `content`, `thumbnail`, `views`, `is_featured`, `created_at`) VALUES

-- --- CHỦ ĐỀ 1: THỂ THAO ---
(1, 2, 
 'Bóng đá hiện đại: Xu hướng chiến thuật Gegenpressing và những bàn thắng đẹp', 
 'bong-da-hien-dai-gegenpressing', 
 'Phân tích chi tiết về triết lý pressing tầm cao Gegenpressing trong bóng đá đỉnh cao và tầm ảnh hưởng đến các giải đấu hàng đầu châu Âu.',
 '<p>Bóng đá hiện đại chứng kiến sự lên ngôi của lối chơi kiểm soát không gian và đoạt lại bóng ngay khi vừa đánh mất. Gegenpressing không chỉ đòi hỏi nền tảng thể lực vượt trội mà còn yêu cầu tư duy chiến thuật đồng bộ của cả tập thể...</p><p>Các câu lạc bộ hàng đầu hiện nay đã hoàn thiện triết lý này, tạo ra những pha chuyển trạng thái chớp nhoáng mang lại những bàn thắng mãn nhãn cho người hâm mộ.</p>',
 'assets/images/thethao-bongda.jpg', 185, 1, '2026-09-11 08:30:00'),

(2, 2, 
 'Khám phá Pickleball: Luật chơi và lý do tạo nên cơn sốt thể thao toàn cầu', 
 'kham-pha-mon-the-thao-pickleball', 
 'Pickleball đang nhanh chóng trở thành bộ môn thể thao phong trào phát triển nhanh nhất với lối chơi dễ tiếp cận và tính đối kháng hấp dẫn.',
 '<p>Pickleball kết hợp tinh hoa giữa bóng bàn, cầu lông và quần vợt. Sân thi đấu nhỏ gọn cùng chiếc vợt composite nhẹ giúp người chơi mọi độ tuổi dễ dàng làm quen ngay trong buổi tập đầu tiên...</p><p>Tại Việt Nam, phong trào tập luyện Pickleball đang bùng nổ mạnh mẽ tại các trường đại học và cao đẳng, thu hút đông đảo sinh viên tham gia rèn luyện sức khỏe.</p>',
 'assets/images/thethao-pickleball.jpg', 142, 1, '2026-09-10 14:15:00'),

(3, 2, 
 'Kỹ thuật giao bóng Tennis uy lực và bí quyết làm chủ từng đường bóng', 
 'ky-thuat-giao-bong-tennis', 
 'Hướng dẫn các bước thực hiện quả giao bóng uy lực, chuẩn xác từ tư thế đứng đến điểm tiếp xúc bóng lý tưởng.',
 '<p>Một cú giao bóng tốt trong quần vợt không chỉ mang lại điểm trực tiếp (Ace) mà còn giúp tay vợt chủ động áp đặt thế trận tấn công ngay từ đầu...</p><p>Điểm mấu chốt nằm ở độ xoáy (Topspin hoặc Slice), sự dẻo dai của cổ tay và việc phối hợp nhịp nhàng toàn bộ cơ thể trong từng pha vung vợt.</p>',
 'assets/images/thethao-tennis.jpg', 95, 0, '2026-09-08 09:00:00'),

-- --- CHỦ ĐỀ 2: KHOA HỌC & CÔNG NGHỆ ---
(4, 1, 
 'Ứng dụng Trí tuệ nhân tạo (AI) trong phân tích dữ liệu và tự động hóa', 
 'ung-dung-ai-phan-tich-du-lieu', 
 'Tổng quan về sự phát triển của các mô hình ngôn ngữ lớn (LLM) và cách doanh nghiệp ứng dụng AI để tối ưu hóa quy trình làm việc.',
 '<p>Trí tuệ nhân tạo (AI) và Học máy (Machine Learning) đang thay đổi hoàn toàn cách chúng ta tương tác với thông tin. Từ phân tích dữ liệu kinh doanh đến lập trình hỗ trợ kỹ sư phần mềm, AI mở ra kỷ nguyên mới của năng suất lao động...</p><p>Các bạn sinh viên ngành Công nghệ thông tin cần chủ động làm chủ các công cụ AI để nâng cao kỹ năng và giải quyết các bài toán thực tiễn phức tạp.</p>',
 'assets/images/khoahoc-ai.jpg', 260, 1, '2026-09-09 10:20:00'),

(5, 1, 
 'Điện toán lượng tử: Bước nhảy vọt tiếp theo của ngành khoa học máy tính', 
 'dien-toan-luong-tu-buoc-nhay-vot', 
 'Khám phá nguyên lý Qubit, trạng thái chồng chập lượng tử và tiềm năng giải quyết những bài toán mà siêu máy tính hiện nay chưa thể xử lý.',
 '<p>Khác với máy tính cổ điển xử lý bit 0 hoặc 1, máy tính lượng tử sử dụng Qubit có khả năng tồn tại đồng thời ở cả hai trạng thái. Điều này cho phép thực hiện các phép tính song song với tốc độ vượt bậc hàng triệu lần...</p><p>Công nghệ này hứa hẹn sẽ cách mạng hóa lĩnh vực mật mã học, khám phá vật liệu mới và mô phỏng sinh học phân tử.</p>',
 'assets/images/khoahoc-quantum.jpg', 110, 0, '2026-09-06 16:45:00'),

(6, 2, 
 'Năng lượng tái tạo và vật liệu bán dẫn mới thúc đẩy chuyển đổi xanh', 
 'nang-luong-tai-tao-ban-dan-moi', 
 'Nghiên cứu về các loại pin thể rắn và vật liệu bán dẫn Gallium Nitride (GaN) giúp tối ưu hóa hiệu suất truyền tải năng lượng sạch.',
 '<p>Cuộc cách mạng năng lượng xanh đang đòi hỏi các giải pháp lưu trữ và truyền dẫn hiệu quả cao hơn bao giờ hết. Các tiến bộ mới về chất bán dẫn đang trực tiếp giải quyết bài toán giảm thiểu thất thoát nhiệt và tăng tuổi thọ pin xe điện...</p>',
 'assets/images/khoahoc-energy.jpg', 78, 0, '2026-09-03 11:30:00'),

-- --- CHỦ ĐỀ 3: TIN TỨC ---
(7, 1, 
 'Tin hot: Trường CĐ Công nghệ Thủ Đức tiếp nhận hồ sơ tuyển sinh tất cả các ngày trong tuần', 
 'tin-hot-truong-cd-cong-nghe-thu-duc-tuyen-sinh', 
 'TIN VUI TUYỂN SINH: Hiện tại, Khoa CNTT vẫn đang tiếp tục nhận hồ sơ tuyển sinh cho 3 ngành mũi nhọn thuộc Khối Công nghệ và Sáng tạo.',
 '<p>Nhằm tạo điều kiện thuận lợi nhất cho phụ huynh và thí sinh hoàn thiện thủ tục nhập học, Trường CĐ Công nghệ Thủ Đức (TDC) tiếp nhận hồ sơ tất cả các ngày trong tuần, kể cả Thứ Bảy và Chủ Nhật...</p><p>Các ngành thế mạnh bao gồm: Công nghệ thông tin, Thiết kế đồ họa và Truyền thông mạng máy tính.</p>',
 'assets/images/tintuc-tuyensinh.jpg', 320, 1, '2026-07-27 10:00:00'),

(8, 1, 
 'Nghề Công nghệ thông tin trình độ cao đẳng được công nhận đạt chuẩn kiểm định chất lượng', 
 'nghe-cntt-dat-chuan-kiem-dinh-chat-luong', 
 'Khoa CNTT - Cao đẳng Công nghệ Thủ Đức chính thức đón nhận giấy chứng nhận đạt chuẩn kiểm định chất lượng chương trình đào tạo.',
 '<p>Sự kiện này đánh dấu cột mốc quan trọng, khẳng định uy tín và chất lượng đào tạo sát với nhu cầu thực tế của các doanh nghiệp phần mềm trong nước và quốc tế...</p><p>Sinh viên tốt nghiệp được trang bị đầy đủ kiến thức chuyên môn, kỹ năng ngoại ngữ và tác phong làm việc chuyên nghiệp.</p>',
 'assets/images/tintuc-kiemdinh.jpg', 215, 0, '2026-07-13 11:20:00'),

(9, 1, 
 'TDC Tháng 09/2026: Nâng tầm chất lượng đào tạo - Kết nối doanh nghiệp công nghệ', 
 'tdc-thang-092026-ket-noi-doanh-nghiep', 
 'Chuỗi hội thảo chuyên đề hợp tác giữa Nhà trường và các tập đoàn công nghệ lớn, mở rộng cơ hội thực tập và tuyển dụng cho sinh viên.',
 '<p>Trong tháng 9, Khoa CNTT đã ký kết biên bản ghi nhớ hợp tác chiến lược với hơn 10 doanh nghiệp hàng đầu trong lĩnh vực phát triển phần mềm và an toàn thông tin...</p><p>Chương trình mang đến hàng trăm vị trí thực tập hưởng lương và học bổng khuyến học giá trị cho các bạn sinh viên xuất sắc.</p>',
 'assets/images/tintuc-doanhnghiep.jpg', 165, 0, '2026-09-02 14:00:00'),

-- --- CHỦ ĐỀ 4: LỊCH HỌC & LỊCH THI ---
(10, 3, 
 'Thông báo: Kế hoạch đào tạo và Thời khóa biểu chính thức Học kỳ 1 Năm học 2026 - 2027', 
 'thong-bao-thoi-khoa-bieu-hk1-2026-2027', 
 'Phòng Đào tạo thông báo thời khóa biểu chi tiết các học phần chuyên ngành và phòng học thực hành cho sinh viên các khóa.',
 '<p>Sinh viên các khóa 2024, 2025, 2026 vui lòng kiểm tra kỹ thời khóa biểu cá nhân trên cổng thông tin đào tạo. Thời gian bắt đầu học kỳ 1 từ ngày 15/09/2026...</p><p>Các lớp học phần thực hành máy tính sẽ diễn ra tại dãy phòng Lab nhà B. Sinh viên có thắc mắc liên hệ trực tiếp văn phòng Khoa để được hỗ trợ kịp thời.</p>',
 'assets/images/lichhoc-tkb.jpg', 450, 1, '2026-09-11 07:00:00'),

(11, 3, 
 'Thông báo điều chỉnh lịch học thực hành các học phần Lập trình Web và CMS tại khu nhà B', 
 'dieu-chinh-lich-hoc-thuc-hanh-web-cms', 
 'Cập nhật phân bổ phòng máy thực hành bộ môn Công nghệ phần mềm nhằm phục vụ công tác nâng cấp đường truyền mạng.',
 '<p>Để đảm bảo tiến độ và chất lượng thực hành môn Lập trình Web CMS của Nhóm C, lịch học các lớp ngày Thứ Ba và Thứ Năm sẽ được chuyển sang phòng Lab 401 và 402...</p><p>Các lớp học lý thuyết vẫn giữ nguyên thời gian và địa điểm theo thông báo trước đó.</p>',
 'assets/images/lichhoc-phongmay.jpg', 280, 0, '2026-09-08 15:30:00'),

(12, 3, 
 'Lịch thi học phần và Quy chế phòng thi dành cho sinh viên cuối kỳ', 
 'lich-thi-hoc-phan-va-quy-che-phong-thi', 
 'Lịch thi chi tiết theo ca, số báo danh và các quy định bắt buộc khi vào phòng thi đối với các học phần lý thuyết và đồ án chuyên ngành.',
 '<p>Sinh viên dự thi phải mang theo thẻ sinh viên có dán ảnh hoặc CCCD hợp lệ. Mọi trường hợp vi phạm quy chế thi cử sẽ bị lập biên bản và hủy kết quả thi theo quy định của nhà trường...</p>',
 'assets/images/lichhoc-lichthi.jpg', 310, 0, '2026-09-05 08:00:00');

-- 4. LIÊN KẾT BÀI VIẾT - DANH MỤC
INSERT INTO `post_category` (`post_id`, `category_id`) VALUES
(1, 1), -- Bài 1 -> Thể thao
(2, 1), -- Bài 2 -> Thể thao
(3, 1), -- Bài 3 -> Thể thao
(4, 2), -- Bài 4 -> Khoa học
(5, 2), -- Bài 5 -> Khoa học
(6, 2), -- Bài 6 -> Khoa học
(7, 3), -- Bài 7 -> Tin tức
(8, 3), -- Bài 8 -> Tin tức
(9, 3), -- Bài 9 -> Tin tức
(10, 4), -- Bài 10 -> Lịch học
(11, 4), -- Bài 11 -> Lịch học
(12, 4); -- Bài 12 -> Lịch học

-- 5. THỜI KHÓA BIỂU / LỊCH HỌC CỤ THỂ (Bảng SCHEDULES)
INSERT INTO `schedules` (`class_code`, `subject_name`, `lecturer_name`, `room`, `day_of_week`, `session`, `time_range`, `semester`, `schedule_type`, `notes`) VALUES
('CD-CNTT23A', 'Phát triển ứng dụng CMS (Nhóm C)', 'ThS. Nguyễn Văn Toàn', 'Phòng Lab 302 (Nhà B)', 'Thứ Hai', 'Sáng', '07:30 - 11:30', 'Học kỳ 1 (2026-2027)', 'lich_hoc', 'Mang theo đồ án nhóm và tài liệu hướng dẫn'),
('CD-CNTT23A', 'Cơ sở dữ liệu nâng cao (MySQL)', 'TS. Lê Hoàng Mai', 'Phòng C204', 'Thứ Ba', 'Chiều', '13:00 - 17:00', 'Học kỳ 1 (2026-2027)', 'lich_hoc', 'Thực hành truy vấn SQL và tối ưu hóa Index'),
('CD-TKDH23B', 'Thiết kế giao diện UI/UX Web', 'ThS. Đặng Thùy Dương', 'Phòng Mac Lab 2', 'Thứ Tư', 'Sáng', '07:30 - 11:30', 'Học kỳ 1 (2026-2027)', 'lich_hoc', 'Thiết kế Figma cho trang tin tức và thể thao'),
('CD-CNTT23A', 'Lập trình Web Front-end với JavaScript', 'ThS. Trần Quang Vinh', 'Phòng Lab 401', 'Thứ Năm', 'Chiều', '13:00 - 17:00', 'Học kỳ 1 (2026-2027)', 'lich_hoc', 'Kiểm tra thực hành giữa kỳ 15 phút'),
('CD-CNTT23A', 'Giáo dục thể chất: Pickleball & Bóng đá', 'HLV. Phạm Minh Tuấn', 'Sân thể thao đa năng', 'Thứ Sáu', 'Sáng', '07:30 - 09:30', 'Học kỳ 1 (2026-2027)', 'lich_hoc', 'Trang phục thể thao đúng quy định trường'),
('CD-CNTT23A', 'Thi kết thúc học phần: Lập trình Web CMS', 'Hội đồng Khoa CNTT', 'Phòng Hội trường A', 'Thứ Bảy', 'Sáng', '08:00 - 10:00', 'Học kỳ 1 (2026-2027)', 'lich_thi', 'Có mặt trước 15 phút, xuất trình thẻ SV');

-- 6. TAGS MẪU
INSERT INTO `tags` (`id`, `name`, `slug`) VALUES
(1, 'Thể thao', 'the-thao'),
(2, 'Bóng đá', 'bong-da'),
(3, 'Pickleball', 'pickleball'),
(4, 'Trí tuệ nhân tạo', 'tri-tue-nhan-tao'),
(5, 'Khoa học máy tính', 'khoa-hoc-may-tinh'),
(6, 'Tuyển sinh TDC', 'tuyen-sinh-tdc'),
(7, 'Thời khóa biểu', 'thoi-khoa-bieu'),
(8, 'Lịch thi', 'lich-thi');

INSERT INTO `post_tag` (`post_id`, `tag_id`) VALUES
(1, 1), (1, 2),
(2, 1), (2, 3),
(3, 1),
(4, 4), (4, 5),
(5, 5),
(7, 6),
(10, 7),
(11, 7),
(12, 8);

-- 7. PAGES MẪU (Trang tĩnh)
INSERT INTO `pages` (`id`, `title`, `slug`, `content`, `thumbnail`, `display_order`) VALUES
(1, 'Giới thiệu Khoa Công nghệ Thông tin TDC', 'gioi-thieu', '<p>Khoa CNTT - Cao đẳng Công nghệ Thủ Đức với bề dày hơn 20 năm phát triển, đào tạo hàng ngàn kỹ sư thực hành chất lượng cao...</p>', 'assets/images/page-about.jpg', 1),
(2, 'Quy chế Đào tạo và Thời khóa biểu', 'quy-che-dao-tao', '<p>Quy định về đăng ký học phần, miễn giảm học phí và tra cứu lịch học theo hệ thống tín chỉ...</p>', 'assets/images/page-rules.jpg', 2),
(3, 'Thông tin Liên hệ', 'lien-he', '<p>Địa chỉ: 53 Võ Văn Ngân, P. Linh Chiểu, TP. Thủ Đức, TP.HCM. Email: fit@tdc.edu.vn - Hotline: 028 3896 6825</p>', 'assets/images/page-contact.jpg', 3);

-- 8. COMMENTS MẪU (Bình luận trên bài viết thể thao, khoa học, lịch học)
INSERT INTO `comments` (`id`, `post_id`, `user_id`, `author_name`, `author_email`, `content`, `parent_id`, `status`, `created_at`) VALUES
(1, 1, 4, 'Trần Bảo Anh', 'sinhvien@tdc.edu.vn', 'Chiến thuật Gegenpressing này đá pressing rát xem cuốn thật sự ạ!', 0, 'approved', '2026-09-11 09:10:00'),
(2, 1, 2, 'Đặng Đăng Nguyên', 'dangnguyen@nhomc.com', 'Đúng rồi em, phong cách này đá rất tốn thể lực nhưng hiệu quả phản công cực kỳ cao!', 1, 'approved', '2026-09-11 09:20:00'),
(3, 4, 4, 'Trần Bảo Anh', 'sinhvien@tdc.edu.vn', 'Thầy cho em hỏi khóa học AI này có mở lớp nâng cao kỳ sau không ạ?', 0, 'approved', '2026-09-09 11:00:00'),
(4, 10, NULL, 'Nguyễn Hoàng Nam', 'hoangnam.cntt@gmail.com', 'Cho em hỏi phòng Lab 302 có trang bị sẵn cổng mạng LAN không thầy?', 0, 'approved', '2026-09-11 08:00:00'),
(5, 10, 3, 'ThS. Nguyễn Văn Toàn', 'gv.cntt@tdc.edu.vn', 'Phòng Lab 302 đã trang bị full máy tính và switch mạng tốc độ cao em nhé!', 4, 'approved', '2026-09-11 08:30:00');

-- 9. CẤU HÌNH SITE_SETTINGS
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `description`) VALUES
('site_name', 'Group C', 'Tên thương hiệu Header'),
('site_title', 'CMS Nhóm C - Thể thao | Khoa học | Tin tức | Lịch học', 'Tiêu đề trang web'),
('nav_menu', 'Home, Thể thao, Khoa học, Tin tức, Lịch học', 'Danh sách menu chính Header'),
('contact_email', 'fit@tdc.edu.vn', 'Email liên hệ'),
('hotline', '028 3896 6825', 'Hotline tư vấn và giải đáp'),
('footer_copyright', '© 2026 CMS Nhóm C - Thiết kế giao diện chuẩn phong cách FIT-TDC.', 'Bản quyền chân trang');
