# Yii2 JWT 示例

使用JWT格式的accessToken和refreshToken来实现身份的认证

流程：
1. 验证用户名密码
2. 生成access_token（有效期2小时）和refresh_token（有效期30天，服务端存储）
3. 客户端请求时带上access_token，refresh_token保存到本地(web 应用setcookie(),httpOnly的，App保存到本地缓存)
4. 服务端判断access_token过期时返回401
5. 客户端发送refresh_token获取新的access_token，保存access_token，重放请求失败的接口


refresh_token解决的问题：
1. access_token失效后，可以无需用户重新登录即可生成新的access_token
2. refresh_token是可控的，可以删除非法的refresh_token


表结构：
```sql
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `nickname` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user_refresh_token` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `refresh_token` char(40) NOT NULL,
  `user_ip` varchar(50) NOT NULL,
  `user_agent` varchar(1000) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

参考
* [JWT authentication tutorial](https://www.yiiframework.com/wiki/2568/jwt-authentication-tutorial)