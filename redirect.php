<?php
// 在文件开头添加输出缓冲
ob_start();

require_once __DIR__ . '/config/config.php';

$tool_url = $_GET['url'] ?? '';
$ip = get_client_ip();

try {
    $conn->begin_transaction();
    
    // 精确查询（行级锁避免并发问题）
    $stmt = $conn->prepare("
        SELECT 
            use_count,
            UNIX_TIMESTAMP(expire_time) AS expire_ts 
        FROM ip_zhichi 
        WHERE ip_address = ? 
        FOR UPDATE
    ");
    $stmt->bind_param("s", $ip);
    if(!$stmt->execute()) throw new Exception("查询失败");
    
    $result = $stmt->get_result();
    $allow_access = false;
    
    if($result->num_rows === 1){
        $row = $result->fetch_assoc();
        $current_ts = time();
        
        // 优先检查有效期
        if($row['expire_ts'] > $current_ts){
            $allow_access = true;
        }
        // 其次检查免费次数
        elseif($row['use_count'] < 3){
            $update_stmt = $conn->prepare("
                UPDATE ip_zhichi 
                SET use_count = use_count + 1 
                WHERE ip_address = ?
            ");
            $update_stmt->bind_param("s", $ip);
            if(!$update_stmt->execute()) throw new Exception("次数更新失败");
            $allow_access = true;
        }
    }
    
    $conn->commit();
    
    if($allow_access){
        // 清除缓冲区并重定向
        ob_end_clean();
        header("Location: ".filter_var($tool_url, FILTER_SANITIZE_URL));
        exit;
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("Redirect Error: ".$e->getMessage());
}

// 显示拦截页面
$support_url = get_setting('support_url', '/zhichi.php');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>访问提示</title>
    <style>
        .expire-alert {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #fff9f9;
            border: 1px solid #ffebeb;
            border-radius: 12px;
            text-align: center;
        }
        .alert-icon {
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="expire-alert">
        <div class="alert-icon">⏳</div>
        <h3>访问权限不足</h3>
        <p>剩余免费次数已用完，请访问支持页面获取权限</p>
        <p>2秒后自动跳转...</p>
    </div>
    
    <script>
        setTimeout(() => {
            window.location.href = "<?=htmlspecialchars($support_url)?>";
        }, 2000);
    </script>
</body>
</html>
<?php
// 结束输出缓冲
ob_end_flush();
?>