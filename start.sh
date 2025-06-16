#!/bin/bash

# 设置默认环境变量
export DB_HOST="${DB_HOST:-db}"
export DB_USER="${DB_USER:-xrtools_user}"
export DB_PASSWORD="${DB_PASSWORD:-xrtools_pass}"
export DB_NAME="${DB_NAME:-xrtools_db}"
export DB_ROOT_PASSWORD="${DB_ROOT_PASSWORD:-root_password}"
export SUPPORT_URL_1="${SUPPORT_URL_1:-https://example.com/support1}"
export SUPPORT_URL_2="${SUPPORT_URL_2:-https://example.com/support2}"
export SUPPORT_URL_3="${SUPPORT_URL_3:-https://example.com/support3}"

# 创建环境变量文件
cat > .env <<EOF
DB_HOST=$DB_HOST
DB_USER=$DB_USER
DB_PASSWORD=$DB_PASSWORD
DB_NAME=$DB_NAME
DB_ROOT_PASSWORD=$DB_ROOT_PASSWORD
SUPPORT_URL_1=$SUPPORT_URL_1
SUPPORT_URL_2=$SUPPORT_URL_2
SUPPORT_URL_3=$SUPPORT_URL_3
EOF

# 启动服务
echo "正在启动Docker服务..."
docker-compose up -d --build

# 等待数据库服务就绪
echo "等待数据库启动..."
while ! docker exec xrtools-db mysqladmin ping -hlocalhost -uroot -p$DB_ROOT_PASSWORD --silent; do
    sleep 2
done
echo "数据库已启动！"

# 初始化数据库
echo "正在初始化数据库..."
docker exec -i xrtools-db mysql -uroot -p$DB_ROOT_PASSWORD $DB_NAME < ./init.sql

# 设置文件权限
echo "设置文件权限..."
docker exec xrtools-app chown -R www-data:www-data /var/www/html
docker exec xrtools-app chmod -R 755 /var/www/html

# 完成提示
echo "================================================"
echo "部署完成！"
echo "访问应用: http://localhost:8080"
echo "访问管理后台: http://localhost:8080/admin"
echo "访问phpMyAdmin: http://localhost:8081"
echo "-----------------------------------------------"
echo "管理员账号: admin"
echo "管理员密码: admin123"
echo "数据库root密码: $DB_ROOT_PASSWORD"
echo "================================================"