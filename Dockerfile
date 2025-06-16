# Dockerfile
FROM php:7.4-apache

# 安装系统依赖和PHP扩展
RUN apt-get update && apt-get install -y \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo_mysql zip

# 启用Apache重写模块
RUN a2enmod rewrite

# 设置工作目录
WORKDIR /var/www/html

# 复制项目文件
COPY . /var/www/html/

# 设置文件权限
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# 设置PHP配置
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "output_buffering = 4096" >> /usr/local/etc/php/conf.d/buffering.ini \
    && echo "implicit_flush = Off" >> /usr/local/etc/php/conf.d/buffering.ini

# 设置环境变量
ENV DB_HOST=db \
    DB_USER=xrtools_user \
    DB_PASSWORD=xrtools_pass \
    DB_NAME=xrtools_db \
    SUPPORT_URL_1=https://example.com/support1 \
    SUPPORT_URL_2=https://example.com/support2 \
    SUPPORT_URL_3=https://example.com/support3

# 暴露端口
EXPOSE 80

# 启动命令
CMD ["apache2-foreground"]