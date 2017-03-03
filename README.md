# LINE-BOT-PHP-Messaging-API
LINE BOT Messaging API  use PHP-SLIM framework Example

![Alt text](https://raw.githubusercontent.com/march23sun/LINE-BOT-PHP-Messaging-API/master/doc/image/01.jpg "Optional title")

##Start
Registered
```
https://business.line.me/zh-hant/services/bot　　
```
Dev. Document
```
https://developers.line.me/messaging-api/overview　
```

##MySql Schema
```
CREATE TABLE IF NOT EXISTS `LineChReg` (
  `UserId` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `displayName` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `pictureUrl` varchar(256) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`UserId`)
)
```

##API Path Example

Webhook URL for your Channel
```
localhost:8080/LINE
```
Push message to all registered user in database
```
localhost:8080/LINE_POST
```
base URL for Imagemap  (Resize Image)
```
localhost:8080/PIC/{SIZE}
```

##TEXT Command In Line chatroom

Reply User data
```
#me
```
Switch User / Room / Group and return ID
```
#event  
```
Reply Template messages
```
#fun 
```
insert userId & data to database
```
#reg 
```
Reply Channel Bearer Access Token
```
#token 
```
Get UserData By UserID
```
#user  
```
Reply Imagemap message
```
#menu
```
###Other
Postback Event && LocationMessage

