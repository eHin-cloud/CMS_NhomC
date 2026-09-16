CREATE DATABASE IF NOT EXISTS `cms_nhomc` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cms_nhomc`;

-- 1. Bảng Users (Tài khoản người dùng & tác giả)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `fullname` VARCHAR(100) DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
    `role` ENUM('admin', 'editor', 'subscriber') DEFAULT 'subscriber',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Bảng Categories (Danh mục)
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `parent_id` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Bảng Posts (Bài viết tin tức)
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `summary` TEXT DEFAULT NULL,                     -- Tóm tắt / trích dẫn hiển thị ở trang chủ
    `content` LONGTEXT NOT NULL,                     -- Nội dung chi tiết bài viết
    `thumbnail` VARCHAR(255) DEFAULT NULL,           -- Đường dẫn ảnh đại diện
    `views` INT DEFAULT 0,                           -- Lượt xem (cho module bài viết nổi bật/xem nhiều)
    `status` ENUM('publish', 'draft', 'trash') DEFAULT 'publish',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP, -- Dùng tách ngày (day), tháng (month)
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FULLTEXT KEY `ft_search` (`title`, `summary`, `content`) -- Tối ưu cho Module Search
) ENGINE=InnoDB;

-- 4. Bảng liên kết Post - Category (Một bài có thể thuộc nhiều danh mục)
CREATE TABLE IF NOT EXISTS `post_category` (
    `post_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    PRIMARY KEY (`post_id`, `category_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Bảng Pages (Trang tĩnh độc lập với bài viết)
CREATE TABLE IF NOT EXISTS `pages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `content` LONGTEXT NOT NULL,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('publish', 'draft') DEFAULT 'publish',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 6. Bảng Comments (Bình luận bài viết, hỗ trợ trả lời lồng nhau parent_id)
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT DEFAULT NULL,                      -- NULL nếu cho phép khách vãng lai comment
    `author_name` VARCHAR(100) DEFAULT NULL,         -- Tên khách vãng lai (nếu chưa login)
    `author_email` VARCHAR(100) DEFAULT NULL,        -- Email khách vãng lai
    `content` TEXT NOT NULL,
    `parent_id` INT DEFAULT 0,                       -- ID comment cha (dùng cho reply / phân cấp)
    `status` ENUM('approved', 'pending', 'spam') DEFAULT 'approved',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;
