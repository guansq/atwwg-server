部署步骤
====
## 环境要求：
 - php >=5.6
 - mysql >=5.6
 - composer >=1.4

## 部署

 0. 安装数据库 分配数据库账号 创建数据库 初始化数据库
 0. 拉取代码到虚拟机目录
 0. cd 项目根目录
 0. cp .env.example .env   填写线上配置
 0. composer install
 0. chmod -R 777 *
 0. 域名解析 配置Nginx


