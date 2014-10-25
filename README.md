#接口服务系统
#系统依赖于workerman(http://www.workerman.net)
###1.配置部署:
####环境要求
要求运行在Linux环境下（centos、RedHat、Ubuntu、debian、mac os等）
安装有PHP-CLI(版本不低于5.3),并安装了pcnlt、posix扩展
sysvshm、sysvmsg、libevent、proctitle扩展建议安装，但不是必须的
####部署步骤
(1)clone代码到任意目录
(2)执行命令 /path/kernel/Server/workman/bin/workermand start 启动服务
其他命令
	stop 停止服务
	restart 重启
	reload 平滑重启
	status 查看状态
启动成功后服务以守护进程运行
(3)前台输入 http://主机host:50505/ 访问测试接口页面
(4)前台输入 http://主机host:55757/ 访问接口数据统计页面
(5)部署成功

###2.调用说明
使用TCP/IP协议
数据使用json协议交互
所有对外接口必须部署在handler中
参考apptest/handler/test中接口示例

###3.测试接口
访问http://主机host:50505/
选择需要测试的服务接口,输入需要测试接口类,方法,参数,系统返回测试结果
