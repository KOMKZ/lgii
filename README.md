# yii2shop
lshop,开源商城系统
## 在线例子
**swagger-ui**: http://47.106.36.175/swagger-ui/dist

**api地址**: http://47.106.36.175:8099

## 安装

### 安装项目

克隆项目：

```
git clone https://github.com/KOMKZ/lgii lshop
git checkout master
```

安装依赖:

```
composer global require "fxp/composer-asset-plugin:^1.2.0"
composer install
```

初始化项目

```
# 选择开发模式即可
./init
```

配置nginx:

```
server {
        listen 8099;
        client_max_body_size 0;
        index index.php index.html index.htm;
        root /path_to/lshop/web;

        location / {
                try_files $uri $uri/ =404;
        }
        location ~ ^(?!/(assets|index.php|favicon.ico|index-test.php))(.*) {
                rewrite ^(?!/index.php)(/.*) /index.php$2 last;
        }
        location ~ \.php {
                fastcgi_read_timeout 6000s;
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php5.6-fpm.sock;
        }
}
```

访问之前请确认以下配置是否正确：

```
./config/web-local.php
./config/console-local.php
```

### 安装数据库和初始数据

执行命令安装数据表：

```
./yii migrate/up
```


执行命令安装数据

```
./yii site/install
```

运行测试api用例
```
./yii site/run-test
```

### 检验安装结果

测试是否ok：

```
curl http://localhost:8099/
```

成功安装您将得到以下信息：

```
{"code":0,"data":"Welcome to LShop","message":""}
```

## 功能简介
todo
## 二次开发说明
todo
