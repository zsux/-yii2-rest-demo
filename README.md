# Yii2框架 rest

## 目录结构

```
├── codes
├── commands
├── components
│   ├── base
│   ├── helpers
│   └── validators
├── config
├── controllers
├── doc
│   └── preview
├── environments
│   ├── demo
│   ├── dev
│   ├── prod
│   └── test
├── forms
│   └── base
├── models
├── runtime
│   ├── HTML
│   ├── URI
│   ├── cache
│   ├── debug
│   ├── gii-2.0.9
│   └── logs
├── views
│   └── email
└── web
    └── assets
```

> 这树形结构怎么来的: `tree -L 2 -d -I 'vendor'`

- commands 命令行
- components/base 基础
- components/helpers 帮助工具
- components/validators 验证器
- config 配置
- controllers 控制器
- doc 文档
- environments 环境配置
- models 模型
- codes 错误码
- forms 表单
- runtime 运行时
- vendor 外部类
- views 视图
- web web入口

## nginx 配置

```
server {
    charset utf-8;
    listen 80;
    server_name union-service.dev.cheyian.com;
    root        /work/php/yii2-rest-frame/web;
    index       index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ .php$ {
        include fastcgi.conf;
        fastcgi_pass   127.0.0.1:9000;
    }

    location ~ /.(ht|svn|git) {
        deny all;
    }
}
```

## 环境切换

```
Usage: init dev|test|demo|prod
```

## 访问控制

```
ip    需要提供用户端的IP
token Token验证,默认xx
```

## 文档

```
doc
├── index.md
└── preview
    ├── index.html
    ├── toc
    └── toc_conf.js
```

- index.md 所有结构的文档
- preview/index.html index.md文档的html格式,带目录

## 数据表字段注释

表字段注释, 字段说明与其他描述之间使用 `//` 隔开, 例如:

```
CREATE TABLE `user`.`<table_name>` (
	`id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增ID',
	`name` varchar(50) NOT NULL COMMENT '用户名称',
	`ctime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
	`utime` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '更新时间',
	`valid` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否删除的//1: 是 0: 否',
	PRIMARY KEY (`id`),
	UNIQUE `name` USING BTREE (`name`) comment ''
) ENGINE=`InnoDB` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ROW_FORMAT=COMPACT COMMENT='注册来源表' CHECKSUM=0 DELAY_KEY_WRITE=0;
```

生成模型

```
/**
 * @inheritdoc
 */
public function attributeLabels()
{
    return [
        'id' => '自增ID',
        'name' => '用户名称',
        'ctime' => '创建时间',
        'utime' => '更新时间',
        'valid' => '是否删除',
    ];
}
```

## 自定义组件自动提示配置

正常情况下, 自定义的组件在使用的时候是没法自动提示的。所以就有了 `config/Yii.php`。这个文件是用来配置应用的属性, 以帮助编辑器以提供自动提示的功能。

因为增加了一个同名的 `Yii` 类, 所以编辑器会提示 `Multiple Implementations`, 虽然没多大影响, 但是对于依赖右上角提示的人来说, 这也是不友善的。

如果你是 `PhpStorm`, 右击 `vendor/yiisoft/yii2/Yii.php` 选择 `Mark as Plain Text` 即可。
