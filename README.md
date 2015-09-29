# zhihuSpider
Just for fun.

一个抓取知乎用户数据的应用。

##运行环境

> linux cli

> PHP version >= 5.6

> pcntl 扩展

> curl 扩展

> pdo 扩展 

> predis

##使用方法
创建数据库zhihu，创建数据表zh_user。建表文件在./sql/zh_user.sql。在命令行下运行get_user_info.php文件。

##查看统计数据
访问result目录下的chart.php可以看到如下类似的数据统计图。

![知乎数据统计图](http://7u2eqw.com1.z0.glb.clouddn.com/知乎数据统计图.png)
