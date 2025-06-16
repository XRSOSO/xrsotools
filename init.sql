-- 创建数据库
CREATE DATABASE IF NOT EXISTS `xrtools_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `xrtools_db`;

-- 访问日志表
CREATE TABLE IF NOT EXISTS `access_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `accessed_url` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 管理员表
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 分类表
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 友情链接表
CREATE TABLE IF NOT EXISTS `friend_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT '',
  `sort_order` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- IP支持表
CREATE TABLE IF NOT EXISTS `ip_zhichi` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `visited_zhichi` tinyint(1) NOT NULL DEFAULT '0',
  `use_count` int(11) NOT NULL DEFAULT '0',
  `last_visited` datetime DEFAULT NULL,
  `expire_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `zhichi_pages` int(11) NOT NULL DEFAULT '0' COMMENT '支持页面计数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 设置表
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 统计缓存表
CREATE TABLE IF NOT EXISTS `stats_cache` (
  `stat_key` varchar(50) NOT NULL,
  `stat_value` bigint(20) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`stat_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 工具表
CREATE TABLE IF NOT EXISTS `tools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tool_id` varchar(50) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(255) DEFAULT NULL,
  `url` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tool_id` (`tool_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入默认管理员 (密码: admin123)
INSERT INTO `admins` (`username`, `password`) 
VALUES ('admin', '$2y$10$EfVc7U8jLdZ2X6m7oKZ2LeY2n8eFg5bWJd8sV5rYt3zA1vB2cD3eF');

-- 插入默认分类
INSERT INTO `categories` (`category_id`, `name`, `description`) VALUES
('all', '全部', '所有工具'),
('network', '网络工具', '网络相关的工具集合'),
('info', '信息查询', '各类信息查询工具'),
('converter', '转换工具', '各种格式转换工具'),
('security', '安全工具', '安全相关的工具集合'),
('other', '其他工具', '未分类的其他工具');

-- 插入默认工具
INSERT INTO `tools` (`tool_id`, `title`, `description`, `icon`, `url`, `category_id`) VALUES
('ipinfo', 'IP地址查询', '查询IP地址地理位置及网络信息', 'fas fa-globe-americas', 'tools/ipinfo.php', 2),
('ping', 'Ping测试', '测试网络连接状态和延迟', 'fas fa-network-wired', 'tools/ping.php', 2),
('md5', 'MD5生成器', '生成文本的MD5哈希值', 'fas fa-hashtag', 'tools/md5.php', 4),
('base64', 'Base64编码/解码', 'Base64编码解码工具', 'fas fa-lock', 'tools/base64.php', 4),
('qr', '二维码生成器', '生成自定义二维码', 'fas fa-qrcode', 'tools/qr.php', 6),
('password', '密码生成器', '生成安全的随机密码', 'fas fa-key', 'tools/password.php', 5),
('json', 'JSON格式化', 'JSON数据格式化工具', 'fas fa-code', 'tools/json.php', 4),
('timestamp', '时间戳转换', '时间戳与日期相互转换', 'fas fa-clock', 'tools/timestamp.php', 4);

-- 插入默认设置
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', '在线工具箱'),
('site_description', '实用工具集合，提高工作效率'),
('site_keywords', '工具箱,在线工具,实用工具'),
('site_footer', '© ' || YEAR(CURRENT_DATE) || ' 在线工具箱 - 所有工具仅供学习和参考使用'),
('support_url', 'https://example.com/support'),
('support_url2', 'https://example.com/support2'),
('support_url3', 'https://example.com/support3'),
('announcement', '<h3>🎉🎉 系统公告</h3><p>欢迎使用在线工具箱！我们持续更新优质工具，如有建议请联系管理员。</p>');

-- 初始化统计缓存
INSERT INTO `stats_cache` (`stat_key`, `stat_value`, `updated_at`) VALUES
('total_visits', 0, NOW());

-- 添加示例友情链接
INSERT INTO `friend_links` (`name`, `url`, `description`, `sort_order`) VALUES
('技术博客', 'https://tech-blog.example.com', '最新技术文章分享', 1),
('开发者社区', 'https://dev-community.example.com', '开发者交流平台', 2),
('开源项目', 'https://opensource.example.com', '开源项目集合', 3);