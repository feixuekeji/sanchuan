# sanchuan
三川开关定时控制

账号密码等放在配置文件config.env里

## 使用

参数解释

name 开关名称

status 开关状态0关1开
### 命令行模式

php start.php 房间 0

第一个参数name  第二个参数status
### web模式

http://abc.com/start.php?name=&status=0


### 定时
- linux corntab 里添加定时任务
- 宝塔里添加定时任务
