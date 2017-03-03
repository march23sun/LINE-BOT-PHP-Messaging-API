# LINE-BOT-PHP-Messaging-API
LINE BOT Messaging API  use PHP-SLIM framework Example

![Alt text](https://raw.githubusercontent.com/march23sun/LINE-BOT-PHP-Messaging-API/master/doc/image/01.jpg "Optional title")


##MySql Schema
```
CREATE TABLE IF NOT EXISTS `LineChReg` (
  `UserId` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `displayName` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `pictureUrl` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`UserId`)
)
```
