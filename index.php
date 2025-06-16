<!--index.php-->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    require_once __DIR__ . '/config/config.php';
    
    // 获取站点配置
    $site_name = get_setting('site_name', '在线工具箱');
    $site_description = get_setting('site_description', '实用工具集合，提高工作效率');
    $site_keywords = get_setting('site_keywords', '工具箱,在线工具,实用工具');
    $support_url1 = get_setting('support_url', 'https://nanolinks.in/ufw93');
    $support_url2 = get_setting('support_url2', 'https://nanolinks.in/ufw93');
    $support_url3 = get_setting('support_url3', 'https://nanolinks.in/ufw93');

    // 访问日志记录
    $client_ip = get_client_ip();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $request_uri = $_SERVER['REQUEST_URI'];
    
    $log_stmt = $conn->prepare("
        INSERT INTO access_log 
        (ip_address, user_agent, accessed_url) 
        VALUES (?, ?, ?)
    ");
    $log_stmt->bind_param("sss", $client_ip, $user_agent, $request_uri);
    $log_stmt->execute();
    $log_stmt->close();

    // 更新访问统计
    $update_cache = $conn->prepare("
        UPDATE stats_cache 
        SET stat_value = stat_value + 1 
        WHERE stat_key = 'total_visits'
    ");
    $update_cache->execute();
    $update_cache->close();

    // 权限验证逻辑
    $has_access = false;
    $zhichi_pages = 0;
    $ip = get_client_ip();
    
    // 记录新IP
    $stmt = $conn->prepare("INSERT IGNORE INTO ip_zhichi (ip_address) VALUES (?)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $stmt->close();

    // 第一次权限查询
    $stmt_access = $conn->prepare("
        SELECT last_visited 
        FROM ip_zhichi 
        WHERE ip_address = ? 
        AND visited_zhichi = 1 
        AND last_visited > NOW() - INTERVAL 12 HOUR
    ");
    $stmt_access->bind_param("s", $ip);
    $stmt_access->execute();
    $has_access = ($stmt_access->get_result()->num_rows > 0);
    $stmt_access->close();

    // 第二次权限查询（获取详细信息）
    $stmt_access = $conn->prepare("
        SELECT last_visited, zhichi_pages 
        FROM ip_zhichi 
        WHERE ip_address = ? 
        AND visited_zhichi = 1 
        AND last_visited > NOW() - INTERVAL 12 HOUR
    ");
    $stmt_access->bind_param("s", $ip);
    $stmt_access->execute();
    $result = $stmt_access->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $zhichi_pages = $row['zhichi_pages'];
    }
    $stmt_access->close();

    // 生成支持链接
    $support_links = [
        $has_access ? '/goju/zhichi.php' : htmlspecialchars($support_url1),
        $has_access ? '/goju/zhichi2.php' : htmlspecialchars($support_url2),
        $has_access ? '/goju/zhichi3.php' : htmlspecialchars($support_url3)
    ];
    $button_text = $has_access ? '查看权益' : '立即支持';
    ?>
    <title><?= htmlspecialchars($site_name) ?> - <?= htmlspecialchars($site_description) ?></title>
    <meta name="description" content="<?= htmlspecialchars($site_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($site_keywords) ?>">

    <style>
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #7f8c8d;
            font-size: 18px;
        }
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .tool-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .tool-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .tool-icon {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .tool-icon img {
            max-width: 60px;
            max-height: 60px;
        }
        .tool-icon i {
            font-size: 48px;
            color: #3498db;
        }
        .tool-content {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .tool-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .tool-description {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .tool-link {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            text-align: center;
            transition: background-color 0.3s;
            font-weight: bold;
            margin-top: auto;
        }
        .tool-link:hover {
            background-color: #2980b9;
        }
        .categories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .category-btn {
            background-color: #f1f1f1;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
        }
        .category-btn:hover, .category-btn.active {
            background-color: #3498db;
            color: white;
        }
        .search-container {
            margin-bottom: 20px;
        }
        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 14px;
        }
        .no-tools {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-size: 16px;
            grid-column: 1 / -1;
        }
        .admin-link {
            text-align: center;
            margin-top: 10px;
        }
        .admin-link a {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 14px;
        }
        .admin-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .tools-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }
        @media (max-width: 480px) {
            .tools-grid {
                grid-template-columns: 1fr;
            }
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 14px;
        }
        .no-tools {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
            font-size: 16px;
            grid-column: 1 / -1;
        }
        .admin-link {
            text-align: center;
            margin-top: 10px;
        }
        .admin-link a {
            color: #7f8c8d;
            text-decoration: none;
            font-size: 14px;
        }
        .admin-link a:hover {
            text-decoration: underline;
        }
        
        /* 美化版权信息和友情链接 */
        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }
        .copyright {
            font-size: 14px;
            color: #7f8c8d;
            padding: 10px 0;
        }
        .friend-links {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 15px 0;
            border-top: 1px solid #eee;
        }
        .friend-links h3 {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
        }
        .links-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        .link-item {
            color: #3498db;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .link-item:hover {
            background-color: #f1f1f1;
            text-decoration: underline;
        }
        /* 新增支持卡片样式 */
        .support-card {
            margin: 0 auto 20px;
            width: 100%;
            max-width: 280px;
            cursor: pointer;
        }
        .announcement-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            animation: modalSlide 0.3s ease-out;
        }
        
        @keyframes modalSlide {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .announcement-content {
            max-height: 70vh;
            overflow-y: auto;
            padding-right: 15px;
        }
        
        .announcement-content h3 {
            color: #3498db;
            margin-top: 0;
        }
        
        .announcement-content p {
            line-height: 1.6;
            color: #666;
        }
        .support-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .support-card .tool-content {
            text-align: center;
        }
        
        .support-card .tool-title::after {
            left: 50%;
            transform: translateX(-50%);
        }
        
        .support-card .tool-link {
            margin: 0 auto;
        }
        
        /* 移动端优化 */
        @media (max-width: 768px) {
            .support-container {
                grid-template-columns: 1fr;
                max-width: 400px;
                margin: 0 auto 20px;
            }
        }
        
        /* 卡片悬停优化 */
        .support-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* 图标尺寸调整 */
        .support-card .tool-icon img {
            max-width: 70px;
            max-height: 70px;
        }
        
        /* 标题强调效果 */
        .support-card .tool-title {
            font-size: 1.1em;
            color: #2c3e50;
            position: relative;
            padding-bottom: 5px;
        }
        
        .support-card .tool-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 2px;
            background: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?= htmlspecialchars($site_name) ?></h1>
            <div class="subtitle"><?= htmlspecialchars($site_description) ?></div>
        </header>
        <div class="support-container">
            <?php if (!$has_access): ?>
                <div class="support-card tool-card">
                    <div class="tool-content">
                        <div class="tool-title">支持通道1</div>
                        <div class="tool-description">当前通道支持后6个小时内免费使用</div>
                        <a href="<?= $support_links[0] ?>" class="tool-link" id="support-btn-1" >
                            <?= htmlspecialchars($button_text) ?>
                        </a>
                    </div>
                </div>
                <div class="support-card tool-card">
                    <div class="tool-content">
                        <div class="tool-title">支持通道2</div>
                        <div class="tool-description">当前通道支持后12个小时内免费使用</div>
                        <a href="<?= $support_links[1] ?>" class="tool-link" id="support-btn-2">
                            <?= htmlspecialchars($button_text) ?>
                        </a>
                    </div>
                </div>
                <div class="support-card tool-card">
                    <div class="tool-content">
                        <div class="tool-title">支持通道3</div>
                        <div class="tool-description">当前通道支持后24个小时内免费使用</div>
                        <a href="<?= $support_links[2] ?>" class="tool-link" id="support-btn-3">
                            <?= htmlspecialchars($button_text) ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php
                $channels = [
                    1 => ['title' => '支持通道1', 'link' => $support_links[0], 'desc' => '支持通道'],
                    2 => ['title' => '支持通道2', 'link' => $support_links[1], 'desc' => '支持通道'],
                    3 => ['title' => '支持通道3', 'link' => $support_links[2], 'desc' => '支持通道']
                ];
                if (isset($channels[$zhichi_pages])) {
                    $channel = $channels[$zhichi_pages];
                    ?>
                    <div class="support-card tool-card">
                        <div class="tool-content">
                            <div class="tool-title"><?= htmlspecialchars($channel['title']) ?></div>
                            <div class="tool-description"><?= htmlspecialchars($channel['desc']) ?></div>
                            <a href="<?= htmlspecialchars($channel['link']) ?>" 
                               class="tool-link" 
                               id="support-btn-<?= $zhichi_pages ?>">
                                查看权益
                            </a>
                        </div>
                    </div>
                    <?php
                }
                ?>
            <?php endif; ?>
        </div>



        <div class="search-container">
            <input type="text" class="search-input" id="searchInput" placeholder="搜索工具...">
        </div>
        
        <div class="categories">
            <?php
            $sql = "SELECT * FROM categories WHERE status = 1 ORDER BY name";
            $categories = $conn->query($sql);
            
            if ($categories->num_rows > 0) {
                echo '<button class="category-btn active" data-category="all">全部</button>';
                while ($category = $categories->fetch_assoc()) {
                    $category_id = htmlspecialchars($category['category_id']);
                    $category_name = htmlspecialchars($category['name']);
                    echo "<button class='category-btn' data-category='{$category_id}'>{$category_name}</button>";
                }
            }
            ?>
        </div>
        
        <div class="tools-grid" id="toolsGrid">
            <?php
            $sql = "SELECT t.*, c.category_id as category_code 
                    FROM tools t 
                    LEFT JOIN categories c ON t.category_id = c.id 
                    WHERE t.status = 1 
                    ORDER BY t.title";
            $tools = $conn->query($sql);
            
            if ($tools->num_rows > 0) {
                while ($tool = $tools->fetch_assoc()) {
                    $category_code = htmlspecialchars($tool['category_code'] ?? 'other');
                    $icon = htmlspecialchars($tool['icon']);
                    $title = htmlspecialchars($tool['title']);
                    $description = htmlspecialchars($tool['description']);
                    $encoded_url = urlencode($tool['url']);
                    ?>
                    <div class="tool-card" data-category="<?= $category_code ?>">
                        <div class="tool-icon">
                            <img src="<?= $icon ?>" alt="<?= $title ?>">
                        </div>
                        <div class="tool-content">
                            <div class="tool-title"><?= $title ?></div>
                            <div class="tool-description"><?= $description ?></div>
                            <a href="redirect.php?url=<?= $encoded_url ?>" class="tool-link">立即使用</a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="no-tools">暂无可用工具</div>';
            }
            ?>
        </div>
        
        <div class="footer">
            <div class="footer-content">
                <?php
                $sql = "SELECT * FROM friend_links WHERE status = 1 ORDER BY sort_order, id";
                $links = $conn->query($sql);
                
                if ($links && $links->num_rows > 0): 
                ?>
                <div class="friend-links">
                    <h3>友情链接</h3>
                    <div class="links-container">
                        <?php while ($link = $links->fetch_assoc()): 
                            $url = htmlspecialchars($link['url']);
                            $name = htmlspecialchars($link['name']);
                            $desc = htmlspecialchars($link['description']);
                        ?>
                            <a href="<?= $url ?>" target="_blank" class="link-item" title="<?= $desc ?>">
                                <?= $name ?>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="copyright">
                    <?= htmlspecialchars(
                        get_setting('site_footer', '© ' . date('Y') . ' 在线工具箱 - 所有工具仅供学习和参考使用')
                    ) ?>
                </div>
                
                <div class="admin-link">
                    <a href="admin/login.php">管理后台</a>
                </div>
            </div>
        </div>
    </div>
    

    <!-- 公告弹窗 -->
    <div id="announcementModal" class="announcement-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="announcement-content">
                <?php echo get_setting('announcement'); ?>
            </div>
        </div>
    </div>
    
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const toolsGrid = document.getElementById('toolsGrid');
        const categoryButtons = document.querySelectorAll('.category-btn');
        const toolCards = document.querySelectorAll('.tool-card');
        const modal = document.getElementById('announcementModal');
        const announcementContent = <?php echo json_encode(get_setting('announcement')); ?>;
        // 搜索功能
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterTools();
        });
        
        // 分类筛选
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                // 移除所有按钮的active类
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                // 为当前按钮添加active类
                this.classList.add('active');
                
                filterTools();
            });
        });
        if (announcementContent && announcementContent.length > 10) { // 简单内容长度判断
            modal.style.display = 'flex';
            
            // 关闭逻辑
            document.querySelector('.close-modal').onclick = () => {
                modal.style.display = 'none';
            }
            
            // 点击外部关闭
            window.onclick = (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        }
        // 工具筛选函数
        function filterTools() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeCategory = document.querySelector('.category-btn.active').getAttribute('data-category');
            
            let visibleCount = 0;
            
            toolCards.forEach(card => {
                const title = card.querySelector('.tool-title').textContent.toLowerCase();
                const description = card.querySelector('.tool-description').textContent.toLowerCase();
                const category = card.getAttribute('data-category');
                if (card.dataset.category === 'support') return;
                // 检查搜索词和分类
                const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                const matchesCategory = activeCategory === 'all' || category === activeCategory;
                
                if (matchesSearch && matchesCategory) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // 如果没有匹配的工具，显示提示信息
            let noToolsMsg = document.querySelector('.no-tools');
            if (visibleCount === 0) {
                if (!noToolsMsg) {
                    noToolsMsg = document.createElement('div');
                    noToolsMsg.className = 'no-tools';
                    noToolsMsg.textContent = '没有找到匹配的工具';
                    toolsGrid.appendChild(noToolsMsg);
                }
                noToolsMsg.style.display = 'block';
            } else if (noToolsMsg) {
                noToolsMsg.style.display = 'none';
            }
        }
    });
    </script>
</body>
</html>